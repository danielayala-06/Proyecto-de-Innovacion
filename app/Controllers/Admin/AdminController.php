<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PromAlumnoModel;
use App\Models\PromPromocionModel;
use App\Models\PromFormularioModel;
use CodeIgniter\HTTP\ResponseInterface;

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

        // Enriquecer con conteo de alumnos
        $db = \Config\Database::connect();
        foreach ($promociones as &$p) {
            $p['total_alumnos'] = (int) $db->table('prom_alumnos')
                ->where('promocion_id', $p['id'])->countAllResults();
            $p['completados'] = (int) $db->table('prom_alumnos')
                ->where('promocion_id', $p['id'])->where('completado', 1)->countAllResults();
        }
        unset($p);

        return view('admin/index', ['promociones' => $promociones]);
    }

    // GET /admin/formularios/promocion/{id}
    public function promocion(int $id)
    {
        $resumen = $this->promocionModel->resumen($id);

        if (!$resumen) {
            return redirect()->to('/admin/formularios')->with('error', 'Promoción no encontrada.');
        }

        $alumnos = $this->alumnoModel->porPromocion($id);
        $stock   = $this->promocionModel->stockDisponible($id);

        return view('admin/promocion', [
            'promocion' => $resumen,
            'alumnos'   => $alumnos,
            'stock'     => $stock,
        ]);
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

    private function _json(array $data, int $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
