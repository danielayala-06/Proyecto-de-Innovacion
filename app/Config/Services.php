<?php

namespace Config;

use App\Services\Clientes\ClienteService;
use App\Services\Contratos\ContratoService;
use App\Services\Cotizaciones\CotizacionService;
use App\Services\Estudiantes\EstudianteService;
use App\Services\Pagos\PagoService;
use App\Services\Paquetes\PaqueteService;
use App\Services\Promociones\PromocionService;
use App\Services\Sesiones\SesionService;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function clienteService(bool $getShared = true): ClienteService
    {
        if ($getShared) return static::getSharedInstance('clienteService');
        return new ClienteService();
    }

    public static function contratoService(bool $getShared = true): ContratoService
    {
        if ($getShared) return static::getSharedInstance('contratoService');
        return new ContratoService();
    }

    public static function cotizacionService(bool $getShared = true): CotizacionService
    {
        if ($getShared) return static::getSharedInstance('cotizacionService');
        return new CotizacionService();
    }

    public static function estudianteService(bool $getShared = true): EstudianteService
    {
        if ($getShared) return static::getSharedInstance('estudianteService');
        return new EstudianteService();
    }

    public static function pagoService(bool $getShared = true): PagoService
    {
        if ($getShared) return static::getSharedInstance('pagoService');
        return new PagoService();
    }

    public static function paqueteService(bool $getShared = true): PaqueteService
    {
        if ($getShared) return static::getSharedInstance('paqueteService');
        return new PaqueteService();
    }

    public static function promocionService(bool $getShared = true): PromocionService
    {
        if ($getShared) return static::getSharedInstance('promocionService');
        return new PromocionService();
    }

    public static function sesionService(bool $getShared = true): SesionService
    {
        if ($getShared) return static::getSharedInstance('sesionService');
        return new SesionService();
    }
}
