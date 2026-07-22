<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ColegiosModel;
use App\Models\PromAlumnoModel;
use App\Models\PromPromocionModel;
use App\Models\PromFormularioModel;
use App\Services\Estudiantes\EstudianteService;

class AdminController extends BaseController
{
    protected ColegiosModel       $colegioModel;
    protected PromAlumnoModel     $alumnoModel;
    protected PromPromocionModel  $promocionModel;
    protected PromFormularioModel $formularioModel;

    public function __construct()
    {
        $this->colegioModel    = new ColegiosModel();
        $this->alumnoModel     = new PromAlumnoModel();
        $this->promocionModel  = new PromPromocionModel();
        $this->formularioModel = new PromFormularioModel();
        helper('tunnel');
    }

    // GET /admin/formularios
    public function index()
    {
        // Si ya existe al menos una promocion, redirigir directamente a la más reciente
        $primera = $this->promocionModel
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($primera) {
            return redirect()->to(base_url('admin/formularios/promocion/' . $primera['id']));
        }

        // Sin promociones: mostrar pantalla de bienvenida con opción de crear
        $db = \Config\Database::connect();

        // Promociones del sistema principal para vincular (solo contratos ACTIVO o COMPLETADO)
        $promoEscolares = $db->query(
            'SELECT pe.id_promocion, pe.id_colegio, pe.nombre, pe.grado, c.nombre_colegio,
                    con.id_contrato
             FROM promociones_escolares pe
             JOIN colegios c ON c.id_colegio = pe.id_colegio
             JOIN cotizaciones cot ON cot.id_cotizacion = pe.id_cotizacion
             JOIN contratos con ON con.id_cotizacion = cot.id_cotizacion
             WHERE con.estado IN ("ACTIVO", "COMPLETADO")
             ORDER BY pe.id_promocion DESC'
        )->getResultArray();

        $promociones = [];
        $colegios    = $this->colegioModel
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre_colegio', 'ASC')
            ->findAll();

        return view('admin/index', [
            'header'         => view('Layouts/header'),
            'footer'         => view('Layouts/footer'),
            'promociones'    => $promociones,
            'promoEscolares' => $promoEscolares,
            'colegios'       => $colegios,
        ]);
    }

    // POST /admin/formularios
    public function crear()
    {
        $body   = $this->request->getJSON(true) ?? [];
        $nombre = trim($body['nombre'] ?? '');
        $nivel  = trim($body['nivel']  ?? '');

        if ($nombre === '') {
            return $this->_json(['ok' => false, 'error' => 'El nombre es obligatorio.'], 422);
        }

        // Resolver colegio: puede venir id_colegio existente o nombre_colegio nuevo
        $idColegio = null;
        if (!empty($body['id_colegio'])) {
            $idColegio = (int) $body['id_colegio'];
        } elseif (!empty($body['nombre_colegio'])) {
            $nombreColegio = trim($body['nombre_colegio']);
            $existente     = $this->colegioModel->where('nombre_colegio', $nombreColegio)->first();
            if ($existente) {
                $idColegio = (int) $existente['id_colegio'];
            } else {
                $idColegio = $this->colegioModel->insert([
                    'nombre_colegio' => $nombreColegio,
                    'distrito'       => '',
                    'provincia'      => '',
                    'estado'         => 'ACTIVO',
                ], true);
            }
        }

        if (!$idColegio) {
            return $this->_json(['ok' => false, 'error' => 'Selecciona o escribe el nombre del colegio.'], 422);
        }

        $nuevoId = $this->promocionModel->insert([
            'colegio_id'           => $idColegio,
            'id_promocion_escolar' => !empty($body['id_promocion_escolar']) ? (int) $body['id_promocion_escolar'] : null,
            'token_compartido'     => bin2hex(random_bytes(24)),
            'nombre'               => $nombre,
            'nivel'                => $nivel,
            'cuadros_total'        => 0,
            'anuarios_total'       => 0,
            'activa'               => 1,
            'created_at'           => date('Y-m-d H:i:s'),
        ], true);

        return $this->_json([
            'ok'       => true,
            'id'       => $nuevoId,
            'redirect' => base_url('admin/formularios/promocion/' . $nuevoId),
        ]);
    }

