	    <div class="row-fluid">
	        <ul class="breadcrumb">
	            <li>运营管理 <span class="divider">/</span></li>
	            <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
	            <li><a href="<?php echo site_url('game_management/viewgame/'.$game_info['game_id']); ?>">查看游戏</a> <span class="divider">/</span></li>
<?php  if($revision_id!=0){ ?>
                    <li class="active">编辑游戏版本</li>
<?php  }else{ ?>
                    <li class="active">添加游戏版本</li>
<?php } ?>
	        </ul>
	    </div>
		<?php flash_message();?>
<?php if(isset($error_msg) || validation_errors()){
    $validation_errors = validation_errors();
    if(!isset($error_msg)) $error_msg='';
echo <<< EOF
    <div class="alert"><p>{$error_msg}</p>
    <p>{$validation_errors}</p>
    </div>
EOF;
}?>
	    <div class='row-fluid'>
            <form id="add_revision_now" class="form-horizontal" action="<?php echo site_url('game_management/experiment/game_revision_handler2') . '/' . $game_info['game_id'] . '/'. $revision_id; ?>" 
                enctype="multipart/form-data" method="post" onsubmit="return checkform()">
            <h3>游戏信息</h3>
	            <div class="control-group">
                    <label class="control-label"><span class="red">*</span>游戏名称：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" disabled name="gamename_face" maxlength="20"  value="<?php echo $game_info['original_game_name']; ?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>游戏模式：</label>
	                <div class="controls">
                        <input class="hidden"  type="hidden" name="game_mode" value="<?php echo set_value('game_mode', $game_info['game_mode']);?>" />
	                    <select class="wh500" name="game_mode_face" disabled>
                        <option value="0" <?php if ($game_info['game_mode']=='0') echo 'selected'; ?>>试玩包</option>
	                        <option value="1" <?php if ($game_info['game_mode']=='1') echo 'selected'; ?>>托管包</option>
	                        <option value="4" <?php if ($game_info['game_mode']=='4') echo 'selected'; ?>>独立包</option>
	                        <option value="7" <?php if ($game_info['game_mode']=='7') echo 'selected'; ?>>Runtime游戏</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>游戏类型：</label>
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
                    <input class="input_txt" disabled type="text" name="apkname_face" maxlength="50"  value= "<?php echo $game_info['package_name']?$game_info['package_name'] . '.apk':''; ?>"/>
	                </div>
	            </div>
                    <div class="control-group">
	                <label class="control-label"><span class="red">*</span>Game Key：</label>
	                <div class="controls">
                    <input class="input_txt" disabled type="text" name="game_key" maxlength="50"  value= "<?php echo $game_info['game_key'] ; ?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>提供商：</label>
	                <div class="controls">
                    <input class="input_txt" type="hidden" name="supplier" maxlength="20" value="<?php echo $game_info['cp_vendor'] ;?>"/>
                    <input disabled class="input_txt" type="text" name="supplier_face" maxlength="20" value="<?php echo $game_info['cp_vendor'] ;?>"/>
	                </div>
	            </div>
<h3>游戏版本信息</h3>
	            <div class="control-group">
                        <label class="control-label"></label>
