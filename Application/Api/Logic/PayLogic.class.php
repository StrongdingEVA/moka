<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/5 0005
 * Time: 11:52
 */
namespace Api\Logic;

use Think\Model;

class PayLogic{
    //统一下单接口
    public function unifiedorder($uid,$rewarder,$amount){
        $memberModel = D('Common/SyncLogin');
        $open_id = $memberModel->getOpenIdByUidAndType($uid,'wxxcx');
        if(!$open_id){
            return array('status' => 0,'info' => '获取唯一标识错误');
        }

        if(!$amount){
            return array('status' => 0,'info' => '订单金额不能为0');
        }
        $order_no = $this->getOrderNo();
        $param['order_no'] = $order_no;
        $param['body'] = '抽奖助手';
        $param['total_fee'] = $amount * 100;
        $param['ip'] = get_client_ip();
        $param['notify_url'] = $_SERVER['SERVER_NAME'] . '/cli.php/UnfiedOrder/unfieCallBack';
        $param['trade_type'] = 'JSAPI';
        $param['openid'] = $open_id;
        $result = $this->doUnfiedOrder($param);

        if($result['return_code'] == 'SUCCESS'){
            if($result['result_code'] == 'SUCCESS'){
                $prepay_id = $result['prepay_id'];
                //生成订单
                $res = D('Common/UnfiedOrder')->saveData(array('order_no' => $order_no,'uid' => $uid,'rewarder' => $rewarder,'amount' => $amount,'wx_no' => $prepay_id));
                if($res){
                    $wechatConfig = C('WXXCX_CONFIG');
                    $data['appId'] = $wechatConfig['appid'];
                    $data['timeStamp'] = time();
                    $data['nonceStr'] = create_rand(15);
                    $data['package'] = 'prepay_id=' . $prepay_id;
                    $data['signType'] = 'MD5';
                    $sign = $this->getSign($data);
                    $data['sign'] = $sign;
                    return array('status' => 1,'info' => '下单成功','data' => array('prepay_id' => $prepay_id,'oid' => $res,'param' => $data));
                }else{
                    return array('status' => 0,'info' => '生成订单失败');
                }
            }else{
                return array('status' => 0,'info' => $this->getRrroMsg($result['err_code']));
            }
        }else{
            return array('status' => 0,'info' => $result['return_msg']);
        }
    }

    //统一下单
    public function doUnfiedOrder($param){
        $wechatConfig = C('WXXCX_CONFIG');
        $data['appid'] = $wechatConfig['appid'];
        $data['mch_id'] = $wechatConfig['mch_id'];
        $data['nonce_str'] = create_rand(15);
        $data['body'] = $param['body'];
        $data['out_trade_no'] = $param['order_no'];
        $data['total_fee'] = $param['total_fee'];
        $data['spbill_create_ip'] = $param['ip'];
        $data['notify_url'] = $param['notify_url']; //回调地址
        $data['trade_type'] = $param['trade_type'];
        $data['openid'] = $param['openid'];
        $sign = $this->getSign($data);
        if(!$sign){
            return false;
        }
        $data['sign'] = $sign;

        $xml = $this->ToXml($data);

        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $result = $this->wxHttpsRequest($url,$xml,false);
        return $this->FromXml($result);
    }

    //统一下单查询支付结果接口  $oid 订单id
    public function searchOrder($oid){
        if(!$oid){
            return array('status' => 0,'info' => '参数错误');
        }
        //查询订单是否存在
        $orderInfo = D('Common/UnfiedOrder')->getById($oid);
        if(!$orderInfo){
            return array('status' => 0,'info' => '不存在此订单');
        }
        if($orderInfo['status'] == 1){ //已支付完成或者支付失败 不查询
            return array('status' => 1,'info' => '打赏成功');
        }
        if($orderInfo['status'] == 2){
            return array('status' => 0,'info' => '订单支付失败');
        }

//        $param['transaction_id'] = trim($orderInfo['wx_no']);// return array('status' => 1,'info' => $orderInfo);
        $param['out_trade_no'] = $orderInfo['order_no'];
        $result = $this->doSearchOrder($param);
        $model = new Model();
        $model->execute("insert into mk_unfied_order_log (`res`,`create_time`) values ('". json_encode($result) ."',". time() .")");

        $orderNo = $result['out_trade_no'];//系统订单号
        if($result['return_code'] == 'SUCCESS'){
            if($result['result_code'] == 'SUCCESS'){
                if($result['trade_state'] == 'SUCCESS'){
                    $model->startTrans();
                    $res1 = D('Common/UnfiedOrder')->saveData(array('status' => 1),array('order_no' => $orderNo));
                    $res2 = D('Common/Reward')->saveData(array('uid' => $orderInfo['uid'],'rewarder' => $orderInfo['rewarder']));
                    if($res1 && $res2){
                        $model->commit();
                        return array('status' => 1,'info' => '打赏成功');
                    }else{
                        //支付成功修改订单状态失败 要做处理 TODO
                        $model->rollback();
                        return array('status' => 1,'info' => '打赏失败');
                    }
                }else{
                    //修改订单支付失败
                    D('Common/UnfiedOrder')->saveData(array('status' => 2),array('order_no' => $orderNo));
                    return array('status' => 0,'info' => $result['trade_state_desc']);
                }
            }else{
                //修改订单支付失败
                D('Common/UnfiedOrder')->saveData(array('status' => 2),array('order_no' => $orderNo));
                return array('status' => 0,'info' => $this->getRrroMsg($result['err_code']));
            }
        }else{
            //修改订单支付失败
            D('Common/UnfiedOrder')->saveData(array('status' => 2),array('order_no' => $orderNo));
            return array('status' => 0,'info' => $result['return_msg']);
        }
    }

