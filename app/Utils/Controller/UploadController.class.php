<?php

namespace Utils\Controller;

use Think\Controller;

class UploadController extends Controller
{
    public function index()
    {
        $this->display();
    }

    /**
     * 上传文件(未保存到库)
     * +-----------------------------------------------------------
     * @functionName : image
     * +-----------------------------------------------------------
     * @param local:上传服务器本地 qiuniu:上传七牛云
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function image()
    {
        $allowType = array('image/jpeg', 'image/png', 'image/gif', 'image/jpg');
        $uploadType = array('local', 'qiniu');
        $type = I('post.type');

        do {
            if (!$type || !in_array($type, $uploadType)) {
                $res = array("code" => -1, "msg" => "上传参数不正确", "data" => array());
                break;
            }
            if (!in_array($_FILES['file']['type'], $allowType)) {
                $res = array("code" => -2, "msg" => "上传失败,只支持jpg、gif、png格式的图片", "data" => array());
                break;
            }
            if ($_FILES['file']['size'] > 2 * 1024 * 1024) {
                $res = array('code' => -3, 'msg' => '上传文件大小超过2M');
                break;
            }
            if ($type == 'local') {
                $config = array(
                    'maxSize' => 3145728,
                    'rootPath' => './Uploads/',
                    'savePath' => '',
                    'saveName' => array('uniqid', ''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date', 'Y-m-d'),
                );
                $upload = new \Think\Upload($config);
                $info = $upload->upload();
                if (!$info) {
                    $res = array('code' => -4, 'msg' => $upload->getError());
                } else {
                    $res = array('code' => 0, 'msg' => $info['file']['savepath'] . $info['file']['savename']);
                }
            } elseif ($type == 'qiniu') {
                $upload = new \Think\Upload(C('UPLOAD_SITEIMG_QINIU'));
                $info = $upload->upload($_FILES);
                if ($info) {
                    $res = array('code' => 0, 'data' => array('imgpath' => $info['Filedata']['url']));
                } else {
                    $res = array('code' => -5, 'data' => '文件上传七牛云失败');
                }
            }
        } while (0);
        $this->ajaxReturn($res);
    }

    /**
     * 使用uploadify插件上传文件(未保存到库)
     * +-----------------------------------------------------------
     * @functionName : upload
     * +-----------------------------------------------------------
     * @param local:上传服务器本地 qiuniu:上传七牛云
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function upload()
    {
        $uploadType = array('local', 'qiniu');
        $type = I('get.type');

        if (!$type || !in_array($type, $uploadType)) {
            $res = array('code' => 500, 'data' => '上传参数错误');
        } else {
            switch ($type) {
                case 'qiniu':  //上传到七牛云
                    $upload = new \Think\Upload(C('UPLOAD_SITEIMG_QINIU'));
                    $info = $upload->upload($_FILES);
                    if ($info) {
                        $this->ajaxReturn(array('code' => 0, 'data' => array('imgpath' => $info['Filedata']['url'])));
                        exit;
                    } else {
                        $this->ajaxReturn(array('code' => 9, 'data' => '文件上传七牛云失败'));
                        exit;
                    }
                    break;
                case 'local':
                    $config = array(
                        'maxSize' => 3145728,
                        'rootPath' => './Uploads/',
                        'savePath' => '',
                        'saveName' => array('uniqid', ''),
                        'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                        'autoSub' => true,
                        'subName' => array('date', 'Y-m-d'),
                    );
                    $upload = new \Think\Upload($config);
                    $info = $upload->upload();
                    if (!$info) {
                        $res = array('code' => 100, 'data' => $upload->getError());
                    } else {
                        $res = array('code' => 0, 'data' => $info['Filedata']['savepath'] . $info['Filedata']['savename']);
                    }
                    break;
            }
        }
        $this->ajaxReturn($res);
    }

    /**
     * 上传多个文件
     * +-----------------------------------------------------------
     * @functionName : uploadMore
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function uploadMore()
    {
        $config = array(
            'maxSize' => 3145728,
            'rootPath' => './Uploads/',
            'savePath' => '',
            'saveName' => array('uniqid', ''),
            'exts' => array('jpg', 'gif', 'png', 'jpeg'),
            'autoSub' => true,
            'subName' => array('date', 'Y-m-d'),
        );
        $upload = new \Think\Upload($config);
        $info = $upload->upload();
        if (!$info) {
            $ret = array('code' => 100, 'data' => $upload->getError());
        } else {
            $ret = array('code' => 0, 'data' => $info['Filedata']['savepath'] . $info['Filedata']['savename']);
        }
        $this->ajaxReturn($ret);
    }

    /**
     * ajax上传Excel
     * +-----------------------------------------------------------
     * @functionName : excel
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function excel()
    {
        $fielffix = strtolower(end(explode('.', $_FILES['excel']['name'])));
        $allowffix = array('xls', 'xlsx');
        do {
            if (!IS_AJAX) {
                $ret = array('code' => 100, 'msg' => '非法请求', 'data' => array());
                break;
            }
            if (!in_array($fielffix, $allowffix)) {
                $ret = array('code' => 300, 'msg' => '上传失败，只支持Excel文件格式', 'data' => array());
                break;
            }
            if ($_FILES['excel']['size'] > 2 * 1024 * 1024) {
                $ret = array('code' => 400, 'msg' => '上传失败，文件超过2M', 'data' => array());
                break;
            }
            $upload = new \Think\Upload();              // 实例化上传类
            $upload->rootPath = './data/';         // 设置附件上传根目录
            $upload->savePath = 'user-' . uniqid() . '/'; // 设置附件上传（子）目录
            $info = $upload->upload();
            if (!$info) {
                $ret = array("code" => 500, "msg" => "上传失败," . $upload->getError(), "data" => array());
            } else {
                $ret = array("code" => 0, "msg" => "上传成功", "data" => array('filePath' => $upload->rootPath . $info['excel']['savepath'] . $info['excel']['savename']));
            }
        } while (0);
        $this->ajaxReturn($ret);
    }
}