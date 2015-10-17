<div class="modal hide fade"  id="rtcorepatchwidget">
  <div class="modal-header">
    <button type="button" class="close modal_close chnconfig_modal_close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3> 渠道配置 </h3>
  </div>
  <div class="modal-body" style="min-height:300px">
    <div class="input-append">
        <?php 
            echo form_open('#');
        ?>
        <div class="dropdown">
            <select name="engine_version" id="engine_version">
            </select>
        <?php
            echo form_submit(array('class'=>'btn', 'id' => 'addengine'), '新增 Runtime Core');
            echo form_close();
        ?>
        </div>
    </div>
    <div id="msginfo">
          
    </div>
    <div>
        <table class="table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th>RT<br />Core</th>
                    <th>patch<br />version</th>
                    <th>x86</th>
                    <th>armeabi</th>
                    <th>armeabi-v7a</th>
                    <th>arm64-v8a</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="rttable">
            </tbody>
        </table>
    </div>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn modal_close rtcore_modal_close" data-dismiss="modal" id="close_modal2">关闭</a>
  </div>
</div>

<script src="/asset/js/backbone/json2.js"></script>
<script src="/asset/js/backbone/underscore-min.js"></script>
<script src="/asset/js/backbone/backbone-min.js"></script>

<script type="text/template"  id="tpl-rtcoreversion">
    <option data-version="<%= runtime_core_version %>" value="<%= rtc_id %>"><%= runtime_core_version %></option>
</script>

<script type="text/template"  id="tpl-rtcore">
    <tr data-rtcid="<%= rtc_id %>" data-revisionid="<?php echo $revision_id;?>">
        <td><%= runtime_core_version %></td>
        <td><%= patch_version %></td>
        <td><a href="<%= x86_patch_url %>">下载</a></td>
        <td><a href="<%= armeabi_patch_url %>">下载</a></td>
        <td><a href="<%= armeabi_v7a_patch_url %>">下载</a></td>
        <td><a href="<%= arm64_v8a_patch_url %>">下载</a></td>
        <td>
            <span style="position:relative;">
                <span class="btn">
                    上传
                    <input data-rtcid="<%= rtc_id %>" data-revisionid="<?php echo $revision_id;?>" 
                    type="file" 
                    style='width:30px;position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;'
                    class="file_plain"/>
                </span>
                <span class="label label-info upload-file-info"></span>
            </span>
            <a href="#" class="btn btn-danger delrtc">删除</a>
        </td>
    </tr>
</script>

<script type="text/javascript">
var rtcorelist;
var rtcores = [];

var Rtversion = Backbone.Model.extend({
    defaults: {
        rtc_id: "",
        runtime_core_version: ""
    }
});

var RtcoreModel = Backbone.Model.extend({
    defaults: {
        id:  "", 
        revision_id:  "", 
        patch_version:  "", 
        runtime_core_id:  "", 
        runtime_core_version:  "", 
        x86_patch_url:  "", 
        x86_patch_md5:  "", 
        armeabi_patch_url:  "", 
        armeabi_patch_md5:  "", 
        armeabi_v7a_patch_url:  "", 
        armeabi_v7a_patch_md5:  "", 
        arm64_v8a_patch_url:  "", 
        arm64_v8a_patch_md5:  ""
    }
});

var RtcoreList = Backbone.Collection.extend({
    model: RtcoreModel
});

var RtcoreView = Backbone.View.extend({
        initialize: function(){
            this.render();
        },
        render: function(){
            var html = [];
            var template = _.template( $("#tpl-rtcore").html());
            this.collection.each(function(item){
                html.push(template(item.toJSON()));
            });
            this.$el.html( html );
            this.$el.find('a').each( function () {
                if($(this).attr('href') == '') {
                    $(this).addClass('hide');
                }
            });
            return this;
        }
    });

function loadrtcore() {
    url = '<?php echo site_url('game_management/experiment/ajax_rtcorepatch');?>';
    revision_id = <?php echo $revision_id;?>;
    data = { revision_id: revision_id };
    $.post(url, data, function(data) {
        if(data.msg == 'ok') {
            rtcores = [];
            var rtcoreversions = [];
            var version_tmpl = _.template($('#tpl-rtcoreversion').html());
            var version_html = [];
            $.each(data.data, function (key, value) {
                if( (value.id == null && value.rtc_id)  || value.revision_id != revision_id) {
                    rtcoreversions.push(new Rtversion(value));
                    version_html.push(version_tmpl(value));
                }else{
                    rtcores.push(new RtcoreModel(value));
                }
            });
            rtcorelist = new RtcoreList(rtcores);
            $('#engine_version').html(version_html);
            var rtcoreview = new RtcoreView({collection:rtcorelist, el:$('#rttable')});
        }else{
            $('#msginfo').html('获取 rtcore 信息失败');
        }
    });
}

function checkcpk (cpkfile) {
    // checkfile type, extension, file size
    return true;
}

$(document).ready(
    function() {
        loadrtcore();
        $('#addengine').click(
            function() {
                //var version_tmpl = _.template($('#tpl-rtcoreversion').html());
                var rtc_id = $('#engine_version option:selected').val();
                var rtc_version = $('#engine_version option:selected').data('version');
                if(typeof rtc_id == 'undefined' || rtc_id == null) {
                }else{
                    var rtcore = new RtcoreModel({runtime_core_id:rtc_id, rtc_id:rtc_id,runtime_core_version : rtc_version, revision_id:<?php echo $revision_id;?>});
                    rtcores.push(rtcore);
                    console.dir(rtcores);
                    rtcorelist = new RtcoreList(rtcores);
                    var rtcoreview = new RtcoreView({collection:rtcorelist, el:$('#rttable')});
                }
                return false;
            }
        );

        $('table').on('change','.file_plain',[],
            function() {
                if(this.files.length >= 1) {
                    var self = this;
                    var reader = new FileReader();
                    var cpkfile = this.files[0];
                    var cpkfilename = cpkfile.name;
                    reader.onloadend = function ()
                    {
                        var b64data = reader.result.split("base64,")[1];
                        var rtcid = $(self).data('rtcid');
                        var revisionid = $(self).data('revisionid');
                        //console.dir(cpkfilename);
                        var url = '<?php echo site_url('game_management/experiment/ajax_upload_rtcorepatch');?>';
                        var data = {revision_id:revisionid, runtime_core_id: rtcid, file_b64:b64data, filename: cpkfilename};
                        $.post( url, data, function (result) {
                                if(result.msg=='ok') {
                                    window.location.reload();
                                }else{
                                    $(self).parent().next('.upload-file-info').html('上传失败');
                                }
                                delete cpkfile;
                            }
                        );
                    }
                    if(checkcpk(cpkfile)) {
                        $(this).parent().next('.upload-file-info').html($(this).val());
                        reader.readAsDataURL(cpkfile);
                    }else{
                        $(this).parent().next('.upload-file-info').html('文件非法');
                    }
                }
            }
        );
    }
);

</script>