    public function doSearchOrder($param){
        $wechatConfig = C('WXXCX_CONFIG');
        $data['appid'] = $wechatConfig['appid'];
        $data['mch_id'] = $wechatConfig['mch_id'];
        $data['transaction_id'] = $param['transaction_id'];
        $data['out_trade_no'] = $param['out_trade_no'];
        $data['nonce_str'] = create_rand(15);
        $sign = $this->getSign($data);
        if(!$sign){
            return array('return_code' => 'FAIL','info' => '签名错误');
        }
        $data['sign'] = $sign;

        $xml = $this->ToXml($data);

        $url = 'https://api.mch.weixin.qq.com/pay/orderquery';
        $result = $this->wxHttpsRequest($url,$xml,false);
        return $this->FromXml($result);
    }

    //获取微信小程序支付错误信息
    public function getRrroMsg($code){
        switch ($code) {
            case 'NOAUTH':
                return '商户无此接口权限';
                break;
            case 'NOTENOUGH':
                return '余额不足';
                break;
            case 'ORDERPAID':
                return '商户订单已支付';
                break;
            case 'ORDERCLOSED':
                return '订单已关闭';
                break;
            case 'SYSTEMERROR':
                return '系统错误';
                break;
            case 'APPID_NOT_EXIST':
                return 'APPID不存在';
                break;
            case 'MCHID_NOT_EXIST':
                return 'MCHID不存在';
                break;
            case 'APPID_MCHID_NOT_MATCH':
                return 'appid和mch_id不匹配';
                break;
            case 'LACK_PARAMS':
                return '缺少参数';
                break;
            case 'OUT_TRADE_NO_USED':
                return '商户订单号重复';
                break;
            case 'SIGNERROR':
                return '签名错误';
                break;
            case 'REQUIRE_POST_METHOD':
                return '请使用post方法';
                break;
            case 'POST_DATA_EMPTY':
                return 'post数据为空';
                break;
            case 'NOT_UTF8':
                return '编码格式错误';
                break;
            case 'CA_ERROR':
                return '请求未携带证书';
                break;
            case 'SIGN_ERROR':
                return '签名错误';
                break;
            case 'NO_AUTH':
                return '没有权限';
                break;
            case 'FREQ_LIMIT':
                return '受频率限制';
                break;
            case 'XML_ERROR':
                return '请求的xml格式错误，或者post的数据为空';
                break;
            case 'PARAM_ERROR':
                return '参数错误';
                break;
            case 'NOT_FOUND':
                return '指定单号数据不存在';
                break;
            default :
                return '未知错误';
                break;
        }
    }

    public function getOrderNo(){
        return date('YmdHis',time()) . rand(100000,999999);
    }

    public function wxHttpsRequest($url, $data = null,$isCert = false){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_TIMEOUT,30);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if($isCert){
            curl_setopt($curl,CURLOPT_SSLCERT,STATIC_PATH.'cert.pem');
            curl_setopt($curl,CURLOPT_SSLKEY,STATIC_PATH.'private.pem');
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    public function ToXml($param){
        if(!is_array($param) || count($param) <= 0){
            return false;
        }

        $xml = "<xml>";
        foreach ($param as $key => $val){
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    public function FromXml($xml){
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    public function getSign($param){
        if(!$param){
            return false;
        }
        ksort($param);
        $str = '';
        foreach($param as $key => $val){
            if(!empty($val) && $key != 'sign'){
                $str .= "{$key}={$val}&";
            }
        }
        $str = rtrim($str,'&');
        $wechatConfig = C('WXXCX_CONFIG');
        if(!$wechatConfig){
            return false;
        }
        $str .= "&key={$wechatConfig['key']}";
        $tempString = strtoupper(md5($str));
        return $tempString;
    }
}