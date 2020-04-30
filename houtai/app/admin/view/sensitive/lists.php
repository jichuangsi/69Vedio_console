<script src="/static/js/jquery.2.1.4.min.js"></script>
<script src="/static/js/XCommon.js"></script>
<style>
    td{
        border-right: dashed 1px #c7c7c7;
        text-align:center;
    }
</style>
<form id="pageListForm" class="layui-form layui-form-pane" method="get">
    <div class="layui-btn-group">
        <a href="{:url('add')}" class="layui-btn layui-btn-primary"><i class="aicon ai-tianjia"></i>添加</a>
        <!--<a data-href="{:url('status?table=atlas&val=1')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-qiyong"></i>启用</a>
        <a data-href="{:url('status?table=atlas&val=0')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-jinyong1"></i>禁用</a>-->
        <a data-href="{:url('del?table=sensitive')}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="aicon ai-jinyong"></i>删除</a>
    </div>
    <div class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <colgroup>
                <col width="50">
            </colgroup>
            <thead>
            <tr>
                <th><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="20px;">ID</th>
                <th width="300px;">敏感词</th>
                <th width="80px;" style="text-align: center">操作</th>
            </tr>
            </thead>
            <tbody>
            {volist name="data_list" id="vo"}
            <tr>
                <td><input type="checkbox" name="ids[]" class="layui-checkbox checkbox-ids" value="{$vo['id']}" lay-skin="primary"></td>
                <td>{$vo['id']}</td>
                <td>{$vo['sensitive']}</td>
                <td>
                    <div class="layui-btn-group">
                        <a data-href="{:url('del?table=sensitive&ids='.$vo['id'])}"  title="删除" class="layui-btn layui-btn-primary layui-btn-small j-tr-del"><i class="layui-icon">&#xe640;</i></a>
                    </div>
                </td>
            </tr>
            {/volist}
            </tbody>
        </table>
       <div class="pagination" style="float: left;">
            <!--<a href="{:url('admin/image/randclick?callback=rand')}" class="layui-btn layui-btn-primary j-iframe-pop fl">随机点击</a>
           <a href="{:url('admin/image/batch_edit')}" class="layui-btn layui-btn-primary j-iframe-poq fl" title="批量修改">批量修改</a>-->
       </div>
        {$pages}
    </div>
</form>