<?php  if($revision_id!=0) { ?>
                        <img src="<?php echo $game_info['icon_url_face'];  ?>"/>
<?php } ?>
                </div>
	            <div class="control-group">
	                <label class="control-label">ICON：</label>
	                <div class="controls">
                        <input type="file" id="icon" name="icon"  accept="image/jpeg,image/png" /><br/>

                        <span id='icon_msg'>支持jpg,png格式,小于50K</span>
	                </div>
	            </div>
	            <div class="control-group">
                    <label class="control-label"><span class="red">*</span>游戏版本名称：</label>
	                <div class="controls">
                    <input type="hidden" name="game_id" maxlength="20"  value="<?php echo set_value('game_id', $game_info['game_id']); ?>"/>
                    <input type="hidden" name="revision_id" maxlength="20"  value="<?php echo $revision_id; ?>"/>
                    <input class="input_txt" type="hidden" name="package_name" maxlength="50"  value="<?php echo $game_info["package_name"]; ?>"/>
                    <input type="hidden" id="active_version_code" name="active_version_code" maxlength="20"  value="<?php //echo $active_version_code; ?>"/>
                    <input class="input_txt" type="text" id="game_name" name="game_name" maxlength="20"  value="<?php
                        if (set_value('game_name', $game_info['game_name'])) echo set_value('game_name', $game_info['game_name']);
                        else echo $game_info['original_game_name'] ; ?>"/>
	                </div>
	            </div>
	            <div class="control-group">
                    <label class="control-label">游戏描述：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" name="game_desc" maxlength="20"  value="<?php
                        if (set_value('game_desc', $game_info['game_desc'])) echo set_value('game_desc', $game_info['game_desc']);
                        else echo $game_info['original_game_name'] ; ?>"/>
	                </div>
	            </div>
	            <div class="control-group">
                <label class="control-label"><span class="red">*</span>星级：</label>
	                <div class="controls">
	                    <select class="wh500" name="star">
                            <option value="1" <?php if ($game_info['star']=='1') echo 'selected';?>>1</option>
                            <option value="2" <?php if ($game_info['star']=='2') echo 'selected';?>>2</option>
                            <option value="3" <?php if ($game_info['star']=='3') echo 'selected';?>>3</option>
                            <option value="4" <?php if ($game_info['star']=='4') echo 'selected';?>>4</option>
                            <option value="5" <?php if ($game_info['star']=='5') echo 'selected'; elseif(empty($game_info['star'])) echo 'selected'?>>5</option>
	                    </select>
	                </div>
	            </div>
<!--
-->
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>游戏 SDK版本：</label>
	                <div class="controls">
	                    <select class="wh500" name="sdk_version">
	                        <option value="2" <?php if ($game_info['sdk_version']=='2') echo 'selected'; ?>>V2</option>
	                        <option value="3" <?php if ($game_info['sdk_version']=='3') echo 'selected'; ?>>V3</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>游戏版本：</label>
	                <div class="controls">
<?php if ($revision_id) { ?>
                    <input class="input_txt" type="text" name="face_package_ver" maxlength="20" placeholder="" value="<?php echo set_value('package_ver', $game_info['package_ver']);?>" disabled="disabled"/>
                    <input class="input_txt" type="hidden" name="package_ver" maxlength="20" placeholder="" value="<?php echo set_value('package_ver', $game_info['package_ver']);?>"/>
<?php }else{ ?>
                    <input class="input_txt" type="text" name="package_ver" maxlength="20" placeholder="" value="<?php echo set_value('package_ver', $game_info['package_ver']);?>"/>
<?php } ?>
                    <br/><span></span>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>版本编号：</label>
	                <div class="controls">

                    <input type="hidden" name="original_package_ver_code" maxlength="9" value="<?php echo set_value('package_ver_code', $game_info['package_ver_code']);?>"/>
<?php if ($revision_id) { ?>
                    <input class="input_txt" type="text" id="face_package_ver_code" name="package_ver_code" maxlength="9" placeholder="" value="<?php echo set_value('package_ver_code', $game_info['package_ver_code']);?>" disabled="disabled"/><br />
                    <input class="input_txt" type="hidden" id="package_ver_code" name="package_ver_code" maxlength="9" placeholder="" value="<?php echo set_value('package_ver_code', $game_info['package_ver_code']);?>"/><br />
<?php }else{ ;?>
                    <input class="input_txt" type="text" id="package_ver_code" name="package_ver_code" maxlength="9" placeholder="" value="<?php echo set_value('package_ver_code', $game_info['package_ver_code']);?>"/><br />
<?php } ?>
                    <span id='version_number_msg' class="">请输入整数版本编号,小于等于9位数</span> 
	                </div>
	            </div>

