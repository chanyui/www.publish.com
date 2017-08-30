<?php

namespace Admin\Controller;

use Think\Controller;

class ActionController extends Controller
{
    public function _initialize()
    {
        if (!session('online')) {
            session(null);
            $this->error('登录失效', U('admin/index/index'));
            exit();
        }

    }
}