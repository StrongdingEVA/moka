<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mob\Controller;

use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller
{

    var $uid = 0;

    protected function _initialize() {
        $this->uid = is_login();
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        if (!C('WEB_SITE_CLOSE')) {
            $this->error('站点已经关闭，请稍后访问~');
        }
        header("Content-Type:text/html; charset=utf-8");
        $this->isapp = is_app(false);
        $this->assign('isapp', $this->isapp);
        $this->assign('apple_touch_icon', '__IMG__/favicon2.ico');
        $this->assign('product', C('PRODUCT'));

        // 用户校验
        $uid = is_login();
        if ($uid) {
            // 用户基本信息
            $user = query_user(array('uid', 'sex', 'thumb', 'nickname'), $uid);
            // 获取用户消息
            $msg_info = D('Common/Member', 'Logic')->get_message_info($uid);
            if ($msg_info['status'] == 1) {
                $user['msg'] = $msg_info['data'];
            }
            $this->assign('user', $user);
        }
    }

    /**
     *  空操作，用于输出404页面 
     */
    public function _empty() {
        header("HTTP/1.0 404 Not Found");
        $this->display("Common:404");
        exit;
    }

    /**
     *  seo数据处理优化
     */
    protected function _seo($title = '', $keywords = '', $description = '', $data = array()) {
        if (!$title) {
            $rs = LC('Common/Seo')->get_seo_info(MODULE_NAME, CONTROLLER_NAME, ACTION_NAME);
            if ($rs['status'] == 1) {
                $title = $rs['data']['seo_title'];
                $keywords = $rs['data']['seo_keywords'];
                $description = $rs['data']['seo_description'];
                if ($data && is_array($data)) {
                    foreach ($data as $k => &$v) {
                        $title = str_replace('[' . $k . ']', $v, $title);
                        $keywords = str_replace('[' . $k . ']', $v, $keywords);
                        $description = str_replace('[' . $k . ']', $v, $description);
                    }
                    unset($v);
                }
            }
        }
        $this->assign('seo_title', $title);
        $this->assign('seo_keywords', $keywords);
        $this->assign('seo_description', $description);
        return;
    }
    
    /**
     * 微信分享
     * @param title 分享标题
     * @param link 分享链接
     * @param imgurl 分享图片
     * @param desc 分享描述
     * @return type
     */
    public function _share($data) {
        // 分享 微信jssdk
        $wx_config = C('WEIXIN_CONFIG');
        $jssdk = new \Org\Util\Jssdk($wx_config['appid'], $wx_config['appsecret']);
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);
        $this->assign('share', $data);
        return;
    }

    public function is_login() {
        if (!$this->uid) {
            $this->error('请先登录！');
        }
    }

    /**
     *  用户登录检测 
     */
    protected function check_login() {
        is_login() || $this->redirect(U('ucenter/login'));
    }

}
