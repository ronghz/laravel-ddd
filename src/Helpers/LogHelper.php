<?php
namespace Ronghz\LaravelDdd\Helpers;

use Illuminate\Support\Facades\Hash;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Carbon\Carbon;

class LogHelper
{
    /**
     * 设置日志文件名
     *
     * @author huangjinbing
     * @date   2019-01-17
     * @param $fileName
     * @param $bugLevel
     * @since  PM_1.3_sibu
     * @return Logger
     * @throws \Exception
     */
    public static function setFileName($fileName, $bugLevel)
    {
        if (in_array($fileName, ['oms', 'rabbitmq'])) {
            $fileName = date('Y/m/d/') . $fileName;
        }
        if (!env('DEFINING_LOG_FILE_ON', true)) $fileName = 'lumen';
        $stream = new StreamHandler(storage_path('logs/' . $fileName . '.log'), $bugLevel);
        $stream->setFormatter(new LineFormatter(null, null, true, true));
        $log = new Logger($fileName);
        $log->pushHandler($stream);

        return $log;
    }

    /**
     * 单个日志输出
     *
     * @param $content
     * @param null $title
     * @param string $fileName
     * @return mixed
     * @author chengciming
     * @date   2019/7/25
     * @throws \Exception
     */
    public static function logInfo($content, $title = null, $fileName = 'lumen')
    {
        if (env('LOG_ON', true)) {
            $log = self::setFileName($fileName, Logger::INFO);
            if ($title) $log->info($title);
            $log->info('==========================');
            $log->info(print_r($content, true));
            $log->info('==========================');
        }
    }


    /**
     * 单个错误日志输出
     *
     * @param $content
     * @param null $title
     * @param string $fileName
     * @return mixed
     * @author chengciming
     * @date   2019/7/25
     * @throws \Exception
     */
    public static function logError($content, $title = null, $fileName = 'lumen')
    {
        $log = self::setFileName($fileName, Logger::ERROR);
        if ($title) $log->error($title);
        $log->error('**************************');
        $log->error($content);
        $log->error('**************************');
    }

    /**
     * 事务异常错误日志输出
     *
     * @param $exception
     * @param null $title
     * @param string $fileName
     * @return mixed
     * @author chengciming
     * @date   2019/7/25
     * @throws \Exception
     */
    public static function logUnusualError($exception, $title = null, $fileName = 'lumen')
    {
        $log = self::setFileName($fileName, Logger::ERROR);
        if ($title) $log->info($title);
        $log->error('**************************');
        $log->error("\n"
            . "----------------------------------------\n"
            . "| 错误信息 | {$exception->getMessage()}\n"
            . "| 文件路径 | {$exception->getFile()} (第{$exception->getLine()}行)\n"
            . "| 访问路径 | [" . request()->method() . "] " . request()->url() . "\n"
            . "| 请求参数 | " . json_encode(request()->all()) . "\n"
            . "----------------------------------------\n");
        $log->error('**************************');
    }

    /**
     * 队列推送日志输出
     *
     * @param $exchange
     * @param $routing
     * @param $message
     * @param null $title
     * @param string $fileName
     * @return mixed
     * @author chengciming
     * @date   2019/7/25
     * @throws \Exception
     */
    public static function logQueuePublish($exchange, $routing, $message, $title = null, $fileName = 'rabbitmq')
    {
        $log = self::setFileName($fileName, Logger::INFO);
        if ($title) $log->info($title);
        $log->info('==========================');
        $log->info("\n"
            . "----------------------------------------\n"
            . "| 路由   | $routing" . "\n"
            . "| 信息   | $message" . "\n"
            . "----------------------------------------\n");
        $log->info('==========================');
        return true;
    }

    /**
     * 多个日志一次性输出
     * @deprecated 并发时会掺杂其他的日志
     *
     * @param $content
     * @param null $title
     * @param bool $isEnd
     * @param string $fileName
     * @return mixed
     * @author chengciming
     * @date   2019/7/25
     * @throws \Exception
     */
    static public function singleLog($content, $title = null, $isEnd = false, $fileName = 'lumen')
    {
        if (!isset($GLOBALS['debugArray'])) {
            $GLOBALS['debugArray'] = array();
        }

        if ($title) {
            array_push($GLOBALS['debugArray'], $title);
            array_push($GLOBALS['debugArray'], '==========================');
        }

        if ($content) {
            array_push($GLOBALS['debugArray'], print_r($content, true));
            array_push($GLOBALS['debugArray'], '--------------------------');
        }

        if ($isEnd) {
            self::logInfo($GLOBALS['debugArray'], null, $fileName);
            unset($GLOBALS['debugArray']);
        }

        return true;
    }

    /**
     * 异步日志
     *
     * @param $keyName
     * @param $content
     * @param null $title
     * @param bool $isEnd
     * @param string $fileName
     * @return mixed
     * @author chengciming
     * @date   2019/7/25
     * @throws \Exception
     */
    public static function asyncLog($keyName, $content, $title = null, $isEnd = false, $fileName = 'lumen')
    {
        if (!isset($GLOBALS[$keyName])) {
            $GLOBALS[$keyName] = array();
        }

        if ($title) {
            array_push($GLOBALS[$keyName], $title);
            array_push($GLOBALS[$keyName], '==========================');
        }

        if ($content) {
            array_push($GLOBALS[$keyName], print_r($content, true));
            array_push($GLOBALS[$keyName], '--------------------------');
        }

        if ($isEnd) {
            self::logInfo($GLOBALS[$keyName], null, $fileName);
            unset($GLOBALS[$keyName]);
        }

        return true;
    }
}
