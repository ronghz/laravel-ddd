<?php

namespace Ronghz\LaravelDdd\Framework\Base;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use ReflectionFunction;
use Ronghz\LaravelDdd\Framework\Events\RepositoryEntityCreated;
use Ronghz\LaravelDdd\Framework\Events\RepositoryEntityDeleted;
use Ronghz\LaravelDdd\Framework\Events\RepositoryEntityUpdated;
use Ronghz\LaravelDdd\Framework\Exceptions\DevelopException;

class DddRepository
{
    /** @var DddModel*/
    protected $model;

    public function __construct()
    {
        $this->resetModel();
    }

    public function resetModel()
    {
        $modelClass = $this->getClassPath();
        if (class_exists($modelClass)) {
            $this->model = app($modelClass);
        } else {
            throw new DevelopException('没有定义对应的Model:' . $modelClass);
        }
    }

    private function getClassPath()
    {
        $classPath = get_class($this);
        $classPath = str_replace('/', '\\', $classPath);
        $classPath = explode('\\', $classPath);

        if ($classPath[0] == 'App') {
            $classPath[3] = 'Models';
            $classPath[4] = str_replace('Repository', '', $classPath[4]);
        } elseif ($classPath[0] == 'Ronghz') {
            $classPath[4] = 'Models';
            $classPath[5] = str_replace('Repository', '', $classPath[5]);
        }
        return implode('\\', $classPath);
    }
}
