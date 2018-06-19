<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE' => 'Home',
    'MODULE_DENY_LIST' => array('Common', 'User', 'Admin'),
    'MODULE_ALLOW_LIST' => array('Home', 'Admin', 'Mob', 'Api', 'Cli'),
    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => ':y`_iE7azQJ4m+qQz|N[t*6h!B<#~.r;5Z^8D){0$]', //默认数据加密KEY
    'DATA_CACHE_KEY' => ':y`_iE7azQJ4m+qQz|N[t*6h!B<#~.r;5Z^8D){0$]',
    /* 完整域名部署 */
    'APP_SUB_DOMAIN_DEPLOY' => 0, // 开启子域名配置
    'APP_SUB_DOMAIN_RULES' => array(
    ),
    /* 扩展配置 */
    'LOAD_EXT_CONFIG' => 'router,router_rule',
    /* 路由配置 */
    'URL_PATHINFO_DEPR' => '/',
    'URL_ROUTER_ON' => true,
    //必须以/结尾
    'BASE_URL' => 'http://www.guimizone.com/',
    'MOBILE_URL' => 'http://www.guimizone.com/',
    /* 调试配置 */
    'SHOW_PAGE_TRACE' => false,
    !APP_DEBUG && 'TMPL_EXCEPTION_FILE' => '/static/common/404.html',
    /* 用户相关设置 */
    'USER_MAX_CACHE' => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    /* 主题设置  */
    'DEFAULT_THEME' => 'default', // 默认模板主题名称

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL' => 2, //URL模式
    'VAR_URL_PARAMS' => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR' => '/', //PATHINFO URL分割符
    'URL_HTML_SUFFIX' => '', // URL伪静态后缀设置

    /* 全局过滤配置  */
    'DEFAULT_FILTER' => '', //全局过滤函数

    /* 数据库配置 */
    'DB_TYPE' => 'mysqli', // 数据库类型
    'DB_HOST' => '10.0.0.188', // 服务器地址
    'DB_NAME' => 'moka', // 数据库名
    'DB_USER' => 'icner', // 用户名
    'DB_PWD' => 'Eovhd^9Edd139E', // 密码
    'DB_PORT' => '3306', // 端口
    'DB_PREFIX' => 'mk_', // 数据库表前缀
    'DB_CHARSET' => 'utf8mb4',

    /* UC数据库配置 */
    'DB_UCENTER' => array(
        'db_type' => 'mysqli', // 数据库类型
        'db_host' => '10.0.0.188', // 服务器地址
        'db_name' => 'moka', // 数据库名
        'db_user' => 'icner', // 用户名
        'db_pwd' => 'Eovhd^9Edd139E', // 密码
        'db_port' => '3306', // 端口
        'db_prefix' => 'mk_', // 数据库表前缀
        'db_charset' => 'utf8mb4',
    ),
    //分表
    'DB_PARTITION_NUM' => 2,
    /* session设置 */
    'SESSION_OPTIONS' => array(
        'type' => 'db', // session采用数据库保存
        'expire' => 3600, // session过期时间，如果不设就是php.ini中设置的默认值
    ),
    'SESSION_TABLE' => 'mk_session', // 必须设置成这样，如果不加前缀就找不到数据表，这个需要注意
    //Redis Session配置
    'SESSION_AUTO_START'    =>  true,    // 是否自动开启Session
    'SESSION_TYPE'          =>  'File',    //session类型
    'SESSION_PERSISTENT'    =>  1,        //是否长连接(对于php来说0和1都一样)
    'SESSION_CACHE_TIME'    =>  1,        //连接超时时间(秒)
    'SESSION_EXPIRE'        =>  0,        //session有效期(单位:秒) 0表示永久缓存
    'SESSION_PREFIX'        =>  'mk_sess_',        //session前缀
    'SESSION_REDIS_HOST'    =>  '10.0.0.188', //分布式Redis,默认第一个为主服务器
    'SESSION_REDIS_PORT'    =>  '6379',           //端口,如果相同只填一个,用英文逗号分隔
    //'SESSION_REDIS_AUTH'    =>  'redis',    //Redis auth认证(密钥中不能有逗号),如果相同只填一个,用英文逗号分隔

    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX' => 'mk_', //缓存前缀
    'DATA_CACHE_TIME' => 86400, // 数据缓存有效期 0表示永久缓存
    'REDIS_RW_SEPARATE' => false, //Redis读写分离 true 开启
    'REDIS_HOST' => '10.0.0.188', //redis服务器ip，多台用逗号隔开；读写分离开启时，第一台负责写，其它[随机]负责读；
    'REDIS_PORT' => '6379', //端口号
    'REDIS_TIMEOUT' => '3600', //超时时间
    'REDIS_PERSISTENT' => false, //是否长连接 false=短连接
    'REDIS_AUTH' => '', //AUTH认证密码
    'DATA_CACHE_TYPE' => 'File',

    /* 文档模型配置 (文档模型核心配置，请勿更改) */
    'DOCUMENT_MODEL_TYPE' => array(2 => '主题', 1 => '目录', 3 => '段落'),
    /* 文件上传相关配置 */
    'DOWNLOAD_UPLOAD' => array(
        'mimes' => '', //允许上传的文件MiMe类型
        'maxSize' => 5 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,mp4,swf', //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => 'Uploads/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //下载模型上传配置（文件上传类配置）
    //图片上传相关配置（文件上传类配置）
    'DOWNLOAD_UPLOAD_DRIVER' => 'local',
    //API请求接口签名字串KEY
    'API_SECRET_KEY' => '',
    //微信小程序配置
    'WXXCX_CONFIG' => array(
        'appid' => 'wxd66cfe40e5128ff3',
        'secret' => '35afb3ef0f093bd78e4ca7dbd4e2cd4d',
        'mch_id' => '1500726811',
        'key' => '91350211MA31PF5C04707405783brton'
//        'appid' => 'wx3bc4bc80a92c4a48',
//        'secret' => 'ade687db14fd669f096057daf2472118',
//        'mch_id' => '1507303581',
//        'key' => '91350211MA31PF5C0476886559brtone'
    ),
    //腾讯云配置
    'BUCKETNAME' => 'moka-02',
    'TENCENTCLOUND' => array(
        'COS_APPID' => '1251036542',
        'COS_KEY' => 'AKID14790g8TaaM1azIck5UipSzKVfeRPM5b',
        'COS_SECRET' => 'TlrlnjUwLfnjTjspeHjz4xDMWUGqFTVW',
        'COS_REGION' => 'cn-east'
    ),
    'IMGDOMAIN' => 'https://img.guimizone.com'
);
