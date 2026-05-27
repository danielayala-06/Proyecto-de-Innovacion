<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaquetesProductosSeeder extends Seeder
{
    public function run()
    {
        $pId = fn(string $n) => (int)($this->db->table('paquetes')
            ->where('nombre_paquete', $n)->get()->getRowArray()['id_paquete'] ?? 0);

        $prId = fn(string $n) => (int)($this->db->table('productos')
            ->where('nombre_producto', $n)->get()->getRowArray()['id_producto'] ?? 0);

        $links = [
            // ── Cuadros Inicial → su producto físico correspondiente ──────────
            ['p' => 'Cuadro Soy una Princesa',         'pr' => 'Cuadro Soy una Princesa',         'q' => 1],
            ['p' => 'Cuadro Soy un Super Héroe',       'pr' => 'Cuadro Soy un Super Héroe',       'q' => 1],
            ['p' => 'Cuadro Mi Princesa',               'pr' => 'Cuadro Mi Princesa',               'q' => 1],
            ['p' => 'Cuadro Soy el Mejor',              'pr' => 'Cuadro Soy el Mejor',              'q' => 1],
            ['p' => 'Cuadro Princesas Mágicas',         'pr' => 'Cuadro Princesas Mágicas',         'q' => 1],
            ['p' => 'Cuadro Super Héroe',               'pr' => 'Cuadro Super Héroe',               'q' => 1],
            ['p' => 'Cuadro Mi Príncipe Super Capy',    'pr' => 'Cuadro Mi Príncipe Super Capy',    'q' => 1],
            ['p' => 'Cuadro Mi Princesa Capy Princess', 'pr' => 'Cuadro Mi Princesa Capy Princess', 'q' => 1],
            ['p' => 'Cuadro Barbie Capibara',           'pr' => 'Cuadro Barbie Capibara',           'q' => 1],
            ['p' => 'Cuadro Avenger',                   'pr' => 'Cuadro Avenger',                   'q' => 1],

            // ── Anuarios Inicial → su producto físico ─────────────────────────
            ['p' => 'Anuario Medium Inicial',      'pr' => 'Anuario Medium Inicial',      'q' => 1],
            ['p' => 'Anuario Big Inicial',         'pr' => 'Anuario Big Inicial',         'q' => 1],
            ['p' => 'Anuario Big Premium Inicial', 'pr' => 'Anuario Big Premium Inicial', 'q' => 1],

            // ── Cuadros Primaria → su producto físico ─────────────────────────
            ['p' => 'Cuadro Maravillas del Mundo',      'pr' => 'Cuadro Maravillas del Mundo',      'q' => 1],
            ['p' => 'Cuadro Académico',                 'pr' => 'Cuadro Académico',                 'q' => 1],
            ['p' => 'Cuadro Blanco Premium Brillante',  'pr' => 'Cuadro Blanco Premium Brillante',  'q' => 1],
            ['p' => 'Cuadro Brillante',                 'pr' => 'Cuadro Brillante',                 'q' => 1],
            ['p' => 'Cuadro Encajoado',                 'pr' => 'Cuadro Encajoado',                 'q' => 1],

            // ── Anuarios Primaria → su producto físico ────────────────────────
            ['p' => 'Anuario Small',               'pr' => 'Anuario Small Primaria',       'q' => 1],
            ['p' => 'Anuario Medium Primaria',     'pr' => 'Anuario Medium Primaria',      'q' => 1],
            ['p' => 'Anuario Big Primaria',        'pr' => 'Anuario Big Primaria',         'q' => 1],
            ['p' => 'Anuario Big Premium Primaria','pr' => 'Anuario Big Premium Primaria', 'q' => 1],

            // ── Packs Inicial: anuario Big Inicial + llavero + USB ────────────
            ['p' => 'Pack Mi Primera Promo',   'pr' => 'Anuario Big Inicial', 'q' => 1],
            ['p' => 'Pack Mi Primera Promo',   'pr' => 'Anuario Llavero',     'q' => 1],
            ['p' => 'Pack Mi Primera Promo',   'pr' => 'USB Fotográfico',     'q' => 1],

            ['p' => 'Pack Mis Recuerdos Inicial', 'pr' => 'Anuario Big Inicial', 'q' => 1],
            ['p' => 'Pack Mis Recuerdos Inicial', 'pr' => 'Anuario Llavero',     'q' => 1],
            ['p' => 'Pack Mis Recuerdos Inicial', 'pr' => 'USB Fotográfico',     'q' => 1],

            ['p' => 'Pack Premium Inicial', 'pr' => 'Anuario Big Inicial', 'q' => 1],
            ['p' => 'Pack Premium Inicial', 'pr' => 'Anuario Llavero',     'q' => 1],
            ['p' => 'Pack Premium Inicial', 'pr' => 'USB Fotográfico',     'q' => 1],

            ['p' => 'Pack Premium Gold Inicial', 'pr' => 'Anuario Big Inicial',    'q' => 1],
            ['p' => 'Pack Premium Gold Inicial', 'pr' => 'Anuario Llavero',        'q' => 1],
            ['p' => 'Pack Premium Gold Inicial', 'pr' => 'USB Fotográfico',        'q' => 1],
            ['p' => 'Pack Premium Gold Inicial', 'pr' => 'Book Anillado de Firma', 'q' => 1],

            // ── Packs Primaria ────────────────────────────────────────────────
            ['p' => 'Pack Adiós Primaria', 'pr' => 'Anuario Big Primaria', 'q' => 1],
            ['p' => 'Pack Adiós Primaria', 'pr' => 'Anuario Llavero',      'q' => 1],
            ['p' => 'Pack Adiós Primaria', 'pr' => 'USB Fotográfico',      'q' => 1],

            ['p' => 'Pack Mis Recuerdos Primaria', 'pr' => 'Anuario Big Primaria',       'q' => 1],
            ['p' => 'Pack Mis Recuerdos Primaria', 'pr' => 'Cuadro Maravillas del Mundo','q' => 1],
            ['p' => 'Pack Mis Recuerdos Primaria', 'pr' => 'Anuario Llavero',            'q' => 1],
            ['p' => 'Pack Mis Recuerdos Primaria', 'pr' => 'USB Fotográfico',            'q' => 1],

            ['p' => 'Pack Premium Primaria', 'pr' => 'Anuario Big Primaria',       'q' => 1],
            ['p' => 'Pack Premium Primaria', 'pr' => 'Cuadro Maravillas del Mundo','q' => 1],
            ['p' => 'Pack Premium Primaria', 'pr' => 'Anuario Llavero',            'q' => 1],
            ['p' => 'Pack Premium Primaria', 'pr' => 'USB Fotográfico',            'q' => 1],

            ['p' => 'Pack Premium Gold Primaria', 'pr' => 'Anuario Big Primaria',   'q' => 1],
            ['p' => 'Pack Premium Gold Primaria', 'pr' => 'Cuadro Brillante',       'q' => 1],
            ['p' => 'Pack Premium Gold Primaria', 'pr' => 'Anuario Llavero',        'q' => 1],
            ['p' => 'Pack Premium Gold Primaria', 'pr' => 'USB Fotográfico',        'q' => 1],
            ['p' => 'Pack Premium Gold Primaria', 'pr' => 'Book Anillado de Firma', 'q' => 1],
        ];

        $data = [];
        foreach ($links as $l) {
            $idP  = $pId($l['p']);
            $idPr = $prId($l['pr']);
            if ($idP === 0 || $idPr === 0) {
                log_message('warning', "[PaquetesProductosSeeder] Skipping link '{$l['p']}' → '{$l['pr']}': ID not found.");
                continue;
            }
            $data[] = ['id_paquete' => $idP, 'id_producto' => $idPr, 'cantidad' => $l['q']];
        }

        if (!empty($data)) {
            $this->db->table('paquetes_productos')->insertBatch($data);
        }
    }
}
