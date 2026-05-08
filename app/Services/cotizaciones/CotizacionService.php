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
            $this->detalleModel->insertBatch($detallesInsert);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException(
                'Error al crear la cotización'
            );
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

        // Agregamos los productos y paquetes
        foreach ($item_detalles as $item_detalle) {
            // PRODUCTO
            if ($item_detalle['tipo_item'] == 'producto') {

                $producto = $this->productoModel
                    ->find($item_detalle['id_referencia']);
                
                $detalles[] = [
                    'id' => $item_detalle['id_detalle'],
                    'tipo_item' => 'producto',
                    'id_referencia' => $item_detalle['id_referencia'],
                    'descripcion' =>$item_detalle['descripcion'],
                    'cantidad' =>$item_detalle['cantidad'],
                    'precio_unitario' =>$item_detalle['precio_unitario'],
                    'referencia_nombre' =>$producto['nombre_producto']
                ];
                continue;
            }

            // PAQUETE
            if ($item_detalle['tipo_item'] == 'paquete') {

                $paquete = $this->paqueteModel
                    ->find($item_detalle['id_referencia']);

                $detalles[] = [
                    'id' => $item_detalle['id_detalle'],
                    'tipo_item' => 'paquete',
                    'id_referencia' => (int)$item_detalle['id_referencia'],
                    'descripcion' =>$item_detalle['descripcion'],
                    'cantidad' =>$item_detalle['cantidad'],
                    'precio_unitario' =>$item_detalle['precio_unitario'],
                    'referencia_nombre' =>$paquete['nombre_paquete']
                ];

                continue;
            }
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

                // PRODUCTO
                if ($item_detalle['tipo_item'] == 'producto') {
                    $producto = $this->productoModel
                        ->find($item_detalle['id_referencia']);

                    $detalles[] = [
                        'id' => $item_detalle['id_detalle'],
                        'tipo_item' => 'producto',
                        'descripcion' =>$item_detalle['descripcion'],
                        'cantidad' =>$item_detalle['cantidad'],
                        'precio_unitario' =>$item_detalle['precio_unitario'],
                        'referencia_nombre' =>$producto['nombre_producto']
                    ];
                }

                // PAQUETE
                if ($item_detalle['tipo_item'] == 'paquete') {

                    $paquete = $this->paqueteModel
                        ->find($item_detalle['id_referencia']);

                    $detalles[] = [
                        'id' => $item_detalle['id_detalle'],
                        'tipo_item' => 'paquete',
                        'descripcion'=> $item_detalle['descripcion'],
                        'cantidad' => $item_detalle['cantidad'],
                        'precio_unitario' => $item_detalle['precio_unitario'],
                        'referencia_nombre' => $paquete['nombre_paquete']
                    ];
                }
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