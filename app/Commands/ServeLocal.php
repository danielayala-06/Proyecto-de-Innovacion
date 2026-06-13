<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ServeLocal extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'serve:local';
    protected $description = 'Inicia el servidor en la IP local de red detectada automáticamente.';
    protected $usage       = 'serve:local [--port <número>]';
    protected $options     = [
        '--port' => 'Puerto del servidor (por defecto: 8080)',
    ];

    public function run(array $params): void
    {
        $port = (int) (CLI::getOption('port') ?? $params[0] ?? 8080);
        $ip   = $this->detectarIP();

        if ($ip === null) {
            CLI::error('No se pudo detectar la IP local de red.');
            exit(EXIT_ERROR);
        }

        $this->actualizarEnv($ip, $port);

        CLI::write('');
        CLI::write('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'yellow');
        CLI::write('  IP local  : ' . CLI::color($ip, 'green'));
        CLI::write('  Puerto    : ' . CLI::color((string) $port, 'green'));
        CLI::write('  URL       : ' . CLI::color("http://{$ip}:{$port}/", 'cyan'));
        CLI::write('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'yellow');
        CLI::write('');

        $spark = escapeshellarg(ROOTPATH . 'spark');
        $php   = escapeshellarg(PHP_BINARY);

        passthru("{$php} {$spark} serve --host {$ip} --port {$port}");
    }

    private function detectarIP(): ?string
    {
        // Método 1: socket UDP (no envía datos, solo consulta qué interfaz usa el SO)
        // Funciona en Linux y Windows si la extensión sockets está habilitada.
        if (extension_loaded('sockets')) {
            $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($sock !== false) {
                @socket_connect($sock, '8.8.8.8', 80);
                @socket_getsockname($sock, $ip);
                socket_close($sock);
                if (!empty($ip) && $ip !== '0.0.0.0' && $ip !== '127.0.0.1') {
                    return $ip;
                }
            }
        }

        // Método 2: gethostbyname — siempre disponible, sin extensiones
        $ip = gethostbyname(gethostname() ?: '');
        if (filter_var($ip, FILTER_VALIDATE_IP) && $ip !== '127.0.0.1') {
            return $ip;
        }

        return null;
    }

    private function actualizarEnv(string $ip, int $port): void
    {
        $envPath = ROOTPATH . '.env';
        if (! is_file($envPath)) {
            return;
        }

        $baseURL = "http://{$ip}:{$port}/";
        $content = file_get_contents($envPath);

        if (preg_match('/^app\.baseURL\s*=/m', $content)) {
            $content = preg_replace(
                '/^app\.baseURL\s*=.*/m',
                "app.baseURL = '{$baseURL}'",
                $content
            );
        } else {
            $content .= "\napp.baseURL = '{$baseURL}'\n";
        }

        file_put_contents($envPath, $content);
    }
}
