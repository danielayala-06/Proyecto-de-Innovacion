<?php

namespace App\Controllers;

use App\Models\PromAlumnoModel;
use App\Models\PromPromocionModel;
use App\Models\PromFormularioModel;

class FormularioController extends BaseController
{
    protected PromAlumnoModel    $alumnoModel;
    protected PromPromocionModel $promocionModel;
    protected PromFormularioModel $formularioModel;

    public function __construct()
    {
        $this->alumnoModel     = new PromAlumnoModel();
        $this->promocionModel  = new PromPromocionModel();
        $this->formularioModel = new PromFormularioModel();
    }

    // GET /formulario/{token}
    public function index(string $token)
    {
        $alumno = $this->alumnoModel->porToken($token);

        if (!$alumno) {
            return view('formulario/error', ['motivo' => 'Token inválido o expirado.']);
        }

        if ((int) $alumno['completado'] === 1) {
            return view('formulario/completado', ['alumno' => $alumno]);
        }

        $promocion = $this->promocionModel->resumen((int) $alumno['promocion_id']);

        if (!$promocion) {
            return view('formulario/error', ['motivo' => 'La promoción no existe.']);
        }

        $stock = $this->promocionModel->stockDisponible((int) $alumno['promocion_id']);

        return view('formulario/index', [
            'alumno'        => $alumno,
            'promocion'     => $promocion,
            'stock'         => $stock,
            'productosInfo' => $this->_productosInfo($promocion),
        ]);
    }

