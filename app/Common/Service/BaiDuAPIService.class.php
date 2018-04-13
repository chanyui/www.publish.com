<?php
/**
 * Created by PhpStorm.
 * User: Lance Li
 * Date: 2017/11/7
 * Time: 上午9:50
 */

namespace Common\Service;

/**
 * 如果需要城市的正确adcode编码请用高德定位
 */
class BaiDuAPIService
{
    //秘钥
    private static $ak = 'syDX6Inu6LxQs8uGSRw3KxGb88yCbfX3';
    //IP定位请求URL
    private static $urlIP = 'http://api.map.baidu.com/location/ip';
    /**
     * 当前需要返回的坐标类型,
     * coor不出现、或为空：百度墨卡托坐标，即百度米制坐标;
     * coor = bd09ll：百度经纬度坐标，在国测局坐标基础之上二次加密而来;
     * coor = gcj02：国测局02坐标，在原始GPS坐标基础上，按照国家测绘行业统一要求，加密后的坐标；
     */
    private static $coor = '';

    /**
     * 通过IP定位
     * @param string $ip
     * return array
     */
    public static function getPositionForBaiDuIP($ip)
    {
        if (!ToolService::rule('ip', $ip)) return [404, 'IP错误，无法定位'];
        $data = [
            'ip' => $ip,
            'ak' => self::$ak,
            'coor' => self::$coor,
        ];
        $info = json_decode(gcurl(self::$urlIP, [], $data), true);
        if ($info['status'] > 0) return [404, '定位失败，错误码：' . $info['status'] . '，错误信息：' . $info['message']];
        return [200, $info];
    }
}