<?php

namespace App\Services\Sesiones;

use App\Models\SesionesFotograficasModel;
use App\Models\SesionAsistenciaModel;
use App\Models\PromocionesEscolaresModel;
use App\Models\EstudiantesModel;
use Config\Database;

class SesionService
{
    protected SesionesFotograficasModel $sesionModel;
    protected SesionAsistenciaModel     $asistenciaModel;
    protected PromocionesEscolaresModel $promocionModel;
    protected EstudiantesModel          $estudianteModel;
    protected $db;

    public function __construct()
    {
        $this->sesionModel     = new SesionesFotograficasModel();
        $this->asistenciaModel = new SesionAsistenciaModel();
        $this->promocionModel  = new PromocionesEscolaresModel();
        $this->estudianteModel = new EstudiantesModel();
        $this->db              = Database::connect();
    }

    // ────────────────────────────────────────────────────────────────────────
    // Listar sesiones — filtrable por id_promocion o id_contrato
    // ────────────────────────────────────────────────────────────────────────
    public function listar(array $filters = []): array
    {
        $builder = $this->sesionModel
            ->select('sesiones_fotograficas.*,
                      promociones_escolares.nombre AS nombre_promocion,
                      promociones_escolares.grado,
                      colegios.nombre_colegio,
                      contratos.id_contrato')
            ->join('promociones_escolares', 'promociones_escolares.id_promocion = sesiones_fotograficas.id_promocion')
            ->join('colegios',      'colegios.id_colegio = promociones_escolares.id_colegio', 'left')
            ->join('cotizaciones',  'cotizaciones.id_cotizacion = promociones_escolares.id_cotizacion', 'left')
            ->join('contratos',     'contratos.id_cotizacion = cotizaciones.id_cotizacion', 'left');

        if (!empty($filters['id_promocion'])) {
            $builder->where('sesiones_fotograficas.id_promocion', (int) $filters['id_promocion']);
        }

        if (!empty($filters['id_contrato'])) {
            $builder->where('contratos.id_contrato', (int) $filters['id_contrato']);
        }

        if (!empty($filters['estado'])) {
            $builder->where('sesiones_fotograficas.estado', $filters['estado']);
        }

        if (!empty($filters['tipo'])) {
            $builder->where('sesiones_fotograficas.tipo', $filters['tipo']);
        }

        return $builder->orderBy('sesiones_fotograficas.fecha_hora_sesion', 'DESC')->findAll();
    }

    // ────────────────────────────────────────────────────────────────────────
    // Detalle de una sesión con lista de asistencia
    // ────────────────────────────────────────────────────────────────────────
    public function obtenerPorId(int $id): ?array
    {
        $sesion = $this->sesionModel
            ->select('sesiones_fotograficas.*, promociones_escolares.nombre AS nombre_promocion')
            ->join('promociones_escolares', 'promociones_escolares.id_promocion = sesiones_fotograficas.id_promocion')
            ->where('sesiones_fotograficas.id_sesion', $id)
            ->first();

        if (!$sesion) return null;

        $sesion['asistencia'] = $this->db->table('sesion_asistencia sa')
            ->select('sa.id_asistencia, sa.id_estudiante, sa.asistio,
                      e.nombres, e.apellidos')
            ->join('estudiantes e', 'e.id_estudiante = sa.id_estudiante')
            ->where('sa.id_sesion', $id)
            ->orderBy('e.apellidos', 'ASC')
            ->get()->getResultArray();

        return $sesion;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Calcular el límite de sesiones para una promoción y tipo dado
    // Retorna ['permitidas' => N, 'usadas' => M, 'puede_crear' => bool]
    // ────────────────────────────────────────────────────────────────────────
    public function calcularLimite(int $idPromocion, string $tipo): array
    {
        $promocion = $this->promocionModel->find($idPromocion);
        if (!$promocion) {
            throw new \RuntimeException('Promoción no encontrada', 404);
        }

        // Paquetes en los detalles de la cotización asociada a la promoción
        $detalles = $this->db->table('cotizaciones_detalles')
            ->where('id_cotizacion', $promocion['id_cotizacion'])
            ->where('tipo_item', 'paquete')
            ->get()->getResultArray();

        $idsPaquetes = array_column($detalles, 'id_referencia');
        $cantidades  = array_column($detalles, 'cantidad', 'id_referencia');

        $permitidas = 0;

        if (!empty($idsPaquetes)) {
            // Máximo de sesiones del tipo solicitado entre todos los paquetes contratados
            $configs = $this->db->table('paquetes_sesiones')
                ->whereIn('id_paquete', $idsPaquetes)
                ->where('tipo_sesion', $tipo)
                ->get()->getResultArray();

            foreach ($configs as $cfg) {
                $permitidas = max($permitidas, (int) $cfg['num_sesiones']);
            }

            // Bonus por reglas de paquete (tipo_beneficio = sesion_unica)
            foreach ($detalles as $det) {
                $idPaq = $det['id_referencia'];
                $cant  = (int) ($cantidades[$idPaq] ?? 0);

                $reglas = $this->db->table('reglas_paquetes')
                    ->where('id_paquete', $idPaq)
                    ->where('tipo_beneficio', 'sesion_unica')
                    ->get()->getResultArray();

                foreach ($reglas as $regla) {
                    $cumple = match ($regla['tipo_condicion']) {
                        'CANTIDAD_MIN' => $cant >= (float) $regla['valor_condicion'],
                        'CANTIDAD_MAX' => $cant <= (float) $regla['valor_condicion'],
                        default        => false,
                    };
                    if ($cumple) $permitidas += 1;
                }
            }
        }

        $usadas = (int) $this->sesionModel
            ->where('id_promocion', $idPromocion)
            ->where('tipo', $tipo)
            ->where('estado !=', 'cancelado')
            ->countAllResults();

        return [
            'tipo'        => $tipo,
            'permitidas'  => $permitidas,
            'usadas'      => $usadas,
            'puede_crear' => $usadas < $permitidas,
        ];
    }

    // ────────────────────────────────────────────────────────────────────────
    // Crear sesión (con validación de límite)
    // ────────────────────────────────────────────────────────────────────────
    public function crear(array $data): int
    {
        $idPromocion = (int) ($data['id_promocion'] ?? 0);
        $tipo        = $data['tipo'] ?? '';

        if (!$this->promocionModel->find($idPromocion)) {
            throw new \RuntimeException('Promoción no encontrada', 404);
        }

        $limite = $this->calcularLimite($idPromocion, $tipo);
        if (!$limite['puede_crear']) {
            throw new \RuntimeException(
                "Límite de sesiones tipo «{$tipo}» alcanzado ({$limite['usadas']}/{$limite['permitidas']}).", 409
            );
        }

        $id = $this->sesionModel->insert([
            'id_promocion'      => $idPromocion,
            'fecha_hora_sesion' => $data['fecha_hora_sesion'],
            'tipo'              => $tipo,
            'observaciones'     => $data['observaciones'] ?? null,
            'estado'            => 'pendiente',
        ]);

        if ($id === false) {
            throw new \RuntimeException(json_encode($this->sesionModel->errors()), 422);
        }

        return $id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Actualizar datos de una sesión
    // ────────────────────────────────────────────────────────────────────────
    public function actualizar(int $id, array $data): void
    {
        $sesion = $this->sesionModel->find($id);
        if (!$sesion) throw new \RuntimeException('Sesión no encontrada', 404);

        if ($sesion['estado'] === 'finalizado') {
            throw new \RuntimeException('No se puede editar una sesión finalizada', 409);
        }

        $update = array_filter([
            'fecha_hora_sesion' => $data['fecha_hora_sesion'] ?? null,
            'observaciones'     => $data['observaciones']     ?? null,
        ], fn($v) => $v !== null);

        if (!empty($update) && $this->sesionModel->update($id, $update) === false) {
            throw new \RuntimeException(json_encode($this->sesionModel->errors()), 422);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Cambiar estado de una sesión
    // ────────────────────────────────────────────────────────────────────────
    public function cambiarEstado(int $id, string $estado): void
    {
        if (!$this->sesionModel->find($id)) {
            throw new \RuntimeException('Sesión no encontrada', 404);
        }
        $this->sesionModel->update($id, ['estado' => $estado]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Agregar estudiante a la lista de asistencia de una sesión
    // ────────────────────────────────────────────────────────────────────────
    public function agregarEstudiante(int $idSesion, int $idEstudiante): void
    {
        $sesion = $this->sesionModel->find($idSesion);
        if (!$sesion) throw new \RuntimeException('Sesión no encontrada', 404);

        $estudiante = $this->estudianteModel->find($idEstudiante);
        if (!$estudiante) throw new \RuntimeException('Estudiante no encontrado', 404);

        // El estudiante debe pertenecer a la misma promoción de la sesión
        if ((int) $estudiante['id_promocion'] !== (int) $sesion['id_promocion']) {
            throw new \RuntimeException('El estudiante no pertenece a la promoción de esta sesión', 409);
        }

        $existe = $this->asistenciaModel
            ->where('id_sesion', $idSesion)
            ->where('id_estudiante', $idEstudiante)
            ->first();

        if ($existe) throw new \RuntimeException('El estudiante ya está registrado en esta sesión', 409);

        $this->asistenciaModel->insert([
            'id_sesion'     => $idSesion,
            'id_estudiante' => $idEstudiante,
            'asistio'       => null,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Quitar estudiante de la asistencia de una sesión
    // ────────────────────────────────────────────────────────────────────────
    public function quitarEstudiante(int $idSesion, int $idEstudiante): void
    {
        $registro = $this->asistenciaModel
            ->where('id_sesion', $idSesion)
            ->where('id_estudiante', $idEstudiante)
            ->first();

        if (!$registro) throw new \RuntimeException('El estudiante no está en esta sesión', 404);

        $this->asistenciaModel->delete($registro['id_asistencia']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Marcar asistencia de un estudiante en una sesión (asistio: 1|0|null)
    // ────────────────────────────────────────────────────────────────────────
    public function marcarAsistencia(int $idSesion, int $idEstudiante, ?int $asistio): void
    {
        $registro = $this->asistenciaModel
            ->where('id_sesion', $idSesion)
            ->where('id_estudiante', $idEstudiante)
            ->first();

        if (!$registro) throw new \RuntimeException('El estudiante no está registrado en esta sesión', 404);

        $this->asistenciaModel->update($registro['id_asistencia'], ['asistio' => $asistio]);
    }
}
