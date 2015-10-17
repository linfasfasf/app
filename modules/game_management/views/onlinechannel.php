<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link href="<?php echo site_url('asset/css/bootstrap.css');?>" rel="stylesheet">
	<link href="<?php echo site_url('asset/css/styles.css');?>" rel="stylesheet">
	<link href="<?php echo site_url('asset/css/dateRange.css');?>" rel="stylesheet">
	<script src="<?php echo site_url('asset/js/jquery.js');?>"></script>
</head>
<body>
	<div class="container-fluid">
	    <div class="row-fluid">
	        <ul class="breadcrumb">
	            <li>运营管理 <span class="divider">/</span></li>
	            <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
	            <li class="active">上线渠道</li>
	        </ul>
	    </div>
	    <div class="alert">
            <strong>当前游戏：</strong>&nbsp;&nbsp;<?php echo $gamename ?>
        </div>
	    <div class='row-fluid'>
            <table class='table table-bordered table-hover table-striped'>
                <thead>
                <tr>
                    <th>渠道ID</th><th>渠道名称</th><th>上线时间</th>
                </tr>
                </thead>
                <tbody>
            <?php if (isset($arr_list) && $arr_list) {
            	$i = 1; 
        	foreach ($arr_list as $key => $value): ?>
                <tr>
                    <td><?php echo $value['channel_id'] ?></td>
                    <td><?php echo $value['channel_name'] ?></td>
                    <td><?php echo strftime("%Y-%m-%d %H:%M:%S", $value['modify_time']) ?></td>
                </tr>
            <?php $i++; endforeach ?>
            <?php } ?>
                </tbody>
            </table>
	    </div>
	</div>
</body>
</html>