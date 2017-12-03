<?php
/**
 * cURL功能（post）
 * Function:xcurl
 * @return mixed
 * @param string $url 地址
 * @param null $ref 包含一个”referer”头的字符串
 * @param array $post 参数
 * @param string $ua
 * @param bool|false $print
 */
function xcurl($url, $ref = null, $post = array(), $ua = "Mozilla/5.0 (X11; Linux x86_64; rv:2.2a1pre) Gecko/20110324 Firefox/4.2a1pre", $print = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    if (!empty($ref)) {
        curl_setopt($ch, CURLOPT_REFERER, $ref);
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (!empty($ua)) {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    }
    if (count($post) > 0) {
        $o = "";
        foreach ($post as $k => $v) {
            $o .= "$k=" . urlencode($v) . "&";
        }
        $post = substr($o, 0, -1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $output = curl_exec($ch);
    curl_close($ch);
    if ($print) {
        print($output);
    } else {
        return $output;
    }
}

/**
 * cURL功能（get）
 * Function:gcurl
 * @return mixed
 * @param string $url 地址
 * @param array $header 请求头
 * @param array $get
 * @param string $ua
 * @param bool|false $print
 */
function gcurl($url, $header = array(), $get = array(), $ua = "Mozilla/5.0 (X11; Linux x86_64; rv:2.2a1pre) Gecko/20110324 Firefox/4.2a1pre", $print = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    if (count($get) > 0) {
        $o = "";
        foreach ($get as $k => $v) {
            $o .= "$k=" . urlencode($v) . "&";
        }
        $get = substr($o, 0, -1);
        $url = $url . '?' . $get;
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (!empty($ua)) {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    }

    $output = curl_exec($ch);
    curl_close($ch);
    if ($print) {
        print($output);
    } else {
        return $output;
    }
}

/**
 * 获取随机码
 * Function:random
 * @return string
 * @param int $length 随机码的长度
 * @param int $numeric 0是字母和数字混合码，不为0是数字码
 */
function random($length, $numeric = 0)
{
    PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
    $seed = base_convert(md5(print_r($_SERVER, 1) . microtime()), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed[mt_rand(0, $max)];
    }
    return $hash;
}

/**
 * 加密解密（可逆）
 * Function:authcode
 * @return string
 * @param string $string 加密的字符串
 * @param string $operation DECODE表示解密,其它表示加密
 * @param string $key 密钥
 * @param int $expiry 密文有效期
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key ? $key : "da7b4db15be94a4c597a34f9cf902b01");
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }

}

/**
 * 加密（不可逆）
 * Function:encrypt
 * @return string
 * @param string $password 原始密码
 * @param string $salt 密钥
 */
function encrypt($password, $salt)
{
    $slt = $password . '{' . $salt . "}";
    $h = 'sha256';

    $digest = hash($h, $slt, true);

    for ($i = 1; $i < 5000; $i++) {
        $digest = hash($h, $digest . $slt, true);
    }

    return base64_encode($digest);
}

/**
 * 加密（可逆）
 * Function:wotu_crypt
 * @return string
 */
function wotu_crypt($str, $op = 'enc', $key = 'wotu')
{
    $from = array('/', '=', '+');
    $to = array('-', '_', '.');
    if ($op == 'enc') {
        $prep_code = serialize($str);
        $block = mcrypt_get_block_size('des', 'ecb');
        if (($pad = $block - (strlen($prep_code) % $block)) < $block) {
            $prep_code .= str_repeat(chr($pad), $pad);
        }
        $encrypt = mcrypt_encrypt(MCRYPT_DES, $key, $prep_code, MCRYPT_MODE_ECB);
        return str_replace($from, $to, base64_encode($encrypt));
    } else if ($op == 'dec') {
        $str = str_replace($to, $from, $str);
        $str = base64_decode($str);
        $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
        $block = mcrypt_get_block_size('des', 'ecb');
        $pad = ord($str[($len = strlen($str)) - 1]);
        if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) {
            $str = substr($str, 0, strlen($str) - $pad);
        }
        return unserialize($str);
    }
}

/**
 * 生成密码
 * Function:get_password
 * @return string
 * @param string $password 原始密码
 * @param string $salt 密钥
 */
function get_password($password, $salt)
{
    return encrypt($password, $salt);
}

/**
 * 验证密码
 * Function:check_password
 * @return bool
 * @param string $password 原始密码
 * @param string $salt 密钥
 * @param string $pwd 加密后密码
 */
function check_password($password, $salt, $pwd)
{
    if (get_password($password, $salt) == $pwd) {
        return true;
    } else {
        return false;
    }
}

/**
 * 手机发送验证码
 * Function:send_by_phone
 * @return bool
 * @param int $phone 手机号码
 * @param string $message 模板id为0是发送信息，模板id不为0是者模板值
 * @param int $tpl_id 模板id
 */
function send_by_phone($phone, $message, $tpl_id = 0)
{
    if (!$phone || !$message) {
        return false;
        exit;
    }

    $apikey = 'b66e3c9ac9e3a877d690560892c43d5f';
    $mobile = $phone;

    $sendSms = new \Org\sendSms();
    if ($tpl_id) {
        $tpl_value = $message;
        $output = $sendSms->tpl_send_sms($apikey, $tpl_id, $tpl_value, $mobile);
    } else {
        $text = $message;
        $output = $sendSms->send_sms($apikey, $text, $mobile);
    }
    $output = json_decode($output, true);
    if ($output['code'] != 0) {
        return false;
    } else {
        return true;
    }
}


/**
 * 创建session
 * Function:set_session
 * @return bool
 * @param array $session session的数组
 * @param string $name session存储的名称
 */
function set_session($session, $name)
{
    if (is_array($session)) {
        $session = json_encode($session);
    }
    $key = C('secret_key');
    $session = authcode($session, 'ENCODE', $key);
    session($name, $session);
}

/**
 * 获得session
 * Function:get_session
 * @return array|bool
 * @param string $name session存储的名称
 */
function get_session($name)
{
    $key = C('secret_key');
    $auth = session($name);

    if ($auth) {
        $session = authcode($auth, 'DECODE', $key);
        $ary = json_decode($session, true);
        if (!$ary) {
            $ary = $session;
        }
        return $ary;
    } else {
        return false;
    }
}

/**
 * 设置用户在线
 * Function:set_online
 * @param $uid
 */
function set_online($uid)
{
    $db = M('user');
    $tmp = array(
        'uid' => $uid,
        'lastIp' => $_SERVER['REMOTE_ADDR'],
        'lastTime' => time()
    );
    $db->save($tmp);
    $db = M('session_online');
    $liveTime = time() + C('SESSION_EXPIRY');
    $expriy = array(
        'uid' => $uid,
        'expiry' => $liveTime,
        'sessionId' => session_id()
    );
    $isOn = $db->where('uid = "' . $uid . '"')->find();
    if ($isOn) {
        $db->save($expriy);
    } else {
        $db->add($expriy);
    }
}

/**
 * 检测用户是否在线
 * Function:check_online
 * @return bool
 * @param $uid
 */
function check_online($uid)
{
    $db = M('session_online');
    $conditions['uid'] = $uid;
    $conditions['expiry'] = array('gt', time());
    $conditions['sessionId'] = array('neq', session_id());
    $res = $db->where($conditions)->find();
    return $res ? true : false;
}

/**
 * Function:chenge_session_table
 * @param int $uid 要操作的用户id
 * @param string $act 操作选项 KILL 删除在线表里的uid记录 LIVE更新存活时间
 */
function change_session_table($uid, $act = 'LIVE')
{
    $db = M('session_online');
    if ($act === 'KILL') {
        $db->delete($uid);
    } else {
        $liveTime = time() + C('SESSION_EXPIRY');
        $db->where('uid = "' . $uid . '"')->setField("expiry", $liveTime);
    }
}

/**
 * +----------------------------------------------------------
 * 添加系统日志
 * +----------------------------------------------------------
 * @param  string $module_name 项目名称
 * +----------------------------------------------------------
 * @param  string $controller_name 控制器名
 * +----------------------------------------------------------
 * @param  string $action_name 操作名
 * +----------------------------------------------------------
 * @param  int $mid 数据ID
 * +----------------------------------------------------------
 */
function add_sys_logs($module_name, $controller_name, $action_name, $mid = 0)
{
    if (!$module_name || !$controller_name || !$action_name) {
        return;
    } else {
        $module_name = strtolower($module_name);
        $controller_name = strtolower($controller_name);
        $action_name = strtolower($action_name);
        //TODO 获取当前用户id 并写入日志
        $user = get_user();

    }
}

/**
 * 获取系统日志
 * Function:get_sys_logs
 * @return array
 * @param string $module_name
 * @param $condition
 */
function get_sys_logs($module_name = '', $condition)
{

}

/**
 * 七牛上传图片加裁切缩放
 * Function:QiNiuUpload
 * @return mixed
 * @param array $file 为要上传的文件
 * @param array $data 裁切参数 $data['x'] 起点x轴  $data['y'] 起点y轴 $data['w'] $data['h'] 图片预裁切宽高 $data['targetW'] $data['targetH']图片尺寸
 */
function QiNiuUpload($file, $data)
{
    $setting = C('UPLOAD_SITEIMG_QINIU');
    $Upload = new \Think\Upload($setting);
    $domain = $setting["driverConfig"]["domain"];
    $info = $Upload->upload(array($file));
    /*裁切*/
    $img = $info[0]['url'];
    $data['copy'] = basename($img);
    $crop = $Upload->uploader->imgCrop($img, $data);
    foreach ($crop as $k => $v) {
        $imgArr = json_decode($v);
        $imgR[$k] = "http://" . $domain . "/" . $imgArr->key;
    }
    return $imgR;
}

/**
 * 七牛上传附件
 * Function:QiNiuUploadFile
 * @return array|bool
 * @param array $file 为要上传的文件
 */
function QiNiuUploadFile($file)
{
    $setting = C('UPLOAD_SITEIMG_QINIU');
    $Upload = new \Think\Upload($setting);
    $info = $Upload->upload(array($file));
    return $info;
}

/**
 * 字符串截取
 * Function:subtext
 * @return string
 * @param string $text 需要截取的字符串
 * @param int $length 截取长度
 */
function subtext($text, $length)
{
    if (mb_strlen($text, 'utf8') > $length)
        return mb_substr($text, 0, $length, 'utf8') . '...';
    return $text;
}

/**
 * 时间转换成秒、分、小时、天
 * @Name:formatTime
 * @Description:
 * @HideInMenu:0
 * @param $time
 * @return string str
 */
function formatTime($time)
{
    $ago = time() - $time;
    if ($ago < 60) {
        return $ago . ' sec';
    } elseif ($ago >= 60 && $ago < 3600) {
        return round($ago / 60) . ' min';
    } elseif ($ago >= 3600 && $ago < 3600 * 24) {
        return round($ago / 3600) . ' hour';
    } else {
        return round($ago / (3600 * 24)) . ' day';
    }
}

/**
 * 获取性别
 * @Name:getSexStr
 * @Description:
 * @HideInMenu:0
 * @param $sex
 * @return string str
 */
function getSexStr($sex)
{
    if ($sex === null) {
        return L('_SEX_UNKNOW_');
    }
    switch ($sex) {
        case 0:
            $str = L('_SEX_MAN_');
            break;
        case 1:
            $str = L('_SEX_WOMAN_');
            break;
        default:
            $str = L('_SEX_UNKNOW_');
            break;
    }
    return $str;
}

/**
 * @Name:getRoleStr
 * @Description:
 * @HideInMenu:0
 * @param $rid
 * @return string str
 */
function getRoleStr($rid)
{
    do {
        if (empty($rid)) {
            $roleStr[] = array(
                'name' => L('_DEFAULT_USER_LEVEL_'),
                'description' => null,
            );
            break;
        }
        $roles = M('roles')->select();
        foreach ($roles as $v) {
            $role[$v['id']]['name'] = $v['name'];
            $role[$v['id']]['description'] = $v['description'];
        }
        foreach ($rid as $v) {
            $roleStr[$v] = $role[$v];
        }
    } while (0);
    return $roleStr;
}

/**
 * 获取用户状态
 * @Name:getLockedStr
 * @Description:
 * @HideInMenu:0
 * @param $locked
 * @return string str
 */
function getStatusStr($locked)
{
    if ($locked == 1) {
        return '<font color="red">' . L('_OFF_') . '</font>';
    } else {
        return '<font color="green">' . L('_ON_') . '</font>';
    }
}


/**
 * 根据用户id获取用户信息
 * @Name:getUserInfoByUid
 * @Description:
 * @HideInMenu:0
 * @param $uid
 * @return array()|false
 */
function getUserInfoByUid($uid)
{
    $conditions['i.uid'] = $uid;
    $fields = "i.uid,i.username,i.phone,i.email,i.idcard,i.password,i.salt,i.locked,i.regIp,i.regTime,i.lastIp,i.lastTime,u.realname,u.nickname,u.sex,u.smallVatar,u.middleVatar,u.largeVatar,u.birthDate,u.degree,u.major,u.school,u.qq,u.wechart";
    $res = M('user')
        ->alias('i')
        ->field($fields)
        ->join("left join wt_user_info u on i.uid = u.uid")
        ->where($conditions)
        ->find();
    return $res;
}

/**
 * @Name:userLogin
 * @Description:
 * @HideInMenu:0
 * @param $data
 * @return array $ret
 */
function userLogin($data)
{
    do {
        if (!$data['username'] || !$data['password']) {
            $ret = array('status' => 'error', 'msg' => L('_NULL_INPUT_'));
            break;
        }
        $db = M('user');
        $conditions['username'] = trim($data['username']);
        $conditions['i.phone'] = trim($data['username']);
        $conditions['i.email'] = trim($data['username']);
        $conditions['i.idcard'] = trim($data['username']);
        $conditions['_logic'] = 'or';
        $fields = "i.uid,i.username,i.phone,i.email,i.idcard,i.password,i.salt,i.locked,i.regIp,i.regTime,i.lastIp,i.lastTime,u.realname,u.nickname,u.sex,u.smallVatar,u.middleVatar,u.largeVatar,u.birthDate,u.degree,u.major,u.school,u.qq,u.wechart";
        $res = $db
            ->alias('i')
            ->field($fields)
            ->join("left join wt_user_info u on i.uid = u.uid")
            ->where($conditions)
            ->find();
        if ($res) {
            if ($res['locked'] == 1) {
                $ret = array('status' => 'error', 'msg' => L('_BAN_'));
                break;
            }
            $pwd = check_password($data['password'], $res['salt'], $res['password']);
            if ($pwd) {
                if (check_online($res['uid'])) {
                    $ret = array('status' => 'error', 'msg' => L('_LOGIN_', array('time' => date('Y-m-d H:i', $res['lastTime']), 'ip' => $res['lastIp'])));
                    break;
                }
                unset($res['password']);
                unset($res['salt']);
                $res['sessionTime'] = time();
                $profile = M('user_info')->where('uid', $res['uid'])->find();
                if ($profile) {
                    set_session($profile, 'profile');
                }
                set_session($res, 'online');
                set_online($res['uid']);

                $userPowers = get_power_by_uid($res['uid']);
                if (!$userPowers || !is_array($userPowers['menu']) || !array_key_exists(strtolower(MODULE_NAME), $userPowers['menu'])) {
                    $ret = array('status' => 'error', 'msg' => L('_NO_PERMISSION_'));
                    killSession();
                    break;
                }
                $ret = array('status' => 'success', 'msg' => L('_LOGIN_SUCCESS_'));
                break;
            } else {
                $ret = array('status' => 'error', 'msg' => L('_PASSWORD_ERROR_'));
                break;
            }
        } else {
            $ret = array('status' => 'error', 'msg' => L('_PASSPORT_ERROR_'));
            break;
        }
    } while (0);
    return $ret;
}

/**
 * 根据用户id获取用户权限
 * @Name:get_power_by_uid
 * @Description:
 * @HideInMenu:0
 * @param $uid
 * @return array()
 */
function get_power_by_uid($uid)
{
    $condition['rid'] = array('in', get_roles_by_uid($uid));
    $myPowers = M('role_power_relation')->where($condition)->select();
    foreach ($myPowers as $v) {
        $myPowersList[] = $v['power'];
    }
    $where['status'] = 0;
    $field = "a.id,a.code,p.id as powerId,p.name,p.icon,p.url,p.fid,p.HideInMenu";
    $apps = M('apps')->alias("a")->field($field)->join('left join wt_powers as p on a.id = p.appId')->where($where)->select();
    foreach ($apps as $v) {
        if (in_array($v['powerId'], $myPowersList)) {
            $appPowers[$v['code']][] = $v;
        }
    }

    $powerList = sort_power_by_fid($appPowers);
    $userPowers = array(
        'ids' => $myPowersList,
        'menu' => $powerList
    );
    session('userPowers', $userPowers);
    return $userPowers;
}

/**
 * @Name:sort_power_by_fid
 * @Description:将权限表根据fid排序
 * @HideInMenu:0
 */
function sort_power_by_fid($data)
{
    foreach ($data as $k => $v) {
        foreach ($v as $a => $b) {
            if ($b['fid'] == 0) {
                $power[$k][] = $b;
            } else {
                $sub[] = $b;
            }

        }
    }
    foreach ($power as $k => $v) {
        foreach ($v as $b) {
            $powers[$k][] = get_sub_power($b, $sub);
        }
    }
    return $powers;
}

/**
 * @Name:get_sub_power
 * @Description:组装子权限
 * @HideInMenu:0
 * @param $data
 * @param $sub
 */
function get_sub_power($data, $sub)
{
    foreach ($sub as $v) {
        if ($v['fid'] == $data['powerId']) {
            $data['sub'][] = $v;
        }
    }
    return $data;
}

/**
 * @Name:get_roles_by_uid
 * @Description:根据用id取用户的角色
 * @HideInMenu:0
 * @param $uid
 * @return array() 角色数组
 */
function get_roles_by_uid($uid)
{
    $condition['uid'] = $uid;
    $userRoles = M('user_role_relation')->where($condition)->select();
    foreach ($userRoles as $v) {
        $roles[] = $v['rid'];
    }
    return $roles;
}

/**
 * @Name:filter_powers
 * @Description: 过滤权限表中的隐藏权限
 * @HideInMenu:0
 * @param $powers
 * @param $appId
 * @return $newPower
 */
function filter_powers($powers, $appId)
{
    $condition['appId'] = $appId;
    $condition['HideInMenu'] = 1;
    $hideMenu = M('powers')->field('id')->where($condition)->select();
    foreach ($hideMenu as $v) {
        $hideIds[] = $v['id'];
    }
    foreach ($powers as $k => $v) {
        if ($v['HideInMenu'] == 0) {
            $newPower[$k] = $v;
            unset($newPower[$k]['sub']);
        }
        if (is_array($v['sub'])) {
            foreach ($v['sub'] as $a => $b) {
                if ($b['HideInMenu'] == 0) {
                    $newPower[$k]['sub'][] = $b;
                }
            }
        }
    }
    return $newPower;
}

/**
 * @Name:killSession
 * @Description:删除session 并清除用户登录状态
 * @HideInMenu:0
 */
function killSession()
{
    $online = get_session('online');
    session(null);
    session_destroy();
    $uid = $online['uid'];
    if ($uid) {
        change_session_table($uid, 'KILL');
    }
}

/**
 * +----------------------------------------------------------
 *  获取用户uid
 * +----------------------------------------------------------
 * @author:chenfeng
 * +----------------------------------------------------------
 */
function get_user_uid()
{
    $user = get_session('online');
    if ($user) {
        return $user['uid'];
    } else {
        return 0;
    }
}

/**
 * +----------------------------------------------------------
 * 二维数组根据某个字段排序
 * +----------------------------------------------------------
 * @param  array $multi_array 排序数组
 * +----------------------------------------------------------
 * @param  string $sort_key 排序字段
 * +----------------------------------------------------------
 * @param  string $sort 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
 * +----------------------------------------------------------
 */
function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
{
    if (is_array($multi_array)) {
        foreach ($multi_array as $row_array) {
            if (is_array($row_array)) {
                $key_array[] = $row_array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_array, $sort, $multi_array);
    return $multi_array;
}

/**
 * 获取中文字符拼音首字母
 * +-----------------------------------------------------------
 * @functionName : getFirstCharter
 * +-----------------------------------------------------------
 * @param string $str
 * +-----------------------------------------------------------
 * @return null|string
 */
function getFirstCharter($str)
{
    if (empty($str)) {
        return '';
    }
    $encode = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
    if ($encode != 'GB2312') {
        $str = iconv($encode, 'GB2312', trim($str));
    }
    $fchar = ord($str{0});
    if ($fchar >= ord('A') && $fchar <= ord('Z')) return strtoupper($str{0});
    $asc = ord($str{0}) * 256 + ord($str{1}) - 65536;
    if ($asc >= -20319 && $asc <= -20284) return 'A';
    if ($asc >= -20283 && $asc <= -19776) return 'B';
    if ($asc >= -19775 && $asc <= -19219) return 'C';
    if ($asc >= -19218 && $asc <= -18711) return 'D';
    if ($asc >= -18710 && $asc <= -18527) return 'E';
    if ($asc >= -18526 && $asc <= -18240) return 'F';
    if ($asc >= -18239 && $asc <= -17923) return 'G';
    if ($asc >= -17922 && $asc <= -17418) return 'H';
    if ($asc >= -17417 && $asc <= -16475) return 'J';
    if ($asc >= -16474 && $asc <= -16213) return 'K';
    if ($asc >= -16212 && $asc <= -15641) return 'L';
    if ($asc >= -15640 && $asc <= -15166) return 'M';
    if ($asc >= -15165 && $asc <= -14923) return 'N';
    if ($asc >= -14922 && $asc <= -14915) return 'O';
    if ($asc >= -14914 && $asc <= -14631) return 'P';
    if ($asc >= -14630 && $asc <= -14150) return 'Q';
    if ($asc >= -14149 && $asc <= -14091) return 'R';
    if ($asc >= -14090 && $asc <= -13319) return 'S';
    if ($asc >= -13318 && $asc <= -12839) return 'T';
    if ($asc >= -12838 && $asc <= -12557) return 'W';
    if ($asc >= -12556 && $asc <= -11848) return 'X';
    if ($asc >= -11847 && $asc <= -11056) return 'Y';
    if ($asc >= -11055 && $asc <= -10247) return 'Z';
    return null;
}

/**
 * 生成二维码
 * @param $url
 */
function generateQRCode($url)
{
    vendor('phpQrCode.phpqrcode'); //引入phpqrcode类
    $QRcode = new \QRcode();
    ob_start();
    $QRcode->png($url, false, 'L', 4);
    $imageString = base64_encode(ob_get_contents());
    ob_end_clean();
    return $imageString;
}

/**
 * 获取视频信息
 * @param $file string 视频文件
 * @return array 视频信息
 */
function getVideoInfo($file)
{

    $command = sprintf(C('FFMPEG_PATH'), $file);

    ob_start();
    passthru($command);
    $info = ob_get_contents();
    ob_end_clean();

    $data = array();
    if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
        $data['duration'] = $match[1]; //播放时间
        $arr_duration = explode(':', $match[1]);
        $data['seconds'] = $arr_duration[0] * 3600 + $arr_duration[1] * 60 + $arr_duration[2]; //转换播放时间为秒数
        $data['start'] = $match[2]; //开始时间
        $data['bitrate'] = $match[3]; //码率(kb)
    }
    if (preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $info, $match)) {
        $data['vcodec'] = $match[1]; //视频编码格式
        $data['vformat'] = $match[2]; //视频格式
        $data['resolution'] = $match[3]; //视频分辨率
        $arr_resolution = explode('x', $match[3]);
        $data['width'] = $arr_resolution[0];
        $data['height'] = $arr_resolution[1];
    }
    if (preg_match("/Audio: (\w*), (\d*) Hz/", $info, $match)) {
        $data['acodec'] = $match[1]; //音频编码
        $data['asamplerate'] = $match[2]; //音频采样频率
    }
    if (isset($data['seconds']) && isset($data['start'])) {
        $data['play_time'] = $data['seconds'] + $data['start']; //实际播放时间
    }
    $data['size'] = filesize($file); //文件大小
    return $data;
}

/**
 * 将秒格式化为 00:00:00格式
 * +-----------------------------------------------------------
 * @functionName : formatSec
 * +-----------------------------------------------------------
 * @param int $seconds 秒数
 * +-----------------------------------------------------------
 * @author yc
 * +-----------------------------------------------------------
 * @return string
 */
function formatSec($seconds)
{
    if (!$seconds || $seconds <= 0) {
        return '未知';
    }
    $hour = floor($seconds / 3600);
    $min = floor(($seconds % 3600) / 60);
    $sec = $seconds % 60;
    if ($hour < 10) {
        $hour = '0' . $hour;
    }
    if ($min < 10) {
        $min = '0' . $min;
    }
    if ($sec < 10) {
        $sec = '0' . $sec;
    }
    return $hour . ':' . $min . ':' . $sec;
}

/**
 * 读取Excel数据
 * +-----------------------------------------------------------
 * @functionName : readExcel
 * +-----------------------------------------------------------
 * @param string $file Excel文件路径
 * +-----------------------------------------------------------
 * @author yc
 * +-----------------------------------------------------------
 */
function readExcel($file)
{
    import('Vendor.PHPExcel.PHPExcel');
    $fileType = \PHPExcel_IOFactory::identify($file);
    $PHPReader = \PHPExcel_IOFactory::createReader($fileType);
    $PHPReader->setReadDataOnly(true);              //只读取数据，去除其他格式
    $objPHPExcel = $PHPReader->load($file);         //读取Excel文件
    $currentSheet = $objPHPExcel->getSheet(0);      //获取第一个工作表
    $allColumn = $currentSheet->getHighestColumn(); //Excel所有列数最大值
    $allRow = $currentSheet->getHighestRow();       //Excel总行数
    $content = $currentSheet->toArray();
    return array('row' => $allRow, 'col' => $allColumn, 'data' => $content);
}

/**
 * PHPMailer发送邮件
 * +-----------------------------------------------------------
 * @functionName : sendPHPMail
 * +-----------------------------------------------------------
 * @param array $tomail 要发送邮件给那个人
 * @param string $title 邮件的标题
 * @param string $body 邮件的内容
 * @param array $config 邮件的配置文件
 * +-----------------------------------------------------------
 * @author yc
 * +-----------------------------------------------------------
 */
function sendPHPMail($tomail, $title, $body, $config = [])
{
    vendor('PHPMailer.phpmailer.PHPMailer');
    vendor('PHPMailer.phpmailer.SMTP');
    $mail = new PHPMailer();  // 实例化PHPMailer核心类

    //Server settings
    // 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
    $mail->SMTPDebug = 0;
    if ($config['mail_type'] == 'smtp') {
        // 使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        // smtp需要鉴权 这个必须是true
        $mail->SMTPAuth = true;
    }
    // 链接smtp.163.com域名服务器地址
    $mail->Host = $config['mail_host'];
    // smtp登录的账号 QQ邮箱即可
    $mail->Username = $config['mail_user'];
    // smtp登录的密码 使用生成的授权码
    $mail->Password = $config['mail_pwd'];
    // 设置使用ssl加密方式登录鉴权
    $mail->SMTPSecure = 'ssl';
    // 设置ssl连接smtp服务器的远程服务器端口号
    $mail->Port = $config['mail_port'];

    // 添加多个收件人 则多次调用方法即可
    //$mail->addAddress('87654321@163.com');
    if (is_array($tomail)) {
        foreach ($tomail as $key => $value) {
            $mail->addAddress($value);
        }
    } else {
        //Recipients(收件人)
        // 设置收件人邮箱地址
        $mail->addAddress($tomail);
    }

    //Attachments
    // 为该邮件添加附件
    //$mail->addAttachment('./example.pdf');

    //Content
    // 设置发送的邮件的编码
    $mail->CharSet = 'UTF-8';
    //$mail->Encoding = "base64"; //编码方式
    // 邮件正文是否为html编码 注意此处是一个方法
    $mail->isHTML(true);
    // 设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
    $mail->FromName = $config['send_name'];
    // 设置发件人邮箱地址 同登录账号
    $mail->From = $config['mail_user'];
    // 添加该邮件的主题
    $mail->Subject = $title;
    // 添加邮件正文
    $mail->Body = $body;

    try {
        // 发送邮件 返回状态
        $status = $mail->send();
        if ($status) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $exception) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

/**
 * PHPMailer发送邮件
 * +-----------------------------------------------------------
 * @functionName : sendSwiftMailer
 * +-----------------------------------------------------------
 * @param array $tomail 要发送邮件给那个人
 * @param string $subject 邮件的标题
 * @param string $body 邮件的内容
 * @param array $config 邮件的配置文件
 * +-----------------------------------------------------------
 * @author yc
 * +-----------------------------------------------------------
 */
function sendSwiftMailer($tomail, $subject, $body, $config = [])
{
    vendor('SwiftMailer.swift_required');

    // 创建Transport对象，设置邮件服务器和端口号，并设置用户名和密码以供验证
    $transport = Swift_SmtpTransport::newInstance($config['mail_host'], $config['mail_port'], 'ssl')
        ->setUsername($config['mail_user'])
        ->setPassword($config['mail_pwd']);

    // 创建mailer对象
    $mailer = Swift_Mailer::newInstance($transport);
    $mailer->protocol = $config['mail_type'];

    // 创建message对象
    $message = Swift_Message::newInstance()
        ->setSubject($subject)
        ->setFrom(array($config['mail_user'] => $config['send_name']))
        ->setTo(array($tomail))
        ->setContentType('text/html')
        ->setBody($body);

    // 创建attachment对象，content-type这个参数可以省略
    /*$attachment = Swift_Attachment::fromPath('image.jpg', 'image/jpeg')
    ->setFilename('cool.jpg');*/

    // 添加附件
    /*$message->attach($attachment);*/

    // 用关联数组设置收件人地址，可以设置多个收件人
    /*$message->setTo(array('to@qq.com' => 'toName'));*/

    // 用关联数组设置发件人地址，可以设置多个发件人
    /*$message->setFrom(array(
        'from@163.com' => 'fromName',
    ));*/

    // 添加抄送人
    /*$message->setCc(array(
        'Cc@qq.com' => 'Cc'
    ));*/

    // 添加密送人
    /*$message->setBcc(array(
        'Bcc@qq.com' => 'Bcc'
    ));*/

    try {
        if ($mailer->send($message)) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        echo 'There was a problem communicating with SMTP: ' . $e->getMessage();
    }
}

/**
 * 冒泡排序算法
 * +----------------------------------------------------------
 * @param array $arr 要排序的数组
 * +----------------------------------------------------------
 * @return array|boolean
 * +----------------------------------------------------------
 */
function bubble_sort($arr)
{
    $len = count($arr);
    if ($len <= 0) {
        return false;
    }
    for ($i = 0; $i < $len; $i++) {
        for ($j = 0; $j < $len - 1 - $i; $j++) {
            if ($arr[$j] < $arr[$j + 1]) {
                $tmp = $arr[$j];
                $arr[$j] = $arr[$j + 1];
                $arr[$j + 1] = $tmp;
            }
        }
    }
    return $arr;
}

/**
 * 获取日期和星期，默认为当前时间
 * +----------------------------------------------------------
 * @param  $string $del1,$del2,$del3,$time
 * +----------------------------------------------------------
 * @return string
 * +----------------------------------------------------------
 */
function getDateStr($time, $del1 = '年', $del2 = '月', $del3 = '日')
{
    $dayArr = array('日', '一', '二', '三', '四', '五', '六');
    $day = date('w', $time); //一周中的第几天 0~6
    return date("Y{$del1}m{$del2}d{$del3}", $time) . ' 星期' . $dayArr[$day];
}

/**
 * 菲波那切数列非递归版（没有使用静态变量，效率非常低）
 * +-----------------------------------------------------------
 * @functionName : fibonacci
 * +-----------------------------------------------------------
 * @param int $a 数列的第几个key
 * +-----------------------------------------------------------
 * @author yc
 * +-----------------------------------------------------------
 * @return int
 */
function fibonacci($a)
{
    if ($a <= 2) {
        return $cache[1] = $cache[2] = 1;
    } else {
        return $cache[$a] = fibonacci($a - 1) + fibonacci($a - 2);
    }
}

/**
 * 菲波那切数列非递归版（使用静态变量，效率高）
 * +-----------------------------------------------------------
 * @functionName : fibonacci_cache
 * +-----------------------------------------------------------
 * @param int $a 数列的第几个key
 * +-----------------------------------------------------------
 * @author yc
 * +-----------------------------------------------------------
 * @return int|mixed
 */
function fibonacci_cache($a)
{
    static $cache = array(); //静态变量

    if (isset($cache[$a])) {
        return $cache[$a];
    } else {
        if ($a <= 2) {
            return $cache[1] = $cache[2] = 1;
        } else {
            return $cache[$a] = fibonacci_cache($a - 1) + fibonacci_cache($a - 2);
        }
    }
}

/**
 * 波那切数列递归版（返回数组）
 * +-----------------------------------------------------------
 * @functionName : fibonac
 * +-----------------------------------------------------------
 * @param int $num 数列的总数
 * +-----------------------------------------------------------
 * @author yc
 * +-----------------------------------------------------------
 * @return array
 */
function fibonac($num)
{
    $array = array();

    $array[0] = 0;
    $array[1] = 1;
    for ($i = 2; $i < $num; $i++) {
        $array[$i] = $array[$i - 1] + $array[$i - 2];
    }
    return $array;
}

/**
 * 获取红包（1个分100有问题） 需改进
 * +-----------------------------------------------------------
 * @functionName : get_redEnvelope
 * +-----------------------------------------------------------
 * @param int $total 红包总金额
 * @param int $num 红包数量
 * +-----------------------------------------------------------
 * @author yc
 * +-----------------------------------------------------------
 */
function get_redEnvelope($total, $num)
{
    static $current = array();
    if ($num == 1) {
        $current[] = $total;
        return $current;
    } else {
        $min = 0.01;
        $max = round(($total / $min) / $num);
        $rand_money = rand($min * 100, $max * 2);
        $money = $rand_money * $min;
        $current[] = $money;
        $lava_money = $total - $money;
        return get_redEnvelope($lava_money, $num - 1);
    }
}

/**
 * 拆分红包算法（1个分100有问题） 需改进
 * +-----------------------------------------------------------
 * @functionName : get_split
 * +-----------------------------------------------------------
 * @param int $total 红包总金额
 * @param int $num 红包总个数
 * +-----------------------------------------------------------
 * @author yc
 * +-----------------------------------------------------------
 */
function get_split($total, $num)
{
    $min = 0.01;        //每个人最少能收到0.01元
    for ($i = 1; $i < $num; $i++) {
        $safe_total = ($total - ($num - $i) * $min) / ($num - $i);//随机安全上限 这个不对有误差
        $money = mt_rand($min * 100, $safe_total * 100) / 100;
        $total = $total - $money;
        echo '第' . $i . '个红包：' . $money . ' 元，余额：' . $total . ' 元 <br/>';
    }
    echo '第' . $num . '个红包：' . $total . ' 元，余额：0 元';
}

