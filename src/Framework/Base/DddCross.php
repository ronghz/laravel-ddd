<?php
namespace Ronghz\LaravelDdd\Framework\Base;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\AbstractPaginator;
use Ronghz\LaravelDdd\Framework\Exceptions\DevelopException;

class DddCross
{
    /** @var DddDomainService */
    protected $domainService = null;

    public function __construct()
    {
        $this->autoInitService();
    }

    protected function autoInitService()
    {
        $classPath = $this->getClassPath();
        $domainService = '';

        if ($classPath[0] == 'App') {
            $domain = $classPath[2];
            $entity = str_replace('Cross', '', $classPath[5]);

            $domainService = 'App\\Domain\\' . $domain . '\\Services\\' . $entity . 'Service';

        } elseif ($classPath[0] == 'Ronghz') {
            $domain = $classPath[3];
            $entity = str_replace('Cross', '', $classPath[6]);

            $domainService = 'Ronghz\\LaravelDdd\\Domain\\' . $domain . '\\Services\\' . $entity . 'Service';
        }

        if (class_exists($domainService)) {
            $this->domainService = app($domainService);
        }
    }

    private function getClassPath()
    {
        $classPath = get_class($this);
        $classPath = str_replace('/', '\\', $classPath);
        return explode('\\', $classPath);
    }

    protected function response($data = [])
    {
        if ($data instanceof DddModel) {
            $dtoClass = $this->getDtoClass($data);
            $response = new $dtoClass($data->getAttributes());
        } elseif ($data instanceof Collection) {
            $dtoClass = $this->getDtoClass($data->first());
            $response = new Collection;
            foreach ($data->getIterator() as $row) {
                $response->add(new $dtoClass($row->getAttributes()));
            }
        } elseif ($data instanceof AbstractPaginator) {
            $dtoClass = $this->getDtoClass($data->first());
            $collection = new Collection;
            foreach ($data->getIterator() as $row) {
                $collection->add(new $dtoClass($row->getAttributes()));
            }
            $data->setCollection($collection);
            $response = $data;
        } else {
            $response = $data;
        }
        return $response;
    }

    protected function getDtoClass($object)
    {
        $model = get_class($object);
        $path = explode('\\', $model);
        $path[3] = 'Dtos';
        $path[4] .= 'Dto';
        $dtoClass = implode('\\', $path);
        if (!class_exists($dtoClass)) {
            throw new DevelopException('Dto class for ' . $model . ' not defined');
        }
        return $dtoClass;
    }
}
