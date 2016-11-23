<?php
namespace Admin\Controller;

use Think\Controller;

class ActionController extends Controller
{
    public function _initialize()
    {
        if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {

            $this->redirect('admin/index/login');
        }
        /*
            $this->redirect('admin/index/login');
        } else {
            $this->redirect('admin/news/index');
        }
        */

        //登录超时为一小时
        if(time() - $_SESSION['last_time'] > 3600){
            session(null);
            $this->error('登录超时...',U('admin/index/index'));
        }

    }
}