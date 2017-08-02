<?php

namespace Admin\Controller;

use Think\Controller;

class ServerController extends Controller
{
    protected $codeArr = array(
        '0' => '成功',
        '9' => '失败',
        '404' => '接口不存在'
    );
    protected $debug = false;

    public function index()
    {
        //处理跨域问题
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $point = $_REQUEST['point'] ? false : $this->debug;
        $param = $this->parse_param($_REQUEST['param'], $point);
        $obj = A($param['class'], CONTROLLER_NAME);
        if ($obj) {
            $return = $obj->$param['method']($param['param']);
            $this->treat_return($return, $this->debug);
        } else {
            $this->treat_return(array('code' => '404'), $this->debug);
        }
    }

    /**
     * 解析参数
     * +------------------------------------------------------------------
     * @functionName : parse_param
     * +------------------------------------------------------------------
     * @param array $param 参数
     * @param bool $point 默认参数不加密
     * +------------------------------------------------------------------
     * @author yucheng
     * +------------------------------------------------------------------
     */
    protected function parse_param($param, $point = false)
    {
        $rsa = new \Org\Rsa\Rsa('shop');
        $point ? $param = $rsa->privEncrypt(base64_decode($param)) : $param = $param;
        $param = json_decode($param, true);
        $t = explode('_', $param['t']);
        $post = array();
        if ($param['post']) {
            foreach ($param['post'] as $key => $value) {
                $post[$key] = htmlspecialchars($value);
            }
        }
        return array('class' => $t[0], 'method' => $t[1], 'param' => $post);
    }

    /**
     * 处理返回值
     * +------------------------------------------------------------------
     * @functionName : treat_return
     * +------------------------------------------------------------------
     * @param array $return 参数
     * @param bool $point 默认参数不加密
     * +------------------------------------------------------------------
     * @author yucheng
     * +------------------------------------------------------------------
     */
    protected function treat_return($return, $point = false)
    {
        $return['msg'] = $this->codeArr[$return['code']];
        echo $point ? base64_encode(json_encode($return)) : json_encode($return);
    }
}