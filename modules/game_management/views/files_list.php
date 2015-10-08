<fieldset>
    <legend>文件列表</legend>
    <button class="btn uploadfile" type="button" data-dir="<?php echo $revision_tmp?>" data-toggle="modal" data-target="#uploadModal">上传文件</button>
    <span>上传文件到通用资源目录，若存在同名资源，则替换</span>
        <table class="table table-condensed table-hover">
            <thead>
            <th>序号</th>
            <th>文件名</th>
            <th>md5</th>
            <th>文件大小</th>
            <th>操作</th>
            </thead>
            <tbody>
                <?php $i = 1; ?>
                <?php foreach ($files_list as $key => $value) { ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><a href="<?php echo $value['download_url']; ?>" target="_blank"><?php echo $key; ?></a></td>
                        <td><?php echo $value['md5']; ?></td>
                        <td><?php echo $value['size']; ?></td>
                        <td></td>
                    </tr>
                <?php }?>
            </tbody>
        </table>
</fieldset>
<script>
    $('.uploadfile').click(
                function () {
                     var data_dir = $(this).attr('data-dir');
                    $('#userdir').val(data_dir);
                    $('#workdir').html(data_dir);
                    uploadwidget( data_dir, <?php echo $game_revision['id']?>, '','bthfile');
                }
            );
</script>
