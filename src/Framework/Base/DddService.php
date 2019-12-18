<?php

namespace Ronghz\LaravelDdd\Framework\Base;

class DddService
{
    /** @var DddRepository 仓库对象 */
    protected $repository = null;

    public function __construct()
    {
        $this->autoInitRepository();
    }

    private function autoInitRepository()
    {
        $classPath = $this->getClassPath();
        if ($classPath[0] == 'App') {
            $classPath[3] = 'Repositories';
            $classPath[4] = str_replace('Service', 'Repository', $classPath[4]);
        } elseif ($classPath[0] == 'Ronghz') {
            $classPath[4] = 'Repositories';
            $classPath[5] = str_replace('Service', 'Repository', $classPath[5]);
        }

        $repositoryName = implode('\\', $classPath);
        if (class_exists($repositoryName)) {
            $this->repository = app($repositoryName);
        }
    }

    private function getClassPath()
    {
        $classPath = get_class($this);
        $classPath = str_replace('/', '\\', $classPath);
        return explode('\\', $classPath);
    }
}
