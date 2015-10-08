<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>cocosplay 后台管理工作平台</title>
<link rel="stylesheet" type="text/css" href="<?php echo site_url('asset/css/style.css');?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo site_url('asset/css/bootstrap.min.css');?>"/>
<script src="<?php echo site_url('asset/js/jquery.js');?>"></script>
<!--<script type="text/javascript" src="<?php echo site_url('asset/js/js.js');?>"></script>-->
<script>
	$(function(){
		/*$('.validate img').click(function(){
			$(this).attr('src','/captcha/index/75/20/4/'+Math.random());
		});*/
        $('#captcha').click(
            function () {
                $.post('<?php echo site_url('admin/ajax_refresh_captcha');?>', function(data) {
                    $('#captcha').html(data); });
            });
	});
	var b = window.top!=window.self;
	if(b)
	{
		window.top.location.href="/admin/login";
	}
</script>
</head>
<body>
<div id="top"></div>
<form id="login" name="login" action="<?php echo site_url('admin/login');?>" method="post">
  <div id="center">
    <!-- <div id="center_left"></div> -->
    <div id="center_middle">
      <div class="login_box">
        <div class="user">
          <label>用户名:
          <input type='text' name='identity' />
          </label>
        </div>
        <div class="user">
          <label>密　码:
          <input type='password' name='password' />
          </label>
        </div>
<!--
        <div class="captcha">
        <label>验证码: <input type='text' name='captcha' /><a href="#" id="captcha"><?php echo $captcha;?></a></label>
        </div>
-->
        <input type="hidden" name="url" id="url" value="<?php echo isset($url)?$url:""?>">
        <button class="button" type="submit">登 录</button>
<p><a href="forgot_password">忘记密码</a></p>
        <?php if(isset($sso_signin_url) && $sso_signin_url) {?>&nbsp;<a href="<?php echo $sso_signin_url;?>">使用开发者帐号登录</a><?php } ?>
        <?php echo $this->session->flashdata('flash_message') ;?>
        <?php if(isset($flash_message)) echo  $flash_message;?>
      </div>
    </div>
  </div>
</form>
<div id="footer"></div>
</body>
</html>