<?php if(!$lock && $this->acl_model->accessible('/system_management', '*') && $revision_id){?>
                    <div class="control-group">
	                <label class="control-label"><span class="red">*</span>小版本号：</label>
	                <div class="controls">
                            <input class="input_txt" type="text" name="hot_versioncode" maxlength="10" value="<?php echo set_value('hot_versioncode',  $game_info['hot_versioncode']);?>"/><span class="">请谨慎修改</span><br />
	                </div>
	            </div>
<?php }else{ ?>
        <input class="input_txt" type="text" style="display: none" name="hot_versioncode" maxlength="10" value="<?php echo set_value('hot_versioncode',  $game_info['hot_versioncode']);?>"/><br />
<?php } ?>

<?php if($game_info['game_mode']  != '7') { ?>
	            <div class="control-group">
	                <label class="control-label">热更新名：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" name="genuine_versionname" maxlength="20" value="<?php echo set_value('genuine_versionname',  $game_info['genuine_versionname']);?>"/><br />
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">热更新号：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" name="genuine_versioncode" maxlength="20" value="<?php echo set_value('genuine_versioncode',  $game_info['genuine_versioncode']);?>"/><br />
	                </div>
	            </div>
<?php } ?>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>引擎版本：</label>
	                <div class="controls">
                    <select class="wh500" name="engine_version">
                        <option value="v2" <?php if ($game_info['engine_version']=='v2') echo 'selected'; ?>>Cocos2d-x v2</option>
                        <option value="v3" <?php if ($game_info['engine_version']=='v3') echo 'selected'; ?>>Cocos2d-x v3</option>
                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>引擎类型：</label>
	                <div class="controls">
                    <select class="wh500" name="engine" id="eng_sel">
                    	<option value="lua" <?php if ($game_info['engine']=='lua') echo 'selected'; ?>>lua</option>
                        <option value="js" <?php if ($game_info['engine']=='js') echo 'selected'; ?>>js</option>
                        <option value="cpp" <?php if ($game_info['engine']=='cpp') echo 'selected'; ?>>cpp</option>
                        <?php if ($revision_id) { ?>
                        	<option value="" <?php if ($game_info['engine']=='') echo 'selected'; ?>>不支持架构文件分离</option>	
                        <?php }else{?>
                        	<option value="">不支持架构文件分离</option>
                        <?php } ?>
                    </select>
                    <span class="">请选择引擎类型<br/></span>
                    <span id="hide_txt" style="display:none">
	                    “不支持架构文件分离”的类型为apk的SO文件没有分离的旧游戏<br/>
	                    新创建的游戏请选择引擎类型并支持架构文件分离<br/>
                    </span>
	                </div>
	            </div>
	            <?php if($game_info['game_mode'] == 7){?>
	            <div class="control-group">
	                <label class="control-label">兼容架构：</label>
	                <div class="controls">
						<?php $arches = array('armeabi','armeabi-v7a','armeabi-v8a','x86');
							foreach ($arches as $arch) {
								if(in_array($arch, $game_info['compatible_arch'])){
									$selected = 'checked="checked"';
								}
								else{
									$selected = '';
								}
						?>
							<label class="checkbox-inline">
								<input type="checkbox" name="compatible_arch[]" <?php echo $selected;?> value="<?php echo $arch;?>"><?php echo ' '.$arch;?>
							</label>
						<?php }?>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">Runtime Core Patch：</label>
	                <div class="controls">
                        <button  class="btn" id="edit_rtcorepatch" name="edit_rtcorepatch" class="btn" data-toggle="modal" data-target="#rtcorepatchwidget">编辑</button>
	                </div>
	            </div>
	            <?php }?>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>屏幕方向：</label>
	                <div class="controls">
	                    <select class="wh500" name="orientation">
                            <option value="0" <?php if ($game_info['orientation']=='0') echo 'selected' ;elseif(empty($game_info['orientation'])) echo 'selected';?>>横屏</option>
                            <option value="1" <?php if ($game_info['orientation']=='1') echo 'selected';?>>竖屏</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">用户系统：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" name="user_system" maxlength="20" value="<?php echo set_value('user_system',  $game_info['user_system']);?>"/><br />
                    <span></span>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">支付系统：</label>
	                <div class="controls">
                    <input class="input_txt" type="text" name="payment" maxlength="20" value="<?php echo set_value('payment', $game_info['payment']);?>"/><br />
                    <span></span>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label"><span class="red">*</span>维护：</label>
	                <div class="controls">
	                    <select class="wh500" name="is_maintain" >
                            <option value="0" <?php if($game_info['is_maintain']=='0') echo 'selected';?>>正常</option>
	                        <option value="1" <?php if($game_info['is_maintain']=='1') echo 'selected';?>>维护中</option>
	                    </select>
	                </div>
	            </div>

	            <div class="control-group">
	                <label class="control-label">维护提示：</label>
	                <div class="controls">
                        <input class="input_txt" type="text" name="maintain_tip" maxlength="80" value="<?php echo set_value('maintain_tip', $game_info['maintain_tip']) ;?>"/>
	                    <font>最长80个字符</font>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">工具版本：</label>
	                <div class="controls">
                        <input class="input_txt" type="text" name="tool_version" maxlength="80" value="<?php echo set_value('tool_version', $game_info['tool_version']) ;?>"/>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">Loading图：</label>
	                <div class="controls">
	                    <input type="file" id="loading" name="bgpic" accept="image/jpeg,image/png"  onchange="">
                        <input type="hidden" id="original_picture_path" name="bg_picture_path"  value = ""/><br/>
                        <?php if($game_info['bg_picture']){?>
                            <img src="<?php echo $game_info['bg_picture_face'];  ?>" width="100"/>
                        <?php } ?>
                        <span>支持jpg,png格式,小于300K</span>
	                </div>
	            </div>
	            <div class="control-group">
	                <label class="control-label">音乐：</label>
	                <div class="controls">
                        <input type="hidden" id="original_music_path" name="bg_music_path"  value = ""/><br/>
	                    <input type="file" id="music" name="bgmusic" onchange="">
                        <?php if($game_info['bg_music']){?>
                            <div class="thumbnail">
                                <audio controls>
                                    <source src="<?php echo $game_info['bg_music_face']; ?>" />
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        <?php } ?>
	                    <br/><span>支持mp3,wav,ogg格式,小于500K</span>
	                </div>
	            </div>
                <?php if (!$revision_id) { ?>
	           	<div class="control-group">
	                <label class="control-label">单机游戏：</label>
	                <div class="controls">
	                <label class="checkbox">
						<input name="person_game" type="checkbox"> 勾选将生成一个单机游戏
				    </label>
				    </div>
	            </div>
                <?php }?>
