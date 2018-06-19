<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------
namespace Addons\SiteStat;
use Common\Controller\Addon;

/**
 * 系统环境信息插件
 * @author thinkphp
 */
class SiteStatAddon extends Addon{

    public $info = array(
        'name'=>'SiteStat',
        'title'=>'站点统计信息',
        'description'=>'统计站点的基础信息',
        'status'=>1,
        'author'=>'thinkphp',
        'version'=>'0.1'
    );

    public function install(){
        return true;
    }

    public function uninstall(){
        return true;
    }

    //实现的AdminIndex钩子方法
    public function AdminIndex($param){
        $config = $this->getConfig();
        $this->assign('addons_config', $config);
        if($config['display']){
            $info['user']		=	D('Member')->count();
            $info['action']		=	D('ActionLog')->count();
            $info['document']	=	D('Document')->count();
            $info['category']	=	D('Category')->count();
            $info['model']		=	D('Model')->count();
            $this->assign('info',$info);
            $this->display('info');
        }
    }
}