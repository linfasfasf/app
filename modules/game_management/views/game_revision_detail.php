<div class="row-fluid">
    <ul class="breadcrumb">
        <li>运营管理 <span class="divider">/</span></li>
        <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
        <li><a href="<?php echo site_url('game_management/viewgame/' . $game_revision['game_id']); ?>">查看游戏</a> <span class="divider">/</span></li>
        <li class="active">详细信息<span class="divider">/</span></li>
    </ul>
</div>
<?php echo flash_message();?>
<div id="msg">
</div>
<div id="tab-container" class="tab-container">
  <ul class='etabs'>
    <li class='tab'><a href="#revision_infomation">详细信息</a></li>
    <?php if($game_revision['game_mode'] != 4)
    {
        echo '<li class="tab"><a href="#setup_channel">渠道资源</a></li>';
    }?>
    <li class='tab'><a href="#chafen_resources">差分资源</a></li>
    <li class='tab'><a href="#files_list">文件列表</a></li>
  </ul>
  <div id="revision_infomation">
      <?php include 'game_revision_form.php';?>
  </div>
    <?php if($game_revision['game_mode'] != 4)
    {?>
        <div id="setup_channel">
        <?php include('setup_channel.php');?>
        </div>
    <?php } ?>
  <div id="chafen_resources">
    <?php include('chafen_list.php');?>
  </div>
  <div id="files_list">
    <?php include("files_list.php");?>
  </div>
  <div id="more">
  </div>
</div>
<script src="/asset/js/jquery.easytabs.js" type="text/javascript"></script>
<script>
    $('#tab-container').easytabs();
</script>
<style>
    .etabs { margin: 0; padding: 0; }
.tab { display: inline-block; zoom:1; *display:inline; background: #eee; border: solid 1px #999; border-bottom: none; -moz-border-radius: 4px 4px 0 0; -webkit-border-radius: 4px 4px 0 0; }
.tab a { font-size: 14px; line-height: 2em; display: block; padding: 0 10px; outline: none; }
.tab a:hover { text-decoration: underline; }
.tab.active { background: #fff; padding-top: 6px; position: relative; top: 1px; border-color: #666; }
.tab a.active { font-weight: bold; }
.tab-container .panel-container { background: #fff; border: solid #666 1px; padding: 10px; -moz-border-radius: 0 4px 4px 4px; -webkit-border-radius: 0 4px 4px 4px; }
</style>
