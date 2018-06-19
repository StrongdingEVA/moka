<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Cli\Controller;

use Api\Logic\PayLogic;
use Think\Controller;
use Think\Model;

class UnfiedOrderController extends Controller
{
    public function unfieCallBack(){
        $post_data = $_REQUEST;
        if(!$post_data){
            $post_data = file_get_contents("php://input");
        }
        if(!$post_data){
            $post_data = $GLOBALS['HTTP_RAW_POST_DATA'];
        }

        //记录回调表
        $model = new Model();
        $model->execute("insert into mk_unfied_order_log (`res`,`create_time`) values ('". json_encode($post_data) ."',". time() .")");

        //验证sign
        $logic = new PayLogic();
        $sign = $logic->getSign($post_data);

        if($sign != $post_data['sign']){
            //签名错误
            return false;
        }

        if($post_data['return_code'] == 'SUCCESS'){
            if($post_data['result_code'] == 'SUCCESS'){
                $orderNo = $post_data['out_trade_no'];
                //查询订单是否存在
                $orderInfo = D('Common/UnfiedOrder')->getByNo($orderNo);
                if(!$orderInfo){
                    //不存在此订单
                    return false;
                }
                if($orderInfo['status'] == 1){
                    //订单已支付完成
                    return true;
                }
                if($orderInfo['status'] == 2){
                    //订单支付失败
                    return false;
                }

                $model = new Model();
                $model->startTrans();
                $res1 = D('Common/UnfiedOrder')->saveData(array('status' => 1),array('order_no' => $orderNo));
                $res2 = D('Common/Reward')->saveData(array('uid' => $orderInfo['uid'],'rewarder' => $orderInfo['rewarder']));
                if($res1 && $res2){
                    $model->commit();
                    return true;
                }else{
                    //支付成功修改订单状态失败 要做处理 TODO
                    $model->rollback();
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}