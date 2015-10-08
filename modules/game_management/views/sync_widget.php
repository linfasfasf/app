<!--
<button type="button" data-toggle="modal" data-target="#uploadModal">显示对话框</button>
-->

<div class="modal hide fade"  id="sync_resources">
  <div class="modal-header">
    <button type="button" class="close modal_close sync_modal_close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>同步游戏至线上</h3>
  </div>
  <div class="modal-body">
<div class="row-fluid sync_dia_content"><span class="alert256"><img width="50px" src="/asset/img/alert256.png"></img></span>请谨慎操作该步骤， 同步到线上的游戏将替换掉已发布的游戏资源信息。</div>
    <div class="row-fluid">
        <h4>请选择要同步的游戏内容</h4> 
        <label class="checkbox">
          <input type="checkbox" id="checkboxchannel" value="">
           渠道资源
        </label>
        <label class="checkbox">
          <input type="checkbox" id="checkboxgeneral" value="">
           游戏通用资源/差分资源
        </label>
        <label class="checkbox">
          <input type="checkbox" id="checkboxrevinfo" value="">
           游戏信息
        </label>
    </div>

    <div>
    <h4>预览:</h4>
    <div class="row-fluid" id="preview"></div>
    </div>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" id="prepare">预览</a>
    <a href="#" class="btn" id="syncme">同步</a>
    <a href="#" class="btn modal_close sync_modal_close" data-dismiss="modal" id="close_modal2">关闭</a>
  </div>
</div>

<script src="/asset/js/backbone/json2.js"></script>
<script src="/asset/js/backbone/underscore-min.js"></script>
<script src="/asset/js/backbone/backbone-min.js"></script>

<script type="text/template"  id="tpl-game-diff">
<div class="row-fluid">
    <span class="span4"><%= field %></span><span class="span4"><a title="<%= local_value %>">LOCAL</a></span><span class="span4"><a title ="<%= remote_value %>" >REMOTE</a></span>
</div>
</script>

<script type="text/javascript">
var typeaheadprompts = [];

var GameDiff = Backbone.Model.extend({
    defaults: {
        field:  "", 
        local_value: 'not defined',
        remote_value: 'not defined'
    }
});

var DiffList = Backbone.Collection.extend({
    model: GameDiff,
});

var DiffView = Backbone.View.extend({
        initialize: function(){
            this.render();
        },
        render: function(){
            var html = [];
            var template = _.template( $("#tpl-game-diff").html());
            this.collection.each(function(item){
                //console.dir(item);
                html.push(template(item.toJSON()));
            });
            this.$el.html( html );
            return this;
        }
    });
    
//var search_view = new SearchView({ el: $("#search_container") });
// suggestion: _.template($('#tpl-typeahead').html())
</script>

<script type="text/javascript">
    function game_diff(local, remote) {
        // 根据选项调整合并策略
        url = '<?php echo site_url('game_management/game_api/ajax_game_diff');?>';

        option = {
            chn_resources:$('#checkboxchannel').prop('checked')?1:0,
            generic_resources:$('#checkboxgeneral').prop('checked')?1:0,
            revision_data:$('#checkboxrevinfo').prop('checked')?1:0,
            chafen_resources:$('#checkboxgeneral').prop('checked')?1:0
        };
        data = { local: local, remote: remote, option:option };
        $.post(url, data, function(data) {
            if(data.res == 'ok') {
                diffdata = data.data;
                var gamediff = [];
                var strategy  = [];
                $.each(diffdata, function (key, value) {
                    var local = value[0];
                    var remote = value[1];
                    gamediff.push(new GameDiff({field:key, local_value:local, remote_value:remote}));
                    strategy.push(key);
                });
                var difflist = new DiffList(gamediff);
                diffviw = new DiffView({collection:difflist, el:$('#preview')});
                $('.syncbtn.active').data({strategy:strategy, syncoption:option}); // 保存 source dest 到节点
            }else if(data.res== 'nodiff') {
                alert('不需要同步');
                // no need to update
            }else{
                // error
            }
        }, 'json');
    }

    // 从线上取数据进行对比
    function sync_prepare(localdata) {
        remote_host = '<?php echo $remote_url ;?>';
        if(remote_host == '') {
            $('#preview').html('请先配置远程 URL');
            return;
        }
        remote_url = remote_host + '/game_management/game_api/ajax_sync_prepare';
        $.post(remote_url, localdata, function(data) {
            // data : remote data
            // write result to some where
            if(data.res == 'ok') {
                local = data.data.source;
                remote = data.data.dest;
                game_diff(local, remote);
                $('.syncbtn.active').data(data); // 保存 source dest 到节点
            }else if( data.res == 'notok') {
                $('#preview').html('线上服务器上没找到数据');
            }
        }, 'json');
    }

    function sync_apply(data) {
        // data.source 本地
        // data.dest 线上
        remote_host = '<?php echo $remote_url ;?>';
        if(remote_host == '') {
            $('#preview').html('请先配置远程 URL');
            return;
        }

        chafenoption = $('.syncbtn.active').data('syncoption').chafen_resources;
        if(chafenoption == 1) {
            data.chafen = $('.syncbtn.active').data('chafen');
        }
        data.option = $('.syncbtn.active').data('syncoption');
        remote_url = remote_host + '/game_management/game_api/ajax_apply';
        $.post(remote_url, data, function(returndata) {
            // data : remote data
            // write result to some where
            if(returndata=='ok') {
                data.option = $('.syncbtn.active').data('syncoption');
                // 同步资源
                local_url = '<?php echo site_url('game_management/game_api/ajax_sync_res');?>';
                $.post(local_url, data, function(result) {
                    // done
                });
                alert('done');
                window.location.reload();
            }else{
                alert(returndata);
            }
        });
    }

    function sync_modal_reset() {
        $('.syncbtn').each(function() {$(this).removeClass('active')});
        $('#preview').html('');
    }

    $(document).ready (function() {
        $('#syncme').click(function() {
            // 请求线上机器进行同步
            // 成功后将本地资源加入同步队列
            gamedata = $('.syncbtn.active').data('data'); // 保存 source dest 到节点
            strategy = $('.syncbtn.active').data('strategy'); // 保存 source dest 到节点
            if (typeof gamedata != 'undefined') {
                gamedata.strategy = strategy;
                var tmpurl = '<?php echo site_url('uploads');?>' + gamedata.source.manifest_url;
                tmpurl = tmpurl.replace(/\/[^\/]*$/, ''); // remove /SceneManifest.xml
                var tmpdir = tmpurl.replace( /.*\//, '' );
                tmpdir = tmpdir.substr(0, 6);             // remove magic suffix
                tmpurl = tmpurl.replace(/\/[^\/]*$/, ''); // remove tmpdir
                gamedata.manifest = tmpurl + '/' + tmpdir + '/SceneManifest.xml';
                sync_apply(gamedata);
            }else{
                $('#preview').html('请先预览!');
            }
        });
        $('#prepare').click(function() {
            //ajax sending request and getting response
            // 导出信息
            channel_id = $('.syncbtn.active').data('channelid');
            revision_id = $('.syncbtn.active').data('revisionid');
            local_url = '<?php echo site_url('game_management/game_api/ajax_sync_export');?>';
            data = {revision_id : revision_id, channel_id : channel_id};
            $.post(local_url, data, function(data){
                if(data.res == 'ok') {
                    sync_prepare(data);
                    $('.syncbtn.active').data('chafen', data.chafen); // 保存 source dest 到节点
                }else{
                    $('#preview').html('没找到信息, 请先在渠道管理中添加本游戏!');
                }
            }, 'json');
        });
        $('.sync_modal_close').click(function() {
            sync_modal_reset();
        });
    });
</script>
