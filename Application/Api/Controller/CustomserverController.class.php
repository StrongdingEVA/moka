<?php

namespace Api\Controller;

/**
 * 客服消息服务
 *
 * @author Administrator
 *
 */
define('TOKEN', 'xiaoli');

class CustomserverController
{

    public function index()
    {
        $log = array();
        $log['controller'] = CONTROLLER_NAME;
        $log['action'] = ACTION_NAME;
        $log['uid'] = 0;
        $log['ip'] = get_client_ip();
        $log['create_time'] = time();
        $log['content'] = json_encode(I('request.')).$GLOBALS["HTTP_RAW_POST_DATA"];
        isset($_SERVER['REQUEST_METHOD']) && $log['method'] = $_SERVER['REQUEST_METHOD'];
        M('ApiAccessLog')->add($log);

        // 判断是否为认证　
        if (isset($_GET['echostr'])) {
            $this->valid(); // 如果认证去验证
        } else {
            $res = $this->responseMsg(); // 否则接收客户发送消息
        }
    }

    /**
     * 验证前置方法
     */
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()) {
            header('content-type:text');
            echo $echoStr;
            exit();
        } else {
            echo $echoStr . '+++' . TOKEN;
            exit();
        }
    }

    /**
     * 签名校验
     */
    private function checkSignature()
    {
        // 微信加密签名
        $signature = $_GET["signature"];
        // 时间戳
        $timestamp = $_GET["timestamp"];
        // 随机数
        $nonce = $_GET["nonce"];
        // 服务端配置的TOKEN
        $token = TOKEN;
        // 将token,时间戳,随机数进行字典排序
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        // 拼接字符串
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送客服消息
     */
    public function responseMsg()
    {
        // 接收来自小程序的客户消息JSON
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr) && is_string($postStr)) {
            // 禁止引用外部xml实体
            // libxml_disable_entity_loader(true);

            // $postObj = simplexml_load_string($postStr, 'SimpleXMLElement',
            // LIBXML_NOCDATA);
            $postArr = json_decode($postStr, true);
            if (!empty($postArr['MsgType']) && $postArr['MsgType'] == 'text') { // 文本消息
                $fromUsername = $postArr['FromUserName']; // 发送者openid
                $toUserName = $postArr['ToUserName']; // 小程序id
                $textTpl = array(
                    "ToUserName" => $fromUsername,
                    "FromUserName" => $toUserName,
                    "CreateTime" => time(),
                    "MsgType" => "transfer_customer_service"
                );
                exit(json_encode($textTpl));
            } elseif (!empty($postArr['MsgType']) && $postArr['MsgType'] == 'image') { // 图文消息
                $fromUsername = $postArr['FromUserName']; // 发送者openid
                $toUserName = $postArr['ToUserName']; // 小程序id
                $textTpl = array(
                    "ToUserName" => $fromUsername,
                    "FromUserName" => $toUserName,
                    "CreateTime" => time(),
                    "MsgType" => "transfer_customer_service"
                );
                exit(json_encode($textTpl));
            } elseif ($postArr['MsgType'] == 'event' && $postArr['Event'] == 'user_enter_tempsession') { // 进入客服动作
                $fromUsername = $postArr['FromUserName']; // 发送者openid
                $data = array(
                    "touser" => $fromUsername,
                    "msgtype" => "link",
                    "link" => array(
                        "title" => "点此进入", // 消息标题
                        "description" => "关注公众号，求片反馈在这滴滴我。", // 图文链接消息
                        "url" => "http://mp.weixin.qq.com/s/XdmW4ifGprGku6adP9lv5Q", // 图文链接消息被点击后跳转的链接
                        "thumb_url" => "http://img.xmyunyou.com/lALPBbCc1VW9o9_NAZfNAZg_408_407.png"// 缩略图的url
                    )
                );
                $json = json_encode($data, JSON_UNESCAPED_UNICODE); // php5.4+

                $access_token = $this->get_accessToken();

                // 以'json'格式POST发送https请求客服接口api
                $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                if (!empty($json)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
                }
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
                $output = curl_exec($curl);
                if (curl_errno($curl)) {
                    echo 'Errno' . curl_error($curl); // 捕抓异常
                }
                curl_close($curl);
                if ($output == 0) {
                    echo 'success';
                }
            } else {
                exit('aaa');
            }
        } else {
            exit();
        }
    }

    /**
     * 调用微信api，获取access_token，有效期7200s -xzz0704
     */
    protected function get_accessToken()
    {
        /* 在有效期，直接返回access_token */
        if (S('access_token')) {
            return S('access_token');
        }         /* 不在有效期，重新发送请求，获取access_token */
        else {
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . C('WXXCX_CONFIG.appid') . '&secret=' . C('WXXCX_CONFIG.secret');
            $result = curl_get_content($url);
            $res = json_decode($result, true); // json字符串转数组

            if ($res) {
                S('access_token', $res['access_token'], 7100);
                return S('access_token');
            } else {
                return 'api return error';
            }
        }
    }

}