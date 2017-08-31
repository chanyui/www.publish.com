<?php

namespace Admin\Controller;

use Think\Controller;

class IndexController extends Controller
{

    protected $db;

    function _initialize()
    {
        if (!method_exists($this, strtolower(ACTION_NAME))) {
            $this->redirect('index/index');
        } else {
            $this->db = D("User");
        }
    }


    public function index()
    {
        $this->display('index');
    }

    /**
     * 注册功能
     * @param $data =array(); 为用户、密码、时间的数组
     */
    public function register()
    {
        if (IS_POST) {
            $data['name'] = I('post.name');
            $data['passowrd'] = I('post.password');
            $data['password'] = md5($data['passowrd']);
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];
            if (!$data['name'] || !$data['password']) {
                $this->error('用户名或密码不能为空');
                exit;
            }
            $result = $this->unique($_POST['name']);
            if ($result) {
                $add = $this->db->add($data);
                if ($add) {
                    $this->success('注册成功', U('index/index'));
                } else {
                    $this->error('注册失败', U('index/register'));
                }
            } else {
                $this->error('用户已存在', U('index/register'));
            }
        } else {
            $this->display();
        }

        /*
         if (IS_POST) {
            $data['name'] = I('post.');
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];
            if (!$this->db->create($data)) {
                $this->error($this->db->getError());
            } else {
                $add = $this->db->add();
                if ($add) {
                    $this->success('注册成功',U('index/index'));
                } else {
                    $this->error('注册失败',U('index/register'));
                }
            }
        } else {
            $this->display();
        }
        */
    }


    /**
     * 登录功能
     */
    public function login()
    {
        if (IS_POST) {
            $name = I('post.name');
            $pwd = I('post.password');
            $code = strtoupper(I('post.verifyCode'));
            if (!$name || !$pwd) {
                $this->error('用户名或密码不能为空');
                exit();
            }
            $pwd = md5($pwd);

            if (session('authimg') !== $code) {
                $this->error('验证码不正确');
                exit();
            }
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];

            $where = array();
            $where['name'] = $name;
            $user = $this->db->where($where)->find();
            if ($user) {
                if ($pwd == $user['password']) {
                    $online = array(
                        'name' => $user['name'],
                        'expire' => $data['create_time']
                    );
                    session('online', $online);
                    $this->success('登录成功', U('Admin/news/index'));
                } else {
                    $this->error('密码错误', U('index'));
                }
            } else {
                $this->error('登录失败');
            }
        } else {
            $this->display('index');
        }
    }


    /**
     * 用户注销，退出登录
     *
     */
    public function logout()
    {
        session(null);
        $this->redirect('admin/index/index');
    }

    /**
     * 用户名唯一性
     * @param string $name 参数组装条件
     * @return bool true表示不存在相同的用户
     */
    private function unique($name)
    {
        $map['name'] = trim($name);
        $where = $this->db->where($map)->find();
        if ($where) {
            return false;
        } else {
            return true;
        }
    }
}