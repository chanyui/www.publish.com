<?php

namespace Admin\Controller;

class NewsController extends ActionController
{
    protected $db;

    function _initialize()
    {
        if (!method_exists($this, strtolower(ACTION_NAME))) {
            $this->redirect('index/index');
        } else {
            parent::_initialize();
            $this->db = D('News');
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
        $keyword = I('get.keyword');
        $where = array();

        //搜索
        if ($keyword) {
            $where['title'] = array('like', '%' . $keyword . '%');
        }

        $count = $this->db->where($where)->count();
        $limit = 20;
        $page = new \Think\Page1($count, $limit);
        $show = $page->show();
        $list = $this->db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('update_time desc')->select();
        foreach ($list as $key => $value) {
            if (!$value['codeimg']) {
                $qrCode = $this->createQrCode($value['id']);
                $this->db->where(array('id' => $value['id']))->setField('codeimg', $qrCode);
            }
        }

        $this->assign('keyword', $keyword);
        $this->assign('count', $count);
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->display();
    }


    /**
     * 添加数据
     * +-----------------------------------------------------------
     * @functionName : add
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function add()
    {
        if (IS_POST) {
            if (!$this->db->create()) {
                $this->error($this->db->getError());
                exit();
            } else {
                if ($this->db->add()) {
                    $this->success('添加成功', U('news/index'));
                } else {
                    $this->error('添加失败');
                }
            }
            /*$data = array();
            $data['title'] = I('post.title');
            $data['content'] = I('post.content');
            $data['status'] = I('post.status');
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];
            $where = $this->uniqe($data['title']);
            if ($where) {
                $result = $this->db->add($data);
                if ($result) {
                    $this->success('成功', U('news/index'));
                } else {
                    $this->error('失败');
                }
            } else {
                $this->error('信息已存在', U('news/add'));
            }*/
        } else {
            $this->display();
        }

    }


    /**
     * 编辑操作
     * +-----------------------------------------------------------
     * @functionName : edit
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function edit()
    {
        if (IS_POST) {
            if (!$this->db->create()) {
                $this->error($this->db->getError());
                exit();
            } else {
                if ($this->db->save()) {
                    $this->success('添加成功', U('news/index'));
                } else {
                    $this->error('添加失败');
                }
            }
        } else {
            $id = I('id');
            $result = $this->db->where(array('id'=>$id))->find();
            $this->assign('result', $result);
            $this->display();
        }
    }

    /**
     * 详情
     * +-----------------------------------------------------------
     * @functionName : detail
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function detail()
    {
        $id = I('get.id');
        $info = $this->db->where(array('id' => $id))->find();
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 删除操作
     * +-----------------------------------------------------------
     * @functionName : del
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function del()
    {
        $id = trim(I('id'));
        if ($this->db->where(array('id=' => $id))->delete()) {
            $this->success('删除成功', U('news/index'));
        } else {
            $this->error('删除失败');
        }
    }


    /**
     * 搜索(模糊查询)
     * +-----------------------------------------------------------
     * @functionName : search
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function search()
    {
        $keyword = I("keyword");
        if (!$keyword || $keyword == '') {
            $this->redirect('news/index');
            exit();
        } else {
            $where = array();
            $where['title'] = array('like', '%' . $keyword . '%');
            $limit = 20;
            $count = $this->db->where($where)->count();
            $page = new \Think\Page1($count, $limit);
            $show = $page->show();
            $list = $this->db->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

            $this->assign('count', $count);
            $this->assign('page', $show);
            $this->assign('list', $list);
            $this->assign('keyword', $keyword);
            $this->display('index');
        }
    }

    /**
     * 修改字段的状态
     * +-----------------------------------------------------------
     * @functionName : status
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function status()
    {
        $id = I('get.id');

        $info = $this->db->where(array('id' => $id))->find();

        $this->db->id = $info['id'];
        $this->db->status = $info['status'] == 0 ? 9 : 0;
        $msg = $info['status'] == 0 ? '禁用' : '启用';

        if ($this->db->save()) {
            $this->success($msg . '成功', U(strtolower(CONTROLLER_NAME) . '/index'));
        } else {
            $this->error($msg . '失败', U(strtolower(CONTROLLER_NAME) . '/index'));
        }
    }

    /**
     * 生成二维码
     * +-----------------------------------------------------------
     * @functionName : createQrCode
     * +-----------------------------------------------------------
     * @param int $id 内容id
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    private function createQrCode($id)
    {
        vendor('phpQrCode.phpqrcode');                  //引入phpqrcode类
        $qrcode = new \QRcode();

        //本地测试分享的地址，线上需另行配置
        $data = '192.168.13.203/github_project/www.yc.com/share/index?id=' . $id;
        //本地需要这些代码，保存在服务器本地
        $logo = ROOT_PATH . "/Public/css/img/logo1.png"; //中间的logo
        if (!is_dir(C('QRCODE_DIR'))) {
            if (!mkdir(C('QRCODE_DIR'), 0755)) {
                E("路径'" . C('QRCODE_DIR') . "'创建失败！");
            }
        }
        $name = uniqid();
        $fileName = 'qrcode/' . $name . '.png';
        $errorCorrectionLevel = 'L';        //纠错级别：L、M、Q、H
        $matrixPointSize = 4;               //点的大小：1到10

        $qrcode::png($data, $fileName, $errorCorrectionLevel, $matrixPointSize, 2);
        return $fileName;
    }
}
