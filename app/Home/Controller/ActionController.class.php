<?php

namespace Home\Controller;

use Think\Controller;

class ActionController extends Controller
{
    public function _initialize()
    {
        if (!method_exists($this, ACTION_NAME) || !method_exists($this, strtolower(ACTION_NAME))) {
            $this->redirect('index/index');
        }
    }

}