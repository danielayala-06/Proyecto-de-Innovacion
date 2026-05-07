<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaquetesProductosSeeder extends Seeder
{
    public function run()
    {
        // Paquetes: 1=Inicial Básico, 2=Primaria Completo, 3=Secundaria Premium, 4=Secundaria Estándar
        // Productos: 1=Cuadro Individual, 2=Cuadro Grupal, 3=Anuario Premium, 4=Anuario Básico, 5=Photobook, 6=Llavero
        $data = [
            // Paquete 1 - Inicial Básico
            ['id_paquete' => 1, 'id_producto' => 4, 'cantidad' => 1], // Anuario Básico
            ['id_paquete' => 1, 'id_producto' => 1, 'cantidad' => 1], // Cuadro Individual

            // Paquete 2 - Primaria Completo
            ['id_paquete' => 2, 'id_producto' => 3, 'cantidad' => 1], // Anuario Premium
            ['id_paquete' => 2, 'id_producto' => 1, 'cantidad' => 1], // Cuadro Individual
            ['id_paquete' => 2, 'id_producto' => 5, 'cantidad' => 1], // Photobook
            ['id_paquete' => 2, 'id_producto' => 6, 'cantidad' => 2], // Llavero x2

            // Paquete 3 - Secundaria Premium
            ['id_paquete' => 3, 'id_producto' => 3, 'cantidad' => 1], // Anuario Premium
            ['id_paquete' => 3, 'id_producto' => 1, 'cantidad' => 1], // Cuadro Individual
            ['id_paquete' => 3, 'id_producto' => 2, 'cantidad' => 1], // Cuadro Grupal
            ['id_paquete' => 3, 'id_producto' => 5, 'cantidad' => 1], // Photobook
            ['id_paquete' => 3, 'id_producto' => 6, 'cantidad' => 3], // Llavero x3

            // Paquete 4 - Secundaria Estándar
            ['id_paquete' => 4, 'id_producto' => 4, 'cantidad' => 1], // Anuario Básico
            ['id_paquete' => 4, 'id_producto' => 2, 'cantidad' => 1], // Cuadro Grupal
            ['id_paquete' => 4, 'id_producto' => 6, 'cantidad' => 1], // Llavero
        ];

        $this->db->table('paquetes_productos')->insertBatch($data);
    }
}
