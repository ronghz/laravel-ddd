<?php
namespace Ronghz\LaravelDdd\Helpers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Carbon\Carbon;

class RequestHelper
{
    public static function getRouteInfo()
    {
        $request = app('request');
        $path = explode('/', $request->path());
        if (config('ddd.router.project_prefix')) {
            $pathInfo['satisfy'] = count($path) > 4;
            $pathInfo['method'] = strtolower($request->getMethod());
            $pathInfo['terminal'] = Str::studly(isset($path[1]) ? $path[1] : '');
            $pathInfo['domain'] = Str::studly(isset($path[2]) ? $path[2] : '');
            $pathInfo['entity'] = Str::studly(isset($path[3]) ? $path[3] : '');
            $pathInfo['action'] = Str::studly(isset($path[4]) ? $path[4] : '');
            $pathInfo['params'] = array_slice($path, 5, count($path) - 5);
            $pathInfo['path'] = implode('/', array_slice($path, 0, 5));
            foreach ($pathInfo['params'] as $index => $param) {
                $pathInfo['path'] .= '/{param' . $index . '}';
            }
        } else {
            $pathInfo['satisfy'] = count($path) > 3;
            $pathInfo['method'] = strtolower($request->getMethod());
            $pathInfo['terminal'] = Str::studly(isset($path[0]) ? $path[0] : '');
            $pathInfo['domain'] = Str::studly(isset($path[1]) ? $path[1] : '');
            $pathInfo['entity'] = Str::studly(isset($path[2]) ? $path[2] : '');
            $pathInfo['action'] = Str::studly(isset($path[3]) ? $path[3] : '');
            $pathInfo['params'] = array_slice($path, 4, count($path) - 4);
            $pathInfo['path'] = implode('/', array_slice($path, 0, 4));
            foreach ($pathInfo['params'] as $index => $param) {
                $pathInfo['path'] .= '/{param' . $index . '}';
            }
        }
        return $pathInfo;
    }

    public static function getClientVersion()
    {
        return app('request')->header(config('ddd.router.client_version_key', 'Release-Version'));
    }

}
