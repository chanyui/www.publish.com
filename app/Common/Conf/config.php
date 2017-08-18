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

    'QRCODE_DIR' => ROOT_PATH.'/qrcode',

    //ffmpeg路径配置
    'FFMPEG_PATH' => '/usr/local/bin/ffmpeg -i "%s" 2>&1',
);