<?php

namespace Ronghz\LaravelDdd\Framework\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Route;
use Ronghz\LaravelDdd\Framework\Exceptions\CommonException;
use Ronghz\LaravelDdd\Framework\Exceptions\ExceptionCode;
use Ronghz\LaravelDdd\Helpers\RequestHelper;

class AutoRouter
{

    public function handle(Request $request, Closure $next)
    {
        $pathInfo = RequestHelper::getRouteInfo();

        if (config('ddd.router.use_auto_router', false) && $pathInfo['satisfy']) {
            $action = $this->actionResolver($request);

            $method = strtolower($request->method());
            if (in_array($method, ['get', 'post', 'put', 'delete', 'patch', 'options'])) {
                Route::$method($pathInfo['path'], $action);
            } else {
                throw new CommonException(ExceptionCode::COMMON_EXCEPTION, '访问的地址无效！');
            }
        }

        $routeFile = base_path('app/Domain/' . $pathInfo['domain'] . '/Ports/' . $pathInfo['terminal'] . '/routes.php');
        if (file_exists($routeFile)) {
            require $routeFile;
        }

        return $next($request);
    }

    private function actionResolver(Request $request)
    {
        $pathInfo = RequestHelper::getRouteInfo();

        // 先检查领域里是否存在对应的Controller
        $relatePath = ucfirst($pathInfo['domain']) . '\\Ports\\' . ucfirst($pathInfo['terminal']) . '\\Controllers\\' . ucfirst($pathInfo['entity']) . 'Controller';
        $ctlPath = '\\App\\Domain\\' . $relatePath;
        if (!class_exists($ctlPath)) {
            // 再检查框架里是否存在对应的Controller
            $ctlPath = '\\Ronghz\\LaravelDdd\\Domain\\' . $relatePath;
            if (!class_exists($ctlPath)) {
                throw new CommonException(ExceptionCode::COMMON_EXCEPTION, '访问的地址无效！');
            }
        }
        //检查是否有版本控制
        $versionRange = ($ctlPath)::VERSION_RANGE;
        $versionSuffix = '';
        $action = strtolower($pathInfo['method']) . ucfirst($pathInfo['action']);
        if (!empty($versionRange) && isset($versionRange[$action])) {
            $currentVersion = RequestHelper::getClientVersion();
            $matchedVersion = '';
            foreach ($versionRange[$action] as $version) {
                if (version_compare($currentVersion, $version, '>=')) {
                    $matchedVersion = $version;
                }
            }

            if (!empty($matchedVersion)) {
                $versionSuffix = 'V' . str_replace('.', '_', $matchedVersion);
            }
        }

        $action .= $versionSuffix;
        // 检查方法是否存在
        if (method_exists($ctlPath, $action)) {
            return $ctlPath . '@' . $action;
        }
    }
}
