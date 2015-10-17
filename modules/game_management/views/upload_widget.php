<!--
<button type="button" data-toggle="modal" data-target="#uploadModal">显示对话框</button>
-->

<div class="modal hide fade"  id="uploadModal">
  <div class="modal-header">
    <button type="button" class="close modal_close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>上传文件 <span id="workdir"></span></h3>
  </div>
  <div class="modal-body">
	<link rel="stylesheet" href="<?php echo site_url('asset/css/fileupload/blueimp-gallery.min.css');?>">
    <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
    <link rel="stylesheet" href="<?php echo site_url('asset/css/fileupload/jquery.fileupload.css');?>">
    <link rel="stylesheet" href="<?php echo site_url('asset/css/fileupload/jquery.fileupload-ui.css');?>">

    <?php echo form_open_multipart('file_management/fake', array('id'=>'fileupload','class'=>'form-horizontal')); ?>
                    <input type="hidden" id="userdir" name="userdir" value="<?php echo $userdir; ?>"></input>
                    <table id="uploadtable" role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
                        <div class="row-fluid fileupload-buttonbar">
                            <span class="btn fileinput-button">
                                <i class="glyphicon glyphicon-plus"></i>
                                <span>选择上传文件...</span>
                                <!-- <input type="file" name="files[]" accept="application/x-zip, application/zip, application/x-zip-compressed, application/octet-stream" multiple> -->
                                <input type="file" name="files[]" accept="">
                            </span>
                            <span class="fileupload-process"></span>
                        </div>
            </form>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn modal_close" data-dismiss="modal" id="close_modal1">关闭</a>
  </div>
