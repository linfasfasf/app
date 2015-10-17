<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<div class="container-fluid">
	    <div class="row-fluid">
	        <ul class="breadcrumb">
	            <li>运营管理 <span class="divider">/</span></li>
	            <li class="active">游戏列表</li>
	        </ul>
	    </div>
            <?php if(!empty($msg)) {?>
                <div>
                   <div class = "alert">
                        <p><?php echo $msg?></p>
                   </div>
               </div>
            <?php }?>
        <div class='row-fluid warning'>
            <?php flash_message();?>
        </div>

	    <div class='row-fluid'>
                <form id="search_form" class="form-horizontal" action="<?php echo site_url('game_management/gamelist');?>" method="post">
            	<div class='row-fluid pull-left'>
                    <label class="control-label">ID/Gamekey/游戏名称：</label>
                    <input type="text" class="" placeholder="" name="game" value="<?php echo $search['game'];?>">
	            </div>
                <div class='row-fluid pull-left'>
                    <label class="control-label">提供商：</label>
                    <select name="cp_vendor">
                        <option value='-1'>全部</option>
                        <?php foreach ($searchcp as $key => $value) {
                            $cp = $value['cp_vendor'];
                            $select = '';
                            if($cp === $search['cp_vendor'])
                            {
                                $select = "selected = 'selected'";
                            }
                            echo "<option value='{$cp}' ".$select.">{$cp}</option>";
                        }?>
                    </select>
	            </div>
	            <div class='row-fluid pull-left'>
                    <label class="control-label">游戏模式：</label>
                    <label class="label"><input type="checkbox" class="checkbox" name="cocosplay" value="1" <?php echo $search['cocosplay'] == 1?'checked="checked"':'';?>> Play 托管包 </label>
                    <label class="label"><input type="checkbox" class="checkbox" name="runtime" value="7" <?php echo $search['runtime'] == 7?'checked="checked"':'';?>> Runtime 游戏</label>
                    </div>
                    <div class='row-fluid pull-left'>
                    <label class="control-label">更新时间范围：</label>
                    <input type="text" name="range_time" id="date" class="form-control col-sm-5 datainput" autocomplete="off"/>
                    <input class="btn" id="search" type="submit" value="搜索">
                    <a class="btn" href="<?php echo site_url('game_management/addgame'); ?>" target="_top">创建游戏</a>
                    <a class="btn" href="<?php echo site_url('game_management/experiment/upload_resource'); ?>" target="_top">上传资源</a>
	            </div>
            </form>
            <table class='table table-bordered table-hover table-striped'>
                <thead>
                <tr>
                    <th>序号</th><th>游戏ID</th><th>Gamekey</th><th style="width:10%">游戏名称</th><th>星级</th><th>游戏类型</th><th>游戏模式</th>
                    <th>线上版本</th><th>线上版本编号</th><th>引擎版本</th><th>游戏SDK版本</th>
                    <th>屏幕方向</th><th>用户系统</th><th>支付系统</th><th>提供商</th><th>是否进行维护</th><th>上线渠道</th><th>入库时间</th>
                    <th>更新时间</th><th>操作</th>
                </tr>
                </thead>
                <tbody>
            <?php if (isset($arr_list) && $arr_list) {
            	$i = ($page-1)*$per_page+1; 
        	foreach ($arr_list as $key => $value): ?>
                <tr class="<?php if($new_game_id==$value['game_id']) echo 'success';?>">

                    <td><?php echo $i ?></td>
                    <td><?php echo $value['game_id'] ?></td>
                    <td><?php echo $value['game_key'] ?></td>
                    <td><?php echo $value['game_name'] ?></td>
                    <td><?php echo $value['star'] ?></td>
                    <td><?php echo $value['game_type'] ?></td>
                    <td><?php if($value['game_mode']==0||$value['game_mode']==2){echo "试玩包";} elseif($value['game_mode']==4) {echo "独立包";} elseif($value['game_mode']==7){echo "Runtime游戏";}else {echo "Play托管包";}?></td>
                    <td><?php echo $value['package_ver'] ?></td>
                    <td><?php echo $value['package_ver_code'] ?></td>
                    <td><?php if($value['engine_version']){$ev = 'Cocos2d-x '. $value['engine_version'];}else{$ev='';} echo $ev;?></td>
                    <td><?php if($value['sdk_version']){$ev = 'V'. $value['sdk_version'];}else{$ev='';} echo $ev;?></td>

                    <td><?php  if($value['orientation']!==''){if($value['orientation']) {echo "竖屏";}else  {echo "横屏";}}?></td>
                    <td><?php echo $value['user_system'] ?></td>
                    <td><?php echo $value['payment'] ?></td>
                    <td><?php echo $value['cp_vendor'] ?></td>
                    <td><?php if($value['is_maintain']==0){echo "正常";} else {echo "维护中";} ?></td>
                    <td>
                        <?php if($value['channelnum'] == 0){ 
                                echo "0";
                            } else {
                        ?>
                        <a href="<?php echo site_url('game_management/onlinechannel/'.$value['game_id']); ?>" target="_top"><?php echo $value['channelnum'];?></a>
                        <?php }?>
                    </td>
                    <td><?php echo strftime("%Y-%m-%d %H:%M:%S", $value['create_time']) ?></td>
                    <td><?php echo strftime("%Y-%m-%d %H:%M:%S", $value['modify_time']) ?></td>
                    <td>
                        <a href="<?php echo site_url("game_management/viewgame/{$value['game_id']}"); ?>" target="_top">查看</a>&nbsp;&nbsp;
                        <a href="<?php echo site_url("game_management/editgame/{$value['game_id']}"); ?>" target="_top">编辑</a>&nbsp;&nbsp;
<!--
<?php if($value['revision_id']) { ?>
                        <a href="<?php echo site_url("game_management/view_revision/{$value['revision_id']}"); ?>" target="_top">查看线上版本</a>&nbsp;&nbsp;
<?php }else{ ?>
                        查看线上版本&nbsp;&nbsp;
<?php }?>
    -->
                        <?php if($this->acl_model->accessible('/game_management/game','delete')):?>
                        <a href="<?php echo site_url('game_management/delete_game/'.$value['game_id']);?>" onclick="return confirm('确定删除吗？')">删除</a>&nbsp;&nbsp;
                        <?php endif;?>
                    </td>
                </tr>
            <?php $i++; endforeach ?>
            <?php } ?>
                </tbody>
            </table>
            <div class="row-fluid">
            	<?php 
                    $search_string = '';
                    foreach ($search as $key => $value) {
                        if(!empty($value)){
                            $search_string .= $key.'='.$value.'&';
                        }
                    }
                    $ap = $total/$per_page;
            		if($page <= 1) {
                         $prepage = "上一页";
                     } else {
                        $p = $page-1;
                        $preurl = site_url("game_management/gamelist/{$p}?$search_string");
                        $prepage = "<a href=\"{$preurl}\" target='_top'>上一页</a>";
                    }
            		if($page >= $ap) {
                        $lastpage = "下一页";
                    } else {
                        $p = $page+1;
                        $lasturl = site_url("game_management/gamelist/{$p}?$search_string");
                        $lastpage = "<a href=\"{$lasturl}\" target='_top'>下一页</a>";
                    }
                ?>
                <div class="pull-right pageOther">
                    共计<?php echo $total ?>条记录&nbsp;
                    <?php echo $per_page ?>条/页 &nbsp;
                    <?php echo $prepage ?>&nbsp;
                    当前第<?php echo $page ?>页/共<?php echo ceil($ap);?>页&nbsp;
                    <?php echo $lastpage ?>
                </div>
            </div>
	<script type="text/javascript" src="<?php echo site_url('asset/js/dateRange.js');?>"></script>
	<script type="text/javascript">
      <?php
            $range_time = $search['range_time'];
            if($range_time != FALSE){
                $start_time = substr($range_time,0,10);
                $end_time = substr($range_time,-10);
            }
      ?>      
	  var dateObj = {
	        inputId: 'date',
	        target: 'datePicker',
	        startDate: '<?php echo $start_time?$start_time:'';?>',
	        endDate: '<?php echo $end_time?$end_time:'';?>',
			startDateId:'startDate',
	        needCompare: 0,
	        defaultText: '' || ' 至 ',
	        singleCompare: '',
	        isTodayValid: '1',
	        validStartTime: '1304611200'
	    };

	    new pickerDateRange(dateObj.inputId, {
	        theme: 'ta', // 日期选择器TA主题
	        autoCommit: true, //自动提交，完成日期初始化，以及图表的展示拉取
	        isTodayValid: dateObj.isTodayValid,
	        startDate: dateObj.startDate,
	        endDate: dateObj.endDate,
	        needCompare: dateObj.needCompare,
	        startCompareDate: dateObj.startCompareDate,
	        endCompareDate: dateObj.endCompareDate,
	        singleCompare: dateObj.singleCompare,
	        defaultText: dateObj.defaultText,
	        autoSubmit: dateObj.autoSubmit || false,
	        shortOpr: dateObj.shortOpr || false,
	        target: dateObj.target,
	        calendars: dateObj.calendars || 2,
	        inputTrigger: dateObj.inputTrigger || 'input_trigger',
	        validStartTime: dateObj.validStartTime,
	        minValidDate: dateObj.minValidDate,
	        isSingleDay: dateObj.autoSubmit || false,
	        success: function (obj) {
	        }
	    });
	  $('#search_form').submit(function(){
             var date = $('#date').val();
             if(date == '')
             {
                 return true;
             }
             var d1 = date.substr(0,10);
             var d2 = date.substr(13,22);
             reg=/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/; 
            if(date[11] != '至'|| !d1.match(reg) || !d2.match(reg)){
                alert('日期不合法'); 
                return false;
            } 
             return true;
          });
	</script>