    // PATCH /admin/formularios/promocion/{id}
    public function actualizarPromocion(int $id)
    {
        $body = $this->request->getJSON(true) ?? [];

        if (!$this->promocionModel->find($id)) {
            return $this->_json(['ok' => false, 'error' => 'Promoción no encontrada.'], 404);
        }

        $campos = [];
        if (isset($body['nombre']) && trim($body['nombre']) !== '') {
            $campos['nombre'] = trim($body['nombre']);
        }
        if (isset($body['nivel'])) {
            $campos['nivel'] = trim($body['nivel']);
        }
        if (!empty($body['id_colegio'])) {
            $campos['colegio_id'] = (int) $body['id_colegio'];
        }
        if (array_key_exists('activa', $body)) {
            $campos['activa'] = $body['activa'] ? 1 : 0;
        }

        if (!empty($campos)) {
            $this->promocionModel->update($id, $campos);
        }

        return $this->_json(['ok' => true]);
    }

    // DELETE /admin/formularios/promocion/{id}
    public function eliminarPromocion(int $id)
    {
        if (!$this->promocionModel->find($id)) {
            return $this->_json(['ok' => false, 'error' => 'Promoción no encontrada.'], 404);
        }

        // Eliminar alumnos y formularios relacionados primero
        $db = \Config\Database::connect();
        $alumnos = $db->table('prom_alumnos')->where('promocion_id', $id)->get()->getResultArray();
        foreach ($alumnos as $a) {
            $db->table('prom_formularios')->where('alumno_id', $a['id'])->delete();
        }
        $db->table('prom_alumnos')->where('promocion_id', $id)->delete();
        $this->promocionModel->delete($id);

        return $this->_json(['ok' => true]);
    }

    // GET /admin/formularios/promocion/{id}
    public function promocion(int $id)
    {
        $resumen = $this->promocionModel->resumen($id);

        if (!$resumen) {
            return redirect()->to('/admin/formularios')->with('error', 'Promoción no encontrada.');
        }

        // Generar token_compartido si la promoción aún no lo tiene
        if (empty($resumen['token_compartido'])) {
            $token = bin2hex(random_bytes(24));
            $this->promocionModel->update($id, ['token_compartido' => $token]);
            $resumen['token_compartido'] = $token;
        }

        // Sincronizar cuadros/anuarios desde el contrato si está vinculada
        if (!empty($resumen['id_promocion_escolar'])) {
            $stockContrato = $this->_stockDesdeContrato((int) $resumen['id_promocion_escolar']);
            if ($stockContrato['cuadros_total']  !== (int) $resumen['cuadros_total']
             || $stockContrato['anuarios_total'] !== (int) $resumen['anuarios_total']) {
                $this->promocionModel->update($id, $stockContrato);
                $resumen = array_merge($resumen, $stockContrato);
            }
        }

        $alumnos = $this->alumnoModel->porPromocion($id);

        // Resolver link de sesiones si está vinculada
        $sesionesLink = null;
        if (!empty($resumen['id_promocion_escolar'])) {
            $db  = \Config\Database::connect();
            $row = $db->query(
                'SELECT c.id_contrato FROM contratos c
                 JOIN cotizaciones cot ON cot.id_cotizacion = c.id_cotizacion
                 JOIN promociones_escolares pe ON pe.id_cotizacion = cot.id_cotizacion
                 WHERE pe.id_promocion = ? LIMIT 1',
                [(int) $resumen['id_promocion_escolar']]
            )->getRowArray();
            if ($row) {
                $sesionesLink = base_url('contratos/' . $row['id_contrato'] . '/sesiones');
            }
        }

        // Lista de todas las promociones para el selector de navegación
        $todasPromociones = $this->promocionModel
            ->select('prom_promociones.id, prom_promociones.nombre, prom_promociones.nivel, colegios.nombre_colegio')
            ->join('colegios', 'colegios.id_colegio = prom_promociones.colegio_id', 'left')
            ->orderBy('prom_promociones.created_at', 'DESC')
            ->findAll();

        // Promociones del sistema con contrato para el modal "Nuevo formulario"
        $db = \Config\Database::connect();
        $promoEscolares = $db->query(
            'SELECT pe.id_promocion, pe.id_colegio, pe.nombre, pe.grado, c.nombre_colegio, con.id_contrato
             FROM promociones_escolares pe
             JOIN colegios c ON c.id_colegio = pe.id_colegio
             JOIN cotizaciones cot ON cot.id_cotizacion = pe.id_cotizacion
             JOIN contratos con ON con.id_cotizacion = cot.id_cotizacion
             WHERE con.estado IN ("ACTIVO", "COMPLETADO")
             ORDER BY pe.id_promocion DESC'
        )->getResultArray();

        return view('admin/promocion', [
            'header'           => view('Layouts/header'),
            'footer'           => view('Layouts/footer'),
            'promocion'        => $resumen,
            'alumnos'          => $alumnos,
            'sesionesLink'     => $sesionesLink,
            'linkCompartido'   => public_url('formulario/grupo/' . $resumen['token_compartido']),
            'todasPromociones' => $todasPromociones,
            'promoEscolares'   => $promoEscolares,
        ]);
    }

