<div class="row-fluid">
    <ul class="breadcrumb">
        <li>运营管理 <span class="divider">/</span></li>
        <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
        <li class="active">查看游戏</li>
    </ul>
</div>
<?php echo flash_message(); ?>

<div id="msg">
</div>

<form class="form-horizontal"  action="">
                <div class="row-fluid pull-left">
                    <label class="control-label"><span class="red">*</span>游戏名称：</label>
                    <span class="input-xlarge uneditable-input"><?php echo $gameinfo['game_name']; ?></span>
                </div>
                <div class="row-fluid pull-left">
                    <label class="control-label"><span class="red">*</span>游戏类型：</label>
                    <span class="input-xlarge uneditable-input">
<?php
$game_type_labels = array( 0=>'未指定', 1=>'角色扮演', 2=>'经营策略', 3=>'即时战斗', 4=>'卡牌', 5=>'模拟养成', 6=>'动作射击', 7=>'休闲时间', 8=>'塔防', 9=>'小游戏', 10=>'棋牌');
echo $game_type_labels[$gameinfo['game_type']];
?></span>
                </div>
                <div class="row-fluid pull-left">
                    <label class="control-label"><span class="red">*</span>游戏模式：</label>
                    <span class="input-xlarge uneditable-input">
<?php
$game_mode_labels = array(0=>'试玩包', 1=>'托管包', 2=>'试玩包', 3=>'托管包', 4=>'独立包', 7=>'Runtime 游戏');
echo $game_mode_labels[$gameinfo['game_mode']];
?></span>
                </div>
                <div class="row-fluid pull-left">
                    <label class="control-label"><span class="red">*</span>Game Key：</label>
                    <span class="input-xlarge uneditable-input"><?php echo $gameinfo['game_key']; ?></span>
                </div>
                <div class="row-fluid pull-left">
                    <label class="control-label"><span class="red">*</span>关联帐号：</label>
                    <span class="input-xlarge uneditable-input"><?php echo $gameinfo['email']; ?></span>
                </div>
                <div class="row-fluid pull-left">
                    <label class="control-label"><span class="red">*</span>提供商：</label>
                    <span class="input-xlarge uneditable-input"><?php echo $gameinfo['cp_vendor']; ?></span>
                </div>
                <div class="row-fluid pull-left">
                    <label class="control-label">离线支持：</label>
                    <span class="input-xlarge uneditable-input">                 
<?php
$offline_support_labels = array( 0=>'不支持离线', 1=>'支持离线');
echo $offline_support_labels[$gameinfo['offline_support']];
?></span>
                </div>
                <div class="row-fluid pull-left" <?php if($gameinfo['offline_support']==1) { echo '';} else {echo 'hidden';}?>>
                    <label class="control-label"><span class="red">*</span>允许删除data目录：</label>
                    <span class="input-xlarge uneditable-input" ><?php  if($gameinfo['allow_del_data']==1) { echo '是';} else {echo '否';};?></span>
                </div>
    
                <div class="row-fluid pull-left">
                    <label class="control-label">是否清除缓存：</label>
                    <span class="input-xlarge uneditable-input">
<?php
$purge_cache_labels = array( 0=>'否', 1=>'是');
echo $purge_cache_labels[$gameinfo['purge_cache']];
?></span>
                </div>
                <div class="row-fluid pull-left" <?php if($gameinfo['purge_cache']==0) { echo '';} else {echo 'hidden';}?>>
                    <label class="control-label"><span class="red">*</span>不清除缓存的时间：</label>
                    <span class="input-xlarge uneditable-input">
                        <?php  if($gameinfo['purge_cache_time']==1){echo '一个月';}
                               else if($gameinfo['purge_cache_time']==2){echo '两个月';}
                               else if($gameinfo['purge_cache_time']==3){echo '三个月';}
                               else if($gameinfo['purge_cache_time']==6){echo '半年';}
                               else if($gameinfo['purge_cache_time']==12){echo '一年';}
                               else if($gameinfo['purge_cache_time']==24){echo '两年';}
                               else if($gameinfo['purge_cache_time']==99){echo '永久';};                         
                        ?>
                    </span>
                </div>
<br />


