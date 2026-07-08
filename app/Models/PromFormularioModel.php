<?php

namespace App\Models;

use CodeIgniter\Model;

class PromFormularioModel extends Model
{
    protected $table            = 'prom_formularios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'alumno_id', 'nombre_alumno', 'fecha_nacimiento', 'color_favorito',
        'profesion_futura', 'nombre_tutor', 'relacion_tutor', 'telefono', 'email',
        'tiene_cuadro', 'tiene_anuario', 'anuario_modelo',
        'acepta_imagenes', 'acepta_datos', 'ip_address', 'created_at',
    ];

    protected $useTimestamps = false;

    public function porPromocion(int $promocion_id): array
    {
        $db = \Config\Database::connect();

        return $db->table('prom_formularios f')
            ->select('f.*, a.nombre AS nombre_alumno_lista, a.token, p.nombre AS nombre_promocion, c.nombre_colegio')
            ->join('prom_alumnos a',       'a.id = f.alumno_id',             'inner')
            ->join('prom_promociones p',   'p.id = a.promocion_id',          'inner')
            ->join('colegios c',           'c.id_colegio = p.colegio_id',    'left')
            ->where('a.promocion_id', $promocion_id)
            ->orderBy('a.nombre', 'ASC')
            ->get()
            ->getResultArray();
    }
}
