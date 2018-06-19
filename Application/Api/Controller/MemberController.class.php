<?php

namespace Api\Controller;

class MemberController extends BaseController {

    /**
     * 第三方同步登录
     */
    public function syncOauthLogin() {
        $http_request_mode = 'request';
        $param = array();
        $param['open_id'] = I($http_request_mode . '.open_id', '', 'op_t');
        $param['access_token'] = I($http_request_mode . '.access_token', '', 'op_t');
        $param['nickname'] = I($http_request_mode . '.nickname', '', 'op_t');
        $param['sex'] = I($http_request_mode . '.sex', 1, 'intval');
        $param['headimg'] = I($http_request_mode . '.headimg', '', 'op_t');
        $param['type'] = I($http_request_mode . '.type', '', 'op_t');
        $param['unionid'] = I($http_request_mode . '.unionid', '', 'op_t');
        
        if($param['type'] == 'wxxcx' && !$param['unionid']){

            $appid = C('WXXCX_CONFIG.appid');
            $secret = C('WXXCX_CONFIG.secret');
            $secret = '35afb3ef0f093bd78e4ca7dbd4e2cd4d';
            $code = $param['access_token'];

            $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
            $res = $this->getInfo($url);// 异常{"errcode":40163,"errmsg":"code been used, hints: [ req_id: MYgAsA0978th48 ]"}
            $res = json_decode($res, true);

            if(!$res['openid']){
                $this->result = array('code' => self::ERROR_CODE, 'msg' => "code无效", 'data' => (object) array());
                exit;
            }

            $param['open_id'] = $res['openid'];
//            $res = array(
//                'openid' => 'E824CA4A-E4EC-DDF5-D964-40E51A32F564',
//                'unionid' => 'A9i4zYUuZdNrn7CQPhvGFH61qcVIMfg2alLtyK5S'
//            );
//            $param['open_id'] = $res['openid'];

            $encryptedData = I($http_request_mode . '.encryptedData', '', 'op_t');
            $iv = I($http_request_mode . '.iv', '', 'op_t');
            
            if($res['unionid']){
                $param['unionid'] = $res['unionid'];
            }elseif(!$res['unionid'] && $encryptedData && $iv){
                import('Vendor.Wxxcx.wxBizDataCrypt');
                
                $sessionKey = $res['session_key'];
                $pc = new \WXBizDataCrypt($appid, $sessionKey);
                $errCode = $pc->decryptData($encryptedData, $iv, $json );

                /*
                 * 正常返回数据示例
                 {
                 "openId": "oGZUI0egBJY1zhBYw2KhdUfwVJJE",
                 "nickName": "Band",
                 "gender": 1,
                 "language": "zh_CN",
                 "city": "Guangzhou",
                 "province": "Guangdong",
                 "country": "CN",
                 "avatarUrl": "http://wx.qlogo.cn/mmopen/vi_32/aSKcBBPpibyKNicHNTMM0qJVh8Kjgiak2AHWr8MHM4WgMEm7GFhsf8OYrySdbvAMvTsw3mo8ibKicsnfN5pRjl1p8HQ/0",
                 "unionId": "ocMvos6NjeKLIBqg5Mr9QjxrP1FA",
                 "watermark": {
                 "timestamp": 1477314187,
                 "appid": "wx4f4bc4dec97d474b"
                 }
                 }
                 */
                if ($errCode == 0) {
                    $wxinfo = json_decode($json, true);
                    $param['unionid'] = $wxinfo['unionId'];
                }
            }else{
                $param['unionid'] = '';
            }
        }

        $rs = D('Common/Member', 'Logic')->sync_oauth_register($param);
        if (!$rs['status']) {
            $this->coverRs($rs);
            return;
        }
        $uid = $rs['data'];
        $data = query_user(array('uid', 'thumb', 'nickname', 'mobile', 'username', 'sex'), $uid);

        //获取用户open_id和token
        $openId = D('Common/Member', 'Logic')->getOpenid($uid);
        $data['access_token'] = isset($openId['data']['access_token']) ? $openId['data']['access_token'] : '';
        $data['open_id'] = isset($openId['data']['open_id']) ? $openId['data']['open_id'] : '';
        $this->result['data'] = $data;
    }

    public function getInfo($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);  //0表示不输出Header，1表示输出
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);
        return $data;
    }
}

?>