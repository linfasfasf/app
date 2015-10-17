<div class="row-fluid">
    <ul class="breadcrumb">
        <li>系统设置 <span class="divider">/</span></li>
        <li class="active">兼容列表</li>
    </ul>
</div>
<div class='row-fluid'>
    <div>
        <?php flash_message(); ?>
        <?php if (!empty($msg)) { ?>
            <div>
                <div class = "alert">
                    <p><?php echo $msg ?></p>
                </div>
            </div>
        </div>
    <?php } ?>
    <ul class="nav nav-tabs">
        <li role="presentation"><a href="<?php echo site_url('game_management/manage_compatibility'); ?>">Cocos play</a></li>
        <li role="presentation" class="active"><a href="<?php echo site_url('game_management/manage_compatibility/runtime'); ?>">Cocos Runtime</a></li>
    </ul>
        <?php
        echo form_open('game_management/manage_compatibility/add',array('class'=>"form-inline"));
        ?>
        <?php
        echo form_label('Runtime Version:&nbsp;&nbsp;',array('for'=>'sel'));
        echo form_dropdown('runtime_ver', $version, NULL, 'id="sel"').'&nbsp;&nbsp;&nbsp;';
        echo form_label('Real SDK Version:&nbsp;&nbsp;');
        echo form_input(array('autocomplete' => "off", 'id' => 'newver', 'placeholder' => '1.0.0.0', 'type' => 'text', 'name' => 'real_sdk_ver'));
        echo form_submit(array('class' => 'btn btn-primary', 'disabled' => '', 'id' => 'btnok'), '确定');  
        ?>
        <?php
        echo form_close();
        ?>
    <table class='table table-bordered table-hover table-striped'>
        <thead>
            <tr>
                <th>序号</th><th>Engine</th><th>Runtime Core Version</th><th>Real SDK Version</th><th>Active</th><th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $n = 1;
            foreach ($data as $item) {
                ?>
                <tr>
                    <td id="<?php echo $item['id']; ?>"><?php echo $n++; ?></td>
                    <td><?php echo $item['engine']; ?></td>
                    <td><?php echo $item['version']; ?></td>
                    <td><?php echo $item['real_sdk_ver']; ?></td>
                    <td>active</td>
                    <td><a  class="delete btn btn-danger" href="<?php echo site_url("game_management/del_manage_compatibility/{$item['id']}/runtime"); ?>">删除</a></td>
                </tr>
<?php } ?>
        </tbody>
    </table>
</div>
<script>
    $('document').ready(function () {
        $('a.delete').click(function () {
            return confirm('确定删除？');
        });
        $('#newver').on('keyup', function (e) {
            checkval();
        });
        $('#sel').change(function () {
            checkval();
        });
        function checkval() {
            var patt = /^[1-9][0-9]{0,1}(\.[1-9][0-9]{0,1}|\.[0-9]){3}$/;
            if (patt.test($('#newver').val()) && $('#sel').val() != 0) {
                $('#btnok').prop('disabled', false);
            } else {
                $('#btnok').prop('disabled', true);
            }
        }
    });
</script>
