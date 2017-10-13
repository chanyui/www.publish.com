<?php

namespace Utils\Controller;
vendor('Pinyin.vendor.autoload');

use Overtrue\Pinyin\Pinyin;
use Think\Controller;

class PinyinController extends Controller
{
    protected $pinyin;

    public function _initialize()
    {
        $this->pinyin = new Pinyin();
    }

    /**
     * 获取中文字的全拼
     * +-----------------------------------------------------------
     * @functionName : convert
     * +-----------------------------------------------------------
     * @param $opt string none(默认不带音标) unicode(带音标) ascii(音标用数字显示)
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function convert()
    {
        $str = I('post.str');
        $opt = I('post.opt') ?: 'none';
        if (!$str) {
            $this->ajaxReturn(array('code' => 100));
        } else {
            $fullPinyin = $this->pinyin->convert($str, $opt);
            $fullPinyin = implode($fullPinyin, '');
            $this->ajaxReturn(array('code' => 0, 'py' => $fullPinyin));
        }
    }

    /**
     * 获取中文首字母，返回大写字符串
     * +-----------------------------------------------------------
     * @functionName : abbr
     * +-----------------------------------------------------------
     * @param $opt string
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function abbr()
    {
        $str = I('post.str');
        $opt = I('post.opt') ?: '';
        if (!$str) {
            $this->ajaxReturn(array('code' => 100));
        } else {
            $firstPinyin = $this->pinyin->abbr($str, $opt);
            $this->ajaxReturn(array('code' => 0, 'py' => strtoupper($firstPinyin)));
        }
    }

    /**
     * 整段文字为拼音(带符号)
     * +-----------------------------------------------------------
     * @functionName : sentence
     * +-----------------------------------------------------------
     * @param $opt bool 为true时，可以显示声标
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function sentence()
    {
        $str = I('post.str');
        $opt = I('post.opt') == 'yse' ? true : false;
        if (!$str) {
            $this->ajaxReturn(array('code' => 100));
        } else {
            $fullPinyin = $this->pinyin->sentence($str, $opt);
            $this->ajaxReturn(array('code' => 0, 'py' => str_replace(' ', '', $fullPinyin)));
        }
    }

    /**
     * 生成带分隔符的全拼拼音字符串
     * +-----------------------------------------------------------
     * @functionName : permalink
     * +-----------------------------------------------------------
     * @param $opt string 默认分隔符为 -、，'_', '-', '.', ''
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function permalink()
    {
        $str = I('post.str');
        $opt = I('post.opt') ?: '-';
        if (!$str) {
            $this->ajaxReturn(array('code' => 100));
        } else {
            $fullPinyin = $this->pinyin->permalink($str, $opt);
            $this->ajaxReturn(array('code' => 0, 'py' => $fullPinyin));
        }
    }

    /**
     * 翻译姓名
     * +-----------------------------------------------------------
     * @functionName : name
     * +-----------------------------------------------------------
     * @param $opt @param $opt string none(默认不带音标) unicode(带音标) ascii(音标用数字显示)
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function name()
    {
        $str = I('post.str');
        $opt = I('post.opt') ?: 'ascii';
        if (!$str) {
            $this->ajaxReturn(array('code' => 100));
        } else {
            $name = $this->pinyin->name($str, $opt);
            $this->ajaxReturn(array('code' => 0, 'py' => $name));
        }
    }
}