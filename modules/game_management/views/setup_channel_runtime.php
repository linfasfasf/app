	    <div class="row-fluid">
	        <ul class="breadcrumb">
	            <li>运营管理 <span class="divider">/</span></li>
                    <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
	            <li><a href="<?php echo site_url('game_management/viewgame/'.$game_id); ?>">查看游戏</a> <span class="divider">/</span></li>
	            <li class="active">设置渠道</li>
	        </ul>
	    </div>
<?php flash_message();?>
<?php $error_msg = validation_errors(); if(isset($error_msg) && !empty($error_msg)){
echo <<< EOF
    <div id="msg" class="alert"><p>{$error_msg}</p>
    </div>
EOF;
} else { ?>
    <div id="msg">
    </div>
<?php };?>
    
	    <div class='row-fluid'>

  <fieldset>
  <form class="form-horizontal" action="<?php echo site_url('game_management/experiment/add_chn_resource');?>" method="post" enctype="multipart/form-data">
    <legend>添加关联渠道</legend>
	            <div class="control-group">
	                <label class="control-label">渠道：</label>
	                <div class="controls chn_apk_mapping">
	                    <select name="channel_id" class="channel">
                            <?php foreach($channel_list as $channel) {
                                echo '<option value="' . $channel['channel_id']. '">' . $channel['channel_id'].'/'. $channel['channel_name'] .'</option>';
                            }?>
	                    </select >
                        <input class="input_txt action" type="hidden" name="action" value="setup" />
                        <input class="input_txt" type="hidden" name="revision_id" value="<?php echo $revision_id ;?>" />
                        <input class="input_txt" type="hidden" name="apk_download_url" value="<?php echo $apk_download_url ;?>" />
                        <button class="btn do" data-revisionid="<?php echo $revision_id ;?>" id="newchnmapping">新建</button>
	                </div>
	            </div>
    </form>
    <ul>
    <?php  foreach($chn_resources_orphans as $chn_resource) {
        echo '<li><a class="delete_chn_res" data-chnresid="' . $chn_resource['id']. '" href="'.$chn_resource['id']. '">删除资源 ' . $chn_resource['id'] . '|'. $chn_resource['apk_download_url'] . '</a></li>';
    } ?>
    </ul>
  </fieldset> 

                      <fieldset>
