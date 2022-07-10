<?php

namespace Bakhadyrovf\EasyFilter\Console;

use Illuminate\Console\Command;

class MakeFilterClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:filter {className}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a filter class';

    /**
     * The Base Path
     */
    protected const BASE_PATH = 'Filters';

    /**
     * The Class Name
     *
     * @var string
     */
    protected string $className;

    /**
     * The folders array
     *
     * @var array
     */
    protected array $folders = [];

    /**
     * The File Path
     *
     * @var string
     */
    protected string $filePath;

    /**
     * The File Contents
     *
     * @var string
     */
    protected string $contents;

    /**
     * The File Namespace
     *
     * @var string
     */
    protected string $namespace;

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->makeBaseDirectory();

        $this->abortIfFilterExists();

        $this->setFoldersAndClassName();
        $this->setFilePath();
        $this->setNamespace();
        $this->prepareDirectories();
        $this->setContents();

        file_put_contents($this->filePath, $this->contents);

        $this->info('Filter has been created');

    }

    protected function abortIfFilterExists()
    {
        if(file_exists(app_path(self::BASE_PATH) . '/' . $this->argument('className') . '.php')) {
            $this->error('Filter already exists');
            die();
        }

    }

    protected function makeBaseDirectory()
    {
        if (!is_dir(app_path(self::BASE_PATH))) {
            mkdir(app_path(self::BASE_PATH));
        }
    }

    protected function setContents()
    {
        $contents = '<?php ' . PHP_EOL . PHP_EOL;
        $contents .= 'namespace ' . $this->namespace . PHP_EOL . PHP_EOL;
        $contents .= 'use Bakhadyrovf\EasyFilter\QueryFilter;' . PHP_EOL . PHP_EOL;
        $contents .= 'class ' . $this->className . ' extends QueryFilter' . PHP_EOL;
        $contents .= '{' . PHP_EOL . PHP_EOL . '}';

        $this->contents = $contents;

    }

    protected function setFoldersAndClassName()
    {
        if (str_contains($this->argument('className'), '/')) {

            $foldersAndClass = explode('/', $this->argument('className'));
            $classIndex = count($foldersAndClass) - 1;

            $class = $foldersAndClass[$classIndex];
            unset($foldersAndClass[$classIndex]);

            $this->folders = $foldersAndClass;

            $this->className = $class;
        } else {

            $this->className = $this->argument('className');

        }

    }

    protected function setFilePath()
    {
        if(!empty($this->folders)) {

            $this->filePath = app_path(self::BASE_PATH . '/' . implode('/', $this->folders) . '/' . $this->className) . '.php';

        } else {

            $this->filePath = app_path(self::BASE_PATH . '/' . $this->className) . '.php';

        }
    }

    protected function setNamespace()
    {
        $namespace = 'App\Filters\\';
        if (!empty($this->folders)) {

            foreach ($this->folders as $folder) {
                $namespace .=  $folder . '\\';
            }

        }

        $this->namespace = substr($namespace, 0, strlen($namespace) - 1) . ';';


    }

    protected function prepareDirectories()
    {
        $folderWithPath = app_path(self::BASE_PATH) . '/';
        if (!empty($this->folders)) {
            foreach ($this->folders as $folder) {
                $folderWithPath .= $folder . '/';

                if (!is_dir($folderWithPath)) {
                    mkdir($folderWithPath);
                }
            }
        }
    }

}
