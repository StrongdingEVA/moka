<?php

namespace Common\Logic;

/**
 * 短信逻辑
 */
class PublicLogic {

    /**
     * 发短信
     * author lcc
     * type (register注册 findpassword 忘记密码 bind 绑定 weixin_register 微信注册 forget_paypwd 忘记交易密码)
     */
    public function sms_send($mobile) {
        if (!check_mobile($mobile)) {
            return array('status' => 0, 'info' => '手机格式不正确', 'data' => (object) array());
        }

        $time = time();
        $resend_time = 60; // 验证码发送间隔时间，默认60秒       
        $last_time = S(C('DATA_CACHE_TWO_PREFIX') . 'verify_time_' . $mobile);
        if ($time <= $last_time + $resend_time) {
            return array('status' => 0, 'info' => '请' . ($resend_time - ($time - $last_time)) . '秒后再发送', 'data' => (object) array());
        }

        // 手机号限制
        $count = S(C('DATA_CACHE_TWO_PREFIX') . 'verify_count_' . $mobile) ? : 0;
        if ($count >= 10) {
            return array('status' => 0, 'info' => '您的短信请求过于频繁，请明天再试', 'data' => (object) array());
        }
        // ip限制
        $ip = get_client_ip();
        $ip_count = S(C('DATA_CACHE_TWO_PREFIX') . 'verify_ip_count_' . $ip) ? : 0;
        if ($ip_count >= 10) {
            return array('status' => 0, 'info' => '您的短信请求过于频繁，请明天再试', 'data' => (object) array());
        }
        // 发送验证码
        $verify = D('Common/Verify')->addVerify($mobile, 'mobile');
        if (!$verify) {
            return array('status' => 0, 'info' => '发送失败', 'data' => (object) array());
        }

        $res = '发送失败';
        if (is_array($verify)) {
            $res = sendSMS($mobile, $verify['verify'], $verify['code']);
        } else {
            $res = sendSMS($mobile, $verify);
        }
        if ($res === true) {
            S('verify_time_' . $mobile, $time);
            S(C('DATA_CACHE_TWO_PREFIX') . 'verify_count_' . $mobile, $count + 1, strtotime(date('Y-m-d 23:59:59', $time)) - $time);
            S(C('DATA_CACHE_TWO_PREFIX') . 'verify_ip_count_' . $ip, $ip_count + 1, strtotime(date('Y-m-d 23:59:59', $time)) - $time);
            return array('status' => 1, 'info' => '发送成功', 'data' => array('code' => $verify['code']));
        } else {
            return array('status' => 0, 'info' => $res, 'data' => (object) array());
        }
    }

    /**
     * 验证短信
     */
    public function sms_verify($mobile, $code) {
        if (D('Common/Verify')->checkVerify($mobile, 'mobile', $code, 0)) {
            return true;
        }
        return false;
    }

}
