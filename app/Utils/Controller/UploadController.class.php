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
     * 上传文件
     * +-----------------------------------------------------------
     * @functionName : image
     * +-----------------------------------------------------------
     * @param string local:上传服务器本地 qiuniu:上传七牛云
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
                    $res = array('code' => -4, 'data' => $upload->getError());
                } else {
                    $res = array('code' => 0, 'data' => $info['file']['savepath'] . $info['file']['savename']);
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
     * 使用uploadify插件上传文件
     * +-----------------------------------------------------------
     * @functionName : upload
     * +-----------------------------------------------------------
     * @param string local:上传服务器本地 qiuniu:上传七牛云
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
}