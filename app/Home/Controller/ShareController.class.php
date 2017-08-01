<?php

namespace Home\Controller;

use Think\Controller;

class ShareController extends Controller
{
    /**
     * 二维码分享页
     * +-----------------------------------------------------------
     * @functionName : index
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function index()
    {
        $id = I('get.id');
        $info = D('News')->where(array('id' => $id))->find();
        $this->assign('info', $info);
        $this->display();
    }

}