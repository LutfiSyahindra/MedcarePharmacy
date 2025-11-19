<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name}';
    protected $description = 'Create a repository class';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $path = app_path("Repositories/{$name}.php");

        (new Filesystem)->ensureDirectoryExists(dirname($path));

        $stub = <<<PHP
        <?php

        namespace App\Repositories;

        class {$name}
        {
            //
        }
        PHP;

        file_put_contents($path, $stub);

        $this->info("Repository created: {$path}");
    }
}
