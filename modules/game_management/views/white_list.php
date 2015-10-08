<div class="row-fluid">
    <ul class="breadcrumb">
        <li>系统设置 <span class="divider">/</span></li>
        <li class="active">设置白名单<span class="divider">/</span></li>
    </ul>
</div>
<?php echo flash_message();?>
<div id="msg">
</div>
<fieldset>
    <legend>白名单</legend>
    <form action="<?php echo site_url('game_management/white_list/action_list')?>" class="form-horizontal" method="get">
        <input type="hidden" value="add" name="action">
        <input class="input_txt" placeholder="请输入宿主包名" name="packagename">
        <button class="btn" type="submit">添加</button>
    </form>
        <table class="table table-condensed table-hover">
            <thead>
            <th>序号</th>
            <th>宿主包名</th>
            <th>操作</th>
            </thead>
            <tbody>
                <?php $i = 1;?>
                <?php foreach ($package_list as $key => $value) { ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $value['packagename']; ?></td>
                        <td>
                            <a class="btn btn-danger delete" href="<?php echo site_url('game_management/white_list/action_list?action=delete&package_id='.$value['id']).'&packagename='.$value['packagename'];?>">删除</a>
                        </td>
                    </tr>
                <?php }?>
            </tbody>
        </table>
</fieldset>
<script>
    $(document).ready (function(){
        $('a.delete').click(function(){
            return confirm('确定删除?');
        });
    });
</script>    
