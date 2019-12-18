<?php

namespace Ronghz\LaravelDdd\Framework\Exceptions;

use Ronghz\LaravelDdd\Framework\Base\DddEnum;

class ExceptionCode extends DddEnum
{
    /**
     * 错误码规则
     * 第1位代表错误类型
     *      1代表系统错误，如系统崩溃、接口错误等;
     *      2代表前端统一拦截的异常，例如之前赞播项目的凭证失效、店铺关闭等;
     *      3代表业务异常，需要在前端显示给用户的信息，例如接口参数校验失败、下单时库存不足等;
     * 第2、3位是领域编码，从10开始，如商品域、订单域;
     * 第4、5位是领域内的模块编码;
     * 第6、7位用来区分这个模块里的异常类型;
     */

    public const COMMON_EXCEPTION = 1000000;

    // 框架2-3位(00)
    // 通用4-5位(00)
    public const DATA_NOT_FOUND = 3000001;

    protected static $texts = [
        self::DATA_NOT_FOUND     => '未查询到相关数据',
    ];
}