    // POST /formulario/guardar  (JSON)
    public function guardar()
    {
        $body = $this->request->getJSON(true) ?? [];

        $token = trim($body['token'] ?? '');
        if ($token === '') {
            return $this->_json(['ok' => false, 'error' => 'Token requerido.'], 422);
        }

        $alumno = $this->alumnoModel->porToken($token);
        if (!$alumno) {
            return $this->_json(['ok' => false, 'error' => 'Token inválido.'], 404);
        }

        if ((int) $alumno['completado'] === 1) {
            return $this->_json(['ok' => false, 'error' => 'Este formulario ya fue completado.'], 409);
        }

        $tieneCuadro  = !empty($body['tiene_cuadro']);
        $tieneAnuario = !empty($body['tiene_anuario']);

        $db = \Config\Database::connect();
        $db->transStart();

        // Bloquear fila para evitar race conditions
        $prom = $db->query(
            'SELECT * FROM prom_promociones WHERE id = ? FOR UPDATE',
            [(int) $alumno['promocion_id']]
        )->getRowArray();

        if (!$prom) {
            $db->transRollback();
            return $this->_json(['ok' => false, 'error' => 'Promoción no encontrada.'], 404);
        }

        // Validar stock cuadros
        if ($tieneCuadro && ((int) $prom['cuadros_usados'] >= (int) $prom['cuadros_total'])) {
            $db->transRollback();
            return $this->_json(['ok' => false, 'error' => 'Los cuadros ya están agotados.'], 409);
        }

        // Validar stock anuarios
        if ($tieneAnuario && ((int) $prom['anuarios_usados'] >= (int) $prom['anuarios_total'])) {
            $db->transRollback();
            return $this->_json(['ok' => false, 'error' => 'Los anuarios ya están agotados.'], 409);
        }

        // Insertar formulario
        $db->table('prom_formularios')->insert([
            'alumno_id'        => (int) $alumno['id'],
            'nombre_alumno'    => $body['nombre_alumno']    ?? null,
            'fecha_nacimiento' => $body['fecha_nacimiento'] ?? null,
            'color_favorito'   => $body['color_favorito']   ?? null,
            'profesion_futura' => $body['profesion_futura'] ?? null,
            'nombre_tutor'     => $body['nombre_tutor']     ?? null,
            'relacion_tutor'   => $body['relacion_tutor']   ?? 'Padre',
            'telefono'         => $body['telefono']         ?? null,
            'email'            => $body['email']            ?? null,
            'tiene_cuadro'     => $tieneCuadro  ? 1 : 0,
            'cuadro_tamano'    => $body['cuadro_tamano']    ?? null,
            'tiene_anuario'    => $tieneAnuario ? 1 : 0,
            'anuario_modelo'   => $body['anuario_modelo']   ?? null,
            'acepta_imagenes'  => !empty($body['acepta_imagenes']) ? 1 : 0,
            'acepta_datos'     => !empty($body['acepta_datos'])    ? 1 : 0,
            'ip_address'       => $this->request->getIPAddress(),
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        // Marcar alumno como completado
        $db->table('prom_alumnos')->where('id', (int) $alumno['id'])->update(['completado' => 1]);

        // Actualizar contadores de stock
        if ($tieneCuadro) {
            $db->table('prom_promociones')
               ->where('id', (int) $prom['id'])
               ->update(['cuadros_usados' => (int) $prom['cuadros_usados'] + 1]);
        }

        if ($tieneAnuario) {
            $db->table('prom_promociones')
               ->where('id', (int) $prom['id'])
               ->update(['anuarios_usados' => (int) $prom['anuarios_usados'] + 1]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->_json(['ok' => false, 'error' => 'Error al guardar. Intenta de nuevo.'], 500);
        }

        // Sincronizar con el sistema de sesiones si la promoción está vinculada
        if (!empty($prom['id_promocion_escolar'])) {
            $this->_sincronizarEstudiante($alumno, $body, (int) $prom['id_promocion_escolar']);
        }

        return $this->_json(['ok' => true]);
    }

    // GET /formulario/grupo/{token_compartido}
    public function grupoIndex(string $token)
    {
        $promocion = $this->promocionModel
            ->where('token_compartido', $token)
            ->where('activa', 1)
            ->first();

        if (!$promocion) {
            return view('formulario/error', ['motivo' => 'El enlace no es válido o la promoción está cerrada.']);
        }

        $stock = $this->promocionModel->stockDisponible((int) $promocion['id']);

        return view('formulario/grupo', [
            'promocion'     => $promocion,
            'stock'         => $stock,
            'token'         => $token,
            'productosInfo' => $this->_productosInfo($promocion),
        ]);
    }

    // POST /formulario/grupo/guardar  (JSON)
    public function grupoGuardar()
    {
        $body  = $this->request->getJSON(true) ?? [];
        $token = trim($body['token_compartido'] ?? '');

        if ($token === '') {
            return $this->_json(['ok' => false, 'error' => 'Token requerido.'], 422);
        }

        $db   = \Config\Database::connect();
        $prom = $db->query(
            'SELECT * FROM prom_promociones WHERE token_compartido = ? AND activa = 1 FOR UPDATE',
            [$token]
        )->getRowArray();

        if (!$prom) {
            return $this->_json(['ok' => false, 'error' => 'Enlace inválido o promoción cerrada.'], 404);
        }

        $nombreAlumno = trim($body['nombre_alumno'] ?? '');
        if ($nombreAlumno === '') {
            return $this->_json(['ok' => false, 'error' => 'El nombre del alumno es obligatorio.'], 422);
        }

        $telefono = trim($body['telefono'] ?? '');
        $email    = trim($body['email'] ?? '');
        if ($telefono !== '' || $email !== '') {
            $duplicate = $db->table('prom_formularios f')
                ->select('f.id')
                ->join('prom_alumnos a', 'a.id = f.alumno_id')
                ->where('a.promocion_id', (int) $prom['id']);

            $duplicate->groupStart();
            if ($telefono !== '') {
                $duplicate->where('f.telefono', $telefono);
            }
            if ($email !== '') {
                if ($telefono !== '') {
                    $duplicate->orWhere('f.email', $email);
                } else {
                    $duplicate->where('f.email', $email);
                }
            }
            $duplicate->groupEnd();

            if ($duplicate->get()->getRowArray()) {
                return $this->_json(['ok' => false, 'error' => 'Este formulario ya fue enviado con estos datos.'], 409);
            }
        }

        $tieneCuadro  = !empty($body['tiene_cuadro']);
        $tieneAnuario = !empty($body['tiene_anuario']);

        $db->transStart();

        if ($tieneCuadro && ((int) $prom['cuadros_usados'] >= (int) $prom['cuadros_total'])) {
            $db->transRollback();
            return $this->_json(['ok' => false, 'error' => 'Los cuadros ya están agotados.'], 409);
        }
        if ($tieneAnuario && ((int) $prom['anuarios_usados'] >= (int) $prom['anuarios_total'])) {
            $db->transRollback();
            return $this->_json(['ok' => false, 'error' => 'Los anuarios ya están agotados.'], 409);
        }

        // Crear prom_alumno + marcar completado
        $alumnoToken = bin2hex(random_bytes(32));
        $db->table('prom_alumnos')->insert([
            'promocion_id' => (int) $prom['id'],
            'nombre'       => $nombreAlumno,
            'token'        => $alumnoToken,
            'completado'   => 1,
            'enviado'      => 0,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $alumnoId = $db->insertID();

        // Insertar formulario
        $db->table('prom_formularios')->insert([
            'alumno_id'        => $alumnoId,
            'nombre_alumno'    => $nombreAlumno,
            'fecha_nacimiento' => $body['fecha_nacimiento'] ?? null,
            'color_favorito'   => $body['color_favorito']   ?? null,
            'profesion_futura' => $body['profesion_futura'] ?? null,
            'nombre_tutor'     => $body['nombre_tutor']     ?? null,
            'relacion_tutor'   => $body['relacion_tutor']   ?? 'Padre',
            'telefono'         => $body['telefono']         ?? null,
            'email'            => $body['email']            ?? null,
            'tiene_cuadro'     => $tieneCuadro  ? 1 : 0,
            'cuadro_tamano'    => $body['cuadro_tamano']    ?? null,
            'tiene_anuario'    => $tieneAnuario ? 1 : 0,
            'anuario_modelo'   => $body['anuario_modelo']   ?? null,
            'acepta_imagenes'  => !empty($body['acepta_imagenes']) ? 1 : 0,
            'acepta_datos'     => !empty($body['acepta_datos'])    ? 1 : 0,
            'ip_address'       => $this->request->getIPAddress(),
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        if ($tieneCuadro) {
            $db->table('prom_promociones')->where('id', $prom['id'])
               ->update(['cuadros_usados' => (int) $prom['cuadros_usados'] + 1]);
        }
        if ($tieneAnuario) {
            $db->table('prom_promociones')->where('id', $prom['id'])
               ->update(['anuarios_usados' => (int) $prom['anuarios_usados'] + 1]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->_json(['ok' => false, 'error' => 'Error al guardar. Intenta de nuevo.'], 500);
        }

        // Sincronizar con el sistema de sesiones si está vinculada
        if (!empty($prom['id_promocion_escolar'])) {
            $alumnoArr = ['id' => $alumnoId, 'nombre' => $nombreAlumno];
            $this->_sincronizarEstudiante($alumnoArr, $body, (int) $prom['id_promocion_escolar']);
        }

        return $this->_json(['ok' => true]);
    }

    // GET /formulario/stock/{promocion_id}
    public function stock(int $promocion_id)
    {
        $stock = $this->promocionModel->stockDisponible($promocion_id);
        return $this->_json(['ok' => true, 'stock' => $stock]);
    }

    // Vista de éxito después de redirigir
    public function gracias()
    {
        return view('formulario/gracias');
    }

    /**
     * Crea los registros de persona, apoderado y estudiante en el sistema de sesiones
     * a partir de los datos del formulario completado.
     * Se ejecuta solo si la prom_promocion está vinculada a una promocion_escolar.
     *
     * No lanza excepciones: si algo falla, el formulario ya quedó guardado correctamente.
     */
    private function _sincronizarEstudiante(
        array $alumno,
        array $body,
        int   $idPromocionEscolar
    ): void {
        try {
            $db = \Config\Database::connect();

            // Evitar duplicado si ya fue sincronizado
            $yaCreado = $db->table('prom_alumnos')
                ->select('id_estudiante')
                ->where('id', (int) $alumno['id'])
                ->where('id_estudiante IS NOT NULL')
                ->get()->getRowArray();
            if ($yaCreado) return;

            // ── Nombre del alumno ───────────────────────────────────────
            $nombreCompleto = trim($body['nombre_alumno'] ?? $alumno['nombre'] ?? '');
            $partes    = preg_split('/\s+/', $nombreCompleto, 2);
            $nombres   = $partes[0] ?? $nombreCompleto;
            $apellidos = $partes[1] ?? '';

            // ── Nombre del tutor ────────────────────────────────────────
            $nombreTutor  = trim($body['nombre_tutor'] ?? '');
            $partesTutor  = preg_split('/\s+/', $nombreTutor, 2);
            $tutorNombres = $partesTutor[0] ?: 'Sin nombre';
            $tutorApells  = $partesTutor[1] ?? '';

            // ── Teléfono (exactamente 9 dígitos requerido por personas) ─
            $tel = preg_replace('/\D/', '', $body['telefono'] ?? '');
            if (strlen($tel) < 9) $tel = str_pad($tel, 9, '0');
            $tel = substr($tel, 0, 9);

            // ── Relación con el estudiante ──────────────────────────────
            $relacion = strtolower(trim($body['relacion_tutor'] ?? 'otro'));
            if (!in_array($relacion, ['padre', 'madre', 'hermano', 'otro'], true)) {
                $relacion = 'otro';
            }

            $db->transStart();

            // Crear persona (tutor/apoderado) — sin validación del modelo
            $db->table('personas')->insert([
                'nombres'          => $tutorNombres,
                'apellidos'        => $tutorApells,
                'telefono'         => $tel,
                'correo'           => $body['email'] ?? null,
                'tipo_documento'   => 'CE',
                'numero_documento' => 'FORM-' . str_pad((int) $alumno['id'], 6, '0', STR_PAD_LEFT),
            ]);
            $idPersona = $db->insertID();

            // Crear apoderado
            $db->table('apoderados')->insert([
                'id_persona'    => $idPersona,
                'tipo_relacion' => $relacion,
            ]);
            $idApoderado = $db->insertID();

            // Crear estudiante
            $db->table('estudiantes')->insert([
                'nombres'          => $nombres,
                'apellidos'        => $apellidos,
                'fecha_nacimiento' => !empty($body['fecha_nacimiento']) ? $body['fecha_nacimiento'] : null,
                'color_fav'        => $body['color_favorito']   ?? null,
                'profesion_futura' => $body['profesion_futura'] ?? null,
                'id_apoderado'     => $idApoderado,
                'id_promocion'     => $idPromocionEscolar,
            ]);
            $idEstudiante = $db->insertID();

            // Registrar el id_estudiante en prom_alumnos para evitar duplicados
            $db->table('prom_alumnos')
               ->where('id', (int) $alumno['id'])
               ->update(['id_estudiante' => $idEstudiante]);

            $db->transComplete();
        } catch (\Throwable) {
            // Fallo silencioso: el formulario ya se guardó; el admin puede re-sincronizar
        }
    }

    private function _productosInfo(array $promocion): array
    {
        if (!empty($promocion['id_promocion_escolar'])) {
            return $this->promocionModel->productosSeleccionables((int) $promocion['id_promocion_escolar']);
        }

        $cuadros  = (int) ($promocion['cuadros_total']  ?? 0) > 0;
        $anuarios = (int) ($promocion['anuarios_total'] ?? 0) > 0;
        return [
            'mostrar_seleccion' => $cuadros && $anuarios,
            'tiene_cuadros'     => $cuadros,
            'tiene_anuarios'    => $anuarios,
        ];
    }

    private function _json(array $data, int $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }

}