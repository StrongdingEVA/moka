<?php

/**
 * 路由配置
 */
return array(
    'URL_ROUTE_RULES' => array(
        '/^(wp|Wp)\/(.*)$/' => 'v1/Wp/:2',
        '/^(member|Member)\/(.*)$/' => 'V1/Member/:2',
        '/^(public|Public)\/(.*)$/' => 'V1/Public/:2',
        '/^(file|File)\/(.*)$/' => 'V1/File/:2',
        '/^(index|Index)\/(.*)$/' => 'V1/Index/:2',
        '/^(verify|Verify)\/(.*)$/' => 'V1/Verify/:2',
        '/^(Cd|cd)\/(.*)$/' => 'V1/Cd/:2',
    ),
);