<?php if($new_game && $game_info['game_mode']  != '7') { ?>
                    <div class="control-group">
	                <label class="control-label">从其他版本复制</label>
	                <div class="controls">
                            <select class="wh500" name="select_rev_id" id="select_hot_versioncode">
                                <option value="0">--请选择版本--</option>
                                <option disabled="">大版本号/小版本号</option>
                                <?php
                                     foreach ($other_ver as $key => $value) {
                                         echo '<option value="'.$value['revision_id'].'">'.$value['package_ver_code'].'  /'.$value['hot_versioncode'].'</option>';
                                     }
                                ?>
                                <option disabled="">---------------------</option>
                                <option value="-1">--从其他游戏复制--</option>
	                    </select>
	                </div>
	            </div>
                    <div class="control-group" hidden="">
	                <label class="control-label"><span class="red">*</span>指定版本id</label>
	                <div class="controls">
                            <input class="input_txt" type="text" name="from_rev_id" value="">
	                </div>
	            </div>
                    <div class="control-group" hidden="">
	                <label class="control-label">复制渠道资源</label>
	                <div class="controls">
                            <label class="label"><input type="checkbox" class="checkbox" name="copy_chn" value="1" checked="checked"> 是 </label>
	                </div>
	            </div> 
<?php }?>
	            <div class="control-group">
	                <label class="control-label"></label>
	                <div class="controls">
                    <input id="submit-btn" type="submit" class="btn btn-primary" value="<?php if($revision_id > 0) echo '更新'; else echo '创建';?>"/>
	                </div>
	            </div>
	        </form>
	    </div>
