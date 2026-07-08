<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestRunner extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test';
    protected $description = 'Corre los tests del proyecto';
    protected $usage       = 'test [suite]';
    protected $arguments   = [
        'suite' => 'Suite a correr: feature, auth, api. Sin argumento corre todos.',
    ];

    private array $suites = [
        'all'     => '--testsuite App',
        'feature' => 'tests/Feature/',
        'auth'    => 'tests/Feature/AuthTest.php',
        'api'     => 'tests/Feature/ApiTest.php',
    ];

    public function run(array $params): void
    {
        $suite = $params[0] ?? 'all';

        if (!isset($this->suites[$suite])) {
            CLI::error("Suite desconocida: '{$suite}'");
            CLI::write('Disponibles: ' . implode(', ', array_keys($this->suites)));
            return;
        }

        $isFlag = str_starts_with($this->suites[$suite], '--');
        $target = $isFlag ? $this->suites[$suite] : ROOTPATH . $this->suites[$suite];

        $bin    = ROOTPATH . 'vendor/bin/phpunit';
        $config = ROOTPATH . 'phpunit.xml.dist';

        CLI::write("► Corriendo suite: {$suite}", 'cyan');
        CLI::newLine();

        chdir(ROOTPATH);
        passthru("{$bin} -c {$config} --no-coverage --testdox {$target}", $exitCode);

        CLI::newLine();

        if ($exitCode === 0) {
            CLI::write('✓ Todos los tests pasaron.', 'green');
        } else {
            CLI::error('✗ Algunos tests fallaron.');
        }

        exit($exitCode);
    }
}
