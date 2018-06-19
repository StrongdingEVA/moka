<?php

namespace Api\Controller;

use Think\Controller;

class BaseController extends Controller {

    public $appid = 0;
    public $result;
    public $isapp = FALSE;
    public $json = '';
    public $uid = 0;
    public $open_id = '';
    public $access_token = '';
    public $eid = '';  

    const SUCCESS_CODE = '1';
    const ERROR_CODE = '0';

    public function _initialize() {
        header("Content-Type:text/html; charset=utf-8");
        $this->appid = I('request.appid', 1, 'intval');
        $this->isapp = is_app(false);
        $this->access_token = I('request.os_access_token', '', 'op_t');
        $this->open_id = I('request.os_open_id', '', 'op_t');
        $this->eid = I('request.eid', '', 'op_t'); // 机器码
        is_jsonp() && $this->json = 'jsonp';
        $this->get=$_GET;

        $this->_init_user();
        $this->_check_signature();
        $this->_add_log();
        
        $config = api('Config/lists');
        C($config); //添加配置

        //$this->result = array('code' => self::SUCCESS_CODE, 'msg' => "成功", 'data' => (object) array());
    }

    /**
     * 判断登录
     * @param 
     */
    protected function checkLogin() {
        if (!$this->uid) {
            $this->result = array('code' => self::ERROR_CODE, 'msg' => "请登录", 'sts' => "1", 'data' => (object) array());
            exit();
        }
    }

    protected function coverRs($rs) {
        if (!is_array($rs) || !isset($rs['status'])) {
            $this->result = array('code' => self::ERROR_CODE, 'msg' => "格式错误", 'data' => (object) array());
        }

        $this->result['code'] = $rs['status'] ? self::SUCCESS_CODE : self::ERROR_CODE;
        isset($rs['info']) && $this->result['msg'] = $rs['info'];
        isset($rs['data']) && $this->result['data'] = $rs['data'];
        if (APP_DEBUG && !$rs['status'] && isset($rs['error'])) {
            $this->result['error'] = $rs['error'];
        }
    }

    /**
     * access_token和open_id验证并登录
     */
    protected function checkToken($open_id, $access_token) {
        $res = D('Common/MemberOpenid')->getOpenid($open_id);
        if (!$res) {
            return array('status' => 0, 'info' => '该open_id不存在');
        }
        if ($res['access_token'] != $access_token) {
            $time = friendlyDate($res['update_time']);
            $user_info = query_user(array('mobile'), $res['uid']);
            return array('status' => 2, 'info' => '您的帐号' . $user_info['mobile'] . '在' . $time . '使用另外一台手机登录。如非本人操作，则密码可能已泄露。建议前往修改密码');
        }
//        if (!D('Common/UserOpenid')->checkAccessToken($res['update_time'])) {
//            return array('status' => 0, 'info' => '该access_token已过期');
//        }
        if (!D('Common/Member')->login($res['uid'])) {
            return array('status' => 0, 'info' => '登录失败');
        }
        return array('status' => 1, 'info' => '');
    }

    /**
     * 验证签名
     */
    private function _check_signature() {
        $signdate = $_REQUEST;
        $signature = $this->_sign($signdate);
        $sign = trim($_REQUEST['sign']);
        
        // 参与签名验证的方法
       if ( in_array(strtolower(ACTION_NAME), array( 'sendsms' ))) {
           if ($sign != $signature) { // 签名不正确
               
               // 兼容安卓漏掉签名参数
               unset($signdate['appid'], $signdate['eid']);
               $signature = $this->_sign($signdate);
               
               if ($sign != $signature) {
                   $log['controller'] = CONTROLLER_NAME;
                   $log['action'] = ACTION_NAME;
                   $log['uid'] = $this->uid;
                   $log['ip'] = get_client_ip();
                   $log['create_time'] = time();
                   $log['content'] = json_encode(I('request.')).'-'.$sign.'-'.$signature;
                   isset($_SERVER['REQUEST_METHOD']) && $log['method'] = $_SERVER['REQUEST_METHOD'];
                   M('ApiAccessLog')->add($log);
                   
                   $this->result = array( 'code' => self::ERROR_CODE, 'msg' => '异常请求', 'data' => array() );
                   exit();
               }
           }
       }
    }

    private function _add_log() {
        if (strtolower(ACTION_NAME) == 'sendsms') {
            $log['controller'] = CONTROLLER_NAME;
            $log['action'] = ACTION_NAME;
            $log['uid'] = $this->uid;
            $log['ip'] = get_client_ip();
            $log['create_time'] = time();
            $log['content'] = json_encode(I('request.'));
            isset($_SERVER['REQUEST_METHOD']) && $log['method'] = $_SERVER['REQUEST_METHOD'];
            strlen($log['content'])<1000 && M('ApiAccessLog')->add($log);
        }
    }

    /**
     * @desc 对指定的数据进行签名请求
     * @param string $param，区分大小写
     * @return string 
     */
    private function _sign($param) {
        //$param['ma'] = $param['_URL_'][0].'/'.$param['_URL_'][1];
        unset($param['sign']);
        ksort($param);
        return md5(implode($param) . C('API_SECRET_KEY'));
    }
    /**
     * 初始化用户登录
     * @return type
     */
    private function _init_user() {
        $this->uid = $this->getUidByOpenId($this->open_id);
//        $this->uid = 1;
    }

    public function getUidByOpenId($openid = '') {
        if (empty($openid)) {
            return 0;
        }
        $res = D('Common/MemberOpenid')->getRowByOpenid($openid);
        return $res ? $res['uid'] : 0;
    }

    public function __destruct() {
        $this->ajaxReturn($this->result, $this->json);
    }

    public function __call($method, $args) { 
        $this->result = array('code' => self::ERROR_CODE, 'msg' => "错误:调用" . $method . '不存在', 'data' => (object) array());
    }   

}
