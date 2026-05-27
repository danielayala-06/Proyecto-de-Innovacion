<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaquetesSesionesSeeder extends Seeder
{
    public function run()
    {
        $pId = fn(string $n) => (int)($this->db->table('paquetes')
            ->where('nombre_paquete', $n)->get()->getRowArray()['id_paquete'] ?? 0);

        // Grupos de sesiones reutilizables
        $soloColeg     = fn(int $id) => [['id' => $id, 'tipo' => 'colegio',   'num' => 1, 'lugar' => 'Centro educativo (lunes a viernes)']];
        $colegExt      = fn(int $id) => [
            ['id' => $id, 'tipo' => 'colegio',    'num' => 1, 'lugar' => 'Centro educativo (lunes a viernes)'],
            ['id' => $id, 'tipo' => 'exteriores', 'num' => 1, 'lugar' => null],
        ];
        $colegExtEst   = fn(int $id) => [
            ['id' => $id, 'tipo' => 'colegio',    'num' => 1, 'lugar' => 'Centro educativo (lunes a viernes)'],
            ['id' => $id, 'tipo' => 'exteriores', 'num' => 1, 'lugar' => null],
            ['id' => $id, 'tipo' => 'estudio',    'num' => 1, 'lugar' => 'Estudio fotográfico principal'],
        ];

        $configs = [
            // ── Cuadros Inicial S/110 — 1 sesión colegio ─────────────────────
            'Cuadro Soy una Princesa'         => 'soloColeg',
            'Cuadro Soy un Super Héroe'       => 'soloColeg',

            // ── Cuadros Inicial S/140 y S/160 — colegio + exteriores ──────────
            'Cuadro Mi Princesa'               => 'colegExt',
            'Cuadro Soy el Mejor'              => 'colegExt',
            'Cuadro Princesas Mágicas'         => 'colegExt',
            'Cuadro Super Héroe'               => 'colegExt',
            'Cuadro Mi Príncipe Super Capy'    => 'colegExt',
            'Cuadro Mi Princesa Capy Princess' => 'colegExt',
            'Cuadro Barbie Capibara'           => 'colegExt',
            'Cuadro Avenger'                   => 'colegExt',

            // ── Anuarios Inicial ──────────────────────────────────────────────
            'Anuario Medium Inicial'      => 'colegExt',
            'Anuario Big Inicial'         => 'colegExt',
            'Anuario Big Premium Inicial' => 'colegExtEst',

            // ── Packs Inicial ─────────────────────────────────────────────────
            'Pack Mi Primera Promo'    => 'colegExt',
            'Pack Mis Recuerdos Inicial' => 'colegExt',
            'Pack Premium Inicial'     => 'colegExt',
            'Pack Premium Gold Inicial'=> 'colegExtEst',

            // ── Cuadros Primaria — colegio + exteriores ───────────────────────
            'Cuadro Maravillas del Mundo'     => 'colegExt',
            'Cuadro Académico'                => 'colegExt',
            'Cuadro Blanco Premium Brillante' => 'colegExt',
            'Cuadro Brillante'                => 'colegExt',
            'Cuadro Encajoado'                => 'colegExt',

            // ── Anuarios Primaria ─────────────────────────────────────────────
            'Anuario Small'               => 'colegExt',
            'Anuario Medium Primaria'     => 'colegExt',
            'Anuario Big Primaria'        => 'colegExt',
            'Anuario Big Premium Primaria'=> 'colegExtEst',

            // ── Packs Primaria ────────────────────────────────────────────────
            'Pack Adiós Primaria'         => 'colegExt',
            'Pack Mis Recuerdos Primaria' => 'colegExt',
            'Pack Premium Primaria'       => 'colegExt',
            'Pack Premium Gold Primaria'  => 'colegExtEst',
        ];

        $data = [];
        foreach ($configs as $nombre => $tipo) {
            $idP = $pId($nombre);
            if ($idP === 0) {
                log_message('warning', "[PaquetesSesionesSeeder] Paquete '{$nombre}' no encontrado, saltando.");
                continue;
            }
            $rows = match ($tipo) {
                'soloColeg'   => $soloColeg($idP),
                'colegExt'    => $colegExt($idP),
                'colegExtEst' => $colegExtEst($idP),
            };
            foreach ($rows as $r) {
                $data[] = [
                    'id_paquete'        => $r['id'],
                    'tipo_sesion'       => $r['tipo'],
                    'lugar_descripcion' => $r['lugar'],
                    'num_sesiones'      => $r['num'],
                ];
            }
        }

        if (!empty($data)) {
            $this->db->table('paquetes_sesiones')->insertBatch($data);
        }
    }
}
