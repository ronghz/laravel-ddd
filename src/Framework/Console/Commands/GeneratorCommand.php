<?php

namespace Ronghz\LaravelDdd\Framework\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Ronghz\LaravelDdd\Framework\Base\DddCommand;

class GeneratorCommand extends DddCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ddd-generator {domain}{--model= : The model need to generate}{--table= : The table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '领域代码生成器';


    public function handle(): void
    {
        $domain = ucfirst($this->argument('domain'));
        $domainPath = base_path() . '/app/Domain/' . $domain . '/';
        is_dir($domainPath) or mkdir($domainPath, 0777, true);

        $ports = config('ddd.generator.ports', ['Platform', 'Customer']);

        $this->createDomainDir($domainPath, $ports);

        $this->createTestDir($domain, $ports);

        $this->createDomainFiles($domainPath, $domain, $ports);

        $this->createTestFiles($domain);
    }

    private function createDomainDir(string $domainPath, $ports): void
    {
        $subDirs = [
            'Commands',
            'Dtos',
            'Events',
            'Jobs',
            'Listeners',
            'Models',
            'Ports',
            'Repositories',
            'Resources',
            'Services',
            'Supports'
        ];
        foreach ($subDirs as $subDir) {
            is_dir($domainPath . $subDir) or mkdir($domainPath . $subDir, 0777, true);
        }

        //生成目录
        foreach ($ports as $port) {
            $path = $domainPath . 'Ports/' . $port . '/Controllers';
            is_dir($path) or mkdir($path, 0777, true);

            $path = $domainPath . 'Ports/' . $port . '/Request';
            is_dir($path) or mkdir($path, 0777, true);

            $path = $domainPath . 'Ports/' . $port . '/Services';
            is_dir($path) or mkdir($path, 0777, true);
        }

        $path = $domainPath . 'Models/migrations';
        is_dir($path) or mkdir($path, 0777, true);

        $path = $domainPath . 'Supports/Enums';
        is_dir($path) or mkdir($path, 0777, true);

        $path = $domainPath . 'Ports/Cross';
        is_dir($path) or mkdir($path, 0777, true);
    }

    private function createTestDir(string $domain, $ports): void
    {
        $testDir = base_path() . '/tests/' . $domain . '/';
        is_dir($testDir) or mkdir($testDir, 0777, true);
        $subDirs = ['Repositories', 'Ports', 'Services'];
        foreach ($subDirs as $subDir) {
            is_dir($testDir . $subDir) or mkdir($testDir . $subDir, 0777, true);
        }

        $path = $testDir . 'Ports/Cross';
        is_dir($path) or mkdir($path, 0777, true);

        foreach ($ports as $port) {
            $path = $testDir . 'Ports/' . $port . '/Controllers';
            is_dir($path) or mkdir($path, 0777, true);

            $path = $testDir . 'Ports/' . $port . '/Services';
            is_dir($path) or mkdir($path, 0777, true);
        }
    }

    private function createDomainFiles(string $domainPath, string $domain, $ports): void
    {
        $tempBase = __DIR__ . '/GeneratorTemplates/';
        $model = ucfirst($this->option('model'));

        if ($model) {
            //领域类
            $files = [
                'Model.temp' => 'Models/' . $model . '.php',
                'Repository.temp' => 'Repositories/' . $model . 'Repository.php',
                'Resource.temp' => 'Resources/' . $model . 'Resource.php',
                'Dto.temp' => 'Dtos/' . $model . 'Dto.php',
                'DomainService.temp' => 'Services/' . $model . 'Service.php',
                'Cross.temp' => 'Ports/Cross/' . $model . 'Cross.php',
            ];
            foreach ($files as $temp => $file) {
                $filePath = $domainPath . $file;
                if (!file_exists($filePath)) {
                    $content = file_get_contents($tempBase . $temp);
                    $content = $this->replace($content, $domain, $model, '');
                    file_put_contents($filePath, $content);
                }
            }
            //应用类
            foreach ($ports as $port) {
                $files = [
                    'Controller.temp' => 'Ports/' . $port . '/Controllers/' . $model . 'Controller.php',
                    'AppService.temp' => 'Ports/' . $port . '/Services/' . $model . 'Service.php',
                    'routes.temp' => 'Ports/' . $port . '/routes.php',
                ];
                foreach ($files as $temp => $file) {
                    $filePath = $domainPath . $file;
                    if (!file_exists($filePath)) {
                        $content = file_get_contents($tempBase . $temp);
                        $content = $this->replace($content, $domain, $model, $port);
                        file_put_contents($filePath, $content);
                    }
                }
            }
        }
    }

    private function createTestFiles(string $domain): void
    {
        $tempBase = __DIR__ . '/GeneratorTemplates/';
        $model = ucfirst($this->option('model'));

        $content = file_get_contents($tempBase . 'TestService.temp');
        $content = $this->replace($content, $domain, $model, '');
        file_put_contents(base_path() . '/tests/' . $domain . '/Services/' . $model . 'ServiceTest.php', $content);

        $content = file_get_contents($tempBase . 'TestCross.temp');
        $content = $this->replace($content, $domain, $model, '');
        file_put_contents(base_path() . '/tests/' . $domain . '/Ports/Cross/' . $model . 'CrossDomainTest.php', $content);
    }

    private function replace($content, $domain, $model, $port)
    {
        $table = strtolower($this->option('table'));
        $fillable = '';
        $attribute = '';
        $fields = '';
        $property = '';

        if ($table) {
            $columns = Schema::getColumnListing($table);
            $fillable = "'" . implode("',\r\n\t\t'", $columns) . "'";

            $attribute = "'" . implode("' => '',\r\n\t\t'", $columns) . "' => ''";

            foreach ($columns as $column) {
                if ($column == 'id') {
                    $fields .= "'$column' => Field::ID(),\r\n\t\t\t";
                } else {
                    switch (Schema::getColumnType($table, $column)) {
                        case 'integer':
                            $fields .= "'$column' => Field::int(),\r\n\t\t\t";
                            break;
                        case 'float':
                            $fields .= "'$column' => Field::float(),\r\n\t\t\t";
                            break;
                        case 'boolean':
                            $fields .= "'$column' => Field::boolean(),\r\n\t\t\t";
                            break;
                        default:
                            $fields .= "'$column' => Field::string(),\r\n\t\t\t";
                    }
                }
            }

            $func = '';
            foreach ($columns as $column) {
                $camel = Str::camel($column);
                $property .= "private $$camel;\r\n\t";

                $func.= "\r\n\tpublic function get" . Str::studly($column) . "()\r\n\t{\r\n\t\treturn \$this->$camel;\r\n\t}\r\n";
                $func.= "\r\n\tpublic function set" . Str::studly($column) . "($$camel)\r\n\t{\r\n\t\treturn \$this->$camel = $$camel;\r\n\t}\r\n";
            }
            $property .= $func;
        }

        $content = str_replace('{{Port}}', $port, $content);
        $content = str_replace('{{port}}', lcfirst($port), $content);
        $content = str_replace('{{Domain}}', $domain, $content);
        $content = str_replace('{{domain}}', lcfirst($domain), $content);
        $content = str_replace('{{Model}}', $model, $content);

        $content = str_replace('{{table}}', $table, $content);
        $content = str_replace('{{fillable}}', $fillable, $content);
        $content = str_replace('{{attribute}}', $attribute, $content);
        $content = str_replace('{{fields}}', $fields, $content);
        $content = str_replace('{{property}}', $property, $content);

        return $content;
    }
}
