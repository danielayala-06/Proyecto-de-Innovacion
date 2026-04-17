<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('RolesSeeder');
        $this->call('FormasPagoSeeder');
        $this->call('ServiciosSeeder');
        $this->call('ProductosSeeder');

        $this->call('PersonasSeeder');
        $this->call('EmpresasSeeder');

        $this->call('ClientesSeeder');
        $this->call('UsuariosSeeder');

        $this->call('PaquetesSeeder');

        $this->call('PaquetesServiciosSeeder');
        $this->call('PaquetesProductosSeeder');

        $this->call('CotizacionesSeeder');
        $this->call('CotizacionesPaquetesSeeder');
        
        $this->call('ContratosSeeder');
        $this->call('PagosSeeder');
        $this->call('ReprogramacionesSeeder');
    }
}
