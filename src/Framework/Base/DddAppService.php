<?php
namespace Ronghz\LaravelDdd\Framework\Base;

class DddAppService extends DddService
{
    protected $domainService;

    public function __construct()
    {
        parent::__construct();
        $this->autoInitService();
        $this->autoInitRepository();
    }

    protected function autoInitService()
    {
        $classPath = $this->getClassPath();
        $domainService = '';

        if ($classPath[0] == 'App') {
            $domain = $classPath[2];
            $entity = str_replace('Controller', '', $classPath[6]);

            $domainService = 'App\\Domain\\' . $domain . '\\Services\\' . $entity . 'Service';

        } elseif ($classPath[0] == 'Ronghz') {
            $domain = $classPath[3];
            $entity = str_replace('Controller', '', $classPath[7]);

            $domainService = 'Ronghz\\LaravelDdd\\Domain\\' . $domain . '\\Services\\' . $entity . 'Service';
        }

        if (class_exists($domainService)) {
            $this->domainService = app($domainService);
        }
    }

    private function autoInitRepository()
    {
        $classPath = $this->getClassPath();
        $repositoryName = '';
        if ($classPath[0] == 'App') {
            $domain = $classPath[2];
            $entity = str_replace('Service', '', $classPath[6]);

            $repositoryName = 'App\\Domain\\' . $domain . '\\Repositories\\' . $entity . 'Repository';

        } elseif ($classPath[0] == 'Ronghz') {
            $domain = $classPath[3];
            $entity = str_replace('Service', '', $classPath[7]);

            $repositoryName = 'Ronghz\\LaravelDdd\\Domain\\' . $domain . '\\Repositories\\' . $entity . 'Repository';
        }

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
