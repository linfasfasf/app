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
	            <li class="active">修改游戏版本</li>
	        </ul>
	    </div>
		<?php flash_message();?>
	    <div class='row-fluid'>
	        <form id="add_revision_now" class="form-horizontal" action="<?php echo site_url('game_management/edit_game_revision_handler/'. $game_revision_info['id']); ?>" enctype="multipart/form-data" method="post" onsubmit="return checkform()">
<h3>游戏信息</h3>
	            <div class="control-group">
                    <label class="control-label">游戏名称：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" disabled name="gamename_face" maxlength="20"  value="<?php echo $game_info['game_name']; ?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">游戏模式：</label>
	                <div class="controls">
                        <input class="hidden"  type="hidden" name="gamemode" value="<?php echo $game_info['game_mode'];?>" />
	                    <select class="wh500" name="gamemode_face" disabled>
                        <option value="0" <?php if ($game_info['game_mode']=='0') echo 'selected'; ?>>试玩包</option>
	                        <option value="1" <?php if ($game_info['game_mode']=='1') echo 'selected'; ?>>托管包</option>
	                        <option value="4" <?php if ($game_info['game_mode']=='4') echo 'selected'; ?>>独立包</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">游戏类型：</label>
	                <div class="controls">
                    <!--<input type="hidden" name="gametype" value="<?php echo $game_info['game_type'] ;?>"/> -->

                       <select class="wh500" name="gametype" disabled>
	                        <option value="0" <?php if($game_info['game_type']==0) echo 'selected';?> >未指定</option>
	                        <option value="1" <?php if($game_info['game_type']==1) echo 'selected';?> >角色扮演</option>
	                        <option value="2" <?php if($game_info['game_type']==2) echo 'selected';?> >经营策略</option>
	                        <option value="3" <?php if($game_info['game_type']==3) echo 'selected';?> >即时战斗</option>
	                        <option value="4" <?php if($game_info['game_type']==4) echo 'selected';?> >卡牌</option>
	                        <option value="5" <?php if($game_info['game_type']==5) echo 'selected';?> >模拟养成</option>
	                        <option value="6" <?php if($game_info['game_type']==6) echo 'selected';?> >动作射击</option>
	                        <option value="7" <?php if($game_info['game_type']==7) echo 'selected';?> >休闲时间</option>
	                        <option value="8" <?php if($game_info['game_type']==8) echo 'selected';?> >塔防</option>
	                        <option value="9" <?php if($game_info['game_type']==9) echo 'selected';?> >小游戏</option>
	                        <option value="10" <?php if($game_info['game_type']==10) echo 'selected';?> >棋牌</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">包名：</label>
	                <div class="controls">
	                    <input disabled class="input_txt" type="text" name="bagname_face" maxlength="50"  value="<?php echo $game_info["package_name"]; ?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">APK文件名：</label>
	                <div class="controls">
                    <input class="input_txt" disabled type="text" name="apkname_face" maxlength="50"  value= "<?php echo $game_info['package_name'] . '.apk'; ?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">提供商：</label>
	                <div class="controls">
                    <input class="input_txt" type="hidden" name="supplier" maxlength="20" value="<?php echo $game_info['cp_vendor'] ;?>"/>
                    <input disabled class="input_txt" type="text" name="supplier_face" maxlength="20" value="<?php echo $game_info['cp_vendor'] ;?>"/>
	                </div>
	            </div>
<h3>游戏版本信息</h3>
	            <div class="control-group">
                        <label class="control-label"></label>
                        <img src="<?php echo $game_revision_info['icon_url_display'];  ?>"/>
                </div>
	            <div class="control-group">
	                <label class="control-label">ICON：</label>
	                <div class="controls">
                        <input type="hidden" id="original_icon_path" name="icon_path"  value = "<?php echo $game_revision_info['icon_path'];?>"/><br/>
                        <input type="file" id="iconImg" name="iconImg" /><br/>
                        <span id='iconImg_msg'>支持jpg,png格式,128x128像素,小于50K</span>
	                </div>
	            </div>
	            <div class="control-group">
                    <label class="control-label">游戏名称：</label>
	                <div class="controls">
                    <input type="hidden" name="game_id" maxlength="20"  value="<?php echo $game_info['game_id']; ?>"/>
                    <input class="input_txt" type="hidden" name="package_name" maxlength="50"  value="<?php echo $game_info["package_name"]; ?>"/>
                    <input type="hidden" id="active_version_code" name="active_version_code" maxlength="20"  value="<?php echo $game_revision_info['id']; ?>"/>
                    <input class="input_txt" type="text" name="gamename" maxlength="20"  value="<?php echo $game_revision_info['game_name']; ?>"/>
	                </div>
	            </div>
	            <div class="control-group">
                    <label class="control-label">游戏描述：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" name="game_desc" maxlength="20"  value="<?php echo $game_revision_info['game_desc']; ?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">星级：</label>
	                <div class="controls">
	                    <select class="wh500" name="star" >
                            <option value="1" <?php if ($game_revision_info['star']=='1')echo 'selected'; ?>>1</option>
                            <option value="2" <?php if ($game_revision_info['star']=='2')echo 'selected'; ?>>2</option>
                            <option value="3" <?php if ($game_revision_info['star']=='3')echo 'selected'; ?>>3</option>
                            <option value="4" <?php if ($game_revision_info['star']=='4')echo 'selected'; ?>>4</option>
                            <option value="5" <?php if ($game_revision_info['star']=='5')echo 'selected'; ?>>5</option>
	                    </select>
	                </div>
	            </div>
