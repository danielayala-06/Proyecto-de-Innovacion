<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DocsCode extends BaseCommand
{
    protected $group       = 'Docs';
    protected $name        = 'docs:code';
    protected $description = 'Genera documentación HTML del código fuente (phpDocumentor)';
    protected $usage       = 'docs:code';

    public function run(array $params): void
    {
        $phar   = ROOTPATH . 'tools/phpDocumentor.phar';
        $config = ROOTPATH . 'phpdoc.xml';
        $output = ROOTPATH . 'build/docs/code/';

        if (!is_file($phar)) {
            CLI::error('No se encuentra tools/phpDocumentor.phar');
            CLI::write('Descargalo con: wget https://phpdoc.org/phpDocumentor.phar -O tools/phpDocumentor.phar');
            return;
        }

        CLI::write('Generando documentación del código fuente...', 'cyan');
        CLI::write('(Esto puede tardar unos segundos)');
        CLI::newLine();

        chdir(ROOTPATH);
        passthru("php {$phar} --config={$config}", $exitCode);

        CLI::newLine();

        if ($exitCode === 0) {
            CLI::write('Documentación generada en:', 'green');
            CLI::write('  ' . $output . 'index.html');
        } else {
            CLI::error('phpDocumentor terminó con errores.');
        }
    }
}
