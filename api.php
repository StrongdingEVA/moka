<?php

/**
 * 调试开关
 * 项目正式部署后请设置为false
 */
define('APP_DEBUG', true);

if (!form_app()) {
    $orgin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
    header('Access-Control-Allow-Origin:' . $orgin);
    // 响应类型  
    header('Access-Control-Allow-Methods:POST,GET');
    // 响应头设置  
    header('Access-Control-Allow-Headers:x-requested-with,content-type');
    header('Access-Control-Allow-Credentials:true');
    !APP_DEBUG && check_domain();
}

function check_domain() {
    $host = strtolower($_SERVER['HTTP_HOST']);
    $pos =$host;// substr($host, -4);
    if (!in_array($pos, array('moka.com','www.guimizone.com','localhost','127.0.0.1','10.0.0.188:8114','www.moka.com'))) {
        header('HTTP/1.1 403 Forbidden');
        header("status: 403 Forbidden");
        exit;
    }
}

function form_app() {
    if (isset($_SERVER['HTTP_X_SOURCE']) && strpos(strtolower($_SERVER['HTTP_X_SOURCE']), 'app') !== false) {
        return TRUE;
    }
    return FALSE;
}

//绑定模块
define('BIND_MODULE', 'Api');
define('APP_MODE', 'api');
define('APP_PATH', './Application/');
define('RUNTIME_PATH', './Runtime/');
define('STATIC_PATH', './static/');
require './ThinkPHP/ThinkPHP.php';