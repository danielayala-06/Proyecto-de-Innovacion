<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PromAlumnoModel;
use App\Models\PromPromocionModel;
use App\Models\PromFormularioModel;

class AdminController extends BaseController
{
    protected PromAlumnoModel     $alumnoModel;
    protected PromPromocionModel  $promocionModel;
    protected PromFormularioModel $formularioModel;

    public function __construct()
    {
        $this->alumnoModel     = new PromAlumnoModel();
        $this->promocionModel  = new PromPromocionModel();
        $this->formularioModel = new PromFormularioModel();
    }

    // GET /admin/formularios
    public function index()
    {
        $promociones = $this->promocionModel->todasConColegio();

        $db = \Config\Database::connect();
        foreach ($promociones as &$p) {
            $p['total_alumnos'] = (int) $db->table('prom_alumnos')
                ->where('promocion_id', $p['id'])->countAllResults();
            $p['completados'] = (int) $db->table('prom_alumnos')
                ->where('promocion_id', $p['id'])->where('completado', 1)->countAllResults();

            // Si está vinculada, resolver el id_contrato para el link de sesiones
            $p['sesiones_link'] = null;
            if (!empty($p['id_promocion_escolar'])) {
                $row = $db->query(
                    'SELECT c.id_contrato FROM contratos c
                     JOIN cotizaciones cot ON cot.id_cotizacion = c.id_cotizacion
                     JOIN promociones_escolares pe ON pe.id_cotizacion = cot.id_cotizacion
                     WHERE pe.id_promocion = ? LIMIT 1',
                    [(int) $p['id_promocion_escolar']]
                )->getRowArray();
                if ($row) {
                    $p['sesiones_link'] = base_url('contratos/' . $row['id_contrato'] . '/sesiones');
                }
            }
        }
        unset($p);

        // Promociones del sistema principal para vincular
        $promoEscolares = $db->query(
            'SELECT pe.id_promocion, pe.nombre, pe.grado, c.nombre_colegio,
                    con.id_contrato
             FROM promociones_escolares pe
             JOIN colegios c ON c.id_colegio = pe.id_colegio
             JOIN cotizaciones cot ON cot.id_cotizacion = pe.id_cotizacion
             JOIN contratos con ON con.id_cotizacion = cot.id_cotizacion
             ORDER BY pe.id_promocion DESC'
        )->getResultArray();

        return view('admin/index', [
            'header'         => view('Layouts/header'),
            'footer'         => view('Layouts/footer'),
            'promociones'    => $promociones,
            'promoEscolares' => $promoEscolares,
        ]);
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
        $stock   = $this->promocionModel->stockDisponible($id);

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

        return view('admin/promocion', [
            'header'          => view('Layouts/header'),
            'footer'          => view('Layouts/footer'),
            'promocion'       => $resumen,
            'alumnos'         => $alumnos,
            'stock'           => $stock,
            'sesionesLink'    => $sesionesLink,
            'linkCompartido'  => base_url('formulario/grupo/' . $resumen['token_compartido']),
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

        $link = base_url('formulario/' . $resultado['token']);

        return $this->_json([
            'ok'    => true,
            'id'    => $resultado['id'],
            'token' => $resultado['token'],
            'link'  => $link,
        ]);
    }

    // POST /admin/formularios/alumno/importar  (JSON)
    public function importarAlumnos()
    {
        $body         = $this->request->getJSON(true) ?? [];
        $promocion_id = (int) ($body['promocion_id'] ?? 0);
        $nombres      = $body['nombres'] ?? [];

        if ($promocion_id === 0 || empty($nombres)) {
            return $this->_json(['ok' => false, 'error' => 'promocion_id y nombres son requeridos.'], 422);
        }

        $insertados = [];
        foreach ($nombres as $nombre) {
            $nombre = trim($nombre);
            if ($nombre === '') {
                continue;
            }
            $res        = $this->alumnoModel->crearConToken($promocion_id, $nombre);
            $insertados[] = [
                'nombre' => $nombre,
                'link'   => base_url('formulario/' . $res['token']),
            ];
        }

        return $this->_json(['ok' => true, 'insertados' => $insertados]);
    }

    // GET /admin/formularios/exportar/{id}
    public function exportarCsv(int $promocion_id)
    {
        $filas = $this->formularioModel->porPromocion($promocion_id);

        $nombre_archivo = 'formularios_' . $promocion_id . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');

        $out = fopen('php://output', 'w');

        // BOM para Excel en español
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Nombre Alumno', 'Fecha Nacimiento', 'Color Favorito', 'Profesión Futura',
            'Nombre Tutor', 'Relación', 'Teléfono', 'Email',
            'Cuadro', 'Tamaño Cuadro', 'Anuario', 'Modelo Anuario',
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
                $f['cuadro_tamano'],
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

    private function _json(array $data, int $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