<!--
-->
	            <div class="control-group">
	                <label class="control-label">游戏 SDK版本：</label>
	                <div class="controls">
	                    <select class="wh500" name="sdk_version">
	                        <option value="2" <?php if ($game_revision_info['sdk_version']=='2') echo 'selected'; ?>>V2</option>
	                        <option value="3" <?php if ($game_revision_info['sdk_version']=='3') echo 'selected'; ?>>V3</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">游戏版本：</label>
	                <div class="controls">
                    <input class="input_txt" type="hidden" name="game_version" maxlength="20" value="<?php echo $game_revision_info['package_ver'];?>"/>
                    <span class='uneditable-input'><?php echo $game_revision_info['package_ver'];?></span>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">版本编号：</label>
	                <div class="controls">
                    <input class="input_txt" type="hidden" name="version_number" maxlength="20"  value="<?php echo $game_revision_info['package_ver_code'];?>"/>
                    <span class='uneditable-input'><?php echo $game_revision_info['package_ver_code'];?></span>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">引擎版本：</label>
	                <div class="controls">
                    <select class="wh500" name="engine_version">
                        <option value="v2" <?php if ($game_revision_info['engine_version']=='v2') echo 'selected'; ?>>Cocos2d-x v2</option>
                        <option value="v3" <?php if ($game_revision_info['engine_version']=='v3') echo 'selected'; ?>>Cocos2d-x v3</option>
                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">屏幕方向：</label>
	                <div class="controls">
	                    <select class="wh500" name="screen_direction">
                        <option value="0" <?php if ($game_revision_info['orientation']=='0') echo 'selected' ;?>>横屏</option>
                        <option value="1" <?php if ($game_revision_info['orientation']=='1') echo 'selected' ;?>>竖屏</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">用户系统：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" name="custom_system" maxlength="20" value="<?php echo $game_revision_info['user_system'];?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">支付系统：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" name="payment_system" maxlength="20" value="<?php echo $game_revision_info['payment'];?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">维护：</label>
	                <div class="controls">
	                    <select class="wh500" name="maintain" >
                            <option value="0" <?php if($game_revision_info['is_maintain']=='0') echo 'selected';?>>正常</option>
	                        <option value="1" <?php if($game_revision_info['is_maintain']=='1') echo 'selected';?>>维护中</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">维护提示：</label>
	                <div class="controls">
                        <input class="input_txt" type="text" name="maintain_tip" maxlength="80" value="<?php echo $game_revision_info['maintain_tip'] ;?>"/>
	                    <font>最长80个字符</font>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">工具版本：</label>
	                <div class="controls">
                        <input class="input_txt" type="text" name="tool_version" maxlength="80" value="<?php echo $game_revision_info['tool_version'] ;?>"/>
	                    <font>最长80个字符</font>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">Loading图：</label>
	                <div class="controls">
	                    <input type="file" id="" name="bgImg" onchange="">
                        <input type="hidden" id="original_picture_path" name="bg_picture_path"  value = "<?php echo $game_revision_info['bg_picture_path'];?>"/><br/>
                        <?php if($game_revision_info['bg_picture']){?>
                            <img src="<?php echo $game_revision_info['bg_picture'];  ?>" width="100"/>
                        <?php } ?>
	                    <font>支持jpg,png格式,960x640像素,小于200K</font>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">音乐：</label>
	                <div class="controls">
                        <input type="hidden" id="original_music_path" name="bg_music_path"  value = "<?php echo $game_revision_info['bg_music_path'];?>"/><br/>
	                    <input type="file" id="" name="music" onchange="">
                        <?php if($game_revision_info['bg_music']){?>
                            <embed src="<?php echo $game_revision_info['bg_music'];?>" autostart="true" loop="true" width="660" height="40">
                        <?php } ?>
	                    <font>支持mp3,wav,ogg格式,小于500K</font>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">游戏包：</label>
	                <div class="controls">
	                    <input type="file" id="" name="gmbag" onchange=""><br/>
	                    <span>支持zip格式</span>
	                </div>
	            </div>
                <div class="control-group <?php if(empty($revision_code_list)) echo 'hide';?>" >
	                <label class="control-label">补丁包：</label>
	                <div class="controls">
	                    <input type="file" id="" name="chafen_package" onchange="checkChafen()"><br />
	                    <span>支持 cpk 格式</span>
	                </div>

	                <div class="controls">
                        <select name="chafen_package_ver_code_lowest" id="chafen_package_ver_code_lowest" class="">
                            <option value="0">请选择最小差分版本</option>
                            <?php arsort($revision_code_list);foreach($revision_code_list as $revision_code) { ?>
                            <option value="<?php echo $revision_code; ?>"><?php echo $revision_code; ?></option>
                            <?php } ;?>
                        </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"></label>
	                <div class="controls">
	                    <input type="submit" class="btn btn-primary" value="提交编辑" />
	                </div>
	            </div>
	        </form>
	    </div>
	</div>
