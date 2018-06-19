<?php

namespace Addons\SyncLogin\Controller;

use Think\Hook;
use User\Api\UserApi;
use Home\Controller\AddonsController;

require_once(dirname(dirname(__FILE__)) . "/ThinkSDK/ThinkOauth.class.php");

class BaseController extends AddonsController
{

    private $access_token = '';
    private $openid = '';
    private $type = '';
    private $token = array();

    public function _initialize() {
        $session = session('SYNCLOGIN');
        $this->token = $session['TOKEN'];
        $this->type = $session['TYPE'];
        $this->openid = $session['OPENID'];
        $this->access_token = $session['ACCESS_TOKEN'];
    }

    //登陆地址
    public function login() {
        $type = I('get.type');
        empty($type) && $this->error('参数错误');
        //加载ThinkOauth类并实例化一个对象
        $sns = \ThinkOauth::getInstance($type);
        //跳转到授权页面
        redirect($sns->getRequestCodeURL());
    }

    /**
     * callback  登陆后回调地址
     * 
     */
    public function callback() {
        $code = I('get.code');
        $type = I('get.type');
        $sns = \ThinkOauth::getInstance($type);

        //腾讯微博需传递的额外参数
        $extend = null;
        if ($type == 'tencent') {
            $extend = array('openid' => I('get.openid'), 'openkey' => I('get.openkey'));
        }

        $token = $sns->getAccessToken($code, $extend);

        if (empty($token)) {
            $this->error('参数错误');
        }
        $session = array('TOKEN' => $token, 'TYPE' => $type, 'OPENID' => $token['openid'], 'ACCESS_TOKEN' => $token['access_token']);
        session('SYNCLOGIN', $session);

        if (is_login()) {
            $this->error('已登录，请刷新页面重试！', U('Home/Index/index'));
        }
        $user_info = D('Addons://SyncLogin/Info')->$type($token);
        $param = array();
        $param['open_id'] = $token['openid'];
        $param['access_token'] = $token['access_token'];
        $param['nickname'] = $user_info['nick'];
        $param['sex'] = $user_info['sex'];
        $param['headimg'] = $user_info['head'];
        $param['type'] = $type;
        $rs = D('Common/Member', 'Logic')->sync_oauth_register($param);
        if (!$rs['status']) {
            $this->error('登录失败，请稍后再试！', U('Home/Index/index'));
            return;
        }

        if (session('_curUrl_')) {
            redirect(cookie('_curUrl_'));
        } else {
            $this->redirect('/ucenter');
        }
    }

}
