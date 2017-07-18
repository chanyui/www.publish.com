<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function _initialize()
    {
        if (!method_exists($this, ACTION_NAME) || !method_exists($this, strtolower(ACTION_NAME))) {
            $this->redirect('index/index');
        }
    }

    /**
     * 首页列表
     * +-----------------------------------------------------------
     * @functionName : index
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function index()
    {
        $news = D('News');
        $where = array();
        $where['status'] = 1;
        $limit = 20;
        $count = $news->where($where)->count();
        $page = new \Think\Page1($count, $limit);
        $show = $page->show();
        $list = $news->where($where)->order('id asc')->limit($page->firstRows . ',' . $page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->display();
    }

    /**
     * 查询
     * +-----------------------------------------------------------
     * @functionName : search
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function search()
    {
        $keyword = I('get.title');
        if (!$keyword || $keyword == '') {
            $this->error('请输入关键词！');
            exit;
        }
        $where = array();
        $where['title'] = array('like', '%' . $keyword . '%');
        $limit = 10;
        $count = D('News')->where($where)->count();
        $page = new \Think\Page1($count, $limit);
        $show = $page->show();
        $list = D('News')->where($where)->order('id asc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('count', $count);
        $this->assign('page', $page);
        $this->assign('title', $keyword);
        $this->assign('list', $list);
        $this->display('index');
    }

    /**
     * SQL中case when用法
     * +-----------------------------------------------------------
     * @functionName : caseWhen
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function caseWhen()
    {
        $db = D('News');
        $field = '';
        $field .= 'id,title,';
        $field .= "(case when status=0 then title when status=1 then id else 0 end) as res";
        $sql = "select " . $field . " from " . $db->getTableName() . " order by create_time asc";
        $result1 = $db->query($sql);
        echo $db->_sql();
        dump($result1);
    }

    /**
     * 原生ajax
     * +-----------------------------------------------------------
     * @functionName : ajaxIndex
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function ajaxIndex()
    {
        $this->display();
    }

    /**
     * ajax返回数据
     * +-----------------------------------------------------------
     * @functionName : ajax
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function ajax()
    {
        $post['name'] = $_POST['name'];
        $this->ajaxReturn($post);
    }
}