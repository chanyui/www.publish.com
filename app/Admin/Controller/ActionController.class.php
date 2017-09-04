<?php

namespace Admin\Controller;

use Think\Controller;

class ActionController extends Controller
{
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
        }
    }
}