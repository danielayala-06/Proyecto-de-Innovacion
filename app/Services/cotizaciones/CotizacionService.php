<?php

namespace App\Services\Cotizaciones;

use App\Models\CotizacionesModel;
use App\Models\CotizacionesDetallesModel;
use App\Models\PaquetesModel;
use App\Models\ProductosModel;

class CotizacionService
{
    protected CotizacionesModel $cotizacionModel;
    protected CotizacionesDetallesModel $detalleModel;
    protected ProductosModel $productoModel;
    protected PaquetesModel $paqueteModel;

    protected $db;

    public function __construct()
    {
        $this->cotizacionModel = new CotizacionesModel();
        $this->detalleModel = new CotizacionesDetallesModel();
        $this->productoModel = new ProductosModel();
        $this->paqueteModel = new PaquetesModel();

        $this->db = db_connect();
    }

    /**
     * Crear cotización completa
     */
    public function crear(array $data): array|int
    {
        $this->db->transStart();

        $cotizacion = [
            'id_cliente'      => $data['id_cliente'],
            'id_usuario'      => $data['id_usuario'],
            'observaciones'   => $data['observaciones'] ?? null,
            'fecha_registro'  => date('Y-m-d H:i:s'),
            'total_estimado'  => $data['total_estimado'],
            'estado'          => 'BORRADOR'
        ];

        $idCotizacion = $this->cotizacionModel->insert($cotizacion);

        // En caso de que no se creo la cotizacion:
        if(!$idCotizacion)return 0;

        $detallesInsert = [];

        foreach ($data['detalles'] as $detalle) {

            $detallesInsert[] = [
                'tipo_item'       => $detalle['tipo_item'],
                'id_referencia'   => $detalle['id_referencia'] ?? null,
                'descripcion'     => $detalle['descripcion'],
                'cantidad'        => $detalle['cantidad'],
                'precio_unitario' => $detalle['precio_unitario'],
                'id_cotizacion'   => $idCotizacion,
            ];
        }

        if (! empty($detallesInsert)) {
            $ok = $this->detalleModel->insertBatch($detallesInsert);
            if ($ok === false) {
                throw new \RuntimeException(
                    'Error al insertar detalles: ' . implode(', ', $this->detalleModel->errors())
                );
            }
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Error al crear la cotización');
        }

        return $this->obtenerPorId($idCotizacion);
    }

    /**
     * Obtener cotización completa
     */
    public function obtenerPorId(int $idCotizacion): ?array
    {
        $cotizacion = $this->cotizacionModel
            ->select([
                'cotizaciones.*',
                'clientes.id_cliente',
                'personas.nombres',
                'personas.apellidos',
                'usuarios.nombre_user'
            ])
            ->join(
                'clientes',
                'clientes.id_cliente = cotizaciones.id_cliente'
            )
            ->join(
                'personas',
                'personas.id_persona = clientes.id_persona'
            )
            ->join(
                'usuarios',
                'usuarios.id_usuario = cotizaciones.id_usuario'
            )
            ->find($idCotizacion);

        if (!$cotizacion) return null;

        // Obtenemos los productos 
        $item_detalles = $this->detalleModel
        ->select('*')
        ->where('id_cotizacion', $idCotizacion)
        ->findAll() ?? []; // En caso de que no haya no se enviara nada

        $detalles = [];

        foreach ($item_detalles as $item_detalle) {
            $tipo             = strtolower($item_detalle['tipo_item'] ?? '');
            $idRef            = $item_detalle['id_referencia'] ?? null;
            $referenciaNombre = null;

            if ($tipo === 'producto' && $idRef) {
                $producto         = $this->productoModel->find($idRef);
                $referenciaNombre = $producto['nombre_producto'] ?? null;
            } elseif ($tipo === 'paquete' && $idRef) {
                $paquete          = $this->paqueteModel->find($idRef);
                $referenciaNombre = $paquete['nombre_paquete'] ?? null;
            }
            // 'personalizado' cae aquí: sin referencia, sin nombre de referencia

            $detalles[] = [
                'id'                => $item_detalle['id_detalle'],
                'tipo_item'         => $tipo,
                'id_referencia'     => $idRef ? (int) $idRef : null,
                'descripcion'       => $item_detalle['descripcion'],
                'cantidad'          => $item_detalle['cantidad'],
                'precio_unitario'   => $item_detalle['precio_unitario'],
                'referencia_nombre' => $referenciaNombre,
            ];
        }

        return [
            'cotizacion' => [
                'id'             => $cotizacion['id_cotizacion'],
                'fecha'          => $cotizacion['fecha_registro'],
                'estado'         => $cotizacion['estado'],
                'observaciones'  => $cotizacion['observaciones'],
                'total'          => (float) $cotizacion['total_estimado']
            ],
            'cliente' => [
                'id' => $cotizacion['id_cliente'],
                'nombre_completo' => trim(
                    $cotizacion['nombres']
                    . ' ' .
                    $cotizacion['apellidos']
                )
            ],
            'usuario' => [
                'username' => $cotizacion['nombre_user']
            ],
            'detalles'=> $detalles
        ];
    }