<?php if (!empty($mappings) ) { ?>
    <legend>已关联的渠道</legend>
<?php foreach($mappings as $index => $mapping) { ?>
    <form name="upload_res" class="form-horizontal" action="<?php echo site_url('game_management/experiment/add_chn_resource');?>" method="post" enctype="multipart/form-data">
        <div class="control-group">
            <table>    
                    <tbody>
                    <tr data-chnresid="<?php echo $mapping['chnres_id'] ;?>" 
                        data-revisionapkid="<?php echo $mapping['revision_apk_id'] ;?>" 
                            data-revisionid="<?php echo $revision_id ;?>" 
                            data-channelid="<?php echo $mapping['channel_id']; ?>" 
                            data-dir="<?php echo $mapping['res_file_dir']; ?>">
                            <td><label class="control-label">
<?php 
        if(!$mapping['apk_download_url'] || $mapping['active'] == 0 || 
          ($mapping['channel_config_type'] == 0 && empty($mapping['channel_config_encoded'])) ||
          ($mapping['download_third_plugin'] == 1 && $mapping['third_plugin_int_version'] === NULL) ||
          ($mapping['download_third_sdk'] == 1 && $mapping['third_sdk_int_version'] === NULL)
          ) {
            echo '<i class="icon-remove"></i>';
        }else{
            echo '<i class="icon-ok"></i>';
        }
?>
                                    渠道：</label></td>
<td><span class="main"><input class="input_txt" type="text" name="" maxlength="30" value="<?php echo $mapping['channel_id'].'/'.$mapping['channel_name']; ?>" disabled="disabled" /></span>
                        <span class="apkdownloadurl input input-xlarge uneditable-input" title='<?php echo $mapping['apk_download_url'];?>'><?php echo $mapping['apk_download_url'];?></span>
                        <span class="input-append hide">
                          <input class="input input-xlarge" id="" type="text" value='<?php echo $mapping['apk_download_url'];?>'>
                          <button class="btn apkdownloadupdate" type="button">更新游戏目录</button>
                        </span>
                        </td>
                        <td>
                            <button class="btn uploadbtn" type="button" data-dir="<?php echo $mapping['res_file_dir']; ?>" data-toggle="modal" data-target="#uploadModal">上传渠道资源</button>
                            <button id="chncfg-<?php echo $index;?>" class="btn chncfg" type="button" data-chnid="<?php echo $mapping['channel_id'];?>" data-apkid="<?php echo $mapping['revision_apk_id'] ;?>" 
                            data-type="<?php echo $mapping['channel_config_type'];?>" data-chnname="<?php echo $mapping['channel_name'];?>" 
                                    data-toggle="modal" data-target="#channelconfigmodal">渠道配置</button>

<!--
  <div class="btn-group">
    <button class="btn dropdown-toggle" data-toggle="dropdown">配置信息<span class="caret"></span></button>
    <ul class="dropdown-menu">
    <li><a href="<?php echo site_url('game_management/experiment/toggle_config_type'). '/' . $mapping['revision_apk_id']; ?>">当前包类型: 
<?php if ($mapping['channel_config_type'] == -1){
            echo '未设置';
        }elseif($mapping['channel_config_type'] == 1) {
            echo '完整渠道包';
        }else{
            echo '非完整渠道包';
        } ?> (点击修改)</a></li>
        <li><a href="<?php echo site_url('game_management/experiment/edit_channel_config'). '/' . $mapping['revision_apk_id']; ?>">配置信息详情</a></li>
    </ul>
  </div>
-->

                            <?php if ($this->acl_model->accessible('/system_management', '*') &&!empty($mapping['apk_download_url']) && $is_published==1) {?>
                            <?php if($enable_sync) { ?>
                            <a class="btn do syncbtn" data-action="sync" data-revisionid="<?php echo $revision_id;?>" data-channelid="<?php echo $mapping['channel_id'];?>" name="" data-toggle="modal" data-target="#sync_resources">同步到线上</a>
                            <?php } ?>
                                                              <span class="dropdown">
                              <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                渠道插件
                                <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                <li><a <?php echo 'href="'.site_url("game_management/experiment/set_thirdsdk?chnres_id={$mapping['chnres_id']}&type=plugin&value=null&revision_id=$revision_id").'"' ?>>不下载<?php echo $mapping['download_third_plugin']==0?'<i class="icon-ok"></i>':'';?></a></li>
                                <li role="separator" class="divider"></li>
                                <?php if(!empty($mapping['third_plug'])){?>
                                <!-- <li><a <?php echo 'href="'.site_url("game_management/experiment/set_thirdsdk?chnres_id={$mapping['chnres_id']}&type=plugin&value=-1&revision_id=$revision_id").'"' ?>>下载最新版本<?php if($mapping['third_plugin_int_version'] == -1){echo '<i class="icon-ok"></i>';}?></a></li> -->
                                <?php }else{ ?>
                                <li><a href="<?php echo site_url('system_management/third_sdk');?>">关联第三方SDK</a></li>
                                <?php }?>
                                <?php 
                                foreach ($mapping['third_plug'] as $value) {
                                    $selected = $mapping['third_plugin_int_version'] == $value['int_version']?'<i class="icon-ok"></i>':'';
                                    echo '<li><a href="'.site_url("game_management/experiment/set_thirdsdk?chnres_id={$mapping['chnres_id']}&type=plugin&value={$value['int_version']}&revision_id=$revision_id").'">'.$value['version'].' | '.$value['int_version'].$selected.'</i></a></li>';
                                }?>
                                <li role="separator" class="divider"></li>
                                <li class="disabled"><a >请重新设置<?php if($mapping['download_third_plugin'] == 1 && $mapping['third_plugin_int_version'] === NULL){echo '<i class="icon-ok"></i>';}?></a></li>
                              </ul>
                            </span>

                            <span class="dropdown">
                              <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                渠道SDK
                                <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                              <li><a <?php echo 'href="'.site_url("game_management/experiment/set_thirdsdk?chnres_id={$mapping['chnres_id']}&type=sdk&value=null&revision_id=$revision_id").'"' ?>>不下载<?php echo $mapping['download_third_sdk']==0?'<i class="icon-ok"></i>':'';?></a></li>
                              <li role="separator" class="divider"></li>
                              <?php if(!empty($mapping['third_sdk'])){?>
                              <!-- <li><a <?php echo 'href="'.site_url("game_management/experiment/set_thirdsdk?chnres_id={$mapping['chnres_id']}&type=sdk&value=-1&revision_id=$revision_id").'"' ?>>下载最新版本<?php if($mapping['third_sdk_int_version'] == -1){echo '<i class="icon-ok"></i>';}?></a></li> -->
                              <?php }else{ ?>
                                <li><a href="<?php echo site_url('system_management/third_sdk');?>">关联第三方SDK</a></li>
                              <?php }?>
                                <?php 
                                foreach ($mapping['third_sdk'] as $value) {
                                    $selected = $mapping['third_sdk_int_version'] == $value['int_version']?'<i class="icon-ok"></i>':'';
                                    echo '<li><a href="'.site_url("game_management/experiment/set_thirdsdk?chnres_id={$mapping['chnres_id']}&type=sdk&value={$value['int_version']}&revision_id=$revision_id").'">'.$value['version'].' | '.$value['int_version'].$selected.'</i></a></li>';
                                }?>
                                <li role="separator" class="divider"></li>
                                <li class="disabled"><a >请重新设置<?php if($mapping['download_third_sdk'] == 1 && $mapping['third_sdk_int_version'] === NULL){echo '<i class="icon-ok"></i>';}?></a></li>
                              </ul>
                            </span>
                            <?php if($mapping['active'] == 0)
                            { 
                            if($mapping['channel_config_type'] == 0 && empty($mapping['channel_config_encoded'])) { 
                            }else{ ?>
                                <a href="<?php echo site_url("game_management/experiment/active_apk?revision_id=$revision_id&apk_id={$mapping['revision_apk_id']}&active=1");?>" class="btn btn-primary" name="">上线</a>
                            <?php } ?>
                            <?php }else{ ?>
                                <a href="<?php echo site_url("game_management/experiment/active_apk?revision_id=$revision_id&apk_id={$mapping['revision_apk_id']}&active=0");?>" class="btn" name="">下线</a>
                            <?php }}
                            ?>
                                <?php if($this->acl_model->accessible('/game_management/game','delete')):?>
                            <a class="btn btn-danger do delete" data-action="delete" name="">X</a></td>
                        <?php endif;?>
                        </tr>
                        <tr class="hide">
                            <td colspan="4">
                            <ul class="thumbnails center">
                              <li class="span4">
                                <div class="thumbnail">
                                    <?php
                                    echo '<img style="width: 100px; height: 100px;" src="'. $mapping['icon_url'] . '"/>'; 
                                    ?>
                                </div>
                              </li>
                              <li class="span4">
                                <div class="thumbnail">
                                    <?php
                                    echo '<img style="width: 100px; height: 100px;" src="'. $mapping['bg_picture'] . '"/>'; 
                                    ?>
                                </div>
                              </li>
                              <li class="span4">
                                <div class="thumbnail">
                                    <audio controls>
                                    <source src="<?php echo $mapping['bg_music'];?>" />
                                    Your browser does not support the audio element.
                                    </audio>
                                </div>
                              </li>
                            </ul>
                            </td>
                        </tr>
                </tbody>
                </table>    
        </div>
    </form>
<?php } ?>
  </fieldset>
<?php } ?>
	        </form>
	    </div>
<script type="text/javascript">
    $(document).ready (function(){
            $('.main').click(
                function () {
                    $(this).parent().parent().next().toggle();
                });
            $('.syncbtn').click(
                function () { 
                    $('.syncbtn').each(function() { $(this).removeClass('active') });
                    $(this).addClass('active');
                }
            );
            $('.uploadbtn').click(
                function () { 
                    var data_dir = $(this).attr('data-dir');
                    var channel_id = $(this).parent().parent().data('channelid');
                    var chnres_id = $(this).parent().parent().data('chnresid');
                    var revisionapk_id = $(this).parent().parent().data('revisionapk_id');
                    $('#userdir').val(data_dir);
                    $('#workdir').html(data_dir);
                    uploadwidget( data_dir, channel_id, chnres_id,'btnupdate');
                }
            );
            $('#newchnmapping').click(
                function () {
                    var channel_id = $(this).parent().find('select').val();
                    var revision_id = $(this).attr('data-revisionid');
                    //return false; 
                }
            );
            $('.change_chn_res').click(
                function() {
                    $(this).parent().find('.upload').click();
                }
            );
            $('.delete_chn_res').click (
                function () {
                    var chnresid = $(this).attr('data-chnresid');
                    $.get('<?php echo site_url('game_management/experiment/ajax_delete_chnres');?>' + '/' + chnresid);
                    $(this).parent().remove();
                    return false; 
                }
            );
            $('a.delete').click(
                function() {
                    if(confirm('确定删除？'))
                    {
                        $.post("<?php echo site_url('game_management/experiment/ajax_remove_channel');?>"
                            ,{ revision_id: $(this).parent().parent().data("revisionid"), 
                                channel_id: $(this).parent().parent().data('channelid') }
                        ,function(result){ 
                            if(result == 'done')  {
                                window.location.reload();
                            } else {
                                $('.alert').remove();
                                $('#msg').addClass("alert").html('操作失败');
                            }
                        });
                        return false; 
                    }
                });
                        
            $('.select_apk').click(function(){
                if($(this).text() == '')
                {
                    $(this).hide();
                    $(this).parent().next().find('select').show();
                    $(this).parent().next().next().find('button.relate').show();
                    $(this).parent().next().next().find('button.uploadbtn').hide();
                }
            });
            $('button.relate').click(function (){
                 var channel_id = $(this).parent().prev().prev().find('input').attr('channel_id')
                 var revision_id = <?php echo $revision_id;?>;
                 var res_id = $(this).parent().prev().find('select option:selected').attr('rev_chn_res_id');
                 $.post("<?php echo site_url('game_management/experiment/copy_chn_res');?>",{
                     channel_id:channel_id,
                     revision_id:revision_id,
                     res_id:res_id
                 },function(result){
                     console.log(result);
                     if(result == "done")
                     {
                         window.location.reload();
                     } else {
                        $('.alert').remove();
                        $('#msg').addClass("alert").html('操作失败');
                    }
                 });
            });    
            $('.apkdownloadupdate').click(function () {
                    if(confirm('确定更新？'))
                    {
                        $.post("<?php echo site_url('game_management/experiment/ajax_update_runtime_gameurl');?>"
                            ,{ revision_id: $(this).parent().parent().parent().data("revisionid"), 
                                channel_id: $(this).parent().parent().parent().data('channelid'),
                                revisionapkid: $(this).parent().parent().parent().data('revisionapkid'),
                                gameurl:    $(this).prev().val()
                            }
                        ,function(result){ 
                            if(result == 'done')  {
                                window.location.reload();
                            }else if(result == 'manifestnotok')  {
                                $('.alert').remove();
                                $('#msg').addClass("alert").html('Manifest信息更新失败');
                            } else {
                                $('.alert').remove();
                                $('#msg').addClass("alert").html('操作失败');
                            }
                        });
                        return false; 
                    }
            });
            $('.apkdownloadurl').click(function () {
                    updateinput = $(this).next();
                    $(this).hide();
                    updateinput.show();
            });
});
</script>
