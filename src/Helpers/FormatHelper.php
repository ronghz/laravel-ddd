<?php
namespace Ronghz\LaravelDdd\Helpers;

use Illuminate\Support\Facades\Hash;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Carbon\Carbon;

class FormatHelper
{
    /**
     * 价格保留两位小数
     *
     * @author ysg <18934006047@163.com>
     * @date   2018-06-21
     * @since  PM_1.0_zantui 
     * @param $price
     * @param bool $trimZero
     * @return string
     */
    public static function formatPrice($price, $trimZero = true)
    {
        if ($trimZero) {
            return (string)floatval(substr(sprintf("%.3f", $price), 0, -1));
        }
        return substr(sprintf("%.3f", $price), 0, -1);
    }

    /**
     * 数值保留两位小数
     *
     * @author wareon <wareon@qq.com>
     * @date 2019/3/15 15:57
     * @param $price
     * @param bool $trimZero
     * @since PM_1.0_scm
     * @return bool|string
     */
    public static function formatNum($price, $trimZero = true)
    {
        if ($trimZero) {
            return (string)floatval(substr(sprintf("%.3f", $price), 0, -1));
        }
        return substr(sprintf("%.3f", $price), 0, -1);
    }

    /**
     * 时间对象转化
     *
     * @author wareon <wareon@qq.com>
     * @date 2019/8/10 20:12
     * @param $timeObj
     * @param string $format
     * @since V1.2
     * @return string
     */
    public static function formatTime($timeObj, $format = 'Y-m-d H:i:s')
    {
        if (!$timeObj) return '';
        return Carbon::parse($timeObj)->format($format);
    }

