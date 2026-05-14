<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DatabaseSeeder — Ejecuta todos los seeders en orden respetando las FK.
 *
 * Uso:
 *   php spark db:seed DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ── Tablas independientes (sin FK) ──────────────────────────────────
        $this->call('PersonasSeeder');
        $this->call('RolesSeeder');
        $this->call('PermisosSeeder');
        $this->call('FormasPagoSeeder');
        $this->call('ColegiosSeeder');
        $this->call('ProductosSeeder');
        $this->call('PaquetesSeeder');

        // ── Tablas que dependen de las anteriores ───────────────────────────
        $this->call('UsuariosSeeder');       // depende de: personas, roles
        $this->call('ClientesSeeder');       // depende de: personas
        $this->call('RolesPermisosSeeder');  // depende de: roles, permisos
        $this->call('PaquetesProductosSeeder'); // depende de: paquetes, productos
        $this->call('ReglasPaquetesSeeder');    // depende de: paquetes
        $this->call('PaquetesSesionesSeeder');  // depende de: paquetes

        // ── Cotizaciones y su árbol ──────────────────────────────────────────
        $this->call('CotizacionesSeeder');        // depende de: clientes, usuarios
        $this->call('PromocionesEscolaresSeeder'); // depende de: colegios, cotizaciones
        $this->call('CotizacionesDetallesSeeder'); // depende de: cotizaciones

        // ── Contratos, pagos y sesiones ──────────────────────────────────────
        $this->call('ContratosSeeder');            // depende de: cotizaciones
        $this->call('PagosSeeder');                // depende de: contratos, formas_pago
        $this->call('SesionesFotograficasSeeder'); // depende de: promociones_escolares

        // ── Estudiantes ──────────────────────────────────────────────────────
        $this->call('ApoderadosSeeder');   // depende de: personas
        $this->call('EstudiantesSeeder');  // depende de: apoderados, promociones_escolares
    }
}
