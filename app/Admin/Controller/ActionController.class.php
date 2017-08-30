<?php

namespace Admin\Controller;

use Think\Controller;

class ActionController extends Controller
{
    public function _initialize()
    {
        /*if (session('name') == '') {
            $this->redirect('admin/index/login');
        }*/

        //登录超时为一小时
        if (time() - session('last_time') > 3600) {
            session(null);
            $this->error('登录超时...', U('admin/index/index'));
        } else {
            //没有超过就更新登录时间
            session('last_time', time());
        }

    }
}