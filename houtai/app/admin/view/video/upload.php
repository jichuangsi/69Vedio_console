
<link rel="stylesheet" type="text/css" href="/static/zmup/css/upload.css" />
<script type="text/javascript" src="/static/zmup/js/jquery.js"></script>
<script type="text/javascript" src="/static/zmup/js/webuploader.js"></script>
<script type="text/javascript" src="/static/zmup/js/md5.js"></script>
<script type="text/javascript" src="/static/zmup/js/upload.js"></script>


<script type="text/javascript" src="/static/zmup/js/jquery.xdomainrequest.min.js"></script>
<script language="javascript">
    <?php
    $yzm_url=x_get_webseting('yzm_upload_url');
    ?>
    //上传地址
    var ServerUrl = "{$yzm_url}/uploads";
</script>
<style type="text/css">
    .layui-form-item .layui-input-inline{max-width:80%;width:auto;min-width:260px;}
    .layui-form-mid{padding:0!important;}
    .layui-form-mid code{color:#5FB878;}
    dl.layui-anim.layui-anim-upbit{z-index:1000;}
</style>
<div style="display:block;width:100%;overflow:hidden;">
    {:runhook('system_admin_index')}
</div>
<form action="#" class="page-list-form layui-form layui-form-pane" method="post">
    <div class="layui-form-item">
        <label class="layui-form-label layui-bg-gray">视频标题：</label>
        <div class="layui-input-inline">
            <input type="text" id="title" class="layui-input" name="video[title]" value="" autocomplete="off" placeholder="请填写">
        </div>
    </div>
    <div class="layui-form-item"  style="display: none;">
        <label class="layui-form-label layui-bg-gray">视频推荐：</label>
        <div class="layui-input-inline">
            <select name="video[reco]" class="field-pid" type="select" lay-filter="pai">
                <option value="0" level="0" >视频推荐</option>
                <option value="1" level="1" >★☆☆☆☆</option>
                <option value="2" level="2" >★★☆☆☆</option>
                <option value="3" level="3" >★★★☆☆</option>
                <option value="4" level="4" >★★★★☆</option>
                <option value="5" level="5" >★★★★★</option>
            </select>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label layui-bg-gray">观看金币：</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input" name="video[gold]" value="" autocomplete="off" >
        </div>
        <div class="layui-form-mid layui-word-aux"> *观看需要支付金币</div>  </div>
<!--	<div class="layui-form-item">
        <label class="layui-form-label layui-bg-gray">关键字：</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input" name="video[key_word]" value="" autocomplete="off" placeholder="请填写">
        </div>
    </div> -->
    <div class="layui-form-item">
        <label class="layui-form-label layui-bg-gray">视频标签</label>
        <div class="layui-input-inline" style="width: 50%;">
            {volist name="tag_result" id="v"}
            <input type="checkbox" class="layui-checkbox checkbox-ids"  name="tag[]"  value="{$v['id']}" title="{$v['name']}">
            {/volist}
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label layui-bg-gray">视频分类：</label>
        <div class="layui-input-inline">
            <select name="video[class]" class="field-pid" type="select" lay-filter="pai">

                {volist name="classlist" id="v" }
                <option value="{$v['id']}" level="{$v['id']}" >|-{$v['name']}</option>
                {volist name="v['childs']" id="vv" }
                <option value="{$vv['id']}" level="{$vv['id']}" >&nbsp;&nbsp;&nbsp;&nbsp;|-{$vv['name']}</option>
                {/volist}
                {/volist}
            </select>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label layui-bg-gray">上传视频：</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input" name="video[url]"  id="odownpath1" value="" autocomplete="off">
        </div>
        <div id="yzm_panel" class="layui-input-inline" style="display:none;">
            <label  id="chosevideo" style="display: none;">上传</label>
        </div>

        <div class="layui-input-inline">
            <a id="video_up_btn" href="javascript:" class="layui-btn layui-btn-primary" style="background-color: #fff;">上传</a>
        </div>
    </div>

    <div class="layui-form-item" id="yzm_file_list" style="width: 450px;font-size:12px!important;color:grey;"></div>
    <div class="layui-form-item" style="display: none;">
        <label class="layui-form-label layui-bg-gray">下载链接：</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input" name="video[download_url]" id="downpath1" value="" autocomplete="off" >
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label layui-bg-gray">缩略图：</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input" name="video[thumbnail]" id="titlepic"  value="" autocomplete="off" >
        </div>
        <div class="layui-input-inline">
            <a id="video_thumb_up_btn" href="javascript:" class="layui-btn layui-btn-primary" style="background-color: #fff;">上传</a>
            &nbsp;&nbsp; <img onmouseout="layer.closeAll();"  onmouseover="imgTips(this,{width:320})" style="border-radius:5px;border:1px solid #ccc;"  height="36" id="img_video_thumb" src="/static/images/images_default.png">
        </div>
    </div>

    <div class="layui-form-item" style="display: none;">
        <label class="layui-form-label layui-bg-gray">视频时长：</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input" name="video[play_time]" id="playtime" id="playtime" value="" autocomplete="off" >
        </div>
    </div>
<!--    <div class="layui-form-item">
        <label class="layui-form-label layui-bg-gray">视频说明</label>
        <div class="layui-input-inline">
            <textarea rows="6"  class="layui-textarea" name="video[short_info]" autocomplete="off" placeholder="请填写视频说明"></textarea>
        </div>
    </div> -->
    <div class="layui-form-item">
        <label class="layui-form-label  layui-bg-gray">视频简介:</label>
        <div class="layui-input-block">
            <!--<textarea id="UEditor1" name="video[info]" style="width: 60%;"></textarea>-->
            <textarea rows="7" name="video[info]" style="width: 60%;"></textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" class="layui-btn"/>
        </div>
    </div>
</form>
{:editor(['UEditor1'], 'ueditor','/Xuploader.php?&from=ueditor')}
<script src="/static/js/jquery.2.1.4.min.js"></script>
<script src="/static/plupload-2.3.6/js/plupload.full.min.js"></script><script src="/static/plupload-2.3.6/js/i18n/zh_CN.js"></script>
<script src="/static/xuploader/webServerUploader.js"></script>
<script src="/static/js/XCommon.js"></script>
<script>
    function afterUpThumb(resp){
    	console.log(resp);
    	var path=resp.filePath;
    	console.log(path.slice(path.indexOf("uploads/")+8));
        $('#img_video_thumb').attr('src',resp.filePath);
        $('#titlepic').val(path.slice(path.indexOf("uploads/")+8));
        layer.msg('上传缩略图完成',{time:500});
    }
    function afterUpVideo(resp){
        console.log(resp);
        var path=resp.filePath;
        console.log(path.slice(path.indexOf("uploads/")+8));
        $('#odownpath1').val(path.slice(path.indexOf("uploads/")+8));
        layer.msg('上传视频完成',{time:500});
    }
    $(function(){
        createWebUploader('video_thumb_up_btn','','','image',afterUpThumb);
        createWebUploader('video_up_btn','','','video',afterUpVideo);

        //隐藏云转码按钮
        {php}$webType=x_get_webseting('video_save_server_type');{/php}
        {if $webType=='yunzhuanma'}
            $("#chosevideo").show();
            $("#video_up_btn").hide();
        {/if}
    });
</script>