    // GET /admin/formularios/promo-escolar/{id_promocion_escolar}
    // Encuentra (o crea) el prom_promocion vinculado a esta promocion_escolar y redirige.
    public function irDesdePromocion(int $idEscolar)
    {
        $existente = $this->promocionModel
            ->where('id_promocion_escolar', $idEscolar)
            ->first();

        if ($existente) {
            return redirect()->to(base_url('admin/formularios/promocion/' . $existente['id']));
        }

        // Crear prom_promocion vinculado automáticamente
        $db = \Config\Database::connect();
        $pe = $db->query(
            'SELECT pe.id_promocion, pe.nombre, pe.grado, pe.id_colegio
             FROM promociones_escolares pe
             WHERE pe.id_promocion = ? LIMIT 1',
            [$idEscolar]
        )->getRowArray();

        if (!$pe) {
            return redirect()->to(base_url('admin/formularios'))
                ->with('error', 'Promoción escolar no encontrada.');
        }

        $stock   = $this->_stockDesdeContrato($idEscolar);

        $nuevoId = $this->promocionModel->insert([
            'colegio_id'           => (int) $pe['id_colegio'],
            'id_promocion_escolar' => $idEscolar,
            'token_compartido'     => bin2hex(random_bytes(24)),
            'nombre'               => $pe['nombre'],
            'nivel'                => $pe['grado'] ?? '',
            'cuadros_total'        => $stock['cuadros_total'],
            'anuarios_total'       => $stock['anuarios_total'],
            'activa'               => 1,
            'created_at'           => date('Y-m-d H:i:s'),
        ], true);

        return redirect()->to(base_url('admin/formularios/promocion/' . $nuevoId));
    }

    // POST /admin/formularios/vincular/{id}
    public function vincularPromocion(int $id)
    {
        $body      = $this->request->getJSON(true) ?? [];
        $idEscolar = isset($body['id_promocion_escolar']) && $body['id_promocion_escolar'] !== ''
            ? (int) $body['id_promocion_escolar']
            : null;

        if (!$this->promocionModel->find($id)) {
            return $this->_json(['ok' => false, 'error' => 'Promoción no encontrada.'], 404);
        }

        $this->promocionModel->update($id, ['id_promocion_escolar' => $idEscolar]);

        $sesionesLink = null;
        if ($idEscolar) {
            $db  = \Config\Database::connect();
            $row = $db->query(
                'SELECT c.id_contrato FROM contratos c
                 JOIN cotizaciones cot ON cot.id_cotizacion = c.id_cotizacion
                 JOIN promociones_escolares pe ON pe.id_cotizacion = cot.id_cotizacion
                 WHERE pe.id_promocion = ? LIMIT 1',
                [$idEscolar]
            )->getRowArray();
            if ($row) {
                $sesionesLink = base_url('contratos/' . $row['id_contrato'] . '/sesiones');
            }
        }

        return $this->_json(['ok' => true, 'sesiones_link' => $sesionesLink]);
    }

