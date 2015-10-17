<div class="row-fluid">
    <ul class="breadcrumb">
        <li>运营管理 <span class="divider">/</span></li>
        <li><a href="<?php echo site_url('game_management/gamelist'); ?>">游戏列表</a> <span class="divider">/</span></li>
        <li class="active">上传游戏资源</li>
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
<?php }; ?>

<div class='row-fluid'>
<!--    <fieldset>
        <form class="form-horizontal" action="<?php echo site_url('game_management/experiment/add_revision3?game_id=' . $game_id); ?>"  method="post" enctype="multipart/form-data">
            <legend>上传游戏资源</legend>
            <div class="control-group">
                <label class="control-label">上传：</label>
                <input class="input_txt" type="" name="revision_txt" disabled="disabled"/>
                <input class="input_txt" type="hidden" name="game_id" value="<?php echo $game_id ?>"/>
                <input type="button" class="btn doupload" value="浏览" title="请上传完整的zip包">
                <input type="file" id="revision_file" class="upload" name="revision_res" accept="application/x-zip, application/zip, application/x-zip-compressed, application/octet-stream" style="display:none">
                <p>请选择通用资源，支持zip格式</p>
            </div>
            <div class="control-group">
                <label class="control-label"></label>
                <input class="input_txt" type="" name="revision_txt" disabled="disabled"/>
                <input class="input_txt" type="" name="game_id" value="<?php echo $game_id ?>" style="display:none" />
                <input type="button" class="btn doupload" value="浏览" title="请上传完整的zip包">
                <input type="file" id="" class="upload" name="channel_res" accept="application/x-zip, application/zip, application/x-zip-compressed, application/octet-stream" style="display:none">
                <p>请选择渠道资源，支持zip格式</p>
            </div>
            <div class="control-group">
                <label class="control-label">渠道：</label>
                <select name="channel" class="channel" title="不选择则不关联">
                    <option value="-1" >--请选择渠道--</option>
                </select >
            </div>
            <button class="btn btn-primary" type="submit" name="" >预览</button>
        </form>
    </fieldset>-->
<fieldset>
        <?php echo form_open_multipart('file_management/fake', array('id'=>'fileupload','class'=>'form-horizontal'));?>
    <?php echo form_fieldset("上传游戏资源");?>
    <label class="control-label"></label>
    
                    <input type="hidden" name="userdir" value="<?php echo $userdir; ?>"></input>
                    <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
                        <div class="row-fluid fileupload-buttonbar">
                            <span class="btn fileinput-button">
                                <i class="glyphicon glyphicon-plus"></i>
                                <span>选择文件上传</span>
                                <!-- <input type="file" name="files[]" accept="application/x-zip, application/zip, application/x-zip-compressed, application/octet-stream" multiple> -->
                                <input type="file" name="files[]" accept="application/x-zip, application/zip, application/x-zip-compressed, application/octet-stream">
                            </span>
                            <span class="fileupload-process"></span>
                        </div>
            </form>
</fieldset>
<fieldset>
    <br>
    <?php if(!empty($files_msg)) {?>
    <table class="table table-striped table-bordered">
        <tr><td>资源</td><td>资源类型</td><td>所属游戏</td><td>版本编号</td><td>文件状态</td><td>提示</td><td>操作</td></tr>
        <?php 
        foreach ($files_msg as $key => $value) { ?>
            <tr>
                <td><?php echo $key;?></td>
                <td><?php echo $value['ziptype']?></td>
                <td><?php echo $value['game_name'];?></td>
                <td><?php echo $value['package_ver_code'];?></td>
                <td><?php echo empty($value['files_status_code'])?'不完整':'完整';?></td>
                <td><?php echo $value['remarks'];?></td>
                <?php
                    if($value['files_status_code'])
                    {
                ?>
                <td>
                    <a class="btn btn-primary" href="<?php echo site_url('game_management/experiment/user_resource_handler?action=create&filename='.$key); ?>" target="_top">创建</a>
                    <a class="btn btn-danger" href="<?php echo site_url('game_management/experiment/user_resource_handler?action=delete&filename='.$key); ?>" target="_top">删除</a>
                </td>
                <?php
                    }
                    else
                    {
                ?>
                <td>
                    <a class="btn" disabled="disabled" href="#" target="_top" style="background:gray">创建</a>
                    <a class="btn btn-danger" href="<?php echo site_url('game_management/experiment/user_resource_handler?action=delete&filename='.$key); ?>" target="_top">删除</a>
                </td>
                <?php
                    }
                ?>
            </tr>
            <?php }?>
    </table>
    <?php }?>
