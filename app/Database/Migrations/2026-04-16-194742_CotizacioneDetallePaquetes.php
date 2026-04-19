<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CotizacionesPaquetes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_cot_paquete'=>[
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'id_cotizacion'=>[
                'type'=>'INT',
                'unsigned'=>true
            ],
            'id_paquete'=>[
                'type'=>'INT',
                'unsigned'=>true
            ],
            'cantidad'=>[
                'type'=>'INT',
                'unsigned'=>true,
                'default'=>1
            ],
            'precio_unitario'=>[
                'type'=>'DECIMAL',
                'constraint'=>'10,2',
                'unsigned'=>true,
            ],
            'subtotal'=>[
                'type'=>'DECIMAL',
                'constraint'=>'10,2',
                'unsigned'=>true,
                'default'=>'0.00'
            ]
        ]);
        $this->forge->addKey('id_cot_paquete', true);
        $this->forge->addForeignKey('id_cotizacion','cotizaciones','id_cotizacion','CASCADE','CASCADE');
        $this->forge->addForeignKey('id_paquete','paquetes','id_paquete','CASCADE','CASCADE');
        $this->forge->createTable('cotizaciones_paquetes');

        // COTIZACIONES SERVICIOS
        $this->forge->addField([
            'id_cotizacion' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'id_servicio' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'cantidad' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 1,
            ],
            'precio_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned'   => true,
            ],
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned'   => true,
                'default'    => '0.00',
            ],
        ]);

        $this->forge->addKey(['id_cotizacion', 'id_servicio'], true);
        $this->forge->addForeignKey('id_cotizacion', 'cotizaciones', 'id_cotizacion', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('id_servicio',   'servicios',    'id_servicio',   'RESTRICT', 'RESTRICT');
        $this->forge->createTable('cotizaciones_servicios');

        // COTIZACIONES PRODUCTOS
        $this->forge->addField([
            'id_cotizacion' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'id_producto' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'cantidad' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 1,
            ],
            'precio_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned'   => true,
            ],
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned'   => true,
                'default'    => '0.00',
            ],
        ]);
        $this->forge->addKey(['id_cotizacion', 'id_producto'], true);
        $this->forge->addForeignKey('id_cotizacion', 'cotizaciones', 'id_cotizacion', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('id_producto',   'productos',    'id_producto',   'RESTRICT', 'RESTRICT');
        $this->forge->createTable('cotizaciones_productos');
    }

    public function down()
    {
        $this->forge->dropTable('cotizaciones_productos');
        $this->forge->dropTable('cotizaciones_servicios');
        $this->forge->dropTable('cotizaciones_paquetes');
    }
}
