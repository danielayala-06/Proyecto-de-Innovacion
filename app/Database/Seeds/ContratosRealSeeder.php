<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Datos reales extraídos de contratos físicos 2026:
 *   026-0013  I.E. Alexander Von Humbolt – Pisco  (22/05/2026)
 *   026-0003  I.E. Donald Scarrow – Primaria 6to   (30/04/2026)
 *   026-0012  I.E. Aurelio Moisés Flores – 6to B   (20/05/2026)
 *   026-0003  I.E. Prolog – Primaria 6to A          (30/04/2026)
 *
 * Ejecutar siempre DESPUÉS de:
 *   ColegiosSeeder, PaquetesSeeder, UsuariosSeeder
 */
class ContratosRealSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        // ── 1. Colegios (buscados por nombre para evitar IDs hardcodeados) ───
        $idProlog    = $this->idColegio($db, 'I.E. PROLOG');
        $idAurelio   = $this->idColegio($db, 'I.E. AURELIO MOISÈS FLORES');
        $idDonald    = $this->idColegio($db, 'I.E. DONALD SCARROW');
        $idHumboldt  = $this->idColegio($db, 'I.E. ALEXANDER VON HUMBOLT');

        // ── 2. Vendedora ──────────────────────────────────────────────────────
        $row = $db->table('usuarios')->where('nombre_user', 'maria.ventas')->get()->getRow();
        if (!$row) {
            throw new \RuntimeException('Usuario maria.ventas no encontrado. Ejecuta UsuariosSeeder primero.');
        }
        $idVendedor = (int) $row->id_usuario;

        // ── 3. Paquetes (buscados por nombre) ────────────────────────────────
        $idPaqCuadroBrillante    = $this->idPaquete($db, 'Cuadro Brillante');
        $idPaqAnuarioBig         = $this->idPaquete($db, 'Anuario Big Primaria');
        $idPaqAnuarioBigPremium  = $this->idPaquete($db, 'Anuario Big Premium Primaria');

        // ── 4. Personas · Clientes · Apoderados ──────────────────────────────
        //    El contacto del contrato sirve también como apoderado de referencia.
        //    Los DNI son placeholder; actualizar cuando el cliente facilite los datos.

        // Humboldt — Luis Medina Mendoza
        $db->table('personas')->insert([
            'nombres'          => 'Luis',
            'apellidos'        => 'Medina Mendoza',
            'telefono'         => '950526787',
            'tipo_documento'   => 'DNI',
            'numero_documento' => 'PEND-HUM-001',
        ]);
        $idPersonaHumboldt = $db->insertID();
        $db->table('clientes')->insert(['id_persona' => $idPersonaHumboldt, 'metodo_comunicacion' => 'whatsapp', 'acepta_promociones' => 0]);
        $idClienteHumboldt = $db->insertID();
        $db->table('apoderados')->insert(['id_persona' => $idPersonaHumboldt, 'tipo_relacion' => 'otro']);
        $idApoHumboldt = $db->insertID();

        // Donald Scarrow — Sra. Lily
        $db->table('personas')->insert([
            'nombres'          => 'Lily',
            'apellidos'        => null,
            'telefono'         => '934580227',
            'tipo_documento'   => 'DNI',
            'numero_documento' => 'PEND-DON-001',
        ]);
        $idPersonaDonald = $db->insertID();
        $db->table('clientes')->insert(['id_persona' => $idPersonaDonald, 'metodo_comunicacion' => 'whatsapp', 'acepta_promociones' => 0]);
        $idClienteDonald = $db->insertID();
        $db->table('apoderados')->insert(['id_persona' => $idPersonaDonald, 'tipo_relacion' => 'otro']);
        $idApoDonald = $db->insertID();

        // Aurelio Moisés Flores — Miss Martha García
        $db->table('personas')->insert([
            'nombres'          => 'Martha',
            'apellidos'        => 'García',
            'telefono'         => '986773245',
            'tipo_documento'   => 'DNI',
            'numero_documento' => 'PEND-AUR-001',
        ]);
        $idPersonaAurelio = $db->insertID();
        $db->table('clientes')->insert(['id_persona' => $idPersonaAurelio, 'metodo_comunicacion' => 'whatsapp', 'acepta_promociones' => 0]);
        $idClienteAurelio = $db->insertID();
        $db->table('apoderados')->insert(['id_persona' => $idPersonaAurelio, 'tipo_relacion' => 'otro']);
        $idApoAurelio = $db->insertID();

        // Prolog — Mayra Cucho
        $db->table('personas')->insert([
            'nombres'          => 'Mayra',
            'apellidos'        => 'Cucho',
            'telefono'         => '910148593',
            'tipo_documento'   => 'DNI',
            'numero_documento' => 'PEND-PRO-001',
        ]);
        $idPersonaProlog = $db->insertID();
        $db->table('clientes')->insert(['id_persona' => $idPersonaProlog, 'metodo_comunicacion' => 'whatsapp', 'acepta_promociones' => 0]);
        $idClienteProlog = $db->insertID();
        $db->table('apoderados')->insert(['id_persona' => $idPersonaProlog, 'tipo_relacion' => 'otro']);
        $idApoProlog = $db->insertID();

        // ── 5. Cotizaciones (APROBADA — contrato ya emitido) ─────────────────

        $db->table('cotizaciones')->insert([
            'id_cliente'     => $idClienteHumboldt,
            'id_usuario'     => $idVendedor,
            'fecha_registro' => '2026-05-22',
            'total_estimado' => 8500.00,
            'estado'         => 'APROBADA',
        ]);
        $idCotHumboldt = $db->insertID();

        $db->table('cotizaciones')->insert([
            'id_cliente'     => $idClienteDonald,
            'id_usuario'     => $idVendedor,
            'fecha_registro' => '2026-04-30',
            'total_estimado' => 3150.00,
            'estado'         => 'APROBADA',
        ]);
        $idCotDonald = $db->insertID();

        $db->table('cotizaciones')->insert([
            'id_cliente'     => $idClienteAurelio,
            'id_usuario'     => $idVendedor,
            'fecha_registro' => '2026-05-20',
            'total_estimado' => 3500.00,
            'estado'         => 'APROBADA',
        ]);
        $idCotAurelio = $db->insertID();

        $db->table('cotizaciones')->insert([
            'id_cliente'     => $idClienteProlog,
            'id_usuario'     => $idVendedor,
            'fecha_registro' => '2026-04-30',
            'total_estimado' => 3520.00,
            'estado'         => 'APROBADA',
        ]);
        $idCotProlog = $db->insertID();

        // ── 6. Detalles de cotizaciones ───────────────────────────────────────

        // Humboldt – Secundaria.
        // 37 × S/200 anuario personalizado = S/7 400
        // + Ceremonia (4 dic) S/500 + Fiesta prom (19 dic) S/500 + Última Campanada S/100 = S/1 100
        // Total: S/8 500
        $db->table('cotizaciones_detalles')->insertBatch([
            [
                'id_cotizacion'   => $idCotHumboldt,
                'tipo_item'       => 'personalizado',
                'id_referencia'   => null,
                'descripcion'     => 'Anuario 25x25 (5 hojas - 10 páginas)',
                'cantidad'        => 37,
                'precio_unitario' => 200.00,
            ],
            [
                'id_cotizacion'   => $idCotHumboldt,
                'tipo_item'       => 'personalizado',
                'id_referencia'   => null,
                'descripcion'     => '[Cortesía] Anuario 25x25 tutor',
                'cantidad'        => 1,
                'precio_unitario' => 0.00,
            ],
            [
                'id_cotizacion'   => $idCotHumboldt,
                'tipo_item'       => 'personalizado',
                'id_referencia'   => null,
                'descripcion'     => 'Ceremonia de Graduación (foto y video) - 4 dic',
                'cantidad'        => 1,
                'precio_unitario' => 500.00,
            ],
            [
                'id_cotizacion'   => $idCotHumboldt,
                'tipo_item'       => 'personalizado',
                'id_referencia'   => null,
                'descripcion'     => 'Fiesta de Promoción (foto y video) - 19 dic',
                'cantidad'        => 1,
                'precio_unitario' => 500.00,
            ],
            [
                'id_cotizacion'   => $idCotHumboldt,
                'tipo_item'       => 'personalizado',
                'id_referencia'   => null,
                'descripcion'     => 'Última Campanada (Reel) - 18 dic + USB con fotos y video',
                'cantidad'        => 1,
                'precio_unitario' => 100.00,
            ],
        ]);

        // Donald Scarrow – Primaria 6to.
        // 14 × Anuario Big @ S/140 = S/1 960
        // 7  × Cuadro Brillante @ S/140 = S/980
        // 1  × 2 Sesiones fotográficas = S/210
        // Cortesía docente = S/0
        // Total: S/3 150
        $db->table('cotizaciones_detalles')->insertBatch([
            [
                'id_cotizacion'   => $idCotDonald,
                'tipo_item'       => 'paquete',
                'id_referencia'   => $idPaqAnuarioBig,
                'descripcion'     => 'Anuario Big 25x25 (5 hojas - 10 páginas)',
                'cantidad'        => 14,
                'precio_unitario' => 140.00,
            ],
            [
                'id_cotizacion'   => $idCotDonald,
                'tipo_item'       => 'paquete',
                'id_referencia'   => $idPaqCuadroBrillante,
                'descripcion'     => 'Cuadro Brillante',
                'cantidad'        => 7,
                'precio_unitario' => 140.00,
            ],
            [
                'id_cotizacion'   => $idCotDonald,
                'tipo_item'       => 'personalizado',
                'id_referencia'   => null,
                'descripcion'     => '[Cortesía] Anuario Big 25x25 docente',
                'cantidad'        => 1,
                'precio_unitario' => 0.00,
            ],
            [
                'id_cotizacion'   => $idCotDonald,
                'tipo_item'       => 'personalizado',
                'id_referencia'   => null,
                'descripcion'     => '2 Sesiones fotográficas',
                'cantidad'        => 1,
                'precio_unitario' => 210.00,
            ],
        ]);

        // Aurelio Moisés Flores – Primaria 6to B.
        // 1  × Anuario Big @ S/140 = S/140
        // 24 × Cuadro Brillante color Beige @ S/140 = S/3 360
        // Cortesía docente = S/0
        // Total: S/3 500
        $db->table('cotizaciones_detalles')->insertBatch([
            [
                'id_cotizacion'   => $idCotAurelio,
                'tipo_item'       => 'paquete',
                'id_referencia'   => $idPaqAnuarioBig,
                'descripcion'     => 'Anuario Big 25x25 (5 hojas - 10 páginas)',
                'cantidad'        => 1,
                'precio_unitario' => 140.00,
            ],
            [
                'id_cotizacion'   => $idCotAurelio,
                'tipo_item'       => 'paquete',
                'id_referencia'   => $idPaqCuadroBrillante,
                'descripcion'     => 'Cuadro Brillante color Beige',
                'cantidad'        => 24,
                'precio_unitario' => 140.00,
            ],
            [
                'id_cotizacion'   => $idCotAurelio,
                'tipo_item'       => 'personalizado',
                'id_referencia'   => null,
                'descripcion'     => '[Cortesía] Cuadro docente',
                'cantidad'        => 1,
                'precio_unitario' => 0.00,
            ],
        ]);

        // Prolog – Primaria 6to A.
        // 22 × Anuario Big Premium @ S/160 = S/3 520  (precio negociado; catálogo S/210)
        // Cortesía 22 llaveros 5x5 = S/0
        // Total: S/3 520
        $db->table('cotizaciones_detalles')->insertBatch([
            [
                'id_cotizacion'   => $idCotProlog,
                'tipo_item'       => 'paquete',
                'id_referencia'   => $idPaqAnuarioBigPremium,
                'descripcion'     => 'Anuario Big Premium (5 hojas - 10 páginas)',
                'cantidad'        => 22,
                'precio_unitario' => 160.00,
            ],
            [
                'id_cotizacion'   => $idCotProlog,
                'tipo_item'       => 'personalizado',
                'id_referencia'   => null,
                'descripcion'     => '[Cortesía] Anuario llavero 5x5',
                'cantidad'        => 22,
                'precio_unitario' => 0.00,
            ],
        ]);

        // ── 7. Promociones Escolares ──────────────────────────────────────────

        $db->table('promociones_escolares')->insert([
            'id_colegio'      => $idHumboldt,
            'id_cotizacion'   => $idCotHumboldt,
            'nombre'          => 'Humboldt Secundaria – Pisco',
            'grado'           => 'Secundaria',
            'seccion'         => null,
            'num_estudiantes' => 37,
            'anio'            => 2026,
            'is_active'       => 1,
        ]);
        $idPromoHumboldt = $db->insertID();

        $db->table('promociones_escolares')->insert([
            'id_colegio'      => $idDonald,
            'id_cotizacion'   => $idCotDonald,
            'nombre'          => 'Donald Scarrow Primaria 6to',
            'grado'           => 'Primaria',
            'seccion'         => null,
            'num_estudiantes' => 21,
            'anio'            => 2026,
            'is_active'       => 1,
        ]);
        $idPromoDonald = $db->insertID();

        $db->table('promociones_escolares')->insert([
            'id_colegio'      => $idAurelio,
            'id_cotizacion'   => $idCotAurelio,
            'nombre'          => 'Aurelio Moisés Flores Primaria 6to B',
            'grado'           => 'Primaria',
            'seccion'         => 'B',
            'num_estudiantes' => 25,
            'anio'            => 2026,
            'is_active'       => 1,
        ]);
        $idPromoAurelio = $db->insertID();

        $db->table('promociones_escolares')->insert([
            'id_colegio'      => $idProlog,
            'id_cotizacion'   => $idCotProlog,
            'nombre'          => 'Prolog Primaria 6to A',
            'grado'           => 'Primaria',
            'seccion'         => 'A',
            'num_estudiantes' => 22,
            'anio'            => 2026,
            'is_active'       => 1,
        ]);
        $idPromoProlog = $db->insertID();

        // ── 8. Contratos (estado ACTIVO) ──────────────────────────────────────

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
            'observaciones'  => 'Contacto: Sra. Lily - 934580227. Sesión 1: primera semana de junio. Pagos: Jun S/950, Set S/1000, Nov S/1000.',
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
            'observaciones'  => 'Contacto: Mayra Cucho - 910148593. Plan de pagos: Adelanto 20%, Jun 20%, Set 40%, Nov 20%.',
            'estado'         => 'ACTIVO',
        ]);
        $idContratoProlog = $db->insertID();

        // ── 9. Pagos ──────────────────────────────────────────────────────────
        //    Por ahora solo los adelantos iniciales (pagos al firmar).
        //    Los abonos siguientes se registran en el sistema a medida que se reciben.

        $db->table('pagos')->insertBatch([
            [
                'id_contrato' => $idContratoHumboldt,
                'fecha'       => '2026-05-22',
                'monto'       => 2000.00,
                'moneda'      => 'PEN',
                'forma_pago'  => 'Efectivo',
                'voucher'     => null,
            ],
            [
                'id_contrato' => $idContratoDonald,
                'fecha'       => '2026-04-30',
                'monto'       => 200.00,
                'moneda'      => 'PEN',
                'forma_pago'  => 'Efectivo',
                'voucher'     => null,
            ],
            [
                'id_contrato' => $idContratoAurelio,
                'fecha'       => '2026-05-20',
                'monto'       => 750.00,
                'moneda'      => 'PEN',
                'forma_pago'  => 'Efectivo',
                'voucher'     => null,
            ],
            [
                'id_contrato' => $idContratoProlog,
                'fecha'       => '2026-04-30',
                'monto'       => 704.00,
                'moneda'      => 'PEN',
                'forma_pago'  => 'Efectivo',
                'voucher'     => null,
            ],
        ]);

        // ── 10. Sesiones fotográficas ─────────────────────────────────────────
        //    Humboldt: fechas reales del contrato.
        //    Donald y Aurelio: primera semana de junio (horas distintas para evitar
        //    conflicto global de horario en el backend).
        //    Prolog: sin fecha acordada → sesión pendiente con fecha estimada.

        $db->table('sesiones_fotograficas')->insertBatch([
            [
                'id_promocion'      => $idPromoHumboldt,
                'fecha_hora_sesion' => '2026-06-16 08:00:00',
                'tipo'              => 'colegio',
                'observaciones'     => 'Uniforme y toga.',
                'estado'            => 'finalizado',
            ],
            [
                'id_promocion'      => $idPromoHumboldt,
                'fecha_hora_sesion' => '2026-07-14 08:00:00',
                'tipo'              => 'exteriores',
                'observaciones'     => 'Ropa sports – Paracas.',
                'estado'            => 'pendiente',
            ],
            [
                'id_promocion'      => $idPromoDonald,
                'fecha_hora_sesion' => '2026-06-05 09:00:00',
                'tipo'              => 'colegio',
                'observaciones'     => null,
                'estado'            => 'finalizado',
            ],
            [
                'id_promocion'      => $idPromoAurelio,
                'fecha_hora_sesion' => '2026-06-05 11:00:00',
                'tipo'              => 'colegio',
                'observaciones'     => null,
                'estado'            => 'finalizado',
            ],
            [
                'id_promocion'      => $idPromoProlog,
                'fecha_hora_sesion' => '2026-08-08 08:00:00',
                'tipo'              => 'colegio',
                'observaciones'     => 'Fecha por confirmar con el colegio.',
                'estado'            => 'pendiente',
            ],
        ]);

        // ── 11. Estudiantes (placeholder) ────────────────────────────────────
        //    Los nombres reales deben cargarse desde el formulario del apoderado
        //    o actualizarse en el panel de administración.

        $this->crearEstudiantes($db, $idPromoHumboldt, $idApoHumboldt, 37);
        $this->crearEstudiantes($db, $idPromoDonald,   $idApoDonald,   21);
        $this->crearEstudiantes($db, $idPromoAurelio,  $idApoAurelio,  25);
        $this->crearEstudiantes($db, $idPromoProlog,   $idApoProlog,   22);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function idColegio(\CodeIgniter\Database\ConnectionInterface $db, string $nombre): int
    {
        $row = $db->table('colegios')->where('nombre_colegio', $nombre)->get()->getRow();
        if (!$row) {
            throw new \RuntimeException("Colegio no encontrado: \"{$nombre}\". Ejecuta ColegiosSeeder primero.");
        }
        return (int) $row->id_colegio;
    }

    private function idPaquete(\CodeIgniter\Database\ConnectionInterface $db, string $nombre): int
    {
        $row = $db->table('paquetes')
            ->where('nombre_paquete', $nombre)
            ->where('nivel_disponible', 'primaria')
            ->get()->getRow();
        if (!$row) {
            throw new \RuntimeException("Paquete primaria no encontrado: \"{$nombre}\". Ejecuta PaquetesSeeder primero.");
        }
        return (int) $row->id_paquete;
    }

    private function crearEstudiantes(
        \CodeIgniter\Database\ConnectionInterface $db,
        int $idPromocion,
        int $idApoderado,
        int $total
    ): void {
        for ($i = 1; $i <= $total; $i++) {
            $db->table('estudiantes')->insert([
                'id_apoderado' => $idApoderado,
                'id_promocion' => $idPromocion,
                'nombres'      => sprintf('Alumno%02d', $i),
                'apellidos'    => 'Pendiente',
            ]);
        }
    }
}
