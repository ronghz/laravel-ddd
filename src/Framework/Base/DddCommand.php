<?php
namespace Ronghz\LaravelDdd\Framework\Base;

use Illuminate\Console\Command;
use Ronghz\LaravelDdd\Helpers\LogHelper;
use Ronghz\LaravelDdd\Helpers\Tools;

class DddCommand extends Command
{
    /**
     * 脚本名称
     * @var string
     */
    protected $name = '脚本名称';

    /**
     * 脚本描述
     * @var string
     */
    protected $description = '脚本描述';

    /**
     * 日志保存文件
     * @var string
     */
    protected $logFileName = 'command';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->description = $this->getTitle();
        parent::__construct();
    }

    /**
     * 开始记录日志
     *
     * @param string $logFileName 日志文件名
     * @return mixed
     * @throws \Exception
     */
    protected function startLog(?string $logFileName = null)
    {
        if (!is_null($logFileName)) {
            $this->logFileName = $logFileName;
        }

        LogHelper::singleLog('开始执行：' . get_class($this), $this->signature . "\n\t" . $this->description, false, $this->logFileName);
    }

    /**
     * 结束记录日志
     *
     * @return mixed
     * @throws \Exception
     */
    protected function endLog()
    {
        LogHelper::singleLog('结束执行：' . get_class($this), $this->signature . "\n\t" . $this->description, true, $this->logFileName);
    }

    /**
     * 记录日志
     *
     * @param $message
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    protected function log($message, $data)
    {
        LogHelper::singleLog($data, $message, false, $this->logFileName);
    }

    /**
     * 组合当前标题
     *
     * @return mixed
     */
    protected function getTitle()
    {
        return $this->name . ': ' . $this->description;
    }
}
