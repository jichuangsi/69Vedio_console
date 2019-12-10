<?php
/**
 * 请求处理中间件
 * Author: weiyongqiang<hayixia606@163.com>
 * Date: 2018/9/27
 * Time: 11:02
 */

namespace app\http\middleware;

use think\Response;

class CrossDomain
{
    public function handle($request, \Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Max-Age: 1728000');
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        return $next($request);
    }
}