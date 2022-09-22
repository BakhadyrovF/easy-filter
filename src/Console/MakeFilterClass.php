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
    protected $signature = 'make:filter {className} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a filter class';

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
     * @const Trait namespace
     */
    protected const TRAIT_NAMESPACE = 'Bakhadyrovf\EasyFilter\Traits\Filterable';

    /**
     * @const Trait name
     */
    protected const TRAIT_NAME = 'Filterable';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->makeBaseDirectory();

        $this->abortIfFilterExists();

        $this->useTrait();

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
        if(file_exists(app_path(config('easy-filter.base-folder')) . '/' . $this->argument('className') . '.php')) {
            $this->error('Filter already exists');
            die();
        }

    }

    protected function makeBaseDirectory()
    {
        if (!is_dir(app_path(config('easy-filter.base-folder')))) {
            mkdir(app_path(config('easy-filter.base-folder')));
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

            $this->filePath = app_path(config('easy-filter.base-folder') . '/' . implode('/', $this->folders) . '/' . $this->className) . '.php';

        } else {

            $this->filePath = app_path(config('easy-filter.base-folder') . '/' . $this->className) . '.php';

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
        $folderWithPath = app_path(config('easy-filter.base-folder')) . '/';
        if (!empty($this->folders)) {
            foreach ($this->folders as $folder) {
                $folderWithPath .= $folder . '/';

                if (!is_dir($folderWithPath)) {
                    mkdir($folderWithPath);
                }
            }
        }
    }

    protected function useTrait()
    {
        $modelNamespace = $this->option('model')
            ? $this->option('model')
            : $this->getFilterableNamespace();

        $path = $this->getFilterablePath($modelNamespace);

        $contents = $this->getContentsWithTrait($path);

        file_put_contents($path, $contents);
    }

    protected function getFilterableNamespace()
    {
        $modelNamespace = 'App\\Models\\' . str_replace(['/', 'Filter'], ['\\', ''], $this->argument('className'));

        if (!class_exists($modelNamespace)) {
            $this->error('Model for this filter does not exist!');
            $this->info('Model namespace must be - ' . $modelNamespace . ' or you can provide model namespace manually with `--model=App\Models\Admin\User` option.');
            die();
        }

        return $modelNamespace;
    }

    protected function getFilterablePath(string $namespace)
    {
        return app_path('Models/' . str_replace(['App\\Models\\', '\\'], ['', '/'], $namespace) . '.php');
    }

    protected function getContentsWithTrait($path)
    {
        $contents = file_get_contents($path);
        $className = str_replace('.php', '', basename($path));

        $firstUseOperatorPosition = strpos($contents, 'use');
        $firstUseOperatorEndLinePosition = strpos($contents, ';', $firstUseOperatorPosition);
        $firstUseLine = substr($contents, $firstUseOperatorPosition, $firstUseOperatorEndLinePosition - $firstUseOperatorPosition + 2);
        $newUseLine =  'use '. self::TRAIT_NAMESPACE . ';' . PHP_EOL . $firstUseLine;
        $contents = str_replace($firstUseLine, $newUseLine, $contents);

        $classNamePosition = strpos($contents, $className . ' extends');
        $classUseOperatorPosition = strpos($contents, 'use', $classNamePosition);
        $classUseOperatorEndLinePosition = strpos($contents, ';', $classUseOperatorPosition);
        $classUseLine = substr($contents, $classUseOperatorPosition, $classUseOperatorEndLinePosition - $classUseOperatorPosition);
        $newClassUseLine = $classUseLine . ', ' . self::TRAIT_NAME;

        return str_replace($classUseLine, $newClassUseLine, $contents);
    }

}
