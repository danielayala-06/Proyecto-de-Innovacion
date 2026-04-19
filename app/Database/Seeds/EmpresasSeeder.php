<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EmpresasSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('empresas')->insertBatch([
            [
                'razon_social'=>'Muebles mariano SAC',
                'ruc'=>'20907867584',
                'nombre_comercial'=>'Elmarianito',
                'telefono'=>'980786798',
                'correo'=>'elmariano@gmail.com'
            ],
            [
                'razon_social'=>'Colegio nacional fujisoto',
                'ruc'=>'198970234',
                'nombre_comercial'=>'El fujisoto',
                'telefono'=>'908128793',
                'correo'=>'fuji_soto@gmail.com'
            ],
            [
                'razon_social'=>'Corporacion E.R.L',
                'ruc'=>'20123456789',
                'nombre_comercial'=>'InkaCorp',
                'telefono'=>'945367453',
                'correo'=>'empresa@gmail.com'
            ],
            [
                 'razon_social'    => 'Eventos Mágicos S.A.C.',
                 'ruc'             => '20512345678',
                 'nombre_comercial'=> 'Eventos Mágicos',
                 'telefono'        => '014567890',
                 'correo'          => 'contacto@eventosmagicos.pe',
             ],
            [
                 'razon_social'    => 'Corporación Lima Norte S.R.L.',
                 'ruc'             => '20598765432',
                 'nombre_comercial'=> 'Lima Norte Corp',
                 'telefono'        => '013456789',
                 'correo'          => 'admin@limanorte.com',
             ],
            [
                 'razon_social'    => 'Grupo Bodas & Sueños E.I.R.L.',
                 'ruc'             => '20567891234',
                 'nombre_comercial'=> 'Bodas & Sueños',
                 'telefono'        => null,
                 'correo'          => 'info@bodasysuen.pe',
             ],
            [
                 'razon_social'    => 'Inversiones Pacífico S.A.',
                 'ruc'             => '20534567891',
                 'nombre_comercial'=> 'Inv. Pacífico',
                 'telefono'        => '016789012',
                 'correo'          => 'contacto@invpacifico.pe',
             ],
         ]);
    }
}