<!--<fieldset>-->
        <!--<button class="btn uploadbtn" type="button" data-dir="" data-toggle="modal" data-target="#uploadModal">上传通用资源</button>-->
    <!--<legend>版本信息</legend>-->
    <div class="row-fluid pull-left">
    <table class="table table-condensed table-hover <?php if ($gameinfo['game_mode'] == 7) echo 'runtime' ;?>" id="report">
                <caption><strong>版本信息</strong></caption>
                <thead>
                    <tr class="info">
                        <th>id</th>
                        <th>上线状态</th>
                        <th>发布状态</th>
                        <th>图标</th>
                        <th>游戏版本名称</th>
                        <th>游戏版本</th>
                        <th>版本编号</th>
                        <th>小版本号</th>
                        <th>文件状态</th>
                        <th>游戏SDK版本</th>
                        <th>引擎版本</th>
                        <th>用户系统</th>
                        <th>支付系统</th>
                        <th>维护状态</th>
                        <th>维护提示</th>
                        <th>更新时间</th>
                        <th>操作</th></tr>
                </thead>
        <?php if (!empty($game_revisions)) { ?>
                <tbody>
<tbody>
<?php $count=0;foreach ($game_revisions as $game_revision) {
     $fdurl = site_url('file_management/manage_files?filedir='. urlencode($game_revision['file_dir']));
     $cdurl = site_url('file_management/manage_files?filedir='. urlencode($game_revision['cpk_file_dir']));
?>
<tr class=" <?php $count++; if($count%2) echo 'warning'; ?>">
    <td><?php echo $game_revision['id'];?></td>
    <td><?php if($game_revision['is_published'] == 0) {
        
    }else{
        if($active_version_code==$game_revision['package_ver_code'])
        {
            echo "<span class='text-error'><strong>线上版本</strong></span> ";
        }
        else{ ?>
            非线上版本<br />
            <a href="<?php echo site_url('game_management/activate_revision/'.$game_revision['id'] );?>">设为线上版本</a>
        <?php }
    }
    ?>
        </td>
        <td>
            
<?php if ($game_revision['is_published'] == 1) {?>
                                <span class="text-success">已发布</span><br>
                                <?php if ($this->acl_model->accessible('/system_management', '*')) {?>
                                <a href="<?php echo site_url('game_management/experiment/set_unpublished?revision_id='.$game_revision['id']).'&game_id='.$game_id; ?>" class="">下线</a>
                                <?php } ?>
                                <?php } else { ?>
    未发布<br /><a id="publish_btn_<?php echo $game_revision['id'];?>" href=" <?php echo site_url('game_management/publish_revision_handler/'. $game_revision['id'] ); ?>" >发布</a>
<?php }; ?>
    </td>
    <td><a href="<?php echo $game_revision['icon_url'];?>" target="_blank"><img src='<?php echo $game_revision['icon_url']; ?>' width=80/></a></td>
    <td><a href="<?php echo site_url('game_management/view_revision/'. $game_revision['id']);?>"><?php echo $game_revision['game_name']; ?></a></td>
    <td><?php echo $game_revision['package_ver']; ?></td>
    <td><?php echo $game_revision['package_ver_code']; ?></td>
    <td><?php echo $game_revision['hot_versioncode']; ?></td>
<td>
<?php if( $game_revision['file_dir']){?>
<!--<span class="label"><a class="file_dir" id="file_dir_<?php echo $game_revision['id']; ?>" target="_blank" href="<?php echo $fdurl;?>" title="<?php echo $game_revision['file_dir'];?>">FD</a></span>-->
<span class="label" title="<?php echo $game_revision['file_dir'];?>">FD</span>
<?php } ?>
<?php if( $game_revision['apk_download_url']){
        if($gameinfo['game_mode'] != 7){
?>
    <span class="label"><a class="apk_url" id="apk_url_<?php echo $game_revision['id']; ?>" target="_blank" href="<?php echo  $game_revision['apk_download_url'];?>" title="APK download url">A</a></span>
<?php }else{ ?>
    <span class="label" title="<?php echo $game_revision['apk_download_url'];?>">R</span>
<?php }}
?>
<?php if(($gameinfo['game_mode']==1 || $gameinfo['game_mode']==3 || $gameinfo['game_mode']==4) && $game_revision['manifest_url']){?>
<span class="label"><a class="manifest_url" id="manifest_url_<?php echo $game_revision['id']; ?>" target="_blank" href="<?php echo $game_revision['manifest_url'];?>" title="Manifest ver:<?php echo $game_revision['manifest_version'];?>">M</a></span>
<?php } ?>
<?php if( $game_revision['chafen_url']){?>
<span class="label"><a class="chafen_url" id="chafen_url_<?php echo $game_revision['id']; ?>" target="_blank" href="<?php echo  $game_revision['chafen_url'];?>" title="chafen url since ver: <?php echo $game_revision['ver_last'];?>">C</a></span>
<?php } ?>
<?php  if( $game_revision['cpk_file_dir']){?>
<!-- <span class="label"><a class="cpk_dir" id="cpk_dir_<?php echo $game_revision['id']; ?>" target="_blank" href="<?php echo $cdurl;?>" title="CPK file dir">CD</a></span> -->
<span class="label" title="<?php echo $game_revision['cpk_file_dir'];?>">CD</span>
<?php } ?>
</td>
    <td><?php if ($game_revision['sdk_version']) echo 'V' . $game_revision['sdk_version'];?></td>
    <td><?php if ($game_revision['engine_version']) echo 'Cocos2d-x ' . $game_revision['engine_version'];?></td>
    <td><?php echo $game_revision['user_system']; ?></td>
    <td><?php echo $game_revision['payment']; ?></td>
    <td><?php if($game_revision['is_maintain']) echo '维护中';else echo '正常'; ?></td>
    <td><?php echo $game_revision['maintain_tip']; ?></td>
    <td><?php echo date('Y:m:d H:i:s', $game_revision['modify_time']); ?></td>
    <td>
    <a href="<?php echo site_url('game_management/view_revision/'. $game_revision['id']);?>">查看</a> 
<!-- <a href="<?php echo site_url('game_management/edit_revision/'.$game_revision['game_id'].'/'.$game_revision['id']);?>">编辑</a>  -->
<a href="<?php echo site_url('game_management/experiment/game_revision_handler2/'.$game_revision['game_id'].'/'.$game_revision['id']);?>">编辑</a> 

<?php if ($gameinfo['game_mode'] != 7) { ?>
<?php if ($this->acl_model->accessible('/file_management/manage_batch', '*')) { ?>
    <a href="<?php echo site_url('file_management/manage_batch').'?revision_id=' . $game_revision['id'];?>">编辑包</a>
<?php } ?>

<?php if ($gameinfo['game_mode']!=4) { ?>
    <a href="<?php echo site_url('game_management/experiment/setup_channel').'/' . $game_revision['id'];?>">设置渠道</a>
<?php } ?>
    <a href="<?php echo site_url("game_management/experiment/chafen_list")."?revision_id={$game_revision['id']}&game_id={$game_revision['game_id']}";?>">差分</a>
<?php }else{ 
// runtime 游戏
?>
    <a href="<?php echo site_url("game_management/experiment/upload_resource");?>">上传runtime包</a>
    <a href="<?php echo site_url('game_management/experiment/setup_channel').'/' . $game_revision['id'];?>">设置渠道</a>
<?php } ?>
    <?php if($this->acl_model->accessible('/game_management/game','delete')):?>
<a href="<?php echo site_url('game_management/delete_revision_handler/'.$game_revision['id']);?>" onclick="return confirm('确定删除版本 <?php echo $game_revision['id'] ;?>')">删除</a> 
<?php endif;?>
<?php /*
<a href="#">解压状态</a>
<a href="#">CDN同步</a>
 */ ?>
    </td>
</tr>
<?php } ?>
</tbody>
                <?php } ?>
            </table>
        <a class="btn" href="<?php echo site_url("game_management/experiment/game_revision_handler2/{$game_id}"); ?>">添加新版本</a>
        <a class="btn" href="<?php echo site_url("game_management/experiment/allow_old_api/{$game_id}"); ?>">允许旧接口访问</a>
    </div>

<!--</fieldset>-->
<script type="text/javascript">
    $(document).ready(
            function () {
                $('a.manifest_url').each(function () {
                    // 当 manifest_url 的资源 404 时， 将这个设为灰
                    //alert($(this).attr('href'))
                    html = $.ajax(
                            {
                                'url': $(this).attr('href'),
                                async: true,
                                context: this,
                                success: function () {
                                },
                                error: function () {
                                    //$(this).parent().html('manifest');
                                }
                            }
                    );
                });
            }
    );

    $('.uploadbtn').click(
            function () {
                var data_dir = $(this).attr('data-dir');
                var channel_id = $(this).parent().parent().data('channelid');
                var chnres_id = $(this).parent().parent().data('chnresid');
                $('#userdir').val(data_dir);
                $('#workdir').html(data_dir);
                uploadwidget(data_dir, channel_id, chnres_id, 'btnrevision');
            }
    );
</script>
<script>
    $('#change').click(function () {
        $(this).hide();
        $('.not_use').removeAttr('disabled');
        $('.not_use_file').show();
        $('.not_use_gen').show();
        $('#submit-btn').show();
    });
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
            if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
            if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
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
function fixapkname(){
    $('#apkname_msg').text($('#packagename').val() + '.apk');
}
</script>
