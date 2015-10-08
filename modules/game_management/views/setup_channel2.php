<div class="container-fluid">
  <div class="row-fluid">
    <ul class="breadcrumb">
      <li>运营管理 <span class="divider">/</span></li>
      <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
      <li><a href="<?php echo site_url('game_management/viewgame/'.$game_id); ?>">查看游戏</a> <span class="divider">/</span></li>
      <li class="active">设置渠道</li>
    </ul>
  </div>
  <div class="row-fluid">
  <?php flash_message();?>
  <?php $error_msg = validation_errors(); if(isset($error_msg) && !empty($error_msg)){
echo <<< EOF
    <div id="msg" class="alert"><p>{$error_msg}</p>
    </div>
EOF;
  } else { ?>
    <div id="msg"></div>
    <?php } ?>
  </div>
  <div class='row-fluid'>
    <form class="form-horizontal" action="<?php echo site_url('game_management/experiment/add_chn_resource');?>" method="post">
      <input class="input_txt channel" name="channel_id" list="channellist" autocomplete="off" placeholder="请输入有效的渠道ID" maxlength="10" />
      <datalist id="channellist">
      <?php foreach($channel_list as $channel) {
        echo '<option value="' . $channel['channel_id']. '">' . $channel['channel_id'].'/'. $channel['channel_name'] .'</option>';
      }?>
      </datalist>
      <input class="input_txt action" type="hidden" name="action" value="setup" />
      <input class="input_txt" type="hidden" name="revision_id" value="<?php echo $revision_id ;?>" />
      <input class="input_txt" type="hidden" name="apk_download_url" value="<?php echo isset($apk_download_url)?$apk_download_url:'';?>" />
      <button disabled="" class="btn do" data-revisionid="<?php echo $revision_id ;?>" id="newchnmapping">添加渠道</button>
    </form>
  </div>

  <?php if(!isset($apk_download_url)){ ?>
  <div class='row-fluid'>
  <p><b class="text-info">Tips:</b></p>
  <p class="muted">
    1、请按照
    <em class="text-error">命名规范</em>
    上传三种类型的APK支持各个版本的SDK，
    <abbr class="mytooltip" data-original-title="支持 1.6.0.0及以下版本的SDK" title="">带头段包的APK</abbr>
    <em class="text-error">( packagename.apk )</em>
    ，
    <abbr class="mytooltip" data-original-title="支持 1.6.0.0 - 2.0.0.0 版本的SDK" title="">真实APK</abbr>
    <em class="text-error">( _packagename.apk )</em>
    ，
    <abbr class="mytooltip" data-original-title="支持 2.0.0.0 及以上版本的SDK" title="">去除架构文件的APK</abbr>
    <em class="text-error">( _so_packagename.apk )</em>
    。
  </p>
    <p class="muted">2、请在<em class="text-error">‘详细信息’</em>中删除资源。</p>
  </div>
  <?php }?>
  <div class="row-fluid">
    <?php  
    $inactive = 0;
    foreach($chn_resources_orphans as $chn_resource) {
      echo '<li><a class="delete_chn_res" data-chnresid="' . $chn_resource['id']. '" href="'.$chn_resource['id']. '">删除资源 ' . $chn_resource['id'] . '|'. $chn_resource['apk_download_url'] . '</a></li>';
    } ?>
  </div>
    <div class="row-fluid">
      <table class="table table-bordered table-hover table-striped">
        <thead>
          <tr>
            <th><input type="checkbox" id="allcheckbox"> 发布状态</th>
            <th>可用于sdk</th>
            <th>渠道</th>
            <th><?php echo isset($apk_download_url)?'游戏目录':'APK'; ?></th>
            <th>渠道配置</th>
            <th>渠道插件</th>
            <th>渠道SDK</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
    <?php
      foreach ($mappings as $key => $mapping) {
        //完整性检查
        //检查APK 渠道配置文件  渠道插件 渠道SDK  游戏是否发布 渠道是否上线 arch文件分离
        $error = array();
        if($is_published == 0){
          $error[] = '游戏未发布';
        }
        if($mapping['active'] == 0){
          $error[] = '渠道离线状态';
        }
        if(empty($mapping['apk_download_url'])){
          $error[] = 'apk未设置';
        }
        if($mapping['channel_config_type'] == 0 && empty($mapping['channel_config_encoded'])){
          $error[] = '渠道配置文件未设置';
        }
        if($mapping['download_third_plugin'] == 1 && $mapping['third_plugin_int_version'] === NULL){
          $error[] = '第三方渠道插件未设置';
        }
        if($mapping['download_third_sdk'] == 1 && $mapping['third_sdk_int_version'] === NULL){
          $error[] = '第三方渠道SDK未设置';
        }
        if(!empty($engine)){
          $no_support = TRUE;
          foreach ($arches as $arch) {
            if($arch['channel_id'] == $mapping['channel_id']){
              $no_support = FALSE;
              break;
            }
          }
          if($no_support){
            $error[] = '需要架构文件支持';
          }
        }
        $copy_apk = '';
        if(empty($mapping['apk_download_url']) || $mapping['active'] == 0){
          $no_option = TRUE;
          $option = array();
          foreach ($mappings as $download) {
            if(!empty($download['apk_download_url']) && strcmp($download['channel_id'], $mapping['channel_id']))
            {
              $no_option = FALSE;
              $delete = empty($mapping['apk_download_url'])?$mapping['res_file_dir']:$mapping['apk_download_url'];
              $option[$download['channel_id']] = site_url("game_management/experiment/copy_chnres?from={$download['apk_download_url']}&to={$mapping['channel_id']}&delete=$delete&chnres={$mapping['chnres_id']}&revid={$revision_id}");
            }
          }
          if($no_option){
            $option['请上传APK'] = '#';
          }
          $copy_apk = dropdown('复制APK以及相关资源',$option);
        }
?>
    <tr data-apkid="<?php echo $mapping['revision_apk_id'];?>" 
    data-channelid="<?php echo $mapping['channel_id'];?>" 
    data-chnresid="<?php echo $mapping['chnres_id'];?>" 
    data-apkdownloadurl="<?php echo $mapping['apk_download_url'];?>"
    <?php if(empty($error)){echo 'class=""';}else{echo 'class="warning"';}?>
    >
      <td>
      <?php 
        if($mapping['active'] == 1){ ?>
          &nbsp;&nbsp;<a class="mytooltip" data-toggle="tooltip" title="点击设置为离线" href="<?php echo  site_url("game_management/experiment/active_apk?revision_id=$revision_id&apk_id={$mapping['revision_apk_id']}&active=0");?>"><span class="label label-success">在线</span></a>
        <?php }else{
          $inactive++;
        ?>
        <input type="checkbox" value="<?php echo $mapping['channel_id'];?>" class="onlinecheck">
        &nbsp;&nbsp;<a class="mytooltip" data-toggle="tooltip" title="点击设置为在线" href="<?php echo site_url("game_management/experiment/active_apk?revision_id=$revision_id&apk_id={$mapping['revision_apk_id']}&active=1");?>"><span class="label label-important">离线</span></a>
      <?php }?>
      </td>
      <td>
        <?php 
          if(!empty($error)) {
            $error_content = ul($error);
          ?>
            <i class="icon-remove"></i><a class="errtips" href="#">查看原因</a><span class="poptip" data-content="<?php echo $error_content;?>" data-html="true"></span>
          <?php }else{ ?>
            <i class="icon-ok"></i>
          <?php if(!isset($apk_download_url)){?>
            <a class="filetips" href="#">文件检查</a><span class="poptip" data-content="d" data-html="true"></span>
          <?php }?>
          <?php }?>
      </td>
      <td><?php echo $mapping['channel_id'].' / '.$mapping['channel_name'];?></td>
      <td>
      <?php if(isset($apk_download_url)){ ?>
      <span class="apkdownloadurl input span5 uneditable-input"  title="<?php echo $mapping['apk_download_url'];?>"><?php echo $mapping['apk_download_url'];?></span>
      <span class="input-append hide span5">
        <input class="input input-xlarge" id="" type="text" value="<?php echo $mapping['apk_download_url'];?>" placeholder="http://">
        <button class="btn apkdownloadupdate" data-chnid="<?php echo $mapping['channel_id'];?>" data-apkid="<?php echo $mapping['revision_apk_id'];?>" type="button">确定</button>
      </span>
      <?php }elseif(!empty($mapping['apk_download_url'])){ ?>
          <a href="<?php echo $mapping['apk_download_url'];?>"><?php echo basename($mapping['apk_download_url']);?></a>
          <a href="#" class="del_apk" data-apkid=><span class="mytooltip label pull-right" data-toggle="tooltip" title="删除头段包APK,真APK,去除架构APK以及架构文件">删除</span></a>
      <?php }else{
          echo $copy_apk;
      }?>
      </td>
      <td>
        <a id="chncfg-<?php echo $key;?>" href="#" class="chncfg" data-chnid="<?php echo $mapping['channel_id'];?>" data-apkid="<?php echo $mapping['revision_apk_id'] ;?>" 
            data-type="<?php echo $mapping['channel_config_type'];?>" data-chnname="<?php echo $mapping['channel_name'];?>" 
            data-toggle="modal" data-target="#channelconfigmodal">渠道配置</a>
      </td>
      <td>
      <?php
        //渠道插件
        $plugin_option = array();
        if($mapping['third_plugin_int_version'] == ''){
          $plugin_name = '不下载';
        }
        if(empty($mapping['third_plug'])){
          $plugin_option['关联第三方SDK'] = site_url('system_management/third_sdk');
        }
        foreach ($mapping['third_plug'] as $value) {
          if($mapping['third_plugin_int_version'] == $value['int_version']){
            $plugin_name = $value['version'].' | '.$value['int_version'];
          }else{
            $plugin_option[$value['version'].' | '.$value['int_version']] = site_url("game_management/experiment/set_thirdsdk?chnres_id={$mapping['chnres_id']}&type=plugin&value={$value['int_version']}&revision_id=$revision_id");
          }
        }
        if($plugin_name != '不下载'){
          $plugin_option['不下载'] = site_url("game_management/experiment/set_thirdsdk?chnres_id={$mapping['chnres_id']}&type=plugin&value=null&revision_id=$revision_id");
        }
        if($mapping['download_third_plugin'] == 1 && $mapping['third_plugin_int_version'] === NULL){
          $plugin_name = '请重新设置';
        }
        echo dropdown($plugin_name,$plugin_option);
      ?>
      </td>
      <td>
      <?php 
        //渠道SDK
        $sdk_option = array();
        if($mapping['third_sdk_int_version'] == ''){
          $sdk_name = '不下载';
        }
        if(empty($mapping['third_sdk'])){
          $sdk_option['关联第三方SDK'] = site_url('system_management/third_sdk');
        }
        foreach ($mapping['third_sdk'] as $value) {
          if($mapping['third_sdk_int_version'] == $value['int_version']){
            $sdk_name = $value['version'].' | '.$value['int_version'];
          }else{
            $sdk_option[$value['version'].' | '.$value['int_version']] = site_url("game_management/experiment/set_thirdsdk?chnres_id={$mapping['chnres_id']}&type=sdk&value={$value['int_version']}&revision_id=$revision_id");
          }
        }
        if($sdk_name != '不下载'){
          $sdk_option['不下载'] = site_url("game_management/experiment/set_thirdsdk?chnres_id={$mapping['chnres_id']}&type=sdk&value=null&revision_id=$revision_id");
        }
        if($mapping['download_third_sdk'] == 1 && $mapping['third_sdk_int_version'] === NULL){
          $sdk_name = '请重新设置';
        }
        echo dropdown($sdk_name,$sdk_option);
      ?>
      </td>
      <td>
        <button class="btn uploadbtn" type="button" 
        data-dir="<?php echo $mapping['res_file_dir'];?>" 
        data-chnid="<?php echo $mapping['channel_id'];?>"
        data-chnresid="<?php echo $mapping['chnres_id'];?>" 
        data-revisionapkid="<?php echo $mapping['revision_apk_id'];?>" 
        data-toggle="modal" data-target="#uploadModal">上传资源</button>
        <button type="button" class="btn btn-lg" data-toggle="modal" data-target="#myModal<?php echo $mapping['channel_id'];?>">详细信息</button>
      <?php if($enable_sync) { ?>
        <a class="btn do syncbtn" data-action="sync" data-revisionid="<?php echo $revision_id;?>" data-channelid="<?php echo $mapping['channel_id'];?>" name="" data-toggle="modal" data-target="#sync_resources">同步到线上</a>
      <?php } ?>
        <a class="btn btn-danger do delete" data-action="delete" name="">删除</a>
      </td>
    </tr>
      <?php } ?>
        </tbody>
      </table>
      <?php if($inactive >1){?>
      <button id="batch_online" class="btn btn-default">批量上线</button>
      <?php } ?>
    </div>

    <?php foreach ($mappings as $key => $mapping) { ?>
    <div class="modal hide fade" id="myModal<?php echo $mapping['channel_id'];?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">渠道：<?php echo $mapping['channel_name'];?></h4>
          </div>
          <div class="modal-body">
              <table class="table table-hover" 
              data-resid="<?php echo $mapping['chnres_id'];?>" 
              data-channelid=<?php echo $mapping['channel_id'];?> 
              data-apkid=<?php echo $mapping['revision_apk_id'];?>>
                <tbody class="nth-child">
                  <tr>
                    <td>
                      <div class="row-fluid">
                        <div class="span6">
                          <div class="col-xs-6 col-md-3">
                            <?php 
                            $src = '';
                            if(empty($mapping['icon_url'])){
                              $src = $mapping['rev_icon_url'];
                            }else{
                              $src = $mapping['icon_url'];
                            }
                            $default_pic = FALSE;
                            if(basename($src) == 'no_pic.gif'){
                              $default_pic = TRUE;
                            }
                            if(!$default_pic && !empty($mapping['icon_url'])){ ?>
                              <i class="icon-remove pull-right removechnres" data-type="icon" data-origin="<?php echo $mapping['rev_icon_url'];?>"></i>
                            <?php }else{ ?>
                              <i class="label pull-right">默认</i>
                            <?php } ?>
                            <?php if(!$default_pic){ ?><a href="<?php echo $src;?>" target="_blank"><?php }?>
                              <span class="thumbnail">
                              <img style="width: 100px; height: 100px;" src="<?php echo $src;?>"/>
                              </span>
                            <?php if(!$default_pic){ ?></a><?php }?>
                        </div>
                        </div>
                        <div class="span6">
                        <div class="col-xs-6 col-md-3">
                            <?php 
                            $src = '';
                            if(empty($mapping['bg_picture'])){
                              $src = $mapping['rev_bg_picture'];
                            }else{
                              $src = $mapping['bg_picture'];
                            }
                            $default_pic = FALSE;
                            if(basename($src) == 'no_pic.gif'){
                              $default_pic = TRUE;
                            }
                            if(!$default_pic && !empty($mapping['bg_picture'])){ ?>
                              <i class="icon-remove pull-right removechnres" data-type="background" data-origin="<?php echo $mapping['rev_bg_picture'];?>"></i>
                            <?php }else{ ?>
                              <i class="label pull-right">默认</i>
                            <?php } ?>
                            <?php if(!$default_pic){ ?><a href="<?php echo $src;?>" target="_blank"><?php } ?>
                              <span class="thumbnail">
                              <img style="width: 100px; height: 100px;" src="<?php echo $src;?>"/>
                              </span>
                            <?php if(!$default_pic){ ?></a><?php } ?>
                        </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <span>背景音乐:</span>
                      <?php 
                      $src = '';
                      if(empty($mapping['bg_music'])){
                        $src = $mapping['rev_bg_music'];
                      }else{
                        $src = $mapping['bg_music'];
                      }
                      if(!empty($mapping['bg_music']) && !empty($mapping['bg_music'])){ ?>
                      <i class="icon-remove pull-right removechnres"  data-type="music" data-origin="<?php echo $mapping['rev_bg_music'];?>"></i>
                      <?php }else{ ?>
                              <i class="label pull-right">默认</i>
                      <?php } ?>
                      <audio controls class="pull-right">
                        <source src="<?php echo $src;?>" />
                        Your browser does not support the audio element.
                      </audio>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <label>游戏描述:</label>
                      <?php
                        $game_desc = empty($mapping['game_desc'])?$mapping['rev_game_desc']:$mapping['game_desc'];
                      ?>
                      <textarea disabled="" style="margin: 0px 0px 10px; width: 500px; height: 60px;" value="<?php echo $game_desc;?>"><?php echo $game_desc;?></textarea>
                      <button class="btn pull-right modify_desc">修改</button>
                      <button class="btn pull-right save_desc" style="display:none">保存</button>
                    </td>
                  </tr>
                  <?php if(!isset($apk_download_url)){ ?>
                  <tr>
                    <td>
                    <label>APK列表:</label>
                    <ul>
                      <li>
                      <?php $tips = FALSE;$recognize = TRUE;?>
                      <?php if(!empty($mapping['apk_download_url'])){?>
                        <p><span class="label label-success">头段包APK</span></p>
                        <p>
                          <a href="<?php echo $mapping['apk_download_url']; ?>"><?php echo basename($mapping['apk_download_url']);?></a>
                          <a href="#"><i class="icon-remove pull-right removechnres" data-type="apk"></i></a>
                        </p>
                        <p>MD5:  <b class="muted"><?php if(empty($mapping['apk_md5'])){$tips = TRUE;}else{echo $mapping['apk_md5'];}?></b></p>
                        <?php }else{ 
                          $tips = TRUE;
                          $recognize = FALSE;
                        ?>
                        <span class="label label-important">请上传头段包APK</span>
                        <?php }?>
                        <?php if(empty($mapping['apk_md5']) && !empty($mapping['apk_download_url'])){?>
                        <p><a href="#"><span class="label label-info recognize" data-filename="<?php echo basename($mapping['apk_download_url']);?>">重新识别</span></a></p>
                        <?php }?>
                      </li>
                      <li>
                      <?php if(!empty($mapping['genuine_apk_download_url'])){?>
                        <p><span class="label label-success">真实APK</span></p>
                        <p>
                          <a href="<?php echo $mapping['genuine_apk_download_url']; ?>"><?php echo basename($mapping['genuine_apk_download_url']);?></a>
                          <a href="#"><i class="icon-remove pull-right removechnres" data-type="genuine_apk"></i></a>
                        </p>
                        <p>MD5:  <b class="muted"><?php if(empty($mapping['genuine_apk_md5'])){$tips = TRUE;}else{echo $mapping['genuine_apk_md5'];}?></b></p>
                        <?php }else{ $tips = TRUE;?>
                        <span class="label label-important">请上传真实APK</span>
                        <?php if($recognize){?>
                        <p><a href="#"><span class="label label-info recognize" data-filename="<?php echo '_'.basename($mapping['apk_download_url']);?>">重新识别</span></a></p>
                        <?php }?>
                        <?php }?>
                      </li>
                      <li>
                      <?php if(!empty($mapping['so_apk_download_url'])){?>
                        <p><span class="label label-success">去除架构APK</span></p>
                        <p>
                          <a href="<?php echo $mapping['so_apk_download_url']; ?>"><?php echo basename($mapping['so_apk_download_url']);?></a>
                          <a href="#"><i class="icon-remove pull-right removechnres" data-type="so_apk"></i></a>
                        </p>
                        <p>MD5:  <b class="muted"><?php if(empty($mapping['so_apk_md5'])){$tips = TRUE;}else{echo $mapping['so_apk_md5'];}?></b></p>
                        <?php }else{ $tips = TRUE;?>
                        <span class="label label-important">请上传去除架构APK</span>
                        <?php if($recognize){?>
                        <p><a href="#"><span class="label label-info recognize" data-filename="<?php echo '_so_'.basename($mapping['apk_download_url']);?>">重新识别</span></a></p>
                        <?php }?>
                        <?php }?>
                      </li>
                      </ul>
                      <?php if($tips && !empty($mapping['apk_download_url'])){ ?>
                        <p class="text-warning">已上传文件但APK列表不显示文件或者MD5值，请点击<em class="text-info">‘重新识别’</em></p>
                      <?php }?>
                    </td>
                  </tr>
                  <?php }?>
                  <?php if(!isset($apk_download_url)){?>
                  <tr>
                  <td>
                    <label>架构支持:</label>
                    <ul>
                      <?php
                        $no_arch = TRUE;
                        foreach ($arches as $value) { 
                          if($value['channel_id'] == $mapping['channel_id']){
                            $no_arch = FALSE;
                        ?>
                        <li>
                          <a href="<?php echo $value['download_url']; ?>"><?php echo basename($value['download_url']); ?></a>
                          <a href="#"><i class="icon-remove pull-right removechnres" data-type="arch" data-gamesoid="<?php echo $value['id']; ?>"></i></a>
                        </li>
                      <?php }}
                      if($no_arch){
                          echo '<li>无</li>';
                      }?>
                    </ul>
                  </td>
                  </tr>
                  <?php }?>
                </tbody>
              </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
          </div>
        </div>
      </div>
    </div>
    <?php }?>
