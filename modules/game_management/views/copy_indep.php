<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link href="<?php echo site_url('asset/css/bootstrap.css');?>" rel="stylesheet">
	<link href="<?php echo site_url('asset/css/styles.css');?>" rel="stylesheet">
	<link href="<?php echo site_url('asset/css/dateRange.css');?>" rel="stylesheet">
	<script src="<?php echo site_url('asset/js/jquery.js');?>"></script>
	<style type="text/css">
		label{width: 100px; text-align: right;}
	</style>
</head>
<body>
	<div class="container-fluid">
	    <div class="row-fluid">
	        <ul class="breadcrumb">
	            <li>运营管理 <span class="divider">/</span></li>
	            <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
	            <li class="active">复制游戏</li>
	        </ul>
	    </div>
		<?php if(!empty($msg)) {?>
                <div>
                   <div class = "alert">
                        <p><?php echo $msg?></p>
                   </div>
               </div>
            <?php }?>
	    <div class='row-fluid'>
	        <form class="form-horizontal" action="<?php echo site_url('game_management/copy_indep'); ?>" enctype="multipart/form-data" method="post" onsubmit="return checkform()">
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>游戏名称：</label>
	                <div class="controls">

                    <input class="input_txt" type="text" name="gamename" maxlength="40" value="<?php if(array_key_exists('gamename',$_POST)) echo $_POST['gamename']; ?>"/>

	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>游戏模式：</label>
	                <div class="controls">
                            <select class="wh500" name="gamemode">
	                        <option value="1" <?php if(array_key_exists('gamemode',$_POST) && $_POST['gamemode']==1) echo 'selected';?>>托管包</option>
	                        <option value="4" <?php if(array_key_exists('gamemode',$_POST) && $_POST['gamemode']==4) {echo 'selected';} if(!array_key_exists('gamemode',$_POST)){echo 'selected';} ?>>独立包</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>包名：</label>
	                <div class="controls">
	                    <input id="packagename" class="input_txt" type="text" name="packagename" maxlength="50" value="<?php if(array_key_exists('packagename',$_POST)) echo $_POST['packagename'];?>" onchange="fixapkname()"/>
                        <span id="apkname_msg" class="input-large uneditable-input">APK 名</span>
	                </div>
	            </div>

                    <div class="control-group">
	                <label class="control-label"><span class="red">*</span>New Game Key：</label>
	                <div class="controls">
	                    <input class="input_txt" id="game_key" type="text" name="game_key" maxlength="20" value="<?php if(array_key_exists('game_key',$_POST)) echo $_POST['game_key']; ?>"/><a class="btn" id="generate">生成</a>
	                </div>
	            </div>
                    <div class="control-group">
	                <label class="control-label"><span class="red">*</span>From Game Key：</label>
	                <div class="controls">
	                    <input class="input_txt" id="from_game_key" type="text" name="from_game_key" maxlength="20" value="<?php if(array_key_exists('from_game_key',$_POST)) echo $_POST['from_game_key']; ?>"/>
	                </div>
	            </div>
                <!--    <div class="control-group">
	                <label class="control-label">Batch Id：</label>
	                <div class="controls">
	                    <input class="input_txt" id="batch_id" type="text" name="batch_id" maxlength="20" value="<?php if(array_key_exists('batch_id',$_POST)) echo $_POST['batch_id']; ?>"/>
	                </div>
	            </div>-->
	            <div class="control-group">
	                <label class="control-label"></label>
	                <div class="controls">
	                    <input type="submit" class="btn btn-primary" value="创建" />
	                    <input type="reset" class="btn" value="重置" />
	                </div>
	            </div>
	        </form>
	    </div>
	</div>
<script type="text/javascript">

function checkform(){
	var arrparam = new Array();
	arrparam['gamename'] = 'input[name=gamename]';
	arrparam['packagename'] = 'input[name=packagename]';
	arrparam['from_game_key'] = 'input[name=from_game_key]';
        arrparam['gamekey'] = 'input[name=game_key]';
//        arrparam['batch_id'] = 'input[name=batch_id]'
	//提示语
	arrtip = new Array();
	arrtip['gamename'] = '游戏名称不能为空';
	arrtip['packagename'] = '包名不能为空';
        arrtip['gamekey'] = 'Game Key不能为空';
	arrtip['from_game_key'] = '不能为空';
//        arrtip['batch_id'] = 'batch_id不能为空';
    error_flag=false; 
    err_msg='';
	for(var ele in arrparam){
        $(arrparam[ele]).parent().parent().removeClass('error');
		if( $(arrparam[ele]).val() == ''){
            err_msg = arrtip[ele] + "\n";
            error_flag=true;
            warnMessage($(arrparam[ele]), err_msg);
		}
	}
    if($('select[name=gametype]').val()==0){
        error_flag = true;
        err_msg = '需指定游戏类型';
        warnMessage($('select[name=gametype]'), err_msg);
    }
    if(error_flag){
        return false;
    }
}

function trimLeft(s){  
	if(s == null) {  
		return "";  
	}  
	var whitespace = new String(" \t\n\r");  
	var str = new String(s);  
	if (whitespace.indexOf(str.charAt(0)) != -1) {  
		var j=0, i = str.length;  
		while (j < i && whitespace.indexOf(str.charAt(j)) != -1){  
			j++;  
		}  
		str = str.substring(j, i);  
	}  
	return str;  
} 
function warnMessage(element, msg){
            element.focus();
            element.parent().parent().addClass('error');
            element.siblings('span').text(msg);
}
function fixapkname(){
    $('#apkname_msg').text($('#packagename').val() + '.apk');
}


//生成gameid，随机一个字符串，并检查是否已经存在
$.extend({ 
  password: function (length, special) {
    var iteration = 0;
    var password = "";
    var randomNumber;
    if(special == undefined){
        var special = false;
    }
    while(iteration < length){
        randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
        if(!special){
            if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
            if ((randomNumber >=58) && (randomNumber <=96)) { continue; }
            if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
        }
        iteration++;
        password += String.fromCharCode(randomNumber);
    }
    return password.toUpperCase();
  }
});

$(document).ready(
    function(){
        $('#generate').on('click',function(){
            var key = $.password(10);
            $('#game_key').val(key);
        });
    }
);
</script>
</body>
</html>