    // POST /admin/formularios/alumno/agregar  (JSON)
    public function agregarAlumno()
    {
        $body         = $this->request->getJSON(true) ?? [];
        $promocion_id = (int) ($body['promocion_id'] ?? 0);
        $nombre       = trim($body['nombre'] ?? '');

        if ($promocion_id === 0 || $nombre === '') {
            return $this->_json(['ok' => false, 'error' => 'promocion_id y nombre son requeridos.'], 422);
        }

        $resultado = $this->alumnoModel->crearConToken($promocion_id, $nombre);

        $link = public_url('formulario/' . $resultado['token']);

        return $this->_json([
            'ok'    => true,
            'id'    => $resultado['id'],
            'token' => $resultado['token'],
            'link'  => $link,
        ]);
    }

    // POST /admin/formularios/alumno/importar
    public function importarAlumnos()
    {
        $body = [];

        try {
            $body = $this->request->getJSON(true) ?? [];
        } catch (\Throwable) {
            $body = $this->request->getPost();
        }

        if (empty($body)) {
            $body = $this->request->getPost();
            if (empty($body)) {
                $raw = $this->request->getRawInput();
                if (!empty($raw)) {
                    $decoded = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $body = $decoded;
                    }
                }
            }
        }

        $promocion_id = (int) ($body['promocion_id'] ?? 0);
        $nombres      = $body['nombres'] ?? [];

        if (is_string($nombres)) {
            $nombres = [$nombres];
        }

        if ($promocion_id === 0 || empty($nombres)) {
            return $this->_json(['ok' => false, 'error' => 'promocion_id y nombres son requeridos.'], 422);
        }

        $rows      = [];
        $insertados = [];
        $now       = date('Y-m-d H:i:s');

        foreach ($nombres as $nombre) {
            $nombre = trim((string) $nombre);
            if ($nombre === '') {
                continue;
            }
            $token   = bin2hex(random_bytes(32));
            $rows[]  = [
                'promocion_id' => $promocion_id,
                'nombre'       => $nombre,
                'token'        => $token,
                'completado'   => 0,
                'enviado'      => 0,
                'created_at'   => $now,
            ];
            $insertados[] = [
                'nombre' => $nombre,
                'link'   => public_url('formulario/' . $token),
            ];
        }

        if (!empty($rows)) {
            $this->alumnoModel->insertBatch($rows);
        }

