<?php

namespace Home\Controller;

use Home\Controller\ActionController;

class IndexController extends ActionController
{
    public function _initialize()
    {
        parent::_initialize();
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
        $where['status'] = 0;
        $limit = 20;
        $count = $news->where($where)->count();
        $page = new \Think\Page1($count, $limit);
        $show = $page->show();
        $list = $news->where($where)->order('id asc')->limit($page->firstRow . ',' . $page->listRows)->select();
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
        $this->assign('page', $show);
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

    /**
     * 验证码
     * +-----------------------------------------------------------
     * @functionName : authimg
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function authimg()
    {
        header("Content-Type:text/html; charset=utf-8");
        $this->createCode(80, 42, 20, 5, 50, 4, "createCode", "255,255,113", '92,189,170');
    }

    /**
     * GD库生成验证码
     * +-----------------------------------------------------------
     * @functionName : createCode
     * +-----------------------------------------------------------
     * @param int $width 图像宽度
     * @param int $height 图像高度
     * @param int $leftX 字符串距离图像X坐标
     * @param int $font 字体
     * @param int $pointNum 像素点数量
     * @param int $length 字符串长度
     * @param string $sname 名称
     * @param string $fontColor 字体颜色
     * @param string $bgColor 背景颜色
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    private function createCode($width, $height, $leftX, $font, $pointNum, $length, $sname = "", $fontColor = "255,255,255", $bgColor = "255,255,255")
    {
        $left = 3;
        $move = 2;
        $str = "9823456789ABCDEFGHIJKLMNZPQRSTUVWXYZ";
        $authStr = "";
        while (strlen($authStr) < $length) {
            $authStr .= substr($str, rand(0, strlen($str)), 1);
        }
        if ($sname != "") {
            session('authimg', $authStr);
        }
        $image = imagecreate($width, $height);
        $fontColor = explode(',', $fontColor);
        $fontColor = imagecolorallocate($image, $fontColor[0], $fontColor[1], $fontColor[2]);
        $bgColor = explode(',', $bgColor);
        $bgColor = imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);
        imagefill($image, 0, 0, $bgColor);
        for ($i = 0; $i < strlen($authStr); $i++) {
            $y = ($height - imagefontheight($font)) / 2 - $move + rand(0, $move * 2);
            imagestring($image, $font, $leftX * $i + $left, $y, substr($authStr, $i, 1), $fontColor);
        }
        for ($i = 1; $i <= $pointNum; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $bgColor);
        }
        header("Content-type: image/PNG");
        imagepng($image);
        imagedestroy($image);
    }

    /**
     * tp验证类生成验证码
     * +-----------------------------------------------------------
     * @functionName : verifyCode
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function verifyCode()
    {
        $config = array(
            'useImgBg' => false,                // 使用背景图片
            'fontSize' => 15,                   // 验证码字体大小(px)
            'useCurve' => true,                 // 是否画混淆曲线
            'useNoise' => false,                // 是否添加杂点
            'imageW'   => 120,                  // 验证码图片宽度
            'imageH'   => 40,                   // 验证码图片高度
            'length'   => 4,                    // 验证码位数
            'fontttf'  => '',                   // 验证码字体，不设置随机获取
            'bg'       => array(243, 251, 254), // 背景颜色
            'reset'    => false,                // 验证成功后是否重置
        );
        $verify = new \Think\Verify($config);
        $verify->entry();
    }
}