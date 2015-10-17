<div class="row-fluid">
    <ul class="breadcrumb">
        <li>运营管理 <span class="divider">/</span></li>
        <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
        <li><a href="<?php echo site_url('game_management/viewgame/' . $game_id); ?>">查看游戏</a> <span class="divider">/</span></li>
        <li class="active">设置渠道</li>
    </ul>
</div>
<?php flash_message(); ?>
<?php
$error_msg = validation_errors();
if (isset($error_msg) && !empty($error_msg)) {
    echo <<< EOF
    <div id="msg" class="alert"><p>{$error_msg}</p>
    </div>
EOF;
} else {
    ?>
    <div id="msg">
    </div>
<?php }?>
<fieldset>
    <legend>差分资源列表</legend>
<!--    <button class="btn uploadchafen" type="button" data-dir="" data-toggle="modal" data-target="#uploadModal">上传差分资源</button>-->
        <table class="table table-condensed table-hover">
            <thead>
            <th>序号</th>
            <th>支持小版本</th>
            <th>大版本号</th>
            <th>文件名</th>
            <th>md5</th>
            <th>文件大小</th>
            <th>操作</th>
            </thead>
            <tbody>
                <?php $i = 1; ?>
                <?php foreach ($chafen_list as $key => $value) { ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $value['support_ver_code']; ?></td>
                        <td><?php echo $value['support_package_ver_code']; ?></td>
                        <td><?php if(!empty($value['file_name'])){?>
                            <a href="<?php echo $value['download_url']; ?>" target="_blank"><?php echo $value['file_name']; ?></a>
                        <?php }else{?>
                            <span>未设置</span>
                        <?php }?>
                        </td>
                        <td><?php echo $value['md5']; ?></td>
                        <td><?php echo $value['size']; ?></td>
                        <td>
                                <?php if (!empty($value['file_name'])) { ?>
                            <button class="btn uploadchafen btn-warning" support_ver="<?php echo $value['support_ver_code'];?>" type="button" btnaction="bthchafen" data-dir="<?php echo $revision_tmp?>" data-toggle="modal" data-target="#uploadModal">替换CPK</button>
                                <?php } else { ?>
                                    <button class="btn uploadchafen" support_ver="<?php echo $value['support_ver_code'];?>" btnaction="bthchafen" type="button" data-dir="<?php echo $revision_tmp?>" data-toggle="modal" data-target="#uploadModal">上传CPK</button>
                                <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
</fieldset>
<script>
    $('.uploadchafen').click(
                function () {
                    var data_dir = $(this).attr('data-dir');
                    $('#userdir').val(data_dir);
                    $('#workdir').html(data_dir);
                    var action = $(this).attr('btnaction');
                    var support_ver = $(this).attr('support_ver');
                    uploadwidget( data_dir, <?php echo $game_revision['id']?>, support_ver,action);
                }
            );
</script>
