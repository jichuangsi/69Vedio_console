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
        <a href="{:url('upload')}" class="layui-btn layui-btn-primary"><i class="aicon ai-tianjia"></i>添加</a>
        <a data-href="{:url('khstatus?table=video&val=1')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-qiyong"></i>启用</a>
        <a data-href="{:url('khstatus?table=video&val=0')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-jinyong1"></i>禁用</a>
        <a data-href="{:url('khdel?table=video')}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="aicon ai-jinyong"></i>删除</a>
    </div>
<div  style="margin-left: 10px; display: inline-block;float: right; ">
        <div class="layui-input-inline">
            <select name="select" lay-verify="">
                <option value="1"{if condition="$select eq 1"}  selected="selected"{/if}>视频ID</option>
                <option value="2" {if condition="$select eq 2"}  selected="selected"{/if}>视频名称</option>
                <option value="3" {if condition="$select eq 3"}  selected="selected"{/if}>关键字</option>
            </select>
        </div>
    <div class="layui-input-inline">
        <input type="text" name="key" value="{$keys}" placeholder="请输入关键词搜索" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-input-inline">
        <select name="class" lay-verify="">
            <option value="0">请选择分类</option>
            {volist name="classlist" id="v" }
            <option value="{$v['id']}" {if condition="$cla eq $v['id']"}  selected="selected"{/if}>|-{$v['name']}</option>
            {volist name="v['childs']" id="vv" }
            <option value="{$vv['id']}" {if condition="$cla eq $vv['id']"}  selected="selected"{/if}>&nbsp;&nbsp;&nbsp;&nbsp;|-{$vv['name']}</option>
            {/volist}
            {/volist}
        </select>
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
                <th width="40px;">ID</th>
                <th width="70px;">缩略图</th>
                <th width="300px;">视频标题</th>
                <th width="70px;">分类</th>
                <th width="70px;">所属会员</th>
                <th width="40px;">点击</th>
                <th width="40px;">点赞数</th>
                <th width="50px;">观看金币</th>
                <th width="90px;">更新时间</th>
                <th width="60px;">是否推荐</th>
                <th width="60px;">显示状态</th>
                <th width="200px;" style="text-align: center">操作</th>
            </tr>
            </thead>
            <tbody>
            {volist name="data_list" id="vo"}
            <tr>
                <td><input type="checkbox" name="ids[]" class="layui-checkbox checkbox-ids" value="{$vo['id']}" lay-skin="primary"></td>
                <td>{$vo['id']}</td>
                <td>
                        <a href="{$vo['thumbnail']}" target="pic">
                        <img height="30" src="{$vo['thumbnail']}" onmouseover="imgTips(this,{width:300})">
                        </a>
                </td>
                <td>{$vo['title']}</td>
                <td>{$vo['class']}</td>
                <td>{$vo['user_id']}</td>
                <td>{$vo['click']}</td>
                <td>{$vo['good']}</td>
                <td>{$vo['gold']}</td>
                <td>{:date('Y-m-d',$vo['update_time'])}</td>
                <td>
                    <input type="checkbox"  {if condition="$vo['recommend'] eq 1"}checked=""{/if} value="{$vo['recommend']}" lay-skin="switch" lay-filter="switchStatus" lay-text="是|否" data-href="{:url('khrecommend?table=video&ids='.$vo['id'])}">
                </td>
                <td>
                    <input type="checkbox"  {if condition="$vo['status'] eq 1"}checked=""{/if} value="{$vo['status']}" lay-skin="switch" lay-filter="switchStatus" lay-text="正常|关闭" data-href="{:url('khstatus?table=video&ids='.$vo['id'])}">
                </td>
                <td>
                    <div class="layui-btn-group">
                        <a href="{:url('admin/video/edit',['id'=>$vo['id']])}" class="layui-btn layui-btn-primary layui-btn-small"><i class="layui-icon">&#xe642;</i></a>
                        <a data-href="{:url('khdel?table=video&id='.$vo['id'])}" class="layui-btn layui-btn-primary layui-btn-small j-tr-del"><i class="layui-icon">&#xe640;</i></a>
                        <a href="{:url('admin/video/play',['id'=>$vo['id']])}" class="layui-btn layui-btn-primary layui-btn-small j-iframe-poq" title="预览视频-《{$vo['title']}》">预览</a>
                    </div>
                </td>
            </tr>
            {/volist}
            </tbody>
        </table>
        <div class="pagination" style="float: left;">
            <a href="{:url('admin/video/randclick?callback=rand')}" class="layui-btn layui-btn-primary j-iframe-pop fl" title="随机点击">随机点击</a>
            <a href="{:url('admin/video/batch_edit')}" class="layui-btn layui-btn-primary j-iframe-poq fl" title="批量修改">批量修改</a>
       </div>
        {$pages}
    </div>
</form>