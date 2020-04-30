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
        <label class="layui-form-label layui-bg-gray">敏感词：</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input" name="sensitive[sensitive]" value="" autocomplete="off" placeholder="请填写敏感词">
        </div>
    </div>

    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" class="layui-btn"/>
        </div>
    </div>
</form>

<script src="/static/js/jquery.2.1.4.min.js"></script>
<script src="/static/plupload-2.3.6/js/plupload.full.min.js"></script><script src="/static/plupload-2.3.6/js/i18n/zh_CN.js"></script>
<script src="/static/xuploader/webServerUploader.js"></script>
<script src="/static/js/XCommon.js"></script>
<script>
    function afterUpLogo(resp){
        $('#img_logo').attr('src',resp.filePath);
        $('#site_logo').val(resp.filePath);
        layer.msg('上传完成',{time:500});
    }

    $(function(){
        createWebUploader('upload_logo_chose_btn','','','image',afterUpLogo);
    });

</script>