</div>
<script type="text/javascript">
$(document).ready (function(){
  $('#batch_online').click(function(event) {
    var online=new Array();
    $(".onlinecheck:checked").each(function(index, el) {
      if(this.checked){
        online.push(this.value);
      }
    });
    $.post('<?php echo site_url('game_management/experiment/ajax_batch_online');?>',
    {
      chns: online, 
      rev_id: <?php echo $revision_id;?>
    },
      function(result) {
        if(result == 'done')  {
            window.location.reload();
        }else{
            $('.alert').remove();
            $('#msg').html('<div class="alert">操作失败</div>');
        }
      });
    });
    $('#allcheckbox').click(function(event) {
      if(this.checked){
        $(".onlinecheck").prop("checked",true);
      }else{
        $(".onlinecheck").prop("checked",false);
      }
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
    var channel_id = $(this).attr('data-chnid');
    var chnres_id = $(this).attr('data-chnresid');
    var revisionapk_id = $(this).attr('data-revisionapkid');
    $('#userdir').val(data_dir);
    $('#workdir').html(data_dir);
    uploadwidget( data_dir, channel_id, chnres_id,'btnupdate');
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
      $.post("<?php echo site_url('game_management/experiment/ajax_remove_channel');?>",
        { 
          revision_id: <?php echo $revision_id;?>, 
          channel_id: $(this).parent().parent().data('channelid')
        }
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
$('.apkdownloadupdate').click(function () {
        if(confirm('确定更新？'))
        {
            $.post("<?php echo site_url('game_management/experiment/ajax_update_runtime_gameurl');?>"
                ,{ revision_id: <?php echo $revision_id;?>,
                    channel_id: $(this).data('chnid'),
                    revisionapkid: $(this).data('apkid'),
                    gameurl:    $(this).prev().val()
                }
            ,function(result){ 
                if(result == 'done')  {
                    window.location.reload();
                }else if(result == 'manifestnotok')  {
                    $('.alert').remove();
                    $('#msg').html('<div class="alert">更新目录失败</div>');
                } else {
                    $('.alert').remove();
                    $('#msg').html('<div class="alert">操作失败</div>');
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
  $('a.errtips').click(function(event) {
    $(this).next().popover('toggle');
  });
  $('a.filetips').click(function(event) {
    $(this).next().attr('data-content','请自行在‘详细信息’中检查真APK，去除架构文件的APK是否上传');
    $(this).next().popover('toggle');
  });
  $('i.removechnres').click(function(event) {
    var type = $(this).data('type');
    var src = $(this).data('origin');
    var resid = $(this).parents('table').data('resid');
    var apkid = $(this).parents('table').data('apkid');
    var gamesoid = $(this).data('gamesoid');
    var _this = $(this);
    $.post('<?php echo site_url('game_management/experiment/ajax_delete_chn_res');?>',
      {
        type:type,
        resid:resid,
        apkid:apkid,
        gamesoid:gamesoid
      },
      function(data) {
        console.log(data);
        if(data == 'done'){
          window.location.reload();
          // _this.next().prop('src', src);
          // _this.next().children().children().prop('src', src);
          // _this.removeClass('removechnres');
          // _this.removeClass('icon-remove');
          // _this.addClass('label');
          // _this.html('默认');
        }
      });
  });
  $('.mytooltip').tooltip('hide');
  $('a.del_apk').click(function(event) {
    $.post('<?php echo site_url('game_management/experiment/ajax_del_apk_related_file');?>', 
      {
        apk_id:$(this).parent().parent().data('apkid'),
        apk_download_url:$(this).parent().parent().data('apkdownloadurl')
      },
      function(data) {
        if(data == 'done'){
          window.location.reload(); 
        }else{
          $('.alert').remove();
          $('#msg').html('<div class="alert">删除失败,请在‘上传资源’手动删除文件并替换APK</div>');
        }
      });
  });
  $("input[name='channel_id']").bind('input propertychange', function() {
    var preg = /^\d{1,10}$/;
    if(preg.test($(this).val())){
      $('#newchnmapping').removeAttr('disabled');
    }else{
      $('#newchnmapping').attr('disabled',true);
    }
  });
  $("button.modify_desc").click(function(event) {
    $(this).toggle();
    $(this).prev().removeAttr('disabled');
    $(this).next().toggle();
  });
  $('button.save_desc').click(function(event) {
    var _this = $(this);
    $.post('<?php echo site_url('game_management/experiment/modify_chn_gamedesc');?>',
     {
        resid:$(this).parents('table').data('resid'),
        text:$(this).prev().prev().val()
     },
      function(data) {
        if(data == 'done'){
          _this.toggle();
          _this.prev().toggle();
          _this.prev().prev().prop('disabled', true);
        }else{

        }
    });
  });
  $('span.recognize').click(function(event) {
    var resid = $(this).parents('table').data('resid');
    var channelid = $(this).parents('table').data('channelid');
    var file_name = $(this).data('filename');
    $.post('<?php echo site_url('game_management/experiment/ajax_update_chn_res');?>',
     {
        revision_id: <?php echo $revision_id;?>,
        channel_id:channelid,
        chnres_id:resid,
        file_name:file_name
     },
      function(data) {
      if(data == 'done')  {
          window.location.reload();
      }else {
          $('.alert').remove();
          $('.modal').modal('hide');
          $('#msg').html('<div class="alert">'+data+'</div>');
      }
    });
  });
});
</script>