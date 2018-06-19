<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', false);

// 应用入口文件
define('BIND_MODULE','Cli');

define('RUNTIME_PATH', dirname(__FILE__) . '/Runtime/');

define('APP_MODE', 'cli');
// 定义应用目录
define('APP_PATH', dirname(__FILE__) . '/Application/');

// 引入ThinkPHP入口文件
require dirname(__FILE__) . '/ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单