	    <div class="row-fluid">
	        <ul class="breadcrumb">
	            <li>运营管理 <span class="divider">/</span></li>
	            <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
	            <li class="active">创建游戏</li>
	        </ul>
	    </div>
            <?php flash_message();?>
		<?php if(!empty($msg)) {?>
                <div>
                   <div class = "alert">
                        <p><?php echo $msg?></p>
                   </div>
               </div>
            <?php }?>
	    <div class='row-fluid'>
	        <form class="form-horizontal" action="<?php echo site_url('game_management/addgamehandler'); ?>" enctype="multipart/form-data" method="post" onsubmit="return checkform()">
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
	                        <option value="1" <?php if(array_key_exists('gamemode',$_POST) && $_POST['gamemode']!=7) echo 'selected';?>>Play 托管包</option>
	                        <option value="7" <?php if(array_key_exists('gamemode',$_POST) && $_POST['gamemode']==7) echo 'selected';?>>Runtime 游戏</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group clspkgname <?php if(array_key_exists('gamemode',$_POST) && $_POST['gamemode']==7) { echo '';} else {echo 'hide';}?>">
	                <label class="control-label"><span class="red">*</span>游戏包名：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" name="packagename" maxlength="40" value="<?php if(array_key_exists('packagename',$_POST)) echo $_POST['packagename']; ?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>游戏类型：</label>
	                <div class="controls">
	                    <select class="wh500" name="gametype">
	                        <option value="0" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==0) echo 'selected';?>>未指定</option>
	                        <option value="1" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==1) echo 'selected';?>>角色扮演</option>
	                        <option value="2" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==2) echo 'selected';?>>经营策略</option>
	                        <option value="3" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==3) echo 'selected';?>>即时战斗</option>
	                        <option value="4" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==4) echo 'selected';?>>卡牌</option>
	                        <option value="5" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==5) echo 'selected';?>>模拟养成</option>
	                        <option value="6" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==6) echo 'selected';?>>动作射击</option>
	                        <option value="7" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==7) echo 'selected';?>>休闲时间</option>
	                        <option value="8" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==8) echo 'selected';?>>塔防</option>
	                        <option value="9" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==9) echo 'selected';?>>小游戏</option>
	                        <option value="10" <?php if(array_key_exists('gametype',$_POST) && $_POST['gametype']==10) echo 'selected';?>>棋牌</option>
	                    </select>
	                </div>
	            </div>
                <div class="control-group">
                <label class="control-label"><span class="red">*</span>Game Key：</label>
                <div class="controls">
                    <input class="input_txt" id="game_key" type="text" name="game_key" maxlength="20" value="<?php if(array_key_exists('game_key',$_POST)) echo $_POST['game_key']; ?>"/><a class="btn" id="generate">生成</a>
                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>关联帐号：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" name="email" maxlength="40" value="<?php echo $identity;?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">离线支持：</label>
	                <div class="controls">
	                    <select class="wh500" name="offline_support">
	                        <option value="0" <?php if(array_key_exists('offline_support',$_POST) && $_POST['offline_support']!=1) echo 'selected';?>>不支持离线</option>
	                        <option value="1" <?php if(array_key_exists('offline_support',$_POST) && $_POST['offline_support']==1) echo 'selected';?>>支持离线</option>
	                    </select>
                            <select class="wh500 clsofflinesupport <?php if(array_key_exists('offline_support',$_POST) && $_POST['offline_support']==1) { echo '';} else {echo 'hide';}?>" name="allow_del_data" >
                                <option value="1" <?php if(array_key_exists('allow_del_data',$_POST) && $_POST['allow_del_data']==1) echo 'selected';?>>允许删除data目录</option>
	                        <option value="0" <?php if(array_key_exists('allow_del_data',$_POST) && $_POST['allow_del_data']!=1) echo 'selected';?>>不允许删除data目录</option>
	                    </select>
  
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">是否清除缓存：</label>
	                <div class="controls">
	                    <select class="wh500" name="purge_cache">
	                        <option value="1" <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']==1) echo 'selected';?>>是</option>
	                        <option value="0" <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']!=1) echo 'selected';?>>否</option>
	                    </select>
                            
                            <select class="wh100 clspurge_cache <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']==0) { echo '';} else {echo 'hide';}?>" name="purge_cache_time">
                                <option value="1" <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']==1) echo 'selected';?>>一个月</option>
	                        <option value="2" <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']==2) echo 'selected';?>>两个月</option>
                                <option value="3" <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']==3) echo 'selected';?> selected = "selected">三个月</option>
	                        <option value="6" <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']==6) echo 'selected';?>>半年</option>
                                <option value="12" <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']==12) echo 'selected';?>>一年</option>
	                        <option value="24" <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']==24) echo 'selected';?>>两年</option>
                                <option value="99" <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']==99) echo 'selected';?>>永久</option>
	                    </select>
                            &nbsp;<span style="color:red;font-size:10px;" class="wh500 clspurge_cache <?php if(array_key_exists('purge_cache',$_POST) && $_POST['purge_cache']==0) { echo '';} else {echo 'hide';}?>">不清除缓存时间的限制，超过设定时间后可被删除</span>
	                </div>
	            </div>
<!--
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>提供商：</label>
	                <div class="controls">
	                    <input class="input_txt" type="text" name="supplier" maxlength="20" value="<?php if(array_key_exists('supplier',$_POST)) echo $_POST['supplier'];?>"/>
	                </div>
	            </div>
-->
	            <div class="control-group">
	                <label class="control-label"></label>
	                <div class="controls">
	                    <input type="submit" class="btn btn-primary" value="创建" />
	                    <input type="reset" class="btn" value="重置" />
	                </div>
	            </div>
	        </form>
	    </div>
<script type="text/javascript">

function checkform(){
	var arrparam = new Array();
	arrparam['gamename'] = 'input[name=gamename]';
    arrparam['gamekey'] = 'input[name=game_key]'
    arrparam['email'] = 'input[name=email]'
	//提示语
	arrtip = new Array();
	arrtip['gamename'] = '游戏名称不能为空';
    arrtip['gamekey'] = 'Game Key不能为空';
	arrtip['email'] = 'email 不能为空';
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
        $('select[name=gamemode]').on('change', function() {
            if($(this).val() == 7) {
                $('.clspkgname').show();
            }else{
                $('.clspkgname').hide();
            }
        });
        $('select[name=offline_support]').on('change', function() {
            if($(this).val() == 1) {
                $('.clsofflinesupport').show();
            }else{
                $('.clsofflinesupport').hide();
            }
        });
        $('select[name=purge_cache]').on('change', function() {
            if($(this).val() == 0) {
                $('.clspurge_cache').show();
            }else{
                $('.clspurge_cache').hide();
            }
        });
    }
);
</script>
