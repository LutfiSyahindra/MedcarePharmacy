<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    protected $signature = 'make:service {name}';
    protected $description = 'Create a new service class';

    public function handle()
    {
        $name = $this->argument('name');

        if (!File::exists(app_path('Services'))) {
            File::makeDirectory(app_path('Services'));
        }

        $content = "<?php\n\nnamespace App\Services;\n\nclass {$name}Service\n{\n    // business logic here\n}\n";
        File::put(app_path("Services/{$name}Service.php"), $content);

        $this->info("Service {$name} created successfully.");
    }
}
