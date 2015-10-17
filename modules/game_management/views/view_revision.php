<div class="row-fluid">
    <ul class="breadcrumb">
        <li>运营管理 <span class="divider">/</span></li>
        <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
        <li><a href="<?php echo site_url('game_management/viewgame/'.$game_id); ?>">查看游戏</a> <span class="divider">/</span></li>
        <li class="active">查看游戏版本</li>
    </ul>
</div>
<?php flash_message();?>
<div class='row-fluid'>
    <form id="add_revision_now" class="form-horizontal" action="<?php echo site_url('game_management/add_game_revision_handler'); ?>" enctype="multipart/form-data" method="post" onsubmit="return checkform()">
        <h2>游戏信息</h2>
        <div class="control-group">
            <label class="control-label">游戏ID：</label>
            <span class="input-xlarge uneditable-input"><?php echo $gameinfo['game_id']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">游戏名称：</label>
            <span class="input-xlarge uneditable-input"><?php echo $gameinfo['game_name']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">游戏类型：</label>
            <span class="input-xlarge uneditable-input"><?php echo $gameinfo['game_type']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">游戏模式：</label>
            <span class="input-xlarge uneditable-input">
<?php
$game_mode_labels = array( 0=>'试玩包', 1=>'托管包', 2=>'试玩包', 3=>'托管包', 4=>'独立包', 7=>'Runtime游戏');
echo $game_mode_labels[$gameinfo['game_mode']];
?></span>
        </div>
        <div class="control-group">
            <label class="control-label">提供商：</label>
            <span class="input-xlarge uneditable-input"><?php echo $gameinfo['cp_vendor']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">包名：</label>
            <span class="input-xlarge uneditable-input"><?php echo $gameinfo['package_name']; ?></span>
        </div>
        <h2>游戏版本信息</h2>
        <div class="control-group">
            <label class="control-label"></label>
            <img src="<?php echo $game_revision_info['icon_url'];  ?>"/>
        </div>
        <div class="control-group">
            <label class="control-label">游戏版本名称：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['game_name']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">游戏描述：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['game_desc']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">星级：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['star']; ?></span>
        </div>
        <!--
                <div class="control-group">
                    <label class="control-label">APK文件名：</label>
                    <div class="controls">
                    <input class="input_txt" type="text" name="apkname" maxlength="50"  value= "<?php echo $game_revision_info['apk_name']; ?>"/>
                    </div>
                </div>
-->
        <div class="control-group">
            <label class="control-label">游戏 SDK 版本：</label>
            <span class="input-xlarge uneditable-input">V<?php echo $game_revision_info['sdk_version']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">游戏版本：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['package_ver']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">版本编号：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['package_ver_code']; ?></span>
        </div>
<?php if($gameinfo['game_mode'] != '7') { ?>
        <div class="control-group">
            <label class="control-label">热更新名：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['genuine_versioncode']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">热更新号：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['genuine_versioncode']; ?></span>
        </div>
<?php } ?>
        <div class="control-group">
            <label class="control-label">引擎版本：</label>
            <span class="input-xlarge uneditable-input">Cocos2d-x <?php echo $game_revision_info['engine_version']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">引擎类型：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['engine']; ?></span>
        </div>
        <?php 
        if($gameinfo['game_mode'] == 7){?>
                <div class="control-group">
                    <label class="control-label">兼容架构：</label>
                    <div class="controls">
                        <?php $arches = array('armeabi','armeabi-v7a','armeabi-v8a','x86');
                            foreach ($arches as $arch) {
                                if(in_array($arch, $game_revision_info['compatible_arch'])){
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
                <?php }?>
        <div class="control-group">
            <label class="control-label">屏幕方向：</label>
            <span class="input-xlarge uneditable-input"><?php if ($game_revision_info['orientation']=='0') echo '横屏' ; else echo '竖屏'; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">用户系统：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['user_system']; ?></span>
        </div>
        <div class="control-group">
            <label class="control-label">支付系统：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['payment']; ?></span>
        </div>
        <!--<div class="control-group">
            <label class="control-label">试玩时长(秒)：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['test_duration']; ?></span>
        </div>-->
        <div class="control-group">
            <label class="control-label">维护：</label>
            <span class="input-xlarge uneditable-input"><?php if($game_revision_info['is_maintain']=='0') echo '正常'; else echo '维护中';?>  </span>
        </div>
        <div class="control-group">
            <label class="control-label">维护提示：</label>
            <span class="input-xlarge uneditable-input"><?php echo $game_revision_info['maintain_tip'];?>  </span>
        </div>
        <div class="control-group">
            <label class="control-label">Loading图：</label>
            <?php if($game_revision_info['bg_picture']){?>
            <img src="<?php echo $game_revision_info['bg_picture'];  ?>" width="200"/>
            <?php } ?>
        </div>
        <div class="control-group">
            <label class="control-label">音乐：</label>
            <?php if($game_revision_info['bg_music']){?>
            <div class="thumbnail">
                <audio controls>
                    <source src="<?php echo $game_revision_info['bg_music']; ?>" />
                    Your browser does not support the audio element.
                </audio>
            </div>
                <?php } ?>
            </div>
            <div class="control-group">
                <label class="control-label">游戏Manifest：</label>
                <p>

                <?php if($gameinfo['game_mode']==0 || $gameinfo['game_mode']==2){?>
                <span class="label inline-help">manifest</span><br/>
                <?php }else{ ?>
                <span class="label inline-help"><a target="_blank" href="<?php echo $game_revision_info['manifest_url'] ;?>">manifest</a></span><br/>
                <?php } ?>

                </p>
            </div>
        </form>
    </div>
