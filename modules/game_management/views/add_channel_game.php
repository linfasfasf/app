<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link href="<?php echo site_url('asset/css/bootstrap.css');?>" rel="stylesheet">
	<link href="<?php echo site_url('asset/css/styles.css');?>" rel="stylesheet">
	<link href="<?php echo site_url('asset/css/dateRange.css');?>" rel="stylesheet">
	<script src="<?php echo site_url('asset/js/jquery.js');?>"></script>
	<style type="text/css">
		label{width: 100px; text-align: right;}
		.input_s{ width: 70px; margin-right: 5px;}
		a.del{ color: red; margin-left: 10px; text-decoration: none;}
		.mb5{ margin-bottom: 5px;}
	</style>
</head>
<body>
	<div class="container-fluid">
	    <div class="row-fluid">
	        <ul class="breadcrumb">
	            <li>运营管理 <span class="divider">/</span></li>
	            <li><a href="<?php echo site_url('game_management/channel_list/1'); ?>">渠道列表</a> <span class="divider">/</span></li>
	            <li class="active">添加游戏</li>
	        </ul>
	    </div>
            <?php flash_message();?>
	    <div class='row-fluid'>
	        <form class="form-horizontal" action="<?php echo site_url('game_management/doAddChannelGame'); ?>" method="post">
	            <div class="control-group"><input type="hidden" name="channel_id" value="<?php echo $channel_id; ?>">
	                <label class="control-label"><span class="red">*</span>添加游戏：</label>
	                <div class="controls">
                            <?php $none = FALSE; if(empty($gamelist)) $none = TRUE?>
                            <select class="wh500" name="gamesel" onchange="selectgame()" <?php echo $none?"disabled='disabled'":''?>>
	                    <?php echo $none?"<option>当前无游戏可添加</option>":''?>
                            <?php foreach ($gamelist as $key => $item) { ?>
	                    	<option value="<?php echo $item['game_id'];?>"><?php echo str_replace('#','&nbsp;',sprintf("[%s]&nbsp;&nbsp;", $item['game_id'])) .  $item['game_name'] ;?></option>
	                    <?php }?>
	                    </select>
	                    <div class="btn-group">
                                <input class="btn btn-primary" type="button" id="addgame" value="添加" <?php echo $none?"disabled='disabled'":""?>/>
                        </div>
                       </div>
	                <div id="gameName" class="control-group"></div>
	            </div>
	            <div class="control-group" id="coopMethod">
	                <label class="control-label"><span class="red">*</span>合作类型：</label>
	                <div class="controls" id="coopitemold">
	                    <input class="input_txt" type="text" value="" disabled />
	                    合作方式：<select class="wh500">
	                        <option value="cpa">CPA</option>
	                        <option value="cps">CPS</option>
	                    </select>
	                </div>
	            </div>
	            <div class="control-group" id="isVisiable">
	                <label class="control-label"><span class="red">*</span>是否可见：</label>
	                <div class="controls" id="visiableitem">
	                   <input class="input_txt" type="text" value="" disabled />
	                    设置：<select class="wh500">
	                        <option value="0">不可见</option>
	                        <option value="1">可见</option>
	                    </select>
	                </div>
	            </div>
	            <!-- <div class="control-group" id="star">
	                <label class="control-label"><span class="red">*</span>星级：</label>
	                <div class="controls" id="staritem">
	                    <input class="input_txt" type="text" value="" disabled />
	                    设置：<select class="wh500">
	                        <option value="1">1</option>
	                        <option value="2">2</option>
	                        <option value="3">3</option>
	                        <option value="4">4</option>
	                        <option value="5">5</option>
	                    </select>
	                </div>
	            </div> -->
	            <div class="control-group">
	                <label class="control-label"></label>
	                <div class="controls">
	                    <input type="submit" class="btn btn-primary" value="创建" />
	                    <input type="reset" class="btn" value="重置" />
	                </div>
	            </div>
	        </form>
	    </div>
	</div>
	<script type="text/javascript">
		function selectgame(){
			var gamesel = document.getElementsByName('gamesel')[0];
			var gameid = $(gamesel).val();
			var gamename = gamesel.options[gamesel.selectedIndex].text;
			$('#addgame').attr('gid', gameid);
			$('#addgame').attr('gname', gamename);
		}
		selectgame();
		var order = 0;
		var gameidAr = new Array();
		$('#addgame').click(function(){
			
			var gname = $(this).attr('gname');
			var gid = $(this).attr('gid');
			for(var i = 0; i < gameidAr.length; i++){
				if(gid == gameidAr[i]){
					alert('已添加！');
					return false;
				}
			}
			order++;
			gameidAr.push(gid);
			var _gamehtml = '<div class="controls" style="height:37px"><br><input class="input_txt" type="text" value="'+$(this).attr('gname')+'" disabled /><a href="javascript:void(0)" class="del" onclick="deletgm(this)"><font size="4">X</font></a><input type="hidden" value="'+gid+'" name="gid[]" /></div>';
			$('#gameName').append(_gamehtml);
			//合作方式
			$('#coopitemold').hide();
			var _coophtml = '<div class="controls mb5" gid="'+gid+'"><input class="input_txt" type="text" value="'+gname+'" disabled />合作方式：<select class="wh500" name="coopmethod[]"><option value="cpa">CPA</option><option value="cps">CPS</option></select></div>';
			$('#coopMethod').append(_coophtml);
			//是否可见
			$('#visiableitem').hide();
			var _visiablehtml = '<div class="controls mb5" gid="'+gid+'"><input class="input_txt" type="text" value="'+gname+'" disabled />设置：<select class="wh500" name="visiable[]"><option value="0">不可见</option><option value="1">可见</option></select></div>';
			$('#isVisiable').append(_visiablehtml);
			/*//星级
			$('#staritem').hide();
			var _starhtml = '<div class="controls mb5" gid="'+gid+'"><input class="input_txt" type="text" value="'+gname+'" disabled />设置：<select class="wh500" name="star[]"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select></div>';
			$('#star').append(_starhtml);*/
		});
		function deletgm(thi){
			//debugger;
			var gid = $(thi).next().val();
			//
			for(var i = 0; i < gameidAr.length; i++){
				if(gid == gameidAr[i]){
					gameidAr.pop(gid);
				}
			}
			if(order == 1){
				$('#coopitemold').show();
				$('#visiableitem').show();
				//$('#staritem').show();
			}
			$(thi).parent().remove();
			$('#coopMethod > div[gid='+gid+']').remove();
			$('#isVisiable > div[gid='+gid+']').remove();
			//$('#star > div[gid='+gid+']').remove();
			order--;
		}
	</script>
</body>
</html>
