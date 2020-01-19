<style>
    .layui-table[lay-skin=row] td, .layui-table[lay-skin=row] th{
        text-align: left;
    }
</style>
<div class="page-toolbar" >
    <div class="layui-btn-group fl">

    </div>
</div>
<div style="width: 96%;height: 30px;display:flex;margin: auto;">
		<div style="width: 33%;">总消费金额:{$price['ptotal']}</div>
		<div style="width: 33%;">近7日消费金额:{$price['pmonth']}</div>
		<div style="width: 33%;">今日消费金额:{$price['pday']}</div>
</div>
<form id="pageListForm">
    <div class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <colgroup>
                <col width="50">
            </colgroup>
            <thead>
            <tr>
                <th><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th>ID</th>
                <th>会员</th>
                <th>消费金币</th>
                <th>消费内容</th>
                <th>消费时间</th>
            </tr>
            </thead>
            <tbody>
            {volist name="data_list" id="vo"}

            <tr>
                <td><input type="checkbox" name="ids[]" class="layui-checkbox checkbox-ids" value="{$vo['id']}" lay-skin="primary"></td>
                <td class="font12">
                {$vo['id']}
                </td>
                <td class="font12">
                    <img src="{if condition="$vo['headimgurl']"}{$vo['headimgurl']}{else /}__ADMIN_IMG__/avatar.png{/if}" width="60" height="60" class="fl" onerror="this.src='__ADMIN_IMG__/avatar.png'">
                    <p class="ml10 fl">昵称：<strong class="mcolor">{$vo['nickname']} </strong></p>
                </td>
                <td class="font12">{$vo['gold']}</td>
                <td class="font12">
                    <p class="ml10 fl"><strong class="mcolor">{$title}：{$vo['title']} </strong><br>分类：{$vo['name']}<br></p>
                </td>
                <td class="font12">{:date('Y-m-d H:i:s', $vo['view_time'])}</td>
            </tr>
            {/volist}
            </tbody>
        </table>
        {$pages}
    </div>
</form>
