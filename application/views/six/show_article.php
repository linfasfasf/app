<?xml version="1.0" encoding="UTF-8"?>

<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $result['title'] ;?></title>
<meta name="viewport" content="width=device-width; initial-scale=1.0; minimum-scale=1.0; maximum-scale=2.0"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css">
@charset "utf-8";
/* CSS Document */
body,form,hr,div,p,img,ul,li,h1,h2,h3,h4,h5,h6{font-size:16px;line-height:28px;font-family:Arial,Helvetica,sans-serif;font-weight:normal;padding:0;margin:0;border:medium none;list-style:none;}
fieldset, img{border:0;}
ol, ul{list-style:none;}
a:link{text-decoration:none;}
a:visited{ text-decoration:none;color:#800080;}
a:hover{ text-decoration:underline;color:#b11b22;}
a:active{text-decoration:underline;color:#800080;} 
body{ font-size:18px; font-family:"微软雅黑"; line-height:32px;}
.clear{clear:both;} 
body,p,div {padding: 0px;margin: 0px;line-height: 25px;font-size: 15px;} 
img{border:0px;margin:0px;padding:0px;}
a {text-decoration: none;color: #163C81;;font-size: 15px;}
.dh {border-bottom: 2px solid #579DD7;border-top: 1px solid #DFDFE0;color: #000;height: 24px;line-height: 25px;padding-left: 2px;}

a:visited {text-decoration: none;color: #163C81;}
.nav {background-color: #176090;border-bottom: 1px solid #99BDD4;color: #fff;font-size: 13px;line-height: 1.4;padding: 4px;}
.nav a {margin:0 4px;color: #fff;}
.nav a:link,.nav a:visited {color:#fff;	text-decoration:none;}
h1{font-size:14px;line-height:1.3;}
li{border-bottom: 1px dashed #CCC;padding: 0 0 0 3px; width:98%;}
</style>
</head>
    <body>
        <div class="dh"><a href="/">特区料</a>&gt;<a href="<?php echo site_url("Welcome/show_title?cid={$result['cid']}");?>">原版正料</a></div>
        <div class="nav"><h1><?php echo $result['title'];?></h1></div>


        <?php echo $result['content'] ;
        
        if($up['article_id']){
            echo "上一篇:<a href='show_article?id={$up['article_id']}&cid={$up['cid']}'>{$up['title']}</a><br/>";
        }
        if($down['article_id']){
            echo "下一篇:<a href='show_article?id={$down['article_id']}&cid={$down['cid']}'>{$down['title']}</a><br/>";
        }
        ?>
        
           特区网(tequ.me)发展离不开你我他，求友友们做下口碑宣传！<br />
<div class="dh"><a href="/">特区网</a>&gt;<a href="zl1.aspx?cid=139">原版正料</a></div>

    </body> 
<?php date_default_timezone_set("Asia/Shanghai");
echo "当前时间是 " . date("h:i:sa");
?>