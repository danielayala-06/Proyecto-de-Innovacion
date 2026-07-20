<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DatabaseSeeder — Ejecuta todos los seeders en orden respetando las FK.
 *
 * Uso:
 *   php spark migrate:fresh && php spark db:seed DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ── Tablas independientes (sin FK) ──────────────────────────────────
        $this->call('PersonasSeeder');
        $this->call('RolesSeeder');
        $this->call('PermisosSeeder');
        $this->call('ColegiosSeeder');
        $this->call('ProductosSeeder');
        $this->call('PaquetesSeeder');

        // ── Tablas que dependen de las anteriores ───────────────────────────
        $this->call('UsuariosSeeder');          // depende de: personas, roles
        $this->call('ClientesSeeder');          // depende de: personas
        $this->call('RolesPermisosSeeder');     // depende de: roles, permisos
        $this->call('PaquetesProductosSeeder'); // depende de: paquetes, productos
        $this->call('PaquetesSesionesSeeder');  // depende de: paquetes

        // ── Contratos reales 2026 ────────────────────────────────────────────
        // Incluye: clientes reales, cotizaciones, detalles, promociones_escolares,
        //          contratos, pagos, sesiones_fotograficas, apoderados, estudiantes.
        $this->call('ContratosRealSeeder');

        // ── Formularios de promoción escolar (sistema de tokens) ─────────────
        $this->call('PromPromocionesSeeder'); // depende de: colegios
        $this->call('PromAlumnosSeeder');     // depende de: prom_promociones
    }
}
