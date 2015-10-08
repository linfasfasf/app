	    <div class="row-fluid">
	        <ul class="breadcrumb">
	            <li>主菜单 <span class="divider">/</span></li>
                    <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
	            <li><a href="<?php echo site_url('game_management/viewgame/'.$game_id); ?>">查看游戏</a> <span class="divider">/</span></li>
	            <li><a href="<?php echo site_url('game_management/experiment/setup_channel/'.$revision_id); ?>">设置渠道</a> <span class="divider">/</span></li>
	            <li class="active">配置信息</li>
	        </ul>
	    </div>
	    <div class='row-fluid'>
                <div>
                    <?php flash_message();?>
            <?php if(!empty($msg)) {?>
                <div>
                   <div class = "alert">
                        <p><?php echo $msg?></p>
                   </div>
               </div>
            <?php }?>
            <form  id="a" method="post" enctype="multipart/form-data" action="<?php echo site_url('game_management/experiment/edit_channel_config/' . $apk_id);?>">
            <table class='table table-bordered table-hover table-striped'>
                <thead>
                <tr>
                    <th>ID</th><th>游戏名称</th><th>渠道名称</th><th>配置文件</th><th>版本</th><th>创建时间</th><th>操作</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                
                    <td></td>
                    <td><?php echo $game_name;?></td>
                    <td><?php echo $channel_name;?></td>
                    <td>
<span style="position:relative;">
        <span class='btn'>
            选择配置文件
            <input type="file" style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;' name="chnconfigfile" size="40"  onchange='$("#upload-file-info").html($(this).val());'>
        </span>
        &nbsp;
        <span class='label label-info' id="upload-file-info"></span>
</span>
</td>
                    <td><input id="channel_config_version" class="ver" autocomplete="off" placeholder="1" type="text" name="channel_config_version" /></td>
                    <td></td>
                    <td><input id="workfolder" type="hidden" name="workfolder" value="" /><input id="add" class="btn btn-primary" type="submit" value = "添加" /></td>
                    </tr>
<?php foreach($configs as $item) { ?>
<tr>
<td><?php echo $item['id'];?></td>
<td><?php echo $game_name;?></td>
<td><?php echo $channel_name;?></td>
<td><a target="_blank" href="<?php echo $item['url'];?>"><?php echo basename($item['url']);?></a></td>
<td><?php echo $item['ver'];?></td>
<td><?php echo $item['modify_time'];?></td>
<td><a  class="delete btn btn-danger" href="<?php echo site_url('game_management/experiment/delete_channel_config/' . $item['id']);?>">删除</a></td>
</tr>
<?php } ?>
                </tbody>
            </table>
            </form>
	    </div>
<script>
    $('document').ready(function(){
        $('.ver').on('keyup', function(event) {
        });
        $('a.delete').click(function(){
            return confirm('确定删除？');
        });
        $('#add').click(function(){
            var ver = $('#channel_config_version').val();
            if(ver == '') {
                if($('.alert').length) {
                    $('.alert').html('<p>请指定版本号和配置文件! </p>');
                }else{
                    $('form').prepend('<div class="alert"><p>请指定版本号! </p></div>');
                }
                return false;
            }
            else{
                return true;
            }
        });
    });
</script>
