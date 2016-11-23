<?php
namespace Admin\Controller;

use Think\Controller;

class NewsController extends ActionController
{
    protected $db;

    function _initialize()
    {
        if (!method_exists($this,strtolower(ACTION_NAME))) {
            $this->redirect('index/index');
        } else {
            parent::_initialize();
            $this->db = D('News');
        }
    }


    /**
     * 查找数据库中的数据并显示在首页上，设置分页
     * @param $show 在页面显示分页
     * @param 
     */
    public function index()
    {
        $count = $this->db->count();
        $where['status'] = 1;
        if ($count > 0) {
            $limit = 20;
            $page = new \Think\Page1($count, $limit);
            $show = $page->show();
            $list = $this->db->order('id asc')->limit($page->firstRow . ',' . $page->listRows)->select();
            /*
            $res = $this->db->getField('id',true);
            var_dump($res);
            */  //返回数组，其中value就是id的值

            /*
            $res = $this->db->getBytitle('iOS 10');
            var_dump($res);die;
            */   //根据title=iOS 10 找出这一条数据，相当于find()

            /*
            $res = $this->db->getFieldBytitle('iOS 10','content');
            var_dump($res);die;
            */   //根据title=iOS 10，找出这条数据所对应content字段的内容
        }
        $name = $_SESSION['name'];
        $this->assign('param',I(''));
        $this->assign('count', $count);
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->assign('name', $name);
        $this->display();
    }


    /**
     * 表单的提交数据写入数据库
     * @param
     */
    public function add()
    {
        if (IS_POST) {
            $data = I('post.');
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];
            $where = $this->uniqe($_POST['title']);
            if ($where) {
                $resu = $this->db->add($data);
                if ($resu) {
                    $this->success('成功', U('index'));
                } else {
                    $this->error('失败');
                }
            } else {
                $this->error('信息已存在', U('news/add'));
            }
        } else {
            $this->display();
        }

    }


    /**
     * 编辑功能 当没有提交表单时执行else语句块
     * @param IS_POST表单提交的数据
     */
    public function edit()
    {
        if (IS_POST) {
            $data = I('post.');
            $data['update_time'] = time();
            $updata = $this->db->save($data);
            if ($updata) {
                $this->success('修改成功', U('index'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $id = I('id');
            $result = $this->db->where('id=' . $id)->find();
            $this->assign('result', $result);
            $this->display();
        }
    }


    /**
     * 删除所选的数据
     * @para $id 利用id来选择所对应的信息
     */
    public function del()
    {
        $id = trim(I('id'));
        if ($this->db->where('id=' . $id)->delete()) {
            $this->success('删除成功', U('index'));
        } else {
            $this->error('删除失败');
        }
    }


    /**
     *利用like模糊查找
     * @param
     */
    public function search()
    {
        $title = I("title");
        if (!$title || $title == '') {
            $this->error('请输入关键字', U('news/index'));
            exit();
        } else {
            $where['title'] = array('like', '%' . $title . '%');
            $limit = 8;
            $count = $this->db->where($where)->count();
            $page = new \Think\Page1($count, $limit);
            $show = $page->show();
            $list = $this->db->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
            $name = $_SESSION['name'];
            $this->assign('count', $count);
            $this->assign('page', $show);
            $this->assign('list', $list);
            $this->assign('key', $title);
            $this->assign('name', $name);
            $this->display('index');
        }
    }

    /**
     * 改变字段的状态
     * @param $id
     * @param $result
     */
    public function status(){
        $id = I('get.id');
        $statusvalue = I('get.statusvalue');
        $where = array();
        $where['id'] = $id;
        $data['status'] = $statusvalue;
        $result = $this->db->where($where)->save($data);
        if($result){
            $this->redirect(strtolower(CONTROLLER_NAME).'/index');
        }else{
            $this->redirect(strtolower(CONTROLLER_NAME).'/index');
        }
    }

    /**
     * 判断用户名是否已存在
     * @param $id 获取过来的id
     * @param return false id已存在
     */
    private function uniqe($id)
    {
        $map['title'] = trim($id);
        $result = $this->db->where($map)->find();
        if ($result) {
            return false;
        } else {
            return true;
        }
    }
}
