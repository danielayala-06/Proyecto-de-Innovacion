<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Convierte imágenes de paquetes (PNG/JPG) a WebP usando GD.
 * Procesa tanto las referenciadas en BD como las huérfanas en disco.
 *
 * Uso: php spark imagenes:convertir
 */
class ConvertirImagenes extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'imagenes:convertir';
    protected $description = 'Convierte imágenes de paquetes (PNG/JPG) a WebP y actualiza la BD.';

    private const IMG_DIR = 'images/paquetes/';
    private const CALIDAD = 82;
    private const MAX_W   = 720;
    private const MAX_H   = 480;

    public function run(array $params)
    {
        if (!function_exists('imagecreatefromjpeg')) {
            CLI::error('La extensión GD no está disponible. Habilítala en php.ini primero.');
            return;
        }

        $db  = \Config\Database::connect();
        $dir = FCPATH . self::IMG_DIR;
        $ok  = 0;
        $err = 0;

        // ── 1. Imágenes referenciadas en BD ────────────────────────────────────
        $filas = $db->table('paquetes')
                    ->select('id_paquete, imagen')
                    ->where('imagen IS NOT NULL')
                    ->where("imagen != ''")
                    ->where("imagen NOT LIKE '%.webp'")
                    ->get()->getResultArray();

        if ($filas) {
            CLI::write('── BD: ' . count($filas) . ' imagen(es) referenciadas pendientes ──', 'yellow');
            foreach ($filas as $fila) {
                $id     = (int) $fila['id_paquete'];
                $origen = $dir . $fila['imagen'];
                if (!is_file($origen)) {
                    CLI::write("  [SKIP] paq_{$id} — no existe en disco", 'yellow');
                    continue;
                }
                $destino = $dir . "paq_{$id}.webp";
                if ($this->_convertir($origen, $destino)) {
                    $db->table('paquetes')->where('id_paquete', $id)->update(['imagen' => "paq_{$id}.webp"]);
                    $ok++;
                } else {
                    $err++;
                }
            }
        } else {
            CLI::write('── BD: sin imágenes pendientes ──', 'green');
        }

        // ── 2. Huérfanos en disco (PNG/JPG sin referencia en BD) ───────────────
        $extensiones = ['png', 'jpg', 'jpeg'];
        $huerfanos   = [];
        foreach ($extensiones as $ext) {
            foreach (glob($dir . "*.$ext") ?: [] as $ruta) {
                $nombre = basename($ruta);
                $huerfanos[$ruta] = $nombre;
            }
        }

        // Excluir los que YA fueron procesados por el bloque anterior
        $procesados = array_column($filas ?? [], 'imagen');
        $huerfanos  = array_filter($huerfanos, fn($n) => !in_array($n, $procesados));

        if ($huerfanos) {
            CLI::write('── Disco: ' . count($huerfanos) . ' archivo(s) huérfano(s) ──', 'yellow');
            foreach ($huerfanos as $origen => $nombre) {
                // Inferir nombre destino: mismo nombre base con .webp
                $base    = preg_replace('/\.[^.]+$/', '', $nombre);
                $destino = $dir . $base . '.webp';
                if ($this->_convertir($origen, $destino)) {
                    $ok++;
                } else {
                    $err++;
                }
            }
        } else {
            CLI::write('── Disco: sin huérfanos pendientes ──', 'green');
        }

        CLI::write("\nTotal: {$ok} convertida(s), {$err} error(es).", $err ? 'yellow' : 'green');
    }

    private function _convertir(string $origen, string $destino): bool
    {
        $nombre = basename($origen);
        try {
            $mime = mime_content_type($origen);
            $src  = match ($mime) {
                'image/jpeg' => imagecreatefromjpeg($origen),
                'image/png'  => imagecreatefrompng($origen),
                'image/webp' => imagecreatefromwebp($origen),
                default      => throw new \RuntimeException("MIME no soportado: {$mime}"),
            };

            if (!$src) {
                throw new \RuntimeException('No se pudo leer la imagen');
            }

            $w     = imagesx($src);
            $h     = imagesy($src);
            $ratio = min(self::MAX_W / $w, self::MAX_H / $h, 1.0);
            $nw    = (int) round($w * $ratio);
            $nh    = (int) round($h * $ratio);

            $dst    = imagecreatetruecolor($nw, $nh);
            $blanco = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $blanco);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagewebp($dst, $destino, self::CALIDAD);

            if (realpath($origen) !== realpath($destino) && is_file($origen)) {
                unlink($origen);
            }

            CLI::write("  [OK]   {$nombre} → " . basename($destino) . " ({$w}×{$h} → {$nw}×{$nh})", 'green');
            return true;

        } catch (\Throwable $e) {
            CLI::write("  [ERR]  {$nombre} — " . $e->getMessage(), 'red');
            return false;
        }
    }
}
