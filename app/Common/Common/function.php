<?php
/**
 * cURL功能（post）
 * Function:xcurl
 * @return mixed
 * @param $url 地址
 * @param null $ref 包含一个”referer”头的字符串
 * @param array $post 参数
 * @param string $ua
 * @param bool|false $print
 */
function xcurl($url,$ref=null,$post=array(),$ua="Mozilla/5.0 (X11; Linux x86_64; rv:2.2a1pre) Gecko/20110324 Firefox/4.2a1pre",$print=false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    if(!empty($ref)) {
        curl_setopt($ch, CURLOPT_REFERER, $ref);
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if(!empty($ua)) {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    }
    if(count($post) > 0){
        $o = "";
        foreach ($post as $k=>$v)
        {
            $o .= "$k=".urlencode($v)."&";
        }
        $post = substr($o,0,-1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $output = curl_exec($ch);
    curl_close($ch);
    if($print) {
        print($output);
    } else {
        return $output;
    }
}

/**
 * cURL功能（get）
 * Function:gcurl
 * @return mixed
 * @param $url 地址
 * @param array $header 请求头
 * @param array $get
 * @param string $ua
 * @param bool|false $print
 */
function gcurl($url,$header=array(),$get=array(),$ua="Mozilla/5.0 (X11; Linux x86_64; rv:2.2a1pre) Gecko/20110324 Firefox/4.2a1pre",$print=false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    if(!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    if(count($get) > 0){
        $o = "";
        foreach ($get as $k=>$v)
        {
            $o .= "$k=".urlencode($v)."&";
        }
        $get = substr($o,0,-1);
        $url = $url.'?'.$get;
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if(!empty($ua)) {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    }

    $output = curl_exec($ch);
    curl_close($ch);
    if($print) {
        print($output);
    } else {
        return $output;
    }
}

/**
 * 获取随机码
 * Function:random
 * @return string
 * @param $length 随机码的长度
 * @param int $numeric 0是字母和数字混合码，不为0是数字码
 */
function random($length, $numeric = 0) {
    PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
    $seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $seed[mt_rand(0, $max)];
    }
    return $hash;
}

/**
 * 加密解密（可逆）
 * Function:authcode
 * @return string
 * @param $string 加密的字符串
 * @param string $operation DECODE表示解密,其它表示加密
 * @param string $key  密钥
 * @param int $expiry 密文有效期
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key ? $key : "da7b4db15be94a4c597a34f9cf902b01");
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }

}

/**
 * 加密（不可逆）
 * Function:encrypt
 * @return string
 * @param $password 原始密码
 * @param $salt 密钥
 */
function encrypt($password,$salt)
{
    $slt = $password.'{'.$salt."}";
    $h = 'sha256';

    $digest = hash($h , $slt , true);

    for($i=1; $i<5000;$i++){
        $digest = hash($h ,$digest.$slt , true);
    }

    return  base64_encode($digest);
}

/**
 * 生成密码
 * Function:get_password
 * @return string
 * @param $password 原始密码
 * @param $salt 密钥
 */
function get_password($password,$salt)
{
    return encrypt($password,$salt);
}

/**
 * 验证密码
 * Function:check_password
 * @return bool
 * @param $password 原始密码
 * @param $salt 密钥
 * @param $pwd 加密后密码
 */
function check_password($password,$salt,$pwd)
{
    if(get_password($password,$salt) == $pwd){
        return true;
    }else{
        return false;
    }
}

/**
 * 手机发送验证码
 * Function:send_by_phone
 * @return bool
 * @param $phone 手机号码
 * @param $message 模板id为0是发送信息，模板id不为0是者模板值
 * @param int $tpl_id 模板id
 */
function send_by_phone($phone,$message,$tpl_id=0)
{
    if(!$phone || !$message){
        return false;
        exit;
    }

    $apikey = 'b66e3c9ac9e3a877d690560892c43d5f';
    $mobile = $phone;

    $sendSms = new \Org\sendSms();
    if($tpl_id){
        $tpl_value = $message;
        $output = $sendSms->tpl_send_sms($apikey, $tpl_id, $tpl_value, $mobile);
    }else{
        $text   = $message;
        $output = $sendSms->send_sms($apikey,$text,$mobile);
    }
    $output = json_decode($output,true);
    if($output['code'] != 0){
        return false;
    }else{
        return true;
    }
}


/**
 * 创建session
 * Function:set_session
 * @return bool
 * @param $session session的数组
 * @param $name session存储的名称
 */
function set_session($session,$name)
{
    if(is_array($session)){
        $session = json_encode($session);
    }
    $key = C('secret_key');
    $session = authcode($session,'ENCODE',$key);
    session($name,$session);
}

/**
 * 获得session
 * Function:get_session
 * @return array|bool
 * @param $name session存储的名称
 */
function get_session($name)
{
    $key = C('secret_key');
    $auth = session($name);

    if($auth){
        $session = authcode($auth,'DECODE',$key);
        $ary = json_decode($session,true);
        if(!$ary){
            $ary = $session;
        }
        return $ary;
    }else{
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
    $isOn = $db->where('uid = "'.$uid.'"')->find();
    if($isOn){
        $db->save($expriy);
    }else{
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
    $conditions['expiry'] = array('gt',time());
    $conditions['sessionId'] = array('neq',session_id());
    $res = $db->where($conditions)->find();
    return $res ? true : false;
}

/**
 * Function:chenge_session_table
 * @param $uid 要操作的用户id
 * @param string $act 操作选项 KILL 删除在线表里的uid记录 LIVE更新存活时间
 */
function change_session_table($uid,$act = 'LIVE')
{
    $db = M('session_online');
    if($act === 'KILL'){
        $db->delete($uid);
    }else{
        $liveTime = time() + C('SESSION_EXPIRY');
        $db->where('uid = "'.$uid.'"')->setField("expiry",$liveTime);
    }
}

/**
 +----------------------------------------------------------
 * 添加系统日志
 +----------------------------------------------------------
 * @param  string $module_name 项目名称
 +----------------------------------------------------------
 * @param  string $controller_name 控制器名
 +----------------------------------------------------------
 * @param  string $action_name 操作名
 +----------------------------------------------------------
 * @param  int $mid 数据ID
 +----------------------------------------------------------
 */
function add_sys_logs($module_name,$controller_name,$action_name,$mid=0)
{
    if(!$module_name || !$controller_name || !$action_name){
        return;
    }else{
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
function get_sys_logs($module_name='',$condition)
{

}

/**
 * 七牛上传图片加裁切缩放
 * Function:QiNiuUpload
 * @return mixed
 * @param $file 为要上传的文件
 * @param $data 裁切参数 $data['x'] 起点x轴  $data['y'] 起点y轴 $data['w'] $data['h'] 图片预裁切宽高 $data['targetW'] $data['targetH']图片尺寸
 */
function QiNiuUpload($file,$data)
{
    $setting = C('UPLOAD_SITEIMG_QINIU');
    $Upload = new \Think\Upload($setting);
    $domain = $setting["driverConfig"]["domain"];
    $info = $Upload->upload(array($file));
    /*裁切*/
    $img = $info[0]['url'];
    $data['copy'] = basename($img);
    $crop = $Upload->uploader->imgCrop($img,$data);
    foreach($crop as $k => $v){
        $imgArr = json_decode($v);
        $imgR[$k] = "http://".$domain."/".$imgArr->key;
    }
    return $imgR;
}

/**
 * 七牛上传附件
 * Function:QiNiuUploadFile
 * @return array|bool
 * @param $file 为要上传的文件
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
 * @param $text 需要截取的字符串
 * @param $length 截取长度
 */
function subtext($text, $length)
{
    if(mb_strlen($text, 'utf8') > $length)
        return mb_substr($text, 0, $length, 'utf8').'...';
    return $text;
}

/**
 * @Name:formatTime
 * @Description:
 * @HideInMenu:0
 * @param $time
 * @return str
 */
function formatTime($time)
{
    $ago = time()-$time;
    if($ago < 60){
        return $ago.' sec';
    }elseif($ago >= 60 && $ago < 3600){
        return round($ago/60).' min';
    }elseif($ago >= 3600 && $ago < 3600*24){
        return round($ago/3600).' hour';
    }else{
        return round($ago/(3600*24)).' day';
    }
}

/**
 * @Name:getSexStr
 * @Description:
 * @HideInMenu:0
 * @param $sex
 * @return str
 */
function getSexStr($sex)
{
    if($sex === null){
        return L('_SEX_UNKNOW_');
    }
    switch($sex){
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
 * @return str
 */
function getRoleStr($rid)
{
    do{
        if(empty($rid)){
            $roleStr[] = array(
                'name' => L('_DEFAULT_USER_LEVEL_'),
                'description' => null,
            );
            break;
        }
        $roles = M('roles')->select();
        foreach($roles as $v){
            $role[$v['id']]['name'] = $v['name'];
            $role[$v['id']]['description'] = $v['description'];
        }
        foreach($rid as $v){
            $roleStr[$v] = $role[$v];
        }
    }while(0);
    return $roleStr;
}

/**
 * @Name:getLockedStr
 * @Description:
 * @HideInMenu:0
 * @param $locked
 * @return str
 */
function getStatusStr($locked)
{
    if($locked == 1){
        return '<font color="red">'.L('_OFF_').'</font>';
    }else{
        return '<font color="green">'.L('_ON_').'</font>';
    }
}


/**
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
    do{
        if (!$data['username'] || !$data['password']) {
            $ret = array('status' => 'error','msg' => L('_NULL_INPUT_'));
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
        if($res){
            if($res['locked'] == 1){
                $ret = array('status' => 'error','msg' => L('_BAN_'));
                break;
            }
            $pwd = check_password($data['password'],$res['salt'],$res['password']);
            if($pwd){
                if(check_online($res['uid'])){
                    $ret = array('status' => 'error','msg' => L('_LOGIN_',array('time' => date('Y-m-d H:i',$res['lastTime']) , 'ip' => $res['lastIp'])));
                    break;
                }
                unset($res['password']);
                unset($res['salt']);
                $res['sessionTime'] = time();
                $profile = M('user_info')->where('uid', $res['uid'])->find();
                if($profile){
                    set_session($profile,'profile');
                }
                set_session($res,'online');
                set_online($res['uid']);

                $userPowers = get_power_by_uid($res['uid']);
                if(!$userPowers || !is_array($userPowers['menu']) || !array_key_exists(strtolower(MODULE_NAME),$userPowers['menu'])){
                    $ret = array('status' => 'error','msg' => L('_NO_PERMISSION_'));
                    killSession();
                    break;
                }
                $ret = array('status' => 'success','msg' => L('_LOGIN_SUCCESS_'));
                break;
            }else{
                $ret = array('status' => 'error','msg' => L('_PASSWORD_ERROR_'));
                break;
            }
        }else{
            $ret = array('status' => 'error','msg' => L('_PASSPORT_ERROR_'));
            break;
        }
    }while(0);
    return $ret;
}

/**
 * @Name:get_power_by_uid
 * @Description:
 * @HideInMenu:0
 * @param $uid
 * @return array()
 */
function get_power_by_uid($uid)
{
    $condition['rid'] = array('in',get_roles_by_uid($uid));
    $myPowers = M('role_power_relation')->where($condition)->select();
    foreach($myPowers as $v){
        $myPowersList[] = $v['power'];
    }
    $where['status'] = 0;
    $field = "a.id,a.code,p.id as powerId,p.name,p.icon,p.url,p.fid,p.HideInMenu";
    $apps = M('apps')->alias("a")->field($field)->join('left join wt_powers as p on a.id = p.appId')->where($where)->select();
    foreach($apps as $v){
        if(in_array($v['powerId'],$myPowersList)){
            $appPowers[$v['code']][] = $v;
        }
    }

    $powerList = sort_power_by_fid($appPowers);
    $userPowers = array(
        'ids' => $myPowersList,
        'menu' => $powerList
    );
    session('userPowers',$userPowers);
    return $userPowers;
}

/**
 * @Name:sort_power_by_fid
 * @Description:将权限表根据fid排序
 * @HideInMenu:0
 */
function sort_power_by_fid($data)
{
    foreach ($data as $k => $v){
        foreach($v as $a => $b){
            if($b['fid'] == 0){
                $power[$k][] = $b;
            }else{
                $sub[] = $b;
            }

        }
    }
    foreach($power as $k => $v){
        foreach($v as $b){
            $powers[$k][] = get_sub_power($b,$sub);
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
function get_sub_power($data,$sub)
{
    foreach($sub as $v){
        if($v['fid'] == $data['powerId']){
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
    foreach($userRoles as $v){
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
function filter_powers($powers,$appId)
{
    $condition['appId'] = $appId;
    $condition['HideInMenu'] = 1;
    $hideMenu = M('powers')->field('id')->where($condition)->select();
    foreach($hideMenu as $v){
        $hideIds[] = $v['id'];
    }
    foreach($powers as $k => $v){
        if($v['HideInMenu'] == 0){
            $newPower[$k] = $v;
            unset($newPower[$k]['sub']);
        }
        if(is_array($v['sub'])){
            foreach($v['sub'] as $a => $b){
                if($b['HideInMenu'] == 0){
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
    if($uid){
        change_session_table($uid,'KILL');
    }
}

/**
 +----------------------------------------------------------
 *  获取用户uid
 +----------------------------------------------------------
 *  @author:chenfeng
 +----------------------------------------------------------
 */
function get_user_uid()
{
    $user = get_session('online');
    if($user){
        return $user['uid'];
    }else{
        return 0;
    }
}
