<?php

/**
 * @file    EstudiantesApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para la gestión de estudiantes dentro de una promoción escolar.
 * Delega la lógica de negocio en EstudianteService y formatea
 * las respuestas con EstudianteTransformer.
 *
 * Endpoints:
 *   GET    /api/estudiantes?id_promocion=X  → listar por promoción
 *   GET    /api/estudiantes/exportar?id_promocion=X → descargar CSV con todos los datos
 *   POST   /api/estudiantes/importar        → importar estudiantes desde CSV (multipart)
 *   POST   /api/estudiantes                 → crear (incluye apoderado + persona en transacción)
 *   PUT    /api/estudiantes/{id}            → actualizar datos del estudiante
 *   DELETE /api/estudiantes/{id}            → eliminar
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Services\Estudiantes\EstudianteService;
use App\Transformers\EstudianteTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API de Estudiantes.
 *
 * Todas las respuestas siguen el formato:
 * { status: 'success'|'error', data?: ..., message?: ..., errors?: ... }
 */
class EstudiantesApi extends BaseApiController
{
    /** @var EstudianteService Servicio con la lógica de negocio de estudiantes. */
    protected EstudianteService $estudianteService;

    /** @var EstudianteTransformer Formateador de respuestas JSON. */
    protected EstudianteTransformer $estudianteTransformer;

    public function __construct()
    {
        $this->estudianteService     = service('estudianteService');
        $this->estudianteTransformer = new EstudianteTransformer();
    }

