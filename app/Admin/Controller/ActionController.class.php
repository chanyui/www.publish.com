<?php

namespace Admin\Controller;

use Think\Controller;

class ActionController extends Controller
{
    /**
     * 初始化(为过期就更新登录时间，过期就退出)
     * +-----------------------------------------------------------
     * @functionName : _initialize
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function _initialize()
    {
        $online = session('online');

        //使用默认的文件保存session方式
        /*if (!$online || time() - $online['expire'] > 3600) {
            session(null);
            $this->error('登录失效', U('admin/index/index'));
            exit();
        } else {
            session('online.expire', time());
        }*/

        //使用redis保存session
        if (!$online || time() - $online['expire'] > 3600) {
            session(null);
            $this->error('登录失效', U('admin/index/index'));
            exit();
        } else {
            session('online.expire', time());
            $this->assign('name', $online['name']);
        }
    }
}