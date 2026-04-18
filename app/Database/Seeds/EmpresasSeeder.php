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
                'razon_social'=>'Eventos SAC',
                'ruc'=>'20123456789',
                'nombre_comercial'=>'EventosPro',
                'telefono'=>'987654321',
                'correo'=>'empresa@test.com'
            ],
         ]);
    }
}
