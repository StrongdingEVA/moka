<?php

return array(
    //'URL_404_REDIRECT' =>  'common/404', // 404 跳转页面 部署模式有效 
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/static/common',
        '__CSS__' => __ROOT__ . '/static/m/css',
        '__JS__' => __ROOT__ . '/static/m/js',
        '__IMG__' => __ROOT__ . '/static/m/images'
    ),
    'URL_PATHINFO_DEPR' => '/',
    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' => array(
        '/^(\/?)$/' => 'index/index',
        '/^download(\/?)$/' => 'index/download',
        '/^download\/(.*)$/' => 'index/download?channel=:1',
        '/^download2(\/?)$/' => 'index/download2',
        '/^help(\/?)$/' => 'help/index',
        '/^help\/(\d+)$/' => 'help/detail?id=:1',
        '/^activity\/(.*)(\/?)$/' => 'activity/:1',
        '/^ucenter(\/?)$/' => 'ucenter/index',
        '/^ucenter\/(.*)(\/?)$/' => 'ucenter/:1',
        '/^search(\/?)$/' => 'search/index',
        '/^search\/wp(\/?)$/' => 'search/wp',
        '/^search\/wp\/keyword\/(.*)(\/?)$/' => 'search/wp?keyword=:1',
        '/^article\/(\d+)(\/?)$/' => 'wp/newsDetail?id=:1',
        '/^article\/(\d+)(.*)(\/?)$/' => 'wp/newsDetail?id=:1',
        '/^special(\/?)$/' => 'wp/special',
        '/^special\/(\d+)$/' => 'wp/specialDetail?id=:1',
        '/^ac\/(\d+)$/' => 'wp/accountDetail?id=:1',
        '/^topic(\/?)$/' => 'topic/index',
        '/^topic\/(\d+)\/(\d+)\/(\d+)$/' => 'topic/contentDetail?topic=:1&source=:2&id=:3',
        '/^topic\/(\d+)$/' => 'topic/topicDetail?id=:1',
        '/^topic\/([p|o].*?)$/' => 'topic/index/:1',
        '/^topic\/fontsize(\/?)$/' => 'topic/fontsize',
        '/^topic\/special\/(\d+)$/' => 'topic/specialDetail?id=:1',
        '/^topic\/ajaxtopiclist(.*?)$/' => 'topic/ajaxTopicList:1',
        '/^topic\/ajaxarticlelist(.*?)$/' => 'topic/ajaxArticleList:1',
        '/^topic\/(\w+)(\/?)$/' => 'topic/index?code=:1',
        '/^topic\/(\w+)\/(.*?)$/' => 'topic/index/code/:1/:2',
        '/^wp\/(\d+)$/' => 'wp/detail?id=:1',
        '/^(\w+)$/' => 'wp/lists?code=:1',
    ),
    //微信分享配置
    'WEIXIN_CONFIG' => array(
        'appid' => 'wxc3c9ceef95f0ba0f',
        'appsecret' => 'b577261cff29448b4ab47739ec61d3f7',
    ),
);
