<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>跳转提示</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" name="viewport">
    <link type="text/css" rel="stylesheet" href="__PUBLIC__/css/bootstrap.min.css">
    <style>
        body,html{
            font-family:'\5fae\8f6f\96c5\9ed1',"Microsoft YaHei"
        }
        .container{
            margin-top: 80px;
        }
        .container > p{
            font-size: 30px;
            line-height: 42px;
            color: #414f6d;
        }
        .error-content{
            width: 100%;
            height: 370px;
            border: solid 1px #dde6f5;
        }
        .error-content .title{
            font-size: 18px;
            line-height: 18px;
            background-color: #eef5ff;
            border-bottom: 1px solid #dde6f5;
        }
        .error-content .title > span{
            padding: 20px;
            display: inline-block;
            font-weight: 600;
            color: #414f6d;
        }
        .error-content .content .content-left,.error-content .content .content-right{
            width: 50%;
            float: left;
        }
        .error-content .content .content-left .content-img .img-success{
            background: url("__PUBLIC__/images/success.jpg");
            background-position: center center;
            background-repeat: no-repeat;
            padding-bottom: 89%;
            background-size: cover;
        }
        .error-content .content .content-left .content-img .img-failed{
            background: url("__PUBLIC__/images/failed.jpg");
            background-position: center center;
            background-repeat: no-repeat;
            padding-bottom: 75%;
            background-size: cover;
        }
        .error-content .content .content-left .content-img{
            margin: 0 auto;
            margin-right: 66px;
            margin-top: 56px;
            width: 185px;
            height: 165px;
        }
        .error-content .content .content-right .text{
            margin: 0 auto;
            margin-left: 40px;
            margin-top: 100px;
        }
        .error-content .content .content-right .text p:first-child{
            font-size: 18px;
            line-height: 25px;
            color: #333;
            margin-bottom: 10px;
        }
        .error-content .content .content-right .text p:last-child{
            font-size: 14px;
            line-height: 20px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="page-header">
      跳转提示
    </h1>
    <?php if(isset($message)) {?>
    <!-- 成功时输出的结构 -->
    <div class="error-content">
        <p class="title">
            <span>成功</span>
        </p>
        <div class="content">
            <div class="content-left">
                <div class="content-img">
                    <div class="img-success"></div>
                </div>
            </div>
            <div class="content-right">
                <div class="text">
                    <p><?php echo($message); ?></p>
                    <p>页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间：<span id="wait"><?php echo($waitSecond); ?></span>s</p>
                </div>
            </div>
        </div>
    </div>
    <?php }else{?>
    <!-- 失败时输出的结构 -->
    <div class="error-content">
        <p class="title">
            <?php if(isset($message)) {?><span>成功</span><?php }else{?><span>失败</span><?php }?>
        </p>
        <div class="content">
            <div class="content-left">
                <div class="content-img">
                    <div class="img-failed"></div>
                </div>
            </div>
            <div class="content-right">
                <div class="text">
                    <p><?php echo($error); ?></p>
                    <p>页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间：<span id="wait"><?php echo($waitSecond); ?></span>s</p>
                </div>
            </div>
        </div>
    </div>
    <?php }?>
  </div>
<script type="text/javascript">
(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
    var time = --wait.innerHTML;
    if(time <= 0) {
        location.replace(href);
        clearInterval(interval);
    };
}, 1000);
})();
</script>
</body>
</html>
