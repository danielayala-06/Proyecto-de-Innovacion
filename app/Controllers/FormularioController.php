<?php

namespace App\Controllers;

use App\Models\PromAlumnoModel;
use App\Models\PromPromocionModel;
use App\Models\PromFormularioModel;
use CodeIgniter\HTTP\ResponseInterface;

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
            'alumno'    => $alumno,
            'promocion' => $promocion,
            'stock'     => $stock,
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

    private function _json(array $data, int $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }

}