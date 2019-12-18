<?php
namespace Ronghz\LaravelDdd\Helpers;

use Illuminate\Support\Facades\Hash;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Carbon\Carbon;

class Tools
{
    // 加密串
    const ENCRYPT_KEY = 'rgB5N8B+DOSoZA10jmcAR6Eg3pPYj950';

    /**
     * 写入成功返回
     *
     * @param string $message
     * @param int $code
     * @param array $data
     * @return array
     */
    public static function success($message = '操作成功', $code = 0, $data = [])
    {
        if (defined('ERROR_CODE')) $code = ERROR_CODE;
        $response = [
            'error_code' => $code,
            'message' => $message
        ];
        if (!empty($data)) $response = array_merge($response, ['data' => $data]);
        return $response;
    }

    /**
     * 写入失败返回
     *
     * @param string $message
     * @param int    $code
     * @param array    $data
     * @return array
     */
    public static function error($message = '操作失败', $code = 1, $data = [])
    {
        if (defined('ERROR_CODE')) $code = ERROR_CODE;
        $response = [
            'error_code' => $code,
            'message' => $message
        ];
        if (!empty($data)) $response = array_merge($response, ['data' => $data]);
        return $response;
    }

    /**
     * 加密
     *
     * @author ysg <18934006047@163.com>
     * @date   2018-06-24
     * @since  PM_1.0_zantui 
     * @param $data
     * @return mixed
     */
    public static function dataEncrypt($data)
    {
        $key = self::ENCRYPT_KEY;
        ksort($data);
        return md5(http_build_query($data) . $key);
    }

    /**
     * 过滤掉EmoJi表情
     *
     * @author ysg <18934006047@163.com>
     * @date   2018-08-18
     * @since  PM_1.0_agent_admin
     * @param $str
     * @return mixed
     */
    public static function filterEmoJi($str)
    {
        $str = preg_replace_callback('/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);

        return $str ?? '?';
    }

    /**
     * 求两个已知经纬度之间的距离,单位为米
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date   2019/1/22 16:42
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @since  PM_1.0_shopping
     * @return float|int
     */
    public static function getDistance($lng1, $lat1, $lng2, $lat2)
    {
        if (!$lng1 && !$lat1) {
            $s = '';
        } else {
            // 将角度转为狐度
            $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
            $radLat2 = deg2rad($lat2);
            $radLng1 = deg2rad($lng1);
            $radLng2 = deg2rad($lng2);
            $a = $radLat1 - $radLat2;
            $b = $radLng1 - $radLng2;
            $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        }
        return $s;
    }

    /**
     * 距离处理
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date   2019/1/22 16:53
     * @param $distance
     * @since  PM_1.0_shopping
     * @return string
     */
    public static function dealDistance($distance)
    {
        if (!$distance) {
            $distance ='';
        } else if ($distance > 1000) {
            $distance = substr(sprintf("%.3f", ($distance / 1000)), 0, -1) . '千米';
        } else {
            $distance = substr(sprintf("%.3f", $distance), 0, -1) . '米';
        }
        return $distance;
    }

    /**
     * 验证文件类型
     *
     * @author huangjinbing
     * @author huangjinbing
     * @date   2019-03-22
     * @since  PM_1.0_importGoods
     * @return bool
     */
    public static function validFileType($fileRes)
    {
        // 允许文件后缀
        $fileTypes = ['csv', 'xlsx', 'xls'];
        // 获取文件类型后缀
        $extension = $fileRes->getClientOriginalExtension();
        return in_array($extension, $fileTypes);
    }

    /**
     * 校验明文密码与加密密码
     *
     * @param string $password 明文密码
     * @return mixed
     * @author chengciming
     * @date   2019/4/30
     */
    public static function makePassword($password)
    {
        return Hash::make($password);
    }

    /**
     * 校验明文密码与加密密码
     *
     * @param string $password 明文密码
     * @param string $oldPassword 密文密码
     * @return mixed
     * @author chengciming
     * @date   2019/4/30
     */
    public static function checkPassword($password, $oldPassword)
    {
        return Hash::check($password, $oldPassword);
    }

    /**
     * 生成token
     *
     * @return mixed
     * @since  分支名称
     * @author chengciming
     * @date   2019/7/25
     */
    public static function createToken()
    {
        // 生成一个不会重复的字符串
        $str = md5(uniqid(md5(microtime(true)), true));
        $str = sha1($str);
        return $str;
    }

    /**
     * 生成订单编号
     *
     * @param string $suffix 单号前缀（C=平台订单）
     * @return string
     */
    public static function createOrderNumber($suffix = 'C')
    {
        // 当前年月日时分秒
        $year = substr(date('Y'), 2, 2);
        $orderNumber = $suffix . $year . date('mdHis');

        // 拼上4位随机数
        $orderNumber .= rand(1000, 9999);
        return $orderNumber;
    }


    /**
     * 生成随机字母与数字组合字符串
     * @param $len
     * @param null $chars
     * @return string
     */
    public static function getRandomString($len, $chars=null)
    {
        if (is_null($chars)){
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    /**
     * curl
     * @author chenjie <113157428@qq.com>
     */
    public static function curlRequest($url, $post = '', $contentType = 'application/json', $cookie = '', $returnCookie = 0)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");

        if (is_array($post)) {
            // 数组类型
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        } else if ($post) {
            // json类型
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: $contentType",
                'Content-Length: ' . strlen($post)
            ));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }

        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if ($returnCookie) {
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie'] = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        } else {
            return $data;
        }
    }


    /**
     * 生成4位邀请码，1位字母，3位数字
     * @param $len
     * @param null $chars
     * @return string
     */
    public static function createAgentShareCode()
    {
        $wordChars = "abcdefghijklmnopqrstuvwxyz";
        $numChars = "0123456789";
        $str = '';
        // 生成1位字母
        for ($i = 0, $lc = strlen($wordChars)-1; $i < 1; $i++){
            $str .= $wordChars[mt_rand(0, $lc)];
        }

        // 生成3位数字
        for ($i = 0, $lc = strlen($numChars)-1; $i < 3; $i++){
            $str .= $numChars[mt_rand(0, $lc)];
        }

        $str = str_shuffle($str);

        return $str;
    }

    /**
     * 获取分页数
     *
     * @param null $limit
     * @return int|mixed|null
     */
    public static function getLimit($limit = null)
    {
        $limit = $limit ?? 10;
        $limit = min($limit, 100);
        return $limit;
    }
}
