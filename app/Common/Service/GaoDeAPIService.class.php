<?php
/**
 * Created by PhpStorm.
 * User: liyongxiang
 * Date: 2017/11/7
 * Time: 上午11:28
 */
namespace Common\Service;

class GaoDeAPIService {
    //秘钥
    private static $key = '53c685021f6e5d00e94115ae37fc918a';
    //IP定位请求URL
    private static $urlIP = 'http://restapi.amap.com/v3/ip';
    //签名，选择数字签名认证的付费用户必填
    private static $sig = '';
    //返回格式，默认json
    private static $output = 'JSON';

    /**
     * 通过IP定位
     * @param string $ip
     * return array
     */
    public static function getPositionForGaoDeIP($ip){
        if (!ToolService::rule('ip',$ip)) return [404,'IP错误，无法定位'];
        $data = [
            'ip'=>'183.129.170.170',
            'output'=>self::$output,
            'key'=>self::$key,
        ];
        if (!empty(self::$sig) && isset(self::$sig)) $data['sig'] = self::$sig;
        $info = json_decode(gcurl(self::$urlIP,[],$data),true);
        if ($info['status'] < 1) return [404,'定位失败，错误码：'.$info['infocode'].'错误信息：'.$info['info']];
        return [200,$info];
    }
}