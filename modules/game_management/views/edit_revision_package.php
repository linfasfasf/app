<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link href="<?php echo site_url('asset/css/bootstrap.css');?>" rel="stylesheet">
	<link href="<?php echo site_url('asset/css/styles.css');?>" rel="stylesheet">
	<link href="<?php echo site_url('asset/css/dateRange.css');?>" rel="stylesheet">
	<script src="<?php echo site_url('asset/js/jquery.js');?>"></script>
	<script src="<?php echo site_url('asset/js/jquery.dataTables.min.js');?>"></script>
	<style type="text/css">
		label{width: 100px; text-align: right;}
	</style>
</head>
<body>
	<div class="container-fluid">
	    <div class="row-fluid">
	        <ul class="breadcrumb">
	            <li>运营管理 <span class="divider">/</span></li>
	            <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
	            <li class="active">管理文件</li>
	        </ul>
	    </div>
		<?php flash_message();?>
	    <div class='row-fluid'>
<h3>FILEDIR: <?php echo $filedir ; ?></h3>
	    </div>
	    <div class='row-fluid'>
        <h4>载入新 revision 到当前目录</h4>
    <form method="POST" action="<?php echo site_url('file_management/manage_files') ;?>">
    <input type="hidden" name="filedir" value="<?php echo $filedir ;?>"></input>
        <input name="game_search_game_key" id="game_search_game_key" type="text" placeholder="输入 gamekey"></input>
        <input name="game_search_ver_code" id="game_search_ver_code" type="text" placeholder="输入版本编号"></input>
        <input type="submit" class="btn" id="search_gamename" value="载入"/>
    </form>
        <div class="row-fluid" id="json_result"> 
        </div>
	    </div>
	    <div class='row-fluid'>
<?php 
foreach($revisions as $revision_info){
    $revision_id = $revision_info['id'];
    $revision_page = site_url('game_management/view_revision/'.$revision_info['id']);
    $manifest = $revision_info['manifest_url'];
    $apk_download_url = array_pop(explode('/', $revision_info['apk_download_url']));
    echo <<< EOF
<table  class="table table-condensed table-hover" id="revision_list" >
<thead>
<tr><th>revision_id</th> <th>game_name</th> <th>package_ver_code</th>
<th>filedir</th>
<th>Manifest</th>
<th>apk_download_url</th>
<th>cpk file dir</th>
<th>chafen url</th>
<th>操作</th>
</tr>
</thead>
<tbody>
<tr class="inactive">
    <td><a href="{$revision_page}">{$revision_info['id']}</a></td>
    <td>{$revision_info['game_name']}</td>
    <td>{$revision_info['package_ver_code']}</td>
    <td><a href="#" class="filedir">{$revision_info['file_dir']}</a></td>
    <td><span class="label inline-help"><a href="{$upload_folder}{$revision_info['manifest_url']}">manifest</a></span></td>
    <td><a href="{$upload_folder}{$revision_info['apk_download_url']}">{$apk_download_url}</a></td>
    <td><a href="#" class="filedir">{$revision_info['cpk_file_dir']}</a></td>
    <td>{$revision_info['chafen_url']}</td>
    <td><a id="edit_{$revision_id}" revision_id="{$revision_id}" class="edit_revision" href="">编辑锁定</a></td>
</tr>
<tbody>
</table>
EOF;
}

?>
        </div>
	    <div class='row-fluid'>
            <table class="table table-condensed table-hover" id='filelist'>
                <thead>
                    <tr><th>文件名</th><th>类型</th><th>md5</th><th>Size (M)</th><th>操作</th></tr>
                </thead>
                <tbody>
                <?php foreach($files as $file){ ?>
                <tr>
                <td val="<?php echo $file;?>"><a href="<?php echo $upload_folder . $filedir. $file;?>"><?php echo $file;?></a>

                <?php
                $ext =  array_pop(explode('.', $file)); 
                if ($ext=='zip') { ?>
                <span class="label inline-help"><a id="unzip<?php echo $file ;?>" class="unzip" 
                req="<?php echo site_url('file_management/ajax_unzip_flat_replace_all?file='.urlencode($file) . '&filedir='.urlencode($filedir))?>" href="#"
                >UNZIP</a></span>
                <?php
                }
                elseif($file=='SceneManifest.xml')
                { ?>
                <span class="label inline-help"><a id="check_integrity_<?php echo $file ;?>" class="xml" 
                req="<?php echo site_url('file_management/check_manifest_integrity?file='.urlencode($file) . '&filedir='.urlencode($filedir))?>" href="#"
                >Validate</a></span>
                <?php }?>

                </td>
                <td><?php echo $ext ;?></td>
                <td class="md5" req="<?php echo site_url('file_management/ajax_get_md5?file='.urlencode($file) . '&filedir='.urlencode($filedir));?>"></td>
                <td></td>
                <td>删除 重命名


                </td></tr>
                <?php }?>
                </tbody>
            </table>
	    </div>
	</div>
<script type="text/javascript">
$(document).ready(function(){
    //$('#filelist').DataTable();
    $('.filedir').each(function(){
        url = "<?php echo site_url('file_management/manage_files?filedir=');?>";
        $(this).attr('href',url + encodeURIComponent($(this).text()));
    });
    $('.unzip').each(function(){
        $(this).on('click', function(){
            to_unzip_all = confirm('解压可能会覆盖文件夹中的同名文件，确定解压？');
            if(!to_unzip_all) return false; 
            html  = $.ajax(
                {
                    'url': $(this).attr('req'),
                        async:true,
                        context: this,
                        success:function(data, textStatus){
                            //console.log(data);
                            //$(this).text(data);
                            //$(this).text(data);
                            alert(data);
                        },
                        error:function(){
                            //$(this).parent().html('manifest');
                        }
                }
            );
            return false; 
        });

    });
    $('.md5').each(function(){
        //filename = $(this).prev().attr('val');
        html  = $.ajax(
            {
                'url': $(this).attr('req'),
                    async:true,
                    context: this,
                    success:function(data, textStatus){
                        //console.log(data);
                        //$(this).text(data);
                        $(this).text(data.split('|')[0]);
                        $(this).next().text(data.split('|')[1]);
                    },
                    error:function(){
                        //$(this).parent().html('manifest');
                    }
            }
        );
        
        });
    $('.edit_revision').each(function(){
        $(this).click(function(){
            $('#revision_list tbody tr').removeClass('active');
            $('#revision_list tbody tr').addClass('inactive');
            $(this).parent().addClass('active');
            alert('ready to edit ' + $(this).attr('revision_id'));
            return false; 
        });
    });
});
</script>
</body>
</html>
