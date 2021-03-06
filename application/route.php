<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::domain('*','/');

//Route::rule('share/:id','/share/share/number/:id');    //分享
//Route::rule('share/:id/:code','/app/download/u/:id/c/:code');    //分享
Route::rule('share/:id','/app/download/u/:id');    //分享

return [
    '__pattern__' => [
        'name' => '\w+',
        'id'=>'\w+',
        //'code'=>'\d+'
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