        return $this->_json(['ok' => true, 'insertados' => $insertados]);
    }

    // POST /admin/formularios/alumno/agregar-completo  (JSON)
    public function agregarCompleto()
    {
        $body         = $this->request->getJSON(true) ?? [];
        $promocion_id = (int) ($body['promocion_id'] ?? 0);

        // ── Validación de entrada ───────────────────────────────────────────
        $nombres   = trim($body['nombres_alumno']   ?? '');
        $apellidos = trim($body['apellidos_alumno'] ?? '');
        $tutor     = trim($body['nombre_tutor']     ?? '');
        $tel       = trim($body['telefono']         ?? '');

        // Nombre completo para prom_alumnos y prom_formularios (compatibilidad)
        $nombre = trim(implode(' ', array_filter([$nombres, $apellidos])));

        if ($promocion_id === 0) {
            return $this->_json(['ok' => false, 'error' => 'Promoción no especificada.'], 422);
        }
        if ($nombres === '') {
            return $this->_json(['ok' => false, 'error' => 'Los nombres del alumno son obligatorios.'], 422);
        }
        // Solo letras Unicode, tildes y espacios — sin números, comas ni especiales
        $reLetras = '/^[\pL\s]+$/u';
        if (!preg_match($reLetras, $nombres)) {
            return $this->_json(['ok' => false, 'error' => 'Los nombres solo pueden contener letras y espacios.'], 422);
        }
        if ($apellidos !== '' && !preg_match($reLetras, $apellidos)) {
            return $this->_json(['ok' => false, 'error' => 'Los apellidos solo pueden contener letras y espacios.'], 422);
        }
        if ($tutor !== '' && !preg_match($reLetras, $tutor)) {
            return $this->_json(['ok' => false, 'error' => 'El nombre del apoderado solo puede contener letras y espacios.'], 422);
        }
        if ($tel !== '' && !preg_match('/^9\d{8}$/', $tel)) {
            return $this->_json(['ok' => false, 'error' => 'El teléfono debe tener 9 dígitos y comenzar con 9.'], 422);
        }

        $fecha = ($body['fecha_nacimiento'] ?? '') ?: '';
        if ($fecha !== '') {
            $dt = \DateTime::createFromFormat('Y-m-d', $fecha);
            // createFromFormat acepta desbordamiento (ej. 30-feb → 01-mar), por eso se re-formatea
            if (!$dt || $dt->format('Y-m-d') !== $fecha) {
                return $this->_json(['ok' => false, 'error' => 'La fecha de nacimiento no es válida.'], 422);
            }
            if ($dt > new \DateTime('today')) {
                return $this->_json(['ok' => false, 'error' => 'La fecha de nacimiento no puede ser en el futuro.'], 422);
            }
        }

        $prom = $this->promocionModel->find($promocion_id);
        if (!$prom) {
            return $this->_json(['ok' => false, 'error' => 'Promoción no encontrada.'], 404);
        }

        $tieneCuadro  = !empty($body['tiene_cuadro']);
        $tieneAnuario = !empty($body['tiene_anuario']);

        // ── Transacción ACID con control manual para poder hacer rollback ──
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $token = bin2hex(random_bytes(32));
            $db->table('prom_alumnos')->insert([
                'promocion_id' => $promocion_id,
                'nombre'       => $nombre,
                'token'        => $token,
                'completado'   => 1,
                'enviado'      => 0,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            $alumnoId = $db->insertID();

            $db->table('prom_formularios')->insert([
                'alumno_id'        => $alumnoId,
                'nombre_alumno'    => $nombre,
                'fecha_nacimiento' => ($body['fecha_nacimiento'] ?? '') ?: null,
                'color_favorito'   => ($body['color_favorito']   ?? '') ?: null,
                'profesion_futura' => ($body['profesion_futura'] ?? '') ?: null,
                'nombre_tutor'     => $tutor ?: null,
                'relacion_tutor'   => $body['relacion_tutor'] ?? 'Padre',
                'telefono'         => $tel ?: null,
                'email'            => ($body['email']            ?? '') ?: null,
                'tiene_cuadro'     => $tieneCuadro  ? 1 : 0,
                'tiene_anuario'    => $tieneAnuario ? 1 : 0,
                'anuario_modelo'   => ($body['anuario_modelo']   ?? '') ?: null,
                'acepta_imagenes'  => !empty($body['acepta_imagenes']) ? 1 : 0,
                'acepta_datos'     => !empty($body['acepta_datos'])    ? 1 : 0,
                'ip_address'       => $this->request->getIPAddress(),
                'created_at'       => date('Y-m-d H:i:s'),
            ]);

            // Incremento atómico: solo actualiza si aún hay stock disponible
            if ($tieneCuadro) {
                $db->query(
                    'UPDATE prom_promociones SET cuadros_usados = cuadros_usados + 1
                     WHERE id = ? AND cuadros_usados < cuadros_total',
                    [$promocion_id]
                );
                if ($db->affectedRows() === 0) {
                    $db->transRollback();
                    return $this->_json(['ok' => false, 'error' => 'No hay cuadros disponibles en este momento.'], 409);
                }
            }

            if ($tieneAnuario) {
                $db->query(
                    'UPDATE prom_promociones SET anuarios_usados = anuarios_usados + 1
                     WHERE id = ? AND anuarios_usados < anuarios_total',
                    [$promocion_id]
                );
                if ($db->affectedRows() === 0) {
                    $db->transRollback();
                    return $this->_json(['ok' => false, 'error' => 'No hay anuarios disponibles en este momento.'], 409);
                }
            }

            $db->transCommit();

            // Sincronización best-effort con el sistema de sesiones.
            // Se ejecuta fuera de la transacción principal para no bloquear
            // el registro del alumno si el estudiante ya existe o hay un error de datos.
            $idPromoEscolar = (int) ($prom['id_promocion_escolar'] ?? 0);
            if ($idPromoEscolar) {
                $this->_sincronizarEstudiante(
                    $alumnoId, $nombres, $apellidos, $body, $idPromoEscolar
                );
            }

            return $this->_json(['ok' => true]);

        } catch (\Exception) {
            $db->transRollback();
            return $this->_json(['ok' => false, 'error' => 'Error al guardar. Intenta de nuevo.'], 500);
        }
    }

    /**
     * Crea el registro de estudiante en el sistema de sesiones a partir
     * de los datos del formulario admin. Operación best-effort: si falla
     * (duplicado, datos incompletos) el formulario ya fue guardado correctamente.
     */
    private function _sincronizarEstudiante(
        int    $alumnoId,
        string $nombres,
        string $apellidos,
        array  $body,
        int    $idPromocionEscolar
    ): void {
        try {
            $db = \Config\Database::connect();

            // Evitar duplicado si ya fue sincronizado en una ejecución anterior
            $yaCreado = $db->table('prom_alumnos')
                ->select('id_estudiante')
                ->where('id', $alumnoId)
                ->where('id_estudiante IS NOT NULL', null, false)
                ->get()->getRowArray();
            if ($yaCreado) return;

            $tutor      = trim($body['nombre_tutor'] ?? '');
            $partes     = preg_split('/\s+/', $tutor, 2);
            $tutorNom   = $partes[0] ?: 'Sin nombre';
            $tutorAp    = $partes[1] ?? '';

            $relacion = strtolower(trim($body['relacion_tutor'] ?? 'otro'));
            if (!in_array($relacion, ['padre', 'madre', 'hermano', 'otro'], true)) {
                $relacion = 'otro';
            }

            $idEstudiante = (new EstudianteService())->crear([
                'id_promocion' => $idPromocionEscolar,
                'estudiante'   => [
                    'nombres'          => $nombres,
                    'apellidos'        => $apellidos ?: null,
                    'fecha_nacimiento' => ($body['fecha_nacimiento'] ?? '') ?: null,
                    'color_fav'        => ($body['color_favorito']   ?? '') ?: null,
                    'profesion_futura' => ($body['profesion_futura'] ?? '') ?: null,
                ],
                'apoderado' => [
                    'nombres'          => $tutorNom,
                    'apellidos'        => $tutorAp   ?: null,
                    'telefono'         => trim($body['telefono'] ?? ''),
                    'correo'           => ($body['email']        ?? '') ?: null,
                    'tipo_relacion'    => $relacion,
                    'tipo_documento'   => 'CE',
                    'numero_documento' => 'FORM-' . str_pad($alumnoId, 6, '0', STR_PAD_LEFT),
                ],
            ]);

            $db->table('prom_alumnos')
               ->where('id', $alumnoId)
               ->update(['id_estudiante' => $idEstudiante]);

        } catch (\Throwable) {
            // Fallo silencioso: el formulario ya quedó guardado correctamente
        }
    }

    // GET /admin/formularios/exportar/{id}
    public function exportarCsv(int $promocion_id)
    {
        $filas    = $this->formularioModel->porPromocion($promocion_id);
        $promo    = $this->promocionModel
            ->select('prom_promociones.nombre, colegios.nombre_colegio')
            ->join('colegios', 'colegios.id_colegio = prom_promociones.colegio_id', 'left')
            ->find($promocion_id);

        $slug = $this->_slugNombreExport(
            $promo['nombre']         ?? '',
            $promo['nombre_colegio'] ?? '',
            $promocion_id
        );

        $nombre_archivo = 'formularios_' . $slug . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');

        $out = fopen('php://output', 'w');

        // BOM para Excel en español
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Nombre Alumno', 'Fecha Nacimiento', 'Color Favorito', 'Profesión Futura',
            'Nombre Tutor', 'Relación', 'Teléfono', 'Email',
            'Cuadro', 'Anuario', 'Modelo Anuario',
            'Acepta Imágenes', 'Acepta Datos', 'Fecha Envío',
        ]);

        foreach ($filas as $f) {
            fputcsv($out, [
                $f['nombre_alumno'],
                $f['fecha_nacimiento'],
                $f['color_favorito'],
                $f['profesion_futura'],
                $f['nombre_tutor'],
                $f['relacion_tutor'],
                $f['telefono'],
                $f['email'],
                $f['tiene_cuadro']  ? 'Sí' : 'No',
                $f['tiene_anuario'] ? 'Sí' : 'No',
                $f['anuario_modelo'],
                $f['acepta_imagenes'] ? 'Sí' : 'No',
                $f['acepta_datos']    ? 'Sí' : 'No',
                $f['created_at'],
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Calcula cuadros_total y anuarios_total sumando las cantidades de las líneas
     * de cotización vinculadas a la promocion_escolar, agrupadas por categoría de paquete:
     *   - categoria = 'Cuadros'   → suma a cuadros_total
     *   - categoria = 'Anuarios'  → suma a anuarios_total
     *   - categoria = 'Paquetes'  → suma a AMBOS (cada pack incluye cuadro + anuario)
     */
    /**
     * Calcula cuadros_total y anuarios_total mirando los PRODUCTOS reales dentro
     * de cada paquete contratado (paquetes_productos JOIN productos):
     *   - productos.categoria = 'cuadro'  → pp.cantidad × cd.cantidad → cuadros
     *   - productos.categoria = 'anuario' → pp.cantidad × cd.cantidad → anuarios
     * Otros tipos de producto (photobook, otro) se ignoran.
     */
    private function _stockDesdeContrato(int $idPromocionEscolar): array
    {
        $db  = \Config\Database::connect();
        $row = $db->query(
            'SELECT
                COALESCE(SUM(CASE WHEN pr.categoria = "cuadro"  THEN pp.cantidad * cd.cantidad ELSE 0 END), 0) AS cuadros_total,
                COALESCE(SUM(CASE WHEN pr.categoria = "anuario" THEN pp.cantidad * cd.cantidad ELSE 0 END), 0) AS anuarios_total
             FROM cotizaciones_detalles cd
             JOIN paquetes p              ON p.id_paquete   = cd.id_referencia
             JOIN paquetes_productos pp   ON pp.id_paquete  = p.id_paquete
             JOIN productos pr            ON pr.id_producto = pp.id_producto
             JOIN promociones_escolares pe ON pe.id_cotizacion = cd.id_cotizacion
             WHERE pe.id_promocion = ?
               AND cd.tipo_item = "paquete"',
            [$idPromocionEscolar]
        )->getRowArray();

        return [
            'cuadros_total'  => (int) ($row['cuadros_total']  ?? 0),
            'anuarios_total' => (int) ($row['anuarios_total'] ?? 0),
        ];
    }

    private function _slugNombreExport(string $nombre, string $colegio, int $id): string
    {
        $base = $nombre !== '' ? $nombre : ($colegio !== '' ? $colegio : 'promocion_' . $id);

        // Transliterar tildes y ñ, quitar caracteres no alfanuméricos, lowercase
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base) ?: $base;
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = trim($slug, '_');

        return $slug !== '' ? $slug : 'promocion_' . $id;
    }

    private function _json(array $data, int $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
