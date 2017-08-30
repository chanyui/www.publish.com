<?php
return array(
    //'配置项'=>'配置值'
    'DB_TYPE' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'publish',
    'DB_USER' => 'root',
    'DB_PWD' => 'root',
    'DB_PORT' => '3306',
    'DB_PREFIX' => 'pb_',// 数据表前缀

    //sesseion用redis缓存
    'SESSION_AUTO_START'    =>  true,
    'SESSION_OPTIONS'       =>  array(),
    'SESSION_TYPE'          =>  'Redis',
    'SESSION_PREFIX'        =>  'sess_',
    'SESSION_REDIS_HOST'    =>  '127.0.0.1',
    'SESSION_REDIS_PORT'    =>  '6379',
    'SESSION_CACHE_TIME'    =>  '',
    'SESSION_PERSISTENT'    =>  '',
    'SESSION_REDIS_AUTH'    =>  '',
    'SESSION_EXPIRY'        =>  60*60,

    'QRCODE_DIR' => ROOT_PATH.'/qrcode',

    //ffmpeg路径配置
    'FFMPEG_PATH' => '/usr/local/bin/ffmpeg -i "%s" 2>&1',
);