<?xml version="1.0" encoding="utf-8"?><!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.2//EN" "http://www.wapforum.org/DTD/wml12.dtd">
<wml>
    <head>
        <title>lin</title>
        <link  rel="stylesheet" href="asset/css/bootstrap.css"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta http-equiv="Cache-Control" content="ust-revalidate" forua="true"/>
        <meta http-equiv="Cache-Control" content="no-cache" forua="true"/>
        <meta http-equiv="Cache-Control" content="max-age=0" forua="true"/>
        <meta http-equiv="Expires" content="0" forua="true"/>
        <meta http-equiv="Pragma" content="no-cache" forua="true"/>
    </head>
    
    
    <card title="独家一波">
        <?php $i=0; foreach ($arr as $val){ 
            $i++;
             if(is_array($arr_chose)&&in_array($val, $arr_chose)){
          echo "<strong>{$val}</strong>";}  else { ?>
          <a href="<?php   echo site_url('liuhecai/px?num=').$val;?>"><?php printf('%02d',$val); }?></a>
            <?php if($i==7){ echo "<br>";$i=0;}?>
        <?php }?>
    </card>
</wml>