<script type="text/javascript">
    $(document).ready(function(){
    	if($('#eng_sel').val() == ''){
    		$('#hide_txt').show();
    	}
    	$('#eng_sel').change(function(event) {
    		if($(this).val() == ''){
    			$('#hide_txt').show();
    		}else{
    			$('#hide_txt').hide();
    		}
    	});

        $('#select_hot_versioncode').change(function(){
            var rev_id = $(this).val();
            if(rev_id == 0)
            {
                $(this).parent().parent().next().hide();
//                $(this).parent().parent().next().next().hide();
            }
            else
            {
//                $(this).parent().parent().next().next().show();
            }
            if(rev_id == -1)
            {
                $(this).parent().parent().next().show();
            }
            else
            {
                $(this).parent().parent().next().hide();
            }
        });
    });

$('#icon').change(function(){
    //console.log($(this).val());
    var name = $(this).val().toLowerCase();
    if(name.match(/\.(png|jpg|jpeg)$/i)){
    }else{
        warnMessage($(this), '图标的格式不对');
        //return false; 
        $(this).val('');
    }
});
$('#loading').on('change', function() {
    //console.log($(this).val());
    var name = $(this).val().toLowerCase();
    if(name.match(/\.(png|jpg|jpeg)$/i)){
    }else{
        warnMessage($(this), '背景图的格式不对');
        $(this).val('');
    }
});
$('#music').on('change', function(){
    //console.log($(this).val());
    var name = $(this).val().toLowerCase();
    if(name.match(/\.(mp3|wav|ogg)$/i)){
    }else{
        warnMessage($(this), '音乐的格式不对');
        $(this).val('');
    }
});
$('#package_file').on('change', function(){
    //console.log($(this).val());
    var name = $(this).val().toLowerCase();
    if(name.match(/\.(zip)$/i)){
    }else{
        warnMessage($(this), '游戏包的需为 zip 格式');
        $(this).val('');
    }
});
$('#chafen_package').on('change', function(){
    //console.log($(this).val());
    var name = $(this).val().toLowerCase();
    if(name.match(/\.(cpk)$/i)){
    }else{
        warnMessage($(this), '差分包需为 cpk 格式');
        $(this).val('');
    }
});


function checkform(){
	var arrparam = new Array();
	arrparam['gamename'] = 'input[name=game_name]';
	arrparam['version_number'] = 'input[name=package_ver_code]';
	arrparam['game_version'] = 'input[name=package_ver]';

	//提示语
	arrtip = new Array();
	arrtip['gamename'] = '游戏名称不能为空';
	arrtip['version_number'] = '版本编号不能为空';
	arrtip['game_version'] = '游戏版本不能为空';
	//arrtip['supplier'] = '提供商不能为空';
	arrtip['gmbag'] = '游戏包不能为空';
        arrtip['test_duration'] = '试玩时间不能为空';
    error_flag=false;
    err_msg='';
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

    ver_code = $('#package_ver_code').val();
    if(isNaN(ver_code) || ver_code=='0'){
        err_msg='版本编号必须是大于 0 的数字';
        warnMessage($('#package_ver_code'),err_msg);
        error_flag=true;
    }
    game_name = $('#game_name').val();
    if(game_name.length < 1 ||game_name.length >20){
        err_msg='游戏至少为 1 个字符';
        warnMessage($('#game_name'),err_msg);
        error_flag=true;
    }
    if(error_flag){
        //error_flag=false;
        return false;
    }
}
function warnMessage(element, msg){
    element.focus();
    element.parent().parent().addClass('error');
    element.siblings('span').text(msg);
}

</script>
