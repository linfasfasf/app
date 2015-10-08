<div class="modal hide fade"  id="channelconfigmodal">
  <div class="modal-header">
    <button type="button" class="close modal_close chnconfig_modal_close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>(<span id="cfg_channelname"></span>) 渠道配置 </h3>
  </div>
  <div class="modal-body">
    <div class="row-fluid">
        <label class="radio"><input type="radio" name="configtype" id="configtyperadio" value="1">完整渠道包</input></label>
        <label class="radio"><input type="radio" name="configtype" id="configtyperadio" value="0">非完整渠道包</input></label>
    </div>
    <div class="row-fluid content cfgtext hide">
<label>明文: 
<span style="position:relative;">
        <span class='btn'>
            选择配置文件
            <input type="file" style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;' id="file_plain" size="40" >
        </span>
        &nbsp;
        <span class='label label-info' id="upload-file-info"></span>
</span>
</label>
        <textarea rows=8 class="clear" name="configtext" id="channelconfigtext" style="width:460px"></textarea>
<label>密文: 
<span style="position:relative;">
        <span class='btn'>
            选择配置文件
            <input type="file" style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;' id="file_encoded" size="40" >
        </span>
        &nbsp;
        <span class='label label-info' id="upload-file-info-encoded"></span>
</span>
</label>
        <textarea rows=4 class="clear" name="configtext" id="channelconfigencoded" style="width:460px"></textarea>
    </div>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" id="chncfgupdate">更新</a>
    <a href="#" class="btn modal_close chnconfig_modal_close" data-dismiss="modal" id="close_modal2">关闭</a>
  </div>
</div>

<script src="<?php echo site_url('asset/js/b64.js');?>"></script>
<script type="text/javascript">

    $(document).ready (function(){
        $('.chncfg').click(
            function() {
                $('#channelconfigmodal').data('source', "#" + $(this).attr('id')).trigger('loadconfig');
            }
        );
        $('#channelconfigmodal').on('loadconfig',
            function(evt) {
                var source = $(this).data('source');
                var chnid = $(source).data('chnid');
                var chnname = $(source).data('chnname');
                var apkid = $(source).data('apkid');
                var dtype = $(source).data('type');
                var cfgtext = $(source).data('b64plain');
                var cfgtextencoded = $(source).data('b64encoded');

                $('#cfg_channelname').text(chnid + '/' + chnname);
                if(dtype == '1' || dtype == '-1') {
                    $('input[name=configtype][value=1]').prop('checked', true).click();
                }else{
                    $('input[name=configtype][value=0]').prop('checked', true).click();
                }

                if(cfgtext && cfgtextencoded) {
                    cfgtext = Base64.decode($(source).data('b64plain'));
                    cfgtextencoded = Base64.decode($(source).data('b64encoded'));
                    // 已经有数据不再请求 
                    $('#channelconfigtext').val(cfgtext);
                    $('#channelconfigencoded').val(cfgtextencoded);
                }else{
                    data = { apkid: apkid, chnid: chnid, dtype: dtype}

                    url = "<?php echo site_url('game_management/experiment') . '/ajax_get_channel_config';?>";
                    $.post(url, data, function(res) {
                        if(res && res.msg == 'ok') {
                            var plain_text = res.data.channel_config_text;
                            var encoded_text = res.data.channel_config_encoded;
                            $(source).data('b64plain', plain_text);
                            $(source).data('b64encoded', encoded_text);

                            plain_text = Base64.decode(plain_text); 
                            encoded_text = Base64.decode(encoded_text); 
                            $('#channelconfigtext').val(plain_text);
                            $('#channelconfigencoded').val(encoded_text);
                        }
                    });
                }
            }
        );

        $('#channelconfigtext').change(
            function ()  {
                var source = $('#channelconfigmodal').data('source');
                var plain_text = Base64.encode($(this).val());
                $(source).data('b64plain', plain_text);
            }
        );

        $('#channelconfigencoded').change(
            function ()  {
                var source = $('#channelconfigmodal').data('source');
                var encoded_text = Base64.encode($(this).val());
                $(source).data('b64encoded', encoded_text);
            }
        );
        $('#chncfgupdate').click(
            function () {
                var source = $('#channelconfigmodal').data('source');
                var chnid = $(source).data('chnid');
                var apkid = $(source).data('apkid');
                var dtype = $('input[name=configtype]:checked').val();
                var cfgtext = $(source).data('b64plain');
                var cfgtextencoded = $(source).data('b64encoded');

                if( cfgtext &&  cfgtextencoded) {
                    data = { apkid: apkid, chnid: chnid, dtype: dtype, cfgtext: cfgtext, cfgtextencoded: cfgtextencoded }
                    url = "<?php echo site_url('game_management/experiment') . '/ajax_update_channel_config';?>";
                    $.post(url, data, function(res) {
                        if(res && res.msg == 'ok') {
                            alert('更新成功');
                            window.location.reload();
                        }else{
                            alert('更新失败');
                        }
                    });
                }else{
                    alert('请提供明文和密文文件');
                }
            });

        $('input[name=configtype]').click(
            function () {
                dtype = $('input[name=configtype]:checked').val();
                if(dtype == '0') {
                    $('.cfgtext').show('slow');
                }else{
                    $('.cfgtext').hide('slow');
                }
            });

    $('#file_plain').change(
        function() {
            if(this.files.length >= 1) {
                if(this.files[0].type == 'application/json' ||  this.files[0].type == '' || this.files[0].type.match('text.*')) {
                    $("#upload-file-info").html($(this).val());
                    var reader = new FileReader();
                    reader.onloadend = function ()
                    {
                      dataToBeSent = reader.result.split("base64,")[1];
                      var plain = Base64.decode(dataToBeSent);
                      $('#channelconfigtext').val(plain);
                      var source = $('#channelconfigmodal').data('source');
                      $(source).data('b64plain', dataToBeSent);
                    }
                    reader.readAsDataURL(this.files[0]);
                }else{
                    $("#upload-file-info").html('不支持的文件类型');
                    delete this.files[0] ;
                }
            }
        });
    $('#file_encoded').change(
        function() {
            if(this.files.length >= 1) {
                if(this.files[0].type == 'application/json' ||  this.files[0].type == '' || this.files[0].type.match('text.*')) {
                    $("#upload-file-info-encoded").html($(this).val());
                    var reader = new FileReader();
                    reader.onloadend = function ()
                    {
                      dataToBeSent = reader.result.split("base64,")[1];
                      var plain = Base64.decode(dataToBeSent);
                      $('#channelconfigencoded').val(plain);
                      var source = $('#channelconfigmodal').data('source');
                      $(source).data('b64encoded', dataToBeSent);
                    }
                    reader.readAsDataURL(this.files[0]);
                }else{
                    $("#upload-file-info-encoded").html('不支持的文件类型');
                    delete this.files[0] ;
                }
            }
        });
    });

</script>