<script type="text/javascript">

function checkform(){
	var arrparam = new Array();
	arrparam['gamename'] = 'input[name=gamename]';
	//arrparam['bagname'] = 'input[name=bagname]';
	arrparam['sdk_version'] = 'input[name=sdk_version]';
	arrparam['version_number'] = 'input[name=version_number]';
	arrparam['game_version'] = 'input[name=game_version]';
	//arrparam['supplier'] = 'input[name=supplier]';
	arrparam['custom_system'] = 'input[name=custom_system]';
	arrparam['payment_system'] = 'input[name=payment_system]';

	//提示语
	arrtip = new Array();
	arrtip['gamename'] = '游戏名称不能为空';
	//arrtip['bagname'] = '包名不能为空';
	arrtip['sdk_version'] = 'SDK版本不能为空';
	arrtip['version_number'] = '版本编号不能为空';
	arrtip['game_version'] = '游戏版本不能为空';
	//arrtip['supplier'] = '提供商不能为空';
	arrtip['custom_system'] = '用户系统不能为空';
	arrtip['payment_system'] = '支付系统不能为空';
    error_flag=false; 
    err_msg='';
    revision_list = [<?php echo implode($revision_code_list,',');?>];
    //console.log(revision_list);
	for(var ele in arrparam){
        $(arrparam[ele]).parent().parent().removeClass('error');
		if( $(arrparam[ele]).val() == ''){
            //console.log( $(arrparam[ele]).parent().parent().attr('class'));
            err_msg = arrtip[ele] + "\n";
            error_flag=true;
            warnMessage($(arrparam[ele]), err_msg);
		}
	}
    $('#chafen_package_ver_code_lowest').parent().parent().removeClass('error');
    if($('input[name=chafen_package]').val()!=''){
        if($('#chafen_package_ver_code_lowest').val()=='0'){
            err_msg='需要指定最小差分版本';
            warnMessage($('#chafen_package_ver_code_lowest'),err_msg);
            error_flag=true;
        }else{
        }
    }
    if(error_flag){
        //error_flag=false;
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

function checkVersionCode(){
    revision_list = [<?php echo implode($revision_code_list,',');?>];
    max_revision_code = <?php echo $max_revision_code ;?>;
    var vercode = $.trim($('input[name=version_number]').val());
    var error_flag=false; 
    if (vercode <= max_revision_code){
        error_flag=true;
    }
    for (var i in revision_list) {
        if (vercode == revision_list[i]){
            error_flag=true; 
            break;
        }else{
            //
        }
    }
    if($('#active_version_code').val()==''){
        // 通常不会走到这里。 防止意外 
        //error_flag=true; 
    }
    if(error_flag){
            $('input[name=version_number]').parent().parent().addClass('error');
            $('#version_number_msg').addClass('text-error');
            $('#version_number_msg').text('请确保版本号大于 <?php echo $max_revision_code;?>');
    }else{
            $('input[name=version_number]').parent().parent().removeClass('error');
            $('#version_number_msg').removeClass('text-error');
            //$('input[name=version_number]').parent().parent().addClass('info');
            $('#version_number_msg').text('');
    }
}
function checkChafen(){
    if($('input[name=chafen_package]').val()!=''){
        $('#chafen_package_ver_code_lowest').addClass('required');
     //   $('#chafen_package_ver_code_lowest').addClass('error');
    }
}
$('#iconImg').on('change', function(){
    //console.log($(this).val());
    var name = $(this).val().toLowerCase();
    if(name.match(/\.(png|jpg|jpeg)$/i)){
    }else{
        alert('图标的格式不对');
        $(this).val('');
    }
});

</script>
</body>
</html>
