<fieldset>
    <legend>版本信息</legend>
    <form id="add_revision_now" class="form-horizontal" action="<?php echo site_url('game_management/experiment/edit_revision'); ?>" 
          enctype="multipart/form-data" method="post" onsubmit="return checkform()">
        <div class="control-group">
            <label class="control-label"></label>
            <?php if ($game_revision['id'] != 0) { ?>
                <img src="<?php echo $game_revision['icon_url']; ?>"/>
            <?php } ?>
        </div>
        <div class="control-group">
            <label class="control-label">ICON：</label>
            <div class="controls">
                <input type="file" id="icon" name="icon_url"  accept="image/jpeg,image/png" class="not_use_file" style="display: none"/><br/>
                <span id='icon_msg'>支持jpg,png格式,小于50K</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><span class="red">*</span>游戏版本名称：</label>
            <div class="controls">
                <input type="hidden" name="icon_url_path" maxlength="20"  value="<?php echo $game_revision['icon_url_face']; ?>"/>
                <input type="hidden" name="game_id" maxlength="20"  value="<?php echo set_value('game_id', $game_revision['game_id']); ?>"/>
                <input type="hidden" name="revision_id" maxlength="20"  value="<?php echo $game_revision['id']; ?>"/>
                <input class="input_txt" type="hidden" name="package_name" maxlength="50"  value="<?php echo $game_revision["package_name"]; ?>"/>
                <input class="input_txt not_use" disabled="disabled" type="text" id="game_name" name="game_name" maxlength="20"  value="<?php
                if (set_value('game_name', $game_revision['game_name']))
                    echo set_value('game_name', $game_revision['game_name']);
                else
                    echo $game_revision['original_game_name'];
                ?>"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">游戏描述：</label>
            <div class="controls">
                <input class="input_txt not_use" disabled="disabled" type="text" name="game_desc" maxlength="20"  value="<?php
                if (set_value('game_desc', $game_revision['game_desc']))
                    echo set_value('game_desc', $game_revision['game_desc']);
                else
                    echo $game_revision['original_game_name'];
                ?>"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">星级：<?php echo $game_revision['star'] ?></label>
            <div class="controls">
                <select class="wh500 not_use" name="star" disabled="disabled">
                    <option value="1" <?php if ($game_revision['star'] == '1') echo 'selected'; ?>>1</option>
                    <option value="2" <?php if ($game_revision['star'] == '2') echo 'selected'; ?>>2</option>
                    <option value="3" <?php if ($game_revision['star'] == '3') echo 'selected'; ?>>3</option>
                    <option value="4" <?php if ($game_revision['star'] == '4') echo 'selected'; ?>>4</option>
                    <option value="5" <?php
                    if ($game_revision['star'] == '5')
                        echo 'selected';
                    elseif (empty($game_revision['star']))
                        echo 'selected'
                        ?>>5</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><span class="red">*</span>游戏 SDK版本：</label>
            <div class="controls">
                <select class="wh500 not_use" name="sdk_version" disabled="disabled">
                    <option value="2" <?php if ($game_revision['sdk_version'] == '2') echo 'selected'; ?>>V2</option>
                    <option value="3" <?php if ($game_revision['sdk_version'] == '3') echo 'selected'; ?>>V3</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><span class="red">*</span>游戏版本：</label>
            <div class="controls">
                <input class="input_txt not_use" disabled="disabled" type="text" name="package_ver" maxlength="20" placeholder="" value="<?php echo set_value('package_ver', $game_revision['package_ver']); ?>"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><span class="red">*</span>版本编号：</label>
            <div class="controls">
                <input class="input_txt not_use" disabled="disabled" type="text" id="package_ver_code" name="package_ver_code" maxlength="9" placeholder="" value="<?php echo $game_revision['package_ver_code']; ?>"/><br />
                <span id='version_number_msg' class="">请输入整数版本编号,小于等于9位数</span> 
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><span class="red">*</span>引擎版本：</label>
            <div class="controls">
                <select class="wh500 not_use" name="engine_version" disabled="disabled">
                    <option value="v2" <?php if ($game_revision['engine_version'] == 'v2') echo 'selected'; ?>>Cocos2d-x v2</option>
                    <option value="v3" <?php if ($game_revision['engine_version'] == 'v3') echo 'selected'; ?>>Cocos2d-x v3</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><span class="red">*</span>屏幕方向：</label>
            <div class="controls">
                <select class="wh500 not_use" name="orientation" disabled="disabled">
                    <option value="0" <?php if ($game_revision['orientation'] == '0') echo 'selected';elseif (empty($game_revision['orientation'])) echo 'selected'; ?>>横屏</option>
                    <option value="1" <?php if ($game_revision['orientation'] == '1') echo 'selected'; ?>>竖屏</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">用户系统：</label>
            <div class="controls">
                <input class="input_txt not_use" disabled="disabled" type="text" name="user_system" maxlength="20" value="<?php echo set_value('user_system', $game_revision['user_system']); ?>"/><br />
                <span></span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">支付系统：</label>
            <div class="controls">
                <input class="input_txt not_use" disabled="disabled" type="text" name="payment" maxlength="20" value="<?php echo set_value('payment', $game_revision['payment']); ?>"/><br />
                <span></span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><span class="red">*</span>维护：</label>
            <div class="controls">
                <select class="wh500 not_use" name="is_maintain" disabled="disabled">
                    <option value="0" <?php if ($game_revision['is_maintain'] == '0') echo 'selected'; ?>>正常</option>
                    <option value="1" <?php if ($game_revision['is_maintain'] == '1') echo 'selected'; ?>>维护中</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">维护提示：</label>
            <div class="controls">
                <input class="input_txt not_use" disabled="disabled" type="text" name="maintain_tip" maxlength="80" value="<?php echo set_value('maintain_tip', $game_revision['maintain_tip']); ?>"/><br/>
                <span id="" class="">最长80个字符</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Loading图：</label>
            <div class="controls">
                <?php if ($game_revision['bg_picture']) { ?>
                    <img src="<?php echo $game_revision['bg_picture']; ?>" width="100"/><br /><br />
                <?php } ?>
                    <input type="file" id="loading" name="bg_picture" accept="image/jpeg,image/png"  onchange="" class="not_use_file" style="display: none">
                <input type="hidden" id="original_picture_path" name="bg_picture_path"  value = "<?php //  echo $game_revision['bg_picture_path'];   ?>"/><br/>

                <span>支持jpg,png格式,小于300K</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">音乐：</label>
            <div class="controls">
                <input type="hidden" id="original_music_path" name="bg_music_path"  value = "<?php // echo $game_revision['bg_music_path'];    ?>"/>
                <?php if ($game_revision['bg_music']) { ?>
                    <audio controls>
                        <source src="<?php echo $game_revision['bg_music']; ?>" />
                        Your browser does not support the audio element.
                    </audio><br/><br />
                <?php } ?>
                <input type="file" id="music" name="bg_music" onchange="" class="not_use_file" style="display: none">

                <br/><span>支持mp3,wav,ogg格式,小于500K</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"></label>
            <div class="controls">
                <input id="change" type="button" class="btn btn-warning" value="修改信息"/>
                <input id="submit-btn" type="submit" class="btn btn-primary" value="更新" style="display: none"/>
            </div>
        </div>
    </form>
</fieldset>
<script>
    $('#change').click(function(){
        $(this).hide();
        $('.not_use').removeAttr('disabled');
        $('.not_use_file').show();
        $('#submit-btn').show();
    });
</script>
