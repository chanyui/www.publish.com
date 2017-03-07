<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2016/8/2
 * Time: 13:40
 */

namespace Admin\Model;

use Think\Model;

class UserModel extends Model
{
    /**
     * @var array
     */
    protected $_validate = array(
        array('name','require','用户名不能为空'),
        array('name','/^([\w\d_]){4,20}$/','用户名长度限制在4-20位，并且只能是英文或数字包括下划线!'),
        array('password','require','密码不能为空'),
        array('password','/^([\w\d]){8,16}$/','密码必须是8-16位，并只能是英文和数字',0,'regex',3),
    );

    /**
     * @var array
     */
    protected $_auto =array(
        array('password','md5',1,'function'),
        array('update_time','time',2,'function'),
    );
}