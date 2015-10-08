	    <div class="row-fluid">
	        <ul class="breadcrumb">
	            <li>系统设置 <span class="divider">/</span></li>
	            <li class="active">兼容列表</li>
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
                    <ul class="nav nav-tabs">
                        <li role="presentation" class="active"><a href="<?php echo site_url('game_management/manage_compatibility');?>">Cocos play</a></li>
                        <li role="presentation"><a href="<?php echo site_url('game_management/manage_compatibility/runtime');?>">Cocos Runtime</a></li>
                    </ul>
                    <form  id="a" method="post" action="manage_compatibility">
            <table class='table table-bordered table-hover table-striped'>
                <thead>
                <tr>
                    <th>ID</th><th>渠道SDK版本</th><th>游戏SDK版本</th><th>Active</th><th>操作</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                
                    <td></td>
                    <td><input id="chn_sdk" class="ver" autocomplete="off" placeholder="1.0.0.0" type="text" name="channel_sdk_version" /></td>
                    <td><input id="game_sdk" class="ver" type="text" autocomplete="off" name="game_sdk_version" /></td>
                    <td><input type="hidden" name="is_active" value='0'></input><select name="disabled_is_active" disabled >
                        <option value="0" selected>active</option>
                        <option value="1">inactive</option>
                        </select></td>
                        <td><input id="add" class="btn btn-primary" type="submit" disabled="true" value = "添加" /></td>
                    </tr>
<?php foreach($data as $item) { ?>
<tr>
<td><?php echo $item['id'];?></td>
<td><?php echo $item['app_version'];?></td>
<td><?php echo $item['sdk_version'];?></td>
<td><?php if ($item['del_flag']=='0') echo 'active' ; else echo 'inactive';?></td>
<td><a  class="delete btn" href="game_management/del_manage_compatibility/<?php echo $item['id'];?>">删除</a></td>
</tr>
<?php } ?>
                </tbody>
            </table>
            </form>
	    </div>
<script>
    $('document').ready(function(){
        $('.ver').on('keyup', function(event) {
            var patt = /^[1-9][0-9]{0,1}(\.[1-9][0-9]{0,1}|\.[0-9]){3}$/;
            var num = /^\d$/;
            if(patt.test($('#chn_sdk').val()) && num.test($('#game_sdk').val()))
            {
                $('#add').prop('disabled', false);
            }
            else
            {
                $('#add').prop('disabled', true);
            }
        });
        $('a.delete').click(function(){
            return confirm('确定删除？');
        });
    });
</script>