    /**
     * 阿拉伯数字转汉字
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date   2019/1/10 10:22
     * @param  int $number 数字
     * @param bool $isRmb  是否是金额数据
     * @since  PM_1.0_shopping
     * @return string
     */
    public static function number2chinese($number, $isRmb = false)
    {
        // 判断正确数字
        if (!preg_match('/^-?\d+(\.\d+)?$/', $number)) {
            return 'number2chinese() wrong number';
        }
        list($integer, $decimal) = explode('.', $number . '.0');

        // 检测是否为负数
        $symbol = '';
        if (substr($integer, 0, 1) == '-') {
            $symbol = '负';
            $integer = substr($integer, 1);
        }
        if (preg_match('/^-?\d+$/', $number)) {
            $decimal = null;
        }
        $integer = ltrim($integer, '0');

        // 准备参数
        $numArr = ['', '一', '二', '三', '四', '五', '六', '七', '八', '九', '.' => '点'];
        $descArr = ['', '十', '百', '千', '万', '十', '百', '千', '亿', '十', '百', '千', '万亿', '十', '百', '千', '兆', '十', '百', '千'];
        if ($isRmb) {
            $number = substr(sprintf("%.5f", $number), 0, -1);
            $numArr = ['', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖', '.' => '点'];
            $descArr = ['', '拾', '佰', '仟', '万', '拾', '佰', '仟', '亿', '拾', '佰', '仟', '万亿', '拾', '佰', '仟', '兆', '拾', '佰', '仟'];
            $rmbDescArr = ['角', '分', '厘', '毫'];
        }

        // 整数部分拼接
        $integerRes = '';
        $count = strlen($integer);
        if ($count > max(array_keys($descArr))) {
            return 'number2chinese() number too large.';
        } else if ($count == 0) {
            $integerRes = '零';
        } else {
            for ($i = 0; $i < $count; $i++) {
                $n = $integer[$i];      // 位上的数
                $j = $count - $i - 1;   // 单位数组 $descArr 的第几位
                // 零零的读法
                $isLing = $i > 1                    // 去除首位
                    && $n !== '0'                   // 本位数字不是零
                    && $integer[$i - 1] === '0';    // 上一位是零
                $cnZero = $isLing ? '零' : '';
                $cnNum = $numArr[$n];
                // 单位读法
                $isEmptyDanwei = ($n == '0' && $j % 4 != 0)     // 是零且一断位上
                    || substr($integer, $i - 3, 4) === '0000';  // 四个连续0
                $descMark = isset($cnDesc) ? $cnDesc : '';
                $cnDesc = $isEmptyDanwei ? '' : $descArr[$j];
                // 第一位是一十
                if ($i == 0 && $cnNum == '一' && $cnDesc == '十') $cnNum = '';
                // 二两的读法
                $isChangeEr = $n > 1 && $cnNum == '二'       // 去除首位
                    && !in_array($cnDesc, ['', '十', '百'])  // 不读两\两十\两百
                    && $descMark !== '十';                   // 不读十两
                if ($isChangeEr) $cnNum = '两';
                $integerRes .= $cnZero . $cnNum . $cnDesc;
            }
        }

        // 小数部分拼接
        $decimalRes = '';
        $count = strlen($decimal);
        if ($decimal === null) {
            $decimalRes = $isRmb ? '整' : '';
        } else if ($decimal === '0') {
            $decimalRes = '零';
        } else if ($count > max(array_keys($descArr))) {
            return 'number2chinese() number too large.';
        } else {
            for ($i = 0; $i < $count; $i++) {
                if ($isRmb && $i > count($rmbDescArr) - 1) break;
                $n = $decimal[$i];
                $cnZero = $n === '0' ? '零' : '';
                $cnNum = $numArr[$n];
                $cnDesc = $isRmb ? $rmbDescArr[$i] : '';
                $decimalRes .= $cnZero . $cnNum . $cnDesc;
            }
        }
        // 拼接结果
        $res = $symbol . ($isRmb ?
                $integerRes . ($decimalRes === '零' ? '元整' : "元$decimalRes") :
                $integerRes . ($decimalRes === '' ? '' : "点$decimalRes"));
        return $res;
    }

    /**
     * 隐藏手机中间四位
     *
     * @author wareon <wareon@qq.com>
     * @date 2018/12/15 17:04
     * @param $mobile
     * @since PM_1.0_scm
     * @return mixed
     */
    public static function hiddenMobile($mobile)
    {
        if (strlen($mobile) > 7) {
            return substr_replace($mobile, '****', 3, 4);
        } else {
            return $mobile;
        }
    }

    /**
     * 将银行卡中间八个字符隐藏为*
     */
    public static function hiddenBankCard($bankCard)
    {
        if (strlen($bankCard) > 4) {
            return substr_replace($bankCard, '****', 4, 8);
        } else {
            if (!$bankCard) return $bankCard;
        }
    }

    /**
     * 转换URL链接HTTP为HTTPS
     *
     * @param $url
     * @return mixed
     */
    public static function httpToHttps($url)
    {
        if (empty($url)) return $url;
        $url = trim($url);
        $prefix = substr($url, 0, 7);
        if (strtolower($prefix) == 'http://') {
            $url = 'https://' . substr($url, 7);
        }
        return $url;
    }

    /**
     * 驼峰命名转下划线命名
     *
     * @param $str
     * @return mixed
     * @author chengciming
     * @date   2019/7/25
     */
    public static function toUnderScore($str, $flag = '_')
    {
        $dstr = preg_replace_callback('/([A-Z]+)/',function($matchs) use ($flag) {
            return $flag.strtolower($matchs[0]);
        },$str);
        return trim(preg_replace('/'.$flag.'{2,}/',$flag,$dstr),$flag);
    }

    /**
     * 下划线命名到驼峰命名
     *
     * @param $str
     * @param $flag
     * @return mixed
     * @author chengciming
     * @date   2019/7/25
     */
    public static function toCamelCase($str, $flag = '_')
    {
        $array = explode($flag, $str);
        $result = $array[0];
        $len=count($array);
        if($len>1) {
            for($i=1;$i<$len;$i++) $result.= ucfirst($array[$i]);
        }
        return $result;
    }
}