</fieldset>
    <script type="text/javascript">
        $('.doupload').click(
                function () {
                    $(this).parent().find('.upload').click();
                }
        );
    </script>
    <link rel="stylesheet" href="<?php echo site_url('asset/css/fileupload/blueimp-gallery.min.css');?>">
    <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
    <link rel="stylesheet" href="<?php echo site_url('asset/css/fileupload/jquery.fileupload.css');?>">
    <link rel="stylesheet" href="<?php echo site_url('asset/css/fileupload/jquery.fileupload-ui.css');?>">
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
    <td><?php // echo $userdir;?></td>
    <!--
        <td>
            <span class="preview"></span>
        </td>
        -->
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>开始上传</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>取消</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
    <td><?php // echo $userdir;?></td>
    <!--
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        -->
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
            {% if (file.deleteUrl) { %}
                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>删除</span>
                </button>
                <!-- <input type="checkbox" name="delete" value="1" class="toggle"> -->
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>取消</span>
                </button>
            {% } %}
            {% if (file.status) { window.location.reload();%}
                
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script src="<?php echo site_url('asset/js/jquery.js');?>"></script>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="<?php echo site_url('asset/js/fileupload/vendor/jquery.ui.widget.js');?>"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="<?php echo site_url('asset/js/fileupload/tmpl.min.js');?>"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="<?php echo site_url('asset/js/fileupload/load-image.all.min.js');?>"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="<?php echo site_url('asset/js/fileupload/canvas-to-blob.min.js');?>"></script>
<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
<script src="<?php echo site_url('asset/js/fileupload/bootstrap.min.js');?>"></script>
<!-- blueimp Gallery script -->
<script src="<?php echo site_url('asset/js/fileupload/jquery.blueimp-gallery.min.js');?>"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="<?php echo site_url('asset/js/fileupload/jquery.iframe-transport.js');?>"></script>
<!-- The basic File Upload plugin -->
<script src="<?php echo site_url('asset/js/fileupload/jquery.fileupload.js');?>"></script>
<!-- The File Upload processing plugin -->
<script src="<?php echo site_url('asset/js/fileupload/jquery.fileupload-process.js');?>"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="<?php echo site_url('asset/js/fileupload/jquery.fileupload-image.js');?>"></script>
<!-- The File Upload audio preview plugin -->
<script src="<?php echo site_url('asset/js/fileupload/jquery.fileupload-audio.js');?>"></script>
<!-- The File Upload video preview plugin -->
<script src="<?php echo site_url('asset/js/fileupload/jquery.fileupload-video.js');?>"></script>
<!-- The File Upload validation plugin -->
<script src="<?php echo site_url('asset/js/fileupload/jquery.fileupload-validate.js');?>"></script>
<!-- The File Upload user interface plugin -->
<script src="<?php echo site_url('asset/js/fileupload/jquery.fileupload-ui.js');?>"></script>
<!-- The main application script -->
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
<!--[if (gte IE 8)&(lt IE 10)]>
<script src="js/cors/jquery.xdr-transport.js"></script>
<![endif]-->
<script>
    $('.btn-danger').click(function(){
        return confirm('确认删除?');
    });
</script>
<script type="text/javascript">
$(function () {
    //'use strict';

    uploadurl = '<?php echo site_url('file_management/ajax_file_upload/' . urlencode($userdir));?>';
    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: uploadurl
    });

    // 续传
    $('#fileupload').fileupload({
        maxChunkSize: 500000, //500K
        add: function (e, data) {
        var that = this;
        $.getJSON( uploadurl, {file: data.files[0].name}, function (result) {
            var file = result.file;
            data.uploadedBytes = file && file.size;
            $.blueimp.fileupload.prototype
                .options.add.call(that, e, data);
        });
        }
    });

    // 重传
    $('#fileupload').fileupload({
    /* ... settings as above plus the following ... */
    maxRetries: 150,
    retryTimeout: 100,
    fail: function (e, data) {
        // jQuery Widget Factory uses "namespace-widgetname" since version 1.10.0:
        var fu = $(this).data('blueimp-fileupload') || $(this).data('fileupload'),
            retries = data.context.data('retries') || 0,
            retry = function () {
                $.getJSON(uploadurl, {file: data.files[0].name})
                    .done(function (result) {
                        var file = result.file;
                        data.uploadedBytes = file && file.size;
                        // clear the previous data:
                        data.data = null;
                        data.submit();
                    })
                    .fail(function () {
                        fu._trigger('fail', e, data);
                    });
            };
        if (data.errorThrown !== 'abort' &&
                data.uploadedBytes < data.files[0].size &&
                retries < fu.options.maxRetries) {
            retries += 1;
            data.context.data('retries', retries);
            window.setTimeout(retry, retries * fu.options.retryTimeout);
            return;
        }
        data.context.removeData('retries');
        $.blueimp.fileupload.prototype
            .options.fail.call(this, e, data);
    }
    });

    /*
    // Enable iframe cross-domain access via redirect option:
    $('#fileupload').fileupload(
        'option',
        'redirect',
        window.location.href.replace(
            /\/[^\/]*$/,
            '/cors/result.html?%s'
        )
    );
     */

    // Load existing files:
    $('#fileupload').addClass('fileupload-processing');
    $.ajax({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: $('#fileupload').fileupload('option', 'url'),
        dataType: 'json',
        context: $('#fileupload')[0]
    }).always(function () {
        $(this).removeClass('fileupload-processing');
    }).done(function (result) {
//        window.location.reload();
        $(this).fileupload('option', 'done')
            .call(this, $.Event('done'), {result: result});
    });
});
</script>
<style type="text/css">
/*    .template-download {
        display:none;
     }*/
.delete {
        display:none;
     }
</style>