</div>

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
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar progress-bar progress-bar-success" style="width:0%;"></div></div>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
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
        <!-- <td>{%= file.upload_dir %}</td> -->
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
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
            {% if (file.deleteUrl) { 
                    console.log(html); 
            if(html=='btnupdate') { %}
                <a href="#" class="btn btnupdate" data-name="{%=file.name%}" data-uploaddir="{%=file.upload_dir%}" data-url="{%=file.deleteUrl%}">
                    <i class="glyphicon"></i>
                    <span>应用</span>
                </a> 
            {% } %}
            {% if(html=='btnrevision'){ %}
                <a href="#" class="btn btnrevision" data-name="{%=file.name%}" data-uploaddir="{%=file.upload_dir%}" data-url="{%=file.deleteUrl%}">
                    <i class="glyphicon"></i>
                    <span>应用</span>
                </a>
            {% } %}
            {% if(html=='bthfile'){ %}
                <a href="#" class="btn bthfile" data-name="{%=file.name%}" data-uploaddir="{%=file.upload_dir%}" data-url="{%=file.deleteUrl%}">
                    <i class="glyphicon"></i>
                    <span>应用</span>
                </a>
            {% } %}
            {% if(html=='bthchafen'){ %}
                <a href="#" class="btn btnchafen" data-name="{%=file.name%}" data-uploaddir="{%=file.upload_dir%}" data-url="{%=file.deleteUrl%}">
                    <i class="glyphicon"></i>
                    <span>应用</span>
                </a>
            {% } %}
                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>删除</span>
                </button>
                <!-- 
                <a href="#" class="btn btnunzip" data-name="{%=file.name%}" data-url="{%=file.deleteUrl%}">
                    <i class="glyphicon"></i>
                    <span>解压</span>
                </a>
                -->
                <!-- <input type="checkbox" name="delete" value="1" class="toggle"> -->
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>取消</span>
                </button>
            {% } %}
            {% if (file.status) { %}
            
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="<?php echo site_url('asset/js/fileupload/vendor/jquery.ui.widget.js');?>"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="<?php echo site_url('asset/js/fileupload/tmpl.min.js');?>"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="<?php echo site_url('asset/js/fileupload/load-image.all.min.js');?>"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="<?php echo site_url('asset/js/fileupload/canvas-to-blob.min.js');?>"></script>
<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
<!-- <script src="<?php // echo site_url('asset/js/fileupload/bootstrap.min.js');?>"></script> -->
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

<script type="text/javascript">
function encodedir(userdir) {
    //var userdir = $('#userdir').val();
    var url = encodeURIComponent(userdir);
    return url; 
}
function uploadurl(userdir) { 
        return  '<?php echo site_url('file_management/ajax_file_upload');?>'+ '?userdir=' + encodedir(userdir) ;
}
    var html;
function uploadwidget( userdir , channel_id, chnres_id,method) {
    var game_id = <?php echo $game_id;?>;
    var upload_dir = uploadurl(userdir);
    html = method;
    switch (method)
    {
        case 'btnupdate':
            $('#uploadtable').undelegate('a.btnupdate', 'click');
            $('#uploadtable').delegate('a.btnupdate', 'click', function() {
                var revision_id = <?php echo isset($revision_id)?$revision_id:0; ?>;
                var file_name = $(this).data('name');
                var upload_dir = $(this).data('uploaddir');
                $.post("<?php echo site_url('game_management/experiment/ajax_update_chn_res');?>"
                        ,{revision_id:revision_id
                        ,file_name: file_name
                        ,channel_id: channel_id
                        ,chnres_id: chnres_id
                        ,upload_dir: upload_dir
                        }
                        ,function(result){
                            if(result == 'done')  {
                                window.location.reload();
                            } else {
                                $('.alert').remove();
                                $('#msg').addClass("alert").html(result);
                                $("#uploadModal").modal('hide');
                            }
                        });
                return false;
            });
        break;
        case 'btnrevision':
            $('#uploadtable').undelegate('a.btnrevision', 'click');
            $('#uploadtable').delegate('a.btnrevision', 'click', function() {
                var file_name = $(this).data('name');
                var upload_dir = $(this).data('uploaddir');
                $.post("<?php echo site_url('game_management/experiment/ajax_handler');?>"
                        ,{file_name: file_name
                        ,upload_dir: upload_dir
                        ,action:'install_revision'
                        ,game_id:game_id
                        }
                        ,function(result){
                            console.log(result);
                            if(result == 'done')  {
                                window.location.reload();
                            } else {
                                $('.alert').remove();
                                $('#msg').addClass("alert").html(result);
                                $("#uploadModal").modal('hide');
                            }
                        });
                return false; 
            });
            break;
        case 'bthfile':
            $('#uploadtable').undelegate('a.bthfile', 'click');
            $('#uploadtable').delegate('a.bthfile', 'click', function() {
                var file_name = $(this).data('name');
                var upload_dir = $(this).data('uploaddir');
                $.post("<?php echo site_url('game_management/experiment/ajax_handler');?>"
                        ,{file_name: file_name
                        ,revision_id:channel_id
                        ,upload_dir: upload_dir
                        ,action:'replace_file'
                        }
                        ,function(result){ 
                            if(result == 'done')  {
                                window.location.reload();
                            } else {
                                $('.alert').remove();
                                $('#msg').addClass("alert").html(result);
                                $("#uploadModal").modal('hide');
                            }
                        });
                return false;
            });
            break;
        case 'bthchafen':
            $('#uploadtable').undelegate('a.btnchafen', 'click');
            $('#uploadtable').delegate('a.btnchafen', 'click', function() {
                var file_name = $(this).data('name');
                var upload_dir = $(this).data('uploaddir');
                $.post("<?php echo site_url('game_management/experiment/ajax_handler');?>"
                        ,{file_name: file_name
                        ,revision_id:channel_id
                        ,upload_dir: upload_dir
                        ,action:'install_chafen'
                        ,support_ver:chnres_id
                        }
                        ,function(result){ 
                            if(result == 'done')  {
                                window.location.reload();
                            } else {
                                $('.alert').remove();
                                $('#msg').addClass("alert").html(result);
                                $("#uploadModal").modal('hide');
                            }
                        });
                return false; 
            });
            break;
        default:
            break;
    }
    

    //'use strict';
    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: '<?php echo site_url('file_management/ajax_file_upload');?>'
        ,upload_dir:upload_dir
        //url: uploadurl()
        ,completed: function(e, data) { 
            //console.dir(data);
        }
        /*
        ,done: function (e, data ) { 
                if (e.isDefaultPrevented()) {
                    return false;
                }
                var that = $(this).data('blueimp-fileupload') ||
                        $(this).data('fileupload'),
                    getFilesFromResponse = data.getFilesFromResponse ||
                        that.options.getFilesFromResponse,
                    files = getFilesFromResponse(data),
                    template,
                    deferred;
                if (data.context) {
                    data.context.each(function (index) {
                        var file = files[index] ||
                                {error: 'Empty file upload result'};
                        deferred = that._addFinishedDeferreds();
                        that._transition($(this)).done(
                            function () {
                                var node = $(this);
                                template = that._renderDownload([file])
                                    .replaceAll(node);
                                that._forceReflow(template);
                                that._transition(template).done(
                                    function () {
                                        data.context = $(this);
                                        that._trigger('completed', e, data);
                                        that._trigger('finished', e, data);
                                        deferred.resolve();
                                    }
                                );
                            }
                        );
                    });
                } else {
                    template = that._renderDownload(files)[
                        that.options.prependFiles ? 'prependTo' : 'appendTo'
                    ](that.options.filesContainer);
                    that._forceReflow(template);
                    deferred = that._addFinishedDeferreds();
                    that._transition(template).done(
                        function () {
                            data.context = $(this);
                            that._trigger('completed', e, data);
                            that._trigger('finished', e, data);
                            deferred.resolve();
                        }
                    );
                }
        }
     */
    });

    // 续传
    $('#fileupload').fileupload({
        maxChunkSize: 500000, //500K
        add: function (e, data) {
        var that = this;
        $.getJSON( uploadurl(userdir), {file: data.files[0].name}, function (result) {
            var file = result.file;
            data.uploadedBytes = file && file.size;
            console.log(data.uploadedBytes);
            $.blueimp.fileupload.prototype
                .options.add.call(that, e, data);
        });
        }
    });

    // 重传
    $('#fileupload').fileupload({
    maxRetries: 150,
    retryTimeout: 100,
    fail: function (e, data) {
        // jQuery Widget Factory uses "namespace-widgetname" since version 1.10.0:
        var fu = $(this).data('blueimp-fileupload') || $(this).data('fileupload'),
            retries = data.context.data('retries') || 0,
            retry = function () {
                $.getJSON(uploadurl(userdir), {file: data.files[0].name})
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
        //url: $('#fileupload').fileupload('option', 'url'),
        url: uploadurl(userdir),
        upload_dir: $('#fileupload').fileupload('option', 'upload_dir'),
        dataType: 'json',
        context: $('#fileupload')[0]
    }).always(function () {
        $(this).removeClass('fileupload-processing');
    }).done(function (result) {
        // 删除其他目录的显示
        $('.template-download').remove();
        $(this).fileupload('option', 'done')
            .call(this, $.Event('done'), {result: result});
    });
}
</script>
