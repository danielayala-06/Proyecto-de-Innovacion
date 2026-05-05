<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Cliente;
use App\Models\Cotizacion;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class CotizacionController extends BaseController
{
    use ResponseTrait;
    public function index(int $page = 1)
    {
        $modelCotizacion = new Cotizacion();// Accedemos al model
        $resumenes = $modelCotizacion->getResumenGeneralCoti();

        $cotizaciones = $modelCotizacion->getCotizacionesResumen(10, $page);

        $data = [
            'header' => view("layouts/header"),
            'footer' => view("layouts/footer"),
            'cotizaciones' => $cotizaciones,
            'resumenes' => $resumenes,
        ];

        return view("cotizaciones/index", $data);
    }
    public function create()
    {
        $data = [
            'header' => view("layouts/header"),
            'footer' => view("layouts/footer"),
        ];

        return view("cotizaciones/crear", $data);
    }
    public function createCotizacion()
    {
        try {
            $data = $this->request->getJSON(true);

            $idCliente    = $data['id_cliente']    ?? null;
            $clienteNuevo = $data['cliente_nuevo'] ?? null;

            // ── PASO 1: resolver id_cliente ──────────────────────────────
            $modelPersona = new \App\Models\Persona();
            $modelCliente = new Cliente();

            if (empty($idCliente)) {
                // Cliente nuevo — validar datos mínimos
                if (empty($clienteNuevo['nombres']) || empty($clienteNuevo['dni'])) {
                    return $this->fail('Proporciona nombre y DNI del cliente.', 422);
                }
                if (empty($clienteNuevo['telefono'])) {
                    return $this->fail('El teléfono del cliente es obligatorio.', 422);
                }

                // Buscar persona por DNI
                $persona = $modelPersona
                    ->where('numero_documento', trim($clienteNuevo['dni']))
                    ->first();

                if ($persona) {
                    $idPersona = (int) $persona['id_persona'];
                    // Actualizar todos los datos, incluidos nombres/apellidos con data de RENIEC
                    $modelPersona->update($idPersona, [
                        'nombres'  => strtoupper(trim($clienteNuevo['nombres'])),
                        'apellidos'=> strtoupper(trim($clienteNuevo['apellidos'] ?? '')),
                        'telefono' => trim($clienteNuevo['telefono']),
                        'correo'   => $clienteNuevo['email'] ?: null,
                    ]);
                } else {
                    $ok = $modelPersona->insert([
                        'nombres'          => strtoupper(trim($clienteNuevo['nombres'])),
                        'apellidos'        => strtoupper(trim($clienteNuevo['apellidos'] ?? '')),
                        'telefono'         => trim($clienteNuevo['telefono']),
                        'correo'           => $clienteNuevo['email'] ?: null,
                        'numero_documento' => trim($clienteNuevo['dni']),
                        'tipo_documento'   => 'DNI',
                    ]);
                    if (!$ok) {
                        return $this->fail('Error al registrar persona: ' . json_encode($modelPersona->errors()), 422);
                    }
                    $idPersona = (int) $modelPersona->getInsertID();
                }

                // Buscar cliente asociado a esa persona (returnType = 'object')
                $clienteExistente = $modelCliente->where('id_persona', $idPersona)->first();

                if ($clienteExistente) {
                    $idCliente = (int) $clienteExistente->id_cliente;
                } else {
                    $ok = $modelCliente->insert([
                        'id_persona'          => $idPersona,
                        'metodo_comunicacion' => 'WHATSAPP',
                        'estado'              => 'ACTIVO',
                    ]);
                    if (!$ok) {
                        return $this->fail('Error al registrar cliente: ' . json_encode($modelCliente->errors()), 422);
                    }
                    $idCliente = (int) $modelCliente->getInsertID();
                }
            } elseif (!empty($clienteNuevo['nombres']) && !empty($clienteNuevo['apellidos'])) {
                // Cliente existente — actualizar nombres/apellidos si vienen datos de RENIEC
                $clienteExistente = $modelCliente->find((int) $idCliente);
                if ($clienteExistente) {
                    $modelPersona->update($clienteExistente->id_persona, [
                        'nombres'  => strtoupper(trim($clienteNuevo['nombres'])),
                        'apellidos'=> strtoupper(trim($clienteNuevo['apellidos'])),
                    ]);
                }
            }

            // ── PASO 2: validar sesión ───────────────────────────────────
            $idUsuario = (int) (session()->get('usuario')['id'] ?? 0);
            if ($idUsuario === 0) {
                return $this->fail('Sesión expirada. Vuelve a iniciar sesión.', 401);
            }

            // ── PASO 3: crear cotización ─────────────────────────────────
            // Convierte ISO 'YYYY-MM-DDTHH:MM' → 'YYYY-MM-DD HH:MM:SS'
            $toMysql = fn(?string $dt) => $dt
                ? (strlen($dt) === 16
                    ? str_replace('T', ' ', $dt) . ':00'
                    : str_replace('T', ' ', $dt))
                : null;

            $modelCotizacion = new Cotizacion();

            $ok = $modelCotizacion->insert([
                'id_cliente'        => (int) $idCliente,
                'id_usuario'        => $idUsuario,
                'nombre_cotizacion' => $data['nombre_cotizacion']  ?? null,
                'fecha_hora_inicio' => $toMysql($data['fecha_hora_inicio'] ?? null),
                'fecha_hora_fin'    => $toMysql($data['fecha_hora_fin']    ?? null),
                'direccion'         => $data['direccion']          ?? null,
                'referencia'        => $data['referencia']         ?? null,
                'observaciones'     => $data['observaciones']      ?? null,
                'total_estimado'    => (float) ($data['total_estimado'] ?? 0),
            ]);

            if (!$ok) {
                return $this->fail('Error al crear cotización: ' . json_encode($modelCotizacion->errors()), 422);
            }

            return $this->respondCreated($modelCotizacion->find($modelCotizacion->getInsertID()));

        } catch (\Exception $e) {
            return $this->fail('Excepción: ' . $e->getMessage(), 500);
        }
    }

    public function searchCliente()
    {
        $data = $this->request->getJSON(true);

        $tipo = $data['tipo'] ?? null;
        $valor = $data['valor'] ?? null;

        // Validación básica
        if (!$tipo || !$valor) {
            return $this->fail('Faltan datos');
        }

        // Validar columnas permitidas (IMPORTANTE)
        $allowed = ['numero_documento', 'telefono', 'nombres'];
        if (!in_array($tipo, $allowed)) {
            return $this->fail('Tipo de búsqueda inválido');
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('personas p');

        $builder->select('c.id_cliente, p.nombres, p.apellidos, p.numero_documento AS dni, p.telefono, p.correo AS email');
        $builder->join('clientes c', 'c.id_persona = p.id_persona', 'left');

        // Prefijo explícito para funcionar con el alias de tabla
        if ($tipo === 'numero_documento') {
            $builder->where('p.numero_documento', $valor);
        } elseif ($tipo === 'telefono') {
            $builder->where('p.telefono', $valor);
        } else {
            $builder->like('p.nombres', $valor);
        }

        $clientes = $builder->get()->getResult();

        if (empty($clientes)) {
            return $this->failNotFound('No se encontraron resultados');
        }

        return $this->respond($clientes, 200);
    }
}
