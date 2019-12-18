<?php

namespace Ronghz\LaravelDdd\Framework\Base;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Routing\Controller;
use Ronghz\LaravelDdd\Framework\Exceptions\DevelopException;

class DddController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const VERSION_RANGE = [];

    /** @var DddAppService */
    protected $appService;
    /** @var DddDomainService */
    protected $domainService;

    public function __construct()
    {
        $this->autoInitService();
    }

    protected function autoInitService()
    {
        $classPath = $this->getClassPath();
        $appService = '';
        $domainService = '';

        if ($classPath[0] == 'App') {
            $domain = $classPath[2];
            $terminal = $classPath[4];
            $entity = str_replace('Controller', '', $classPath[6]);

            $domainService = 'App\\Domain\\' . $domain . '\\Services\\' . $entity . 'Service';
            $appService = 'App\\Domain\\' . $domain . '\\Ports\\' . $terminal . '\\Services\\' . $entity . 'Service';

        } elseif ($classPath[0] == 'Ronghz') {
            $domain = $classPath[3];
            $terminal = $classPath[5];
            $entity = str_replace('Controller', '', $classPath[7]);

            $domainService = 'Ronghz\\LaravelDdd\\Domain\\' . $domain . '\\Services\\' . $entity . 'Service';
            $appService = 'Ronghz\\LaravelDdd\\Domain\\' . $domain . '\\Ports\\' . $terminal . '\\Services\\' . $entity . 'Service';
        }

        if (class_exists($domainService)) {
            $this->domainService = app($domainService);
        }
        if (class_exists($appService)) {
            $this->appService = app($appService);
        }
    }

    private function getClassPath()
    {
        $classPath = get_class($this);
        $classPath = str_replace('/', '\\', $classPath);
        return explode('\\', $classPath);
    }

    /**
     * 成功返回数据
     *
     * @param array $data 返回数据
     * @param int $status 状态码
     * @param string $message 提示信息
     * @return mixed
     */
    protected function success($data = [], int $status = 0, string $message = 'success')
    {
        $layer = [
            'status' => $status,
            'message' => $message
        ];

        if ($data instanceof DddModel) {
            $resourceClass = $this->getResourceClass($data);
            $layer['data'] = new $resourceClass($data);
        } elseif ($data instanceof Collection || $data instanceof AbstractPaginator) {
            $resourceClass = $this->getResourceClass($data->first());
            if ($resourceClass) {
                $layer = $resourceClass::collection($data)->additional($layer);
            } else {
                $layer = JsonResource::collection($data)->additional($layer);
            }
        } elseif ($data instanceof AbstractPaginator) {
            $resourceClass = $this->getResourceClass($data->first());
            if ($resourceClass) {
                $layer = $resourceClass::collection($data)->additional($layer);
            } else {
                $layer = JsonResource::collection($data)->additional($layer);
            }
        } else {
            $layer['data'] = $data;
        }
        return $layer;
    }

    protected function getResourceClass($object)
    {
        $model = get_class($object);
        $path = explode('\\', $model);
        $path[3] = 'Resources';
        $path[4] .= 'Resource';
        $resourceClass = implode('\\', $path);
        if (!class_exists($resourceClass)) {
            return null;
        }
        return $resourceClass;
    }
}