    /**
     * GET /api/estudiantes/stock?id_promocion=X
     *
     * Devuelve el stock de productos del paquete para una promoción:
     * total contratado y disponible (no asignado aún a ningún estudiante).
     *
     * @return ResponseInterface 200 con array de productos | 422 si falta id_promocion.
     */
    public function stockPromocion()
    {
        $idPromocion = (int) ($this->request->getGet('id_promocion') ?? 0);

        if (!$idPromocion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_promocion es requerido']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->estudianteService->stockPorPromocion($idPromocion),
            ]);
    }

    /**
     * GET /api/estudiantes/{id}
     *
     * Retorna el perfil completo: datos personales, apoderado,
     * productos contratados y historial de asistencia a sesiones.
     *
     * @param  mixed $id ID del estudiante.
     * @return ResponseInterface 200 con el perfil | 404 si no existe.
     */
    public function show($id)
    {
        $estudiante = $this->estudianteService->obtenerDetalle((int) $id);

        if (!$estudiante) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Estudiante no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => [
                    'id_estudiante'       => (int)   $estudiante['id_estudiante'],
                    'nombres'             =>          $estudiante['nombres'],
                    'apellidos'           =>          $estudiante['apellidos'],
                    'fecha_nacimiento'    =>          $estudiante['fecha_nacimiento']    ?? null,
                    'color_fav'           =>          $estudiante['color_fav']           ?? null,
                    'profesion_futura'    =>          $estudiante['profesion_futura']    ?? null,
                    'apoderado_nombres'   =>          $estudiante['apoderado_nombres']   ?? null,
                    'apoderado_apellidos' =>          $estudiante['apoderado_apellidos'] ?? null,
                    'apoderado_telefono'  =>          $estudiante['apoderado_telefono']  ?? null,
                    'tipo_relacion'       =>          $estudiante['tipo_relacion']       ?? null,
                    'productos'           =>          $estudiante['productos'],
                    'sesiones'            =>          $estudiante['sesiones'],
                ],
            ]);
    }

    /**
     * GET /api/estudiantes?id_promocion=X
     *
     * @return ResponseInterface 200 con la lista de estudiantes | 422 si falta id_promocion.
     */
    public function index()
    {
        $idPromocion = (int) ($this->request->getGet('id_promocion') ?? 0);

        if (!$idPromocion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_promocion es requerido']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->estudianteTransformer->transformMany(
                    $this->estudianteService->listarPorPromocion($idPromocion)
                ),
            ]);
    }

    /**
     * POST /api/estudiantes
     *
     * Body: {
     *   id_promocion,
     *   estudiante: { nombres, apellidos, fecha_nacimiento?, color_fav?, profesion_futura? },
     *   apoderado:  { nombres, apellidos?, telefono, tipo_relacion,
     *                 tipo_documento, numero_documento, correo? }
     * }
     *
     * @return ResponseInterface 201 con id_estudiante | 404 si no existe la promoción | 422 si falla validación.
     */
    public function create()
    {
        $body  = $this->request->getJSON(true) ?? [];
        $rules = [
            'id_promocion'            => 'required|integer|is_natural_no_zero',
            'estudiante.nombres'      => 'required|max_length[100]',
            'apoderado.nombres'       => 'required|max_length[100]',
            'apoderado.telefono'      => 'required|exact_length[9]|regex_match[/^\d{9}$/]',
            'apoderado.tipo_relacion' => 'required|in_list[padre,madre,hermano,otro]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $id = $this->estudianteService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante registrado', 'id_estudiante' => $id]);
    }

    /**
     * PUT /api/estudiantes/{id}
     *
     * Body: { nombres?, apellidos?, fecha_nacimiento?, color_fav?, profesion_futura? }
     *
     * @param  mixed $id ID del estudiante.
     * @return ResponseInterface 200 | 404 | 422.
     */
    public function update($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        try {
            $this->estudianteService->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante actualizado']);
    }

    /**
     * DELETE /api/estudiantes/{id}
     *
     * La asistencia del estudiante se elimina en cascada por FK.
     *
     * @param  mixed $id ID del estudiante.
     * @return ResponseInterface 200 | 404.
     */
    public function delete($id)
    {
        try {
            $this->estudianteService->eliminar((int) $id);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante eliminado']);
    }

    /**
     * GET /api/estudiantes/exportar?id_promocion=X
     *
     * Descarga un archivo CSV con todos los estudiantes y apoderados de la promoción.
     * El BOM (EF BB BF) permite que Excel lo abra correctamente en UTF-8.
     *
     * @return ResponseInterface Descarga CSV | 422 si falta id_promocion.
     */
    public function exportarCsv(): ResponseInterface
    {
        $idPromocion = (int) ($this->request->getGet('id_promocion') ?? 0);

        if (!$idPromocion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_promocion es requerido']);
        }

        $filas    = $this->estudianteService->exportarDatos($idPromocion);
        $filename = $this->_slugExportEstudiantes($idPromocion);

        $columnas = [
            'nombres', 'apellidos', 'fecha_nacimiento', 'color_favorito', 'profesion_futura',
            'apoderado_nombres', 'apoderado_apellidos', 'apoderado_telefono', 'apoderado_correo',
            'tipo_relacion',
        ];

        ob_start();
        $handle = fopen('php://output', 'w');
        fwrite($handle, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel
        fputcsv($handle, $columnas);

        foreach ($filas as $fila) {
            fputcsv($handle, [
                $fila['nombres']             ?? '',
                $fila['apellidos']           ?? '',
                $fila['fecha_nacimiento']    ?? '',
                $fila['color_fav']           ?? '',
                $fila['profesion_futura']    ?? '',
                $fila['apoderado_nombres']   ?? '',
                $fila['apoderado_apellidos'] ?? '',
                $fila['apoderado_telefono']  ?? '',
                $fila['apoderado_correo']    ?? '',
                $fila['tipo_relacion']       ?? '',
            ]);
        }

        fclose($handle);
        $csv = ob_get_clean();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($csv);
    }

    /**
     * Genera el nombre de archivo para el CSV de exportación de estudiantes.
     * Formato: estudiantes_{nombre}_{grado}{seccion}.csv
     * Usa iconv para transliterar tildes/ñ y queda como slug limpio.
     *
     * @param  int    $idPromocion
     * @return string Nombre de archivo listo para Content-Disposition.
     */
    private function _slugExportEstudiantes(int $idPromocion): string
    {
        $db   = \Config\Database::connect();
        $promo = $db->query(
            'SELECT pe.nombre, pe.grado, pe.seccion, c.nombre_colegio
             FROM promociones_escolares pe
             LEFT JOIN colegios c ON c.id_colegio = pe.id_colegio
             WHERE pe.id_promocion = ?',
            [$idPromocion]
        )->getRowArray();

        if (!$promo) {
            return 'estudiantes_promo_' . $idPromocion . '.csv';
        }

        $partes = array_filter([
            $promo['nombre']        ?? '',
            $promo['grado']         ?? '',
            $promo['seccion']       ?? '',
        ]);

        $base = implode('_', $partes) ?: ($promo['nombre_colegio'] ?? 'promo_' . $idPromocion);
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base) ?: $base;
        $slug = strtolower((string) $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = trim($slug, '_');

        return 'estudiantes_' . ($slug ?: 'promo_' . $idPromocion) . '.csv';
    }

    /**
     * POST /api/estudiantes/importar
     *
     * Importa estudiantes desde un archivo CSV multipart.
     * Acepta tanto el formato estándar del sistema como el CSV exportado
     * desde el módulo de formularios (exportarCsv en AdminController).
     *
     * @return ResponseInterface 200 con resumen | 422 si el archivo es inválido.
     */
    public function importarCsv(): ResponseInterface
    {
        $idPromocion = (int) ($this->request->getPost('id_promocion') ?? 0);

        if (!$idPromocion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_promocion es requerido']);
        }

        $archivo = $this->request->getFile('archivo');

        if (!$archivo || !$archivo->isValid()) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Archivo CSV no recibido o inválido.']);
        }

        $handle = fopen($archivo->getTempName(), 'r');

        // Saltar BOM si existe
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $encabezado = fgetcsv($handle);

        if (!$encabezado) {
            fclose($handle);
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'El archivo está vacío o no es un CSV válido.']);
        }

        // Normalizar encabezados: soporta el formato del export de formularios
        // y el formato estándar del sistema en la misma importación.
        $encabezado = array_map([$this, '_normalizarColumna'], $encabezado);
        $filas      = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }
            $filas[] = array_combine($encabezado, array_pad($row, count($encabezado), ''));
        }

        fclose($handle);

        if (empty($filas)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'El CSV no contiene filas de datos.']);
        }

        $resultado = $this->estudianteService->importarDesdeArray($idPromocion, $filas);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'  => 'success',
                'message' => "{$resultado['creados']} estudiante(s) importado(s).",
                'data'    => $resultado,
            ]);
    }

    /**
     * Normaliza un encabezado de columna CSV al nombre canónico del sistema.
     * Permite importar tanto el CSV estándar como el CSV exportado desde formularios.
     *
     * @param  string $col Nombre de columna tal como viene en el archivo.
     * @return string      Nombre canónico o la columna original si no se reconoce.
     */
    private function _normalizarColumna(string $col): string
    {
        // Convertir a minúsculas y eliminar tildes para comparación robusta
        $norm = mb_strtolower(trim($col), 'UTF-8');
        $norm = strtr($norm, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']);

        $mapa = [
            // Formato export de formularios (AdminController::exportarCsv)
            'nombre alumno'    => 'nombres',
            'fecha nacimiento' => 'fecha_nacimiento',
            'color favorito'   => 'color_favorito',
            'profesion futura' => 'profesion_futura',
            'nombre tutor'     => 'apoderado_nombres',
            'relacion'         => 'tipo_relacion',
            'telefono'         => 'apoderado_telefono',
            'email'            => 'apoderado_correo',
            // Alias adicionales para mayor compatibilidad
            'apellido'         => 'apellidos',
            'apellidos alumno' => 'apellidos',
            'correo'           => 'apoderado_correo',
            'nombre apoderado' => 'apoderado_nombres',
        ];

        return $mapa[$norm] ?? $norm;
    }

}