    /**
     * Actualizar cotización completa
     */
    public function actualizar(
        int $idCotizacion,
        int $itemId, 
        string $action,
        array $data
    ): array {

        $this->db->transStart();

        /**
         * UPDATE CABECERA
         */
        /*$this->cotizacionModel->update(
            $idCotizacion,
            [
                'observaciones'  => $data['observaciones'] ?? null,
                'estado'         => $data['estado'] ?? 'PENDIENTE'
            ]
        );*/

        /**
         * ELIMINAR DETALLES
         */
        /*$this->detalleModel
            ->where(
                'id_cotizacion',
                $idCotizacion
            )
            ->delete();*/

        /**
         * INSERTAR NUEVOS ITEMS
         */
        $detallesInsert = [];

        foreach ($data['detalles'] as $detalle) {

            $detallesInsert[] = [
                'tipo_item'       => $detalle['tipo_item'],
                'id_referencia'   => $detalle['id_referencia'] ?? null,
                'descripcion'     => $detalle['descripcion'],
                'cantidad'        => $detalle['cantidad'],
                'precio_unitario' => $detalle['precio_unitario'],
                'id_cotizacion'   => $idCotizacion,
            ];
        }

        if (! empty($detallesInsert)) {
            $this->detalleModel->insertBatch($detallesInsert);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException(
                'Error al actualizar la cotización'
            );
        }

        return $this->obtenerPorId($idCotizacion);
    }

    /**
     * Listado general
     */
    public function listar(array $filters = []): array
    {
        $cotizacionesDB = $this->cotizacionModel
            ->select([
                'cotizaciones.*',
                'clientes.id_cliente',
                'personas.nombres',
                'personas.apellidos',
                'usuarios.nombre_user'
            ])
            ->join(
                'clientes',
                'clientes.id_cliente = cotizaciones.id_cliente'
            )
            ->join(
                'personas',
                'personas.id_persona = clientes.id_persona'
            )
            ->join(
                'usuarios',
                'usuarios.id_usuario = cotizaciones.id_usuario'
            )
            ->paginate();

        if (!$cotizacionesDB) {
            return [];
        }

        $cotizaciones = [];

        foreach ($cotizacionesDB as $cotizacion) {

            $idCotizacion = $cotizacion['id_cotizacion'];

            $detalles = [];

            $item_detalles = $this->detalleModel
                ->where('id_cotizacion', $idCotizacion)
                ->findAll() ?? [];

            foreach ($item_detalles as $item_detalle) {

                $tipo  = strtoupper($item_detalle['tipo_item'] ?? '');
                $idRef = $item_detalle['id_referencia'] ?? null;
                $referenciaNombre = null;

                if ($tipo === 'PRODUCTO' && $idRef) {
                    $producto = $this->productoModel->find($idRef);
                    $referenciaNombre = $producto['nombre_producto'] ?? null;
                } elseif ($tipo === 'PAQUETE' && $idRef) {
                    $paquete = $this->paqueteModel->find($idRef);
                    $referenciaNombre = $paquete['nombre_paquete'] ?? null;
                }

                $detalles[] = [
                    'id'               => $item_detalle['id_detalle'],
                    'tipo_item'        => $tipo,
                    'id_referencia'    => $idRef,
                    'descripcion'      => $item_detalle['descripcion'],
                    'cantidad'         => $item_detalle['cantidad'],
                    'precio_unitario'  => $item_detalle['precio_unitario'],
                    'referencia_nombre'=> $referenciaNombre,
                ];
            }

            $cotizaciones[] = [

                'cotizacion' => [
                    'id' => $cotizacion['id_cotizacion'],
                    'fecha' => $cotizacion['fecha_registro'],
                    'estado' => $cotizacion['estado'],
                    'observaciones' =>$cotizacion['observaciones'],
                    'total' =>(float)$cotizacion['total_estimado']
                ],
                'cliente' => [
                    'id' => $cotizacion['id_cliente'],
                    'nombre_completo' => trim(
                        $cotizacion['nombres']
                        . ' ' .
                        $cotizacion['apellidos']
                    )
                ],
                'usuario' => [
                    'username' =>$cotizacion['nombre_user']
                ],
                'detalles' => $detalles
            ];
        }

        // IMPORTANTE
        return $cotizaciones;
    }
}