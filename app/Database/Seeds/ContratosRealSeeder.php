<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Datos reales extraídos de contratos físicos 2026:
 *  - 026-0013  Humboldt Secundaria – Pisco        (22/05/2026)
 *  - 026-0003  Donald Scarrow Primaria 6to         (30/04/2026)
 *  - 026-0012  Aurelio Moisés Flores Primaria 6to B(20/05/2026)
 *  - 026-0003  Prolog Primaria 6to A               (30/04/2026)
 */
class ContratosRealSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        // ──────────────────────────────────────────────────────────────────────
        // 1. COLEGIOS (solo los que no existen aún)
        //    id=8 I.E. PROLOG ya existe | id=9 ALEXANDER VON HUMBOLT ya existe
        // ──────────────────────────────────────────────────────────────────────
        $db->table('colegios')->insert([
            'nombre_colegio' => 'I.E. Donald Scarrow',
            'distrito'       => 'Chincha Alta',
            'provincia'      => 'Chincha',
            'estado'         => 'ACTIVO',
        ]);
        $idDonald = $db->insertID(); // 10

        $db->table('colegios')->insert([
            'nombre_colegio' => 'I.E. Aurelio Moisés Flores',
            'distrito'       => 'Chincha Alta',
            'provincia'      => 'Chincha',
            'estado'         => 'ACTIVO',
        ]);
        $idAurelio = $db->insertID(); // 11

        // ──────────────────────────────────────────────────────────────────────
        // 2. PERSONAS + CLIENTES  (contactos de cada promoción)
        // ──────────────────────────────────────────────────────────────────────

        // Contrato 1 – Luis Medina Mendoza (Humboldt)
        $db->table('personas')->insert([
            'nombres'          => 'Luis',
            'apellidos'        => 'Medina Mendoza',
            'telefono'         => '950526787',
            'tipo_documento'   => 'DNI',
            'numero_documento' => 'P0000001',
        ]);
        $db->table('clientes')->insert(['id_persona' => $db->insertID(), 'metodo_comunicacion' => 'whatsapp', 'acepta_promociones' => 0]);
        $idClienteHumboldt = $db->insertID();

        // Contrato 2 – Lily (Donald Scarrow)
        $db->table('personas')->insert([
            'nombres'          => 'Lily',
            'apellidos'        => null,
            'telefono'         => '934580227',
            'tipo_documento'   => 'DNI',
            'numero_documento' => 'P0000002',
        ]);
        $db->table('clientes')->insert(['id_persona' => $db->insertID(), 'metodo_comunicacion' => 'whatsapp', 'acepta_promociones' => 0]);
        $idClienteDonald = $db->insertID();

        // Contrato 3 – Martha García (Aurelio Moisés Flores)
        $db->table('personas')->insert([
            'nombres'          => 'Martha',
            'apellidos'        => 'García',
            'telefono'         => '986773245',
            'tipo_documento'   => 'DNI',
            'numero_documento' => 'P0000003',
        ]);
        $db->table('clientes')->insert(['id_persona' => $db->insertID(), 'metodo_comunicacion' => 'whatsapp', 'acepta_promociones' => 0]);
        $idClienteAurelio = $db->insertID();

        // Contrato 4 – Mayra Cucho (Prolog)
        $db->table('personas')->insert([
            'nombres'          => 'Mayra',
            'apellidos'        => 'Cucho',
            'telefono'         => '910148593',
            'tipo_documento'   => 'DNI',
            'numero_documento' => 'P0000004',
        ]);
        $db->table('clientes')->insert(['id_persona' => $db->insertID(), 'metodo_comunicacion' => 'whatsapp', 'acepta_promociones' => 0]);
        $idClienteProlog = $db->insertID();

        // ──────────────────────────────────────────────────────────────────────
        // 3. COTIZACIONES  (estado APROBADA – contrato ya generado)
        //    id_usuario=2 (maria.ventas / Vendedor)
        // ──────────────────────────────────────────────────────────────────────

        // Contrato 1 – Humboldt  total=8500
        $db->table('cotizaciones')->insert([
            'id_cliente'     => $idClienteHumboldt,
            'id_usuario'     => 2,
            'fecha_registro' => '2026-05-22',
            'total_estimado' => 8500.00,
            'estado'         => 'APROBADA',
        ]);
        $idCotHumboldt = $db->insertID();

        // Contrato 2 – Donald Scarrow  total=3150
        $db->table('cotizaciones')->insert([
            'id_cliente'     => $idClienteDonald,
            'id_usuario'     => 2,
            'fecha_registro' => '2026-04-30',
            'total_estimado' => 3150.00,
            'estado'         => 'APROBADA',
        ]);
        $idCotDonald = $db->insertID();

        // Contrato 3 – Aurelio  total=3500
        $db->table('cotizaciones')->insert([
            'id_cliente'     => $idClienteAurelio,
            'id_usuario'     => 2,
            'fecha_registro' => '2026-05-20',
            'total_estimado' => 3500.00,
            'estado'         => 'APROBADA',
        ]);
        $idCotAurelio = $db->insertID();

        // Contrato 4 – Prolog  total=3520
        $db->table('cotizaciones')->insert([
            'id_cliente'     => $idClienteProlog,
            'id_usuario'     => 2,
            'fecha_registro' => '2026-04-30',
            'total_estimado' => 3520.00,
            'estado'         => 'APROBADA',
        ]);
        $idCotProlog = $db->insertID();

        // ──────────────────────────────────────────────────────────────────────
        // 4. DETALLES DE COTIZACIONES
        //    Paquetes usados (primaria):
        //      id=21 Cuadro Brillante        | id=25 Anuario Big Primaria
        //      id=26 Anuario Big Premium Primaria
        //    Secundaria y servicios extra: tipo_item='personalizado'
        // ──────────────────────────────────────────────────────────────────────

        // ── Humboldt Secundaria ── (sin paquetes de secundaria en catálogo)
        // 37×200=7400 | Ceremonia=500 | Fiesta prom=500 | Última campanada+USB=100
        $db->table('cotizaciones_detalles')->insertBatch([
            ['id_cotizacion' => $idCotHumboldt, 'tipo_item' => 'personalizado', 'id_referencia' => null,
             'descripcion' => 'Anuario 25x25 (5 hojas - 10 páginas)',
             'cantidad' => 37, 'precio_unitario' => 200.00],
            ['id_cotizacion' => $idCotHumboldt, 'tipo_item' => 'personalizado', 'id_referencia' => null,
             'descripcion' => '[Cortesía] Anuario 25x25 tutor',
             'cantidad' => 1, 'precio_unitario' => 0.00],
            ['id_cotizacion' => $idCotHumboldt, 'tipo_item' => 'personalizado', 'id_referencia' => null,
             'descripcion' => 'Ceremonia de Graduación (foto y video) - 4 dic',
             'cantidad' => 1, 'precio_unitario' => 500.00],
            ['id_cotizacion' => $idCotHumboldt, 'tipo_item' => 'personalizado', 'id_referencia' => null,
             'descripcion' => 'Fiesta de Promoción (foto y video) - 19 dic',
             'cantidad' => 1, 'precio_unitario' => 500.00],
            ['id_cotizacion' => $idCotHumboldt, 'tipo_item' => 'personalizado', 'id_referencia' => null,
             'descripcion' => 'Última Campanada (Reel) - 18 dic + USB con fotos y video',
             'cantidad' => 1, 'precio_unitario' => 100.00],
        ]);

        // ── Donald Scarrow Primaria 6to ──
        // 14×140=1960 | 7×140=980 | 2 sesiones=210 | cortesía=0  → total 3150
        $db->table('cotizaciones_detalles')->insertBatch([
            ['id_cotizacion' => $idCotDonald, 'tipo_item' => 'paquete', 'id_referencia' => 25,
             'descripcion' => 'Anuario Big 25x25 (5 hojas - 10 páginas)',
             'cantidad' => 14, 'precio_unitario' => 140.00],
            ['id_cotizacion' => $idCotDonald, 'tipo_item' => 'paquete', 'id_referencia' => 21,
             'descripcion' => 'Cuadro Brillante',
             'cantidad' => 7, 'precio_unitario' => 140.00],
            ['id_cotizacion' => $idCotDonald, 'tipo_item' => 'personalizado', 'id_referencia' => null,
             'descripcion' => '[Cortesía] Anuario Big 25x25 docente',
             'cantidad' => 1, 'precio_unitario' => 0.00],
            ['id_cotizacion' => $idCotDonald, 'tipo_item' => 'personalizado', 'id_referencia' => null,
             'descripcion' => '2 Sesiones fotográficas',
             'cantidad' => 1, 'precio_unitario' => 210.00],
        ]);

        // ── Aurelio Moisés Flores Primaria 6to B ──
        // 1×140=140 | 24×140=3360 | cortesía=0  → total 3500
        $db->table('cotizaciones_detalles')->insertBatch([
            ['id_cotizacion' => $idCotAurelio, 'tipo_item' => 'paquete', 'id_referencia' => 25,
             'descripcion' => 'Anuario Big 25x25 (5 hojas - 10 páginas)',
             'cantidad' => 1, 'precio_unitario' => 140.00],
            ['id_cotizacion' => $idCotAurelio, 'tipo_item' => 'paquete', 'id_referencia' => 21,
             'descripcion' => 'Cuadro Brillante color Beige',
             'cantidad' => 24, 'precio_unitario' => 140.00],
            ['id_cotizacion' => $idCotAurelio, 'tipo_item' => 'personalizado', 'id_referencia' => null,
             'descripcion' => '[Cortesía] Cuadro docente',
             'cantidad' => 1, 'precio_unitario' => 0.00],
        ]);

        // ── Prolog Primaria 6to A ──
        // 22×160=3520 | llaveros incluidos en precio  → total 3520
        $db->table('cotizaciones_detalles')->insertBatch([
            ['id_cotizacion' => $idCotProlog, 'tipo_item' => 'paquete', 'id_referencia' => 26,
             'descripcion' => 'Anuario Big Premium (5 hojas - 10 páginas)',
             'cantidad' => 22, 'precio_unitario' => 160.00],
            ['id_cotizacion' => $idCotProlog, 'tipo_item' => 'personalizado', 'id_referencia' => null,
             'descripcion' => '[Cortesía] Anuario llavero 5x5',
             'cantidad' => 22, 'precio_unitario' => 0.00],
        ]);

        // ──────────────────────────────────────────────────────────────────────
        // 5. PROMOCIONES ESCOLARES
        // ──────────────────────────────────────────────────────────────────────
        $db->table('promociones_escolares')->insert([
            'id_colegio'      => 9,  // Alexander Von Humbolt
            'id_cotizacion'   => $idCotHumboldt,
            'nombre'          => 'Humboldt Secundaria – Pisco',
            'grado'           => 'Secundaria',
            'seccion'         => null,
            'num_estudiantes' => 37,
            'anio'            => 2026,
            'is_active'       => 1,
        ]);

        $db->table('promociones_escolares')->insert([
            'id_colegio'      => $idDonald,
            'id_cotizacion'   => $idCotDonald,
            'nombre'          => 'Donald Scarrow Primaria 6to',
            'grado'           => 'Primaria',
            'seccion'         => '6to',
            'num_estudiantes' => 21,
            'anio'            => 2026,
            'is_active'       => 1,
        ]);

        $db->table('promociones_escolares')->insert([
            'id_colegio'      => $idAurelio,
            'id_cotizacion'   => $idCotAurelio,
            'nombre'          => 'Aurelio Moisés Flores Primaria 6to B',
            'grado'           => 'Primaria',
            'seccion'         => '6to B',
            'num_estudiantes' => 25,
            'anio'            => 2026,
            'is_active'       => 1,
        ]);

        $db->table('promociones_escolares')->insert([
            'id_colegio'      => 8,  // I.E. PROLOG
            'id_cotizacion'   => $idCotProlog,
            'nombre'          => 'Prolog Primaria 6to A',
            'grado'           => 'Primaria',
            'seccion'         => '6to A',
            'num_estudiantes' => 22,
            'anio'            => 2026,
            'is_active'       => 1,
        ]);

        // ──────────────────────────────────────────────────────────────────────
        // 6. CONTRATOS  (estado ACTIVO)
        // ──────────────────────────────────────────────────────────────────────
        $db->table('contratos')->insert([
            'id_cotizacion'  => $idCotHumboldt,
            'fecha_creacion' => '2026-05-22',
            'adelanto'       => 2000.00,
            'total'          => 8500.00,
            'observaciones'  => 'Contacto: Luis Medina Mendoza - 950526787. Sesión 1: 16 Jun (uniforme y toga). Sesión 2: 14 Jul (ropa sports, Paracas).',
            'estado'         => 'ACTIVO',
        ]);
        $idContratoHumboldt = $db->insertID();

        $db->table('contratos')->insert([
            'id_cotizacion'  => $idCotDonald,
            'fecha_creacion' => '2026-04-30',
            'adelanto'       => 200.00,
            'total'          => 3150.00,
            'observaciones'  => 'Contacto: Sra Lily - 934580227. Sesión 1: primera semana de junio. Pagos: Jun S/950, Set S/1000, Nov S/1000.',
            'estado'         => 'ACTIVO',
        ]);
        $idContratoDonald = $db->insertID();

        $db->table('contratos')->insert([
            'id_cotizacion'  => $idCotAurelio,
            'fecha_creacion' => '2026-05-20',
            'adelanto'       => 750.00,
            'total'          => 3500.00,
            'observaciones'  => 'Contacto: Miss Martha García - 986773245. Sesión 1: primera semana de junio.',
            'estado'         => 'ACTIVO',
        ]);
        $idContratoAurelio = $db->insertID();

        $db->table('contratos')->insert([
            'id_cotizacion'  => $idCotProlog,
            'fecha_creacion' => '2026-04-30',
            'adelanto'       => 704.00,
            'total'          => 3520.00,
            'observaciones'  => 'Contacto: Mayra Cucho - 910148593. Pagos: Adelanto 20%, Jun 20%, Set 40%, Nov 20%.',
            'estado'         => 'ACTIVO',
        ]);
        $idContratoProlog = $db->insertID();

        // ──────────────────────────────────────────────────────────────────────
        // 7. PAGOS  (adelantos — forma de pago Efectivo id=1)
        // ──────────────────────────────────────────────────────────────────────
        $db->table('pagos')->insertBatch([
            ['id_contrato' => $idContratoHumboldt, 'id_form_pago' => 1, 'monto' => 2000.00, 'moneda' => 'PEN', 'fecha' => '2026-05-22'],
            ['id_contrato' => $idContratoDonald,   'id_form_pago' => 1, 'monto' => 200.00,  'moneda' => 'PEN', 'fecha' => '2026-04-30'],
            ['id_contrato' => $idContratoAurelio,  'id_form_pago' => 1, 'monto' => 750.00,  'moneda' => 'PEN', 'fecha' => '2026-05-20'],
            ['id_contrato' => $idContratoProlog,   'id_form_pago' => 1, 'monto' => 704.00,  'moneda' => 'PEN', 'fecha' => '2026-04-30'],
        ]);
    }
}
