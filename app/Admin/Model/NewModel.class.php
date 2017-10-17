<?php

namespace Admin\Model;

use Think\Model;

class NewModel extends Model
{
    protected $tableName = 'news';

    protected $_validate = array(
        array('title', 'require', '标题必填'),
        array('title', '', '标题已经被使用！', 0, 'unique'),
        array('title', 'require', '标题必填'),
        array('content', 'require', '内容必填'),
    );

    protected $_auto = array(
        array('create_time', 'time', '1', 'function'),
        array('update_time', 'time', '1', 'function'),
        array('update_time', 'time', '2', 'function'),
    );

    /**
     * 判断用户是否存在（不需要这样写个方法）
     * +-----------------------------------------------------------
     * @functionName : uniqe
     * +-----------------------------------------------------------
     * @param int $title 标题
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     * @return bool
     */
    private function uniqe($title)
    {
        $map = array();
        $map['title'] = trim($title);
        $result = $this->db->where($map)->find();
        if ($result) {
            return false;
        } else {
            return true;
        }
    }
}