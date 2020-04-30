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
        <!--<a href="{:url('add')}" class="layui-btn layui-btn-primary"><i class="aicon ai-tianjia"></i>添加</a>
        <a data-href="{:url('status?table=atlas&val=1')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-qiyong"></i>启用</a>
        <a data-href="{:url('status?table=atlas&val=0')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-jinyong1"></i>禁用</a>-->
        <a data-href="{:url('del?table=chat')}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="aicon ai-jinyong"></i>删除</a>
    </div>
    <div  style="margin-left: 10px; display: inline-block;float: right; ">
    	<div class="layui-input-inline">
            <select name="select2" lay-verify="">
                <option value="1" {if condition="$select2 eq 1"}  selected="selected"{/if}>发送者</option>
                <option value="2" {if condition="$select2 eq 2"}  selected="selected"{/if}>接收者</option>
            </select>
        </div>
        <div class="layui-input-inline">
            <select name="select" lay-verify="">
                <option value="1" {if condition="$select eq 1"}  selected="selected"{/if}>内容</option>
                <option value="2" {if condition="$select eq 2"}  selected="selected"{/if}>昵称</option>
                <option value="3" {if condition="$select eq 3"}  selected="selected"{/if}>账号</option>
            </select>
        </div>
    <div class="layui-input-inline">
        <input type="text" name="key" value="{$keys}" placeholder="请输入关键词搜索" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-input-inline">
        <input class="layui-btn"  type="submit" value="搜索"/>
    </div>
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
                <th width="300px;">发送者</th>
                <th width="300px;">接收者</th>
                <th width="350px;">内容</th>
                <th width="140px;">发送时间</th>
                <th width="140px;">更新时间</th>
                <th width="80px;">审核状态</th>
                <th width="80px;" style="text-align: center">操作</th>
            </tr>
            </thead>
            <tbody>
            {volist name="data_list" id="vo"}
            <tr>
                <td><input type="checkbox" name="ids[]" class="layui-checkbox checkbox-ids" value="{$vo['cid']}" lay-skin="primary"></td>
                <td>{$vo['cid']}</td>
                <td>账号:{$vo['susername']}<br/>昵称:{$vo['snickname']}</td>
                <td>账号:{$vo['tusername']}<br/>昵称:{$vo['tnickname']}</td>
                <td>{$vo['ccon']}</td>
                <td>{:date('Y-m-d',$vo['ctime'])}</td>
                <td>{:date('Y-m-d',$vo['cltime'])}</td>
                <td>
                	
                    {if condition="$vo['cstatus'] eq 0"}
                    <span style="color:blue">未处理</span>
                    {/if}
                    {if condition="$vo['cstatus'] eq 1"}
                    <span style="color:green">已通过</span>
                    {/if}

                    {if condition="$vo['cstatus'] eq 2"}
                    <span style="color:red">已拒绝</span>
                    {/if}
                                    </td>
                
                <td>
                    <div class="layui-btn-group">
                        <a data-href="{:url('del?table=chat&ids='.$vo['cid'])}"  title="删除" class="layui-btn layui-btn-primary layui-btn-small j-tr-del"><i class="layui-icon">&#xe640;</i></a>
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