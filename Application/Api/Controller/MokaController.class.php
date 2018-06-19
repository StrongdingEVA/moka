<?php

namespace Api\Controller;

use Api\Logic\ApiLogic;
use Api\Logic\MakeImageLogic;
use Api\Logic\PayLogic;

class MokaController extends BaseController {

    /**
     *  获取用户基本信息
     */
    public function getUinfo(){
        $this->checkLogin();
        $http_request_mode = 'request';

        $strange = I($http_request_mode . '.strange', 0, 'intval');
        $logic = new ApiLogic();
        $result = $logic->getUinfoFormat($this->uid,$strange);
        $this->coverRs($result);
    }

    /**
     * 保存用户基本信息
     */
    public function saveUinfo(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $arr['gentle'] = I($http_request_mode . '.gentle', 1, 'intval');
        $arr['nickname'] = I($http_request_mode . '.nickname','','htmlspecialchars');
        $arr['country'] = I($http_request_mode . '.country','','htmlspecialchars');
        $arr['province'] = I($http_request_mode . '.province','','htmlspecialchars');
        $arr['city'] = I($http_request_mode . '.city','','htmlspecialchars');
        $arr['area'] = I($http_request_mode . '.area','','htmlspecialchars');
        $arr['height'] = I($http_request_mode . '.height',0,'intval');
        $arr['weight'] = I($http_request_mode . '.weight',0,'intval');
        $arr['chestline'] = I($http_request_mode . '.chestline',0,'intval');//胸围
        $arr['waistline'] = I($http_request_mode . '.waistline',0,'intval');//腰围
        $arr['hipline'] = I($http_request_mode . '.hipline',0,'intval');//臀围
        $arr['thumb'] = I($http_request_mode . '.thumb');//头像
        $arr['mobile'] = I($http_request_mode . '.mobile');//手机号
        $arr['level'] = I($http_request_mode . '.level');//淘宝等级
        $arr['naughty'] = I($http_request_mode . '.naughty');//淘气值
        $arr['wechat'] = I($http_request_mode . '.wechat','','htmlspecialchars');//微信号
        $arr['shose_size'] = I($http_request_mode . '.shose_size',0,'intval');//鞋码
        $arr['uid'] = $this->uid;

        $logic = new ApiLogic();
        $result = $logic->saveUinfo($arr,$this->uid);
        $this->coverRs($result);
    }

    /**
     * 获取我的模卡列表
     * @param $page_no 页码
     * @param $page_size 页尺寸
     * @param $strange 获取路人模卡列表
     */
    public function getCardList(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $strange = I($http_request_mode . '.strange', 0, 'intval');
        $logic = new ApiLogic();
        $result = $logic->getCardList($this->uid,$strange);
        $this->coverRs($result);
    }

    /**
     * 获取模卡信息
     * @param $cid 模卡id
     */
    public function getCardInfo(){
        $http_request_mode = 'request';
        $cid = I($http_request_mode . '.cid', 0, 'intval');

        if(!$cid){
            $this->coverRs(array('status' => 0,'info' => '参数错误'));exit;
        }
        $logic = new ApiLogic();
        $result = $logic->getCardInfo($cid,$this->uid);
        $this->coverRs($result);
    }

    /**
     * 删除模卡
     * @param $cid 模卡id
     */
    public function delCard(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $cid = I($http_request_mode . '.cid', 0, 'intval');

        if(!$cid){
            $this->coverRs(array('info' => '缺少参数','status' => 0));exit;
        }

        $logic = new ApiLogic();
        $result = $logic->delCard($cid);
        $this->coverRs($result);
    }

    /**
     * 修改模卡
     * @param $cid 修改时传模卡id
     * @param $s_id 模卡风格id  新增时要传
     * @param $pic_json json字符串
     */
    public function saveCardInfo(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $cid = I($http_request_mode . '.cid', 0, 'intval');
        $s_id = I($http_request_mode . '.s_id', 1, 'intval');
        $picJson = I($http_request_mode . '.pic_json');

        $logic = new ApiLogic();
        $result = $logic->saveCardInfo($picJson,$this->uid,$s_id,$cid);
        $this->coverRs($result);
    }

    /**
     * 获取模卡风格
     */
    public function getStyleList(){
        $list = D('Common/Style')->getList('id,id_a,name,pics,title,ext_info');
        $this->coverRs(array('info' => '成功','status' => 1,'data' => $list));exit;
    }

    /**
     * 图片上传
     * 文件域是file
     */
    public function imgUpload(){
        $ImgLogic = new MakeImageLogic();
        $res = $ImgLogic->tencentCloundUpload();
        if($res){
            $this->coverRs(array('data' => $res,'info' => '上传成功', 'status' => 1));exit;
        }else{
            $this->coverRs(array('info' => '文件上传失败','status' => 0));exit;
        }
    }

    /**
     * 获取人气模特
     * @param $page_no int 默认1  一般这两个可不传
     * @param $page_size int 默认3
     * @param $type int 0 如果是模卡大厅页面的三个人气模特不传或传0 否则传1
     */
    public function getHotModel(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $page = I($http_request_mode . '.page_no', 1, 'intval');
        $pageSize = I($http_request_mode . '.page_size', 3, 'intval');
        $type = I($http_request_mode . '.type', 0, 'intval');
        $logic = new ApiLogic();
        $result = $logic->getHotModel(array('page' => $page,'pageSize' => $pageSize,'order' => 'clicks desc,svip desc,uid asc'));$this->coverRs($result);
        if($type){
            $result['data']['list'] = $logic->hotModelFormat($result['data']['list'],$this->uid,1);
        }else{
            $result['data']['list'] = $logic->hotModelFormatOther($result['data']['list']);
        }
        $this->coverRs($result);
    }

    /**
     * 根据条件筛选模特
     * @param $page_no int 默认1  一般这两个可不传
     * @param $page_size int 默认3
     * @param $level int 等级
     * @param $naughty string  淘气值 例：0,5555 用逗号隔开
     * @param $province string 省  福建
     * @param $city string 市 厦门
     * @param $area string 区 思明区
     */
    public function getModelBySearch(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $data['level']  = I($http_request_mode . '.level',0,'intval');//淘宝等级
        $data['naughty'] = I($http_request_mode . '.naughty','');//淘气值
        $data['province'] = I($http_request_mode . '.province',''); //省
        $data['city'] = I($http_request_mode . '.city',''); //市
        $data['area'] = I($http_request_mode . '.area',''); //区
        $data['page'] = I($http_request_mode . '.page_no', 1, 'intval');
        $data['pageSize'] = I($http_request_mode . '.page_size', 8, 'intval');
        $logic = new ApiLogic();
        $result = $logic->getHotModel($data);
        $result['data']['list'] = $logic->hotModelFormat($result['data']['list'],$this->uid);
        $result['data']['list'] = $logic->modelSortByCreateTime($result['data']['list']);
        $this->coverRs($result);
    }

    /**
     * 收藏模特的模卡
     * @param $cid int 模卡id
     * @param $type int 1收藏 否则取消收藏
     */
    public function collectMoka(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $cid = I($http_request_mode . '.cid',0,'intval');//模卡id
        $type = I($http_request_mode . '.type',1,'intval');//1收藏 否则取消收藏
        $logic = new ApiLogic();
        if($type == 1){
            $result = $logic->collectMoka($cid,$this->uid);
        }else{
            $result = $logic->collectMokaCancel($cid,$this->uid);
        }
        $this->coverRs($result);
    }

    /**
     * 点赞模卡
     * @param $cid int 模卡id
     */
    public function giveMeFive(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $cid = I($http_request_mode . '.cid',0,'intval');//模卡id
        $logic = new ApiLogic();
        $result = $logic->giveMeFive($cid,$this->uid);
        $this->coverRs($result);
    }

    /**
     * 查看微信号  打赏
     */
    public function checkWechat(){
        //TODO
    }

    /**
     * 主页点击
     * @param $uid int 用户ID
     */
    public function setClicks(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $uid = I($http_request_mode . '.uid',0,'intval');//uid
        $logic = new ApiLogic();
        $result = $logic->setClicks($uid);
        $this->coverRs($result);
    }

    /**
     * 我的收藏
     * @param $page_no int 默认1
     * @param $page_size int 默认5
     */
    public function myCollect(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $page = I($http_request_mode . '.page_no', 1, 'intval');
        $pageSize = I($http_request_mode . '.page_size', 5, 'intval');
        $logic = new ApiLogic();
        $result = $logic->getUserCollect($this->uid,$page,$pageSize);
        $this->coverRs($result);
    }

    /**
     * 放单大厅
     * @param $page_no int 默认1
     * @param $page_size int 默认5
     * @param $order int 0 按时间最新排序 1按数量从高到低 2按佣金从高到低
     */
    public function getOrder(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $param['page'] = I($http_request_mode . '.page_no', 1, 'intval');
        $param['pageSize'] = I($http_request_mode . '.page_size', 5, 'intval');
        $param['order'] = I($http_request_mode . '.order', 0, 'intval');
        if(!in_array($param['order'],array(0,1,2))){
            $this->coverRs(array('info' => '排序类型错误','status' => 0));exit;
        }
        $logic = new ApiLogic();
        $result = $logic->getOrder($param);
        $result['data']['list'] = $logic->orderFormat($result['data']['list'],$this->uid);
        $this->coverRs($result);
    }

    /**
     * 订单详情
     * @param $oid int  订单id
     */
    public function orderDetail(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $oid = I($http_request_mode . '.oid', 0, 'intval');
        $apiLogic = new ApiLogic();
        $result = $apiLogic->orderDetail($oid,$this->uid);
        $this->coverRs($result);
    }

    /**
     * 设置订单被分享
     * @param $oid int 榜单id
     */
    public function setShare(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $oid = I($http_request_mode . '.oid', 0, 'intval');
        if(!$oid){
            $this->coverRs(array('info' => '参数错误','status' => 0));exit;
        }
        $logic = new ApiLogic();
        $result = $logic->setShare($oid,$this->uid);
        $this->coverRs($result);
    }

    /**
     * 获取问题列表
     * @param $page_no int 页码
     * @param $page_size 页大小
     */
    public function questions(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $page_no = I($http_request_mode . '.page_no', 1,'intval');
        $page_size = I($http_request_mode . '.page_size', 10,'intval');
        $logic = new ApiLogic();
        $result = $logic->helps($page_no,$page_size);
        $this->coverRs($result);
    }

    /**
     * 下载图片
     * @param int $cid
     * @return bool
     */
    public function makeUnionPic(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $cid = I($http_request_mode . '.cid', 0, 'intval');
        $apiLogic = new ApiLogic();
        $result = $apiLogic->downloadImage($cid,$this->uid);
        $this->coverRs($result);
    }

    /**
     * @param $rewarder int 被打赏的用户id
     * 统一下单
     */
    public function unfileOrder(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $rewarder = I($http_request_mode . '.rewarder', 0, 'intval');
        if(!$rewarder){
            $this->coverRs(array('status' => 0,'info' => '参数错误'));
        }
        $amount = 0.01;
        $logic = new PayLogic();
        $result = $logic->unifiedorder($this->uid,$rewarder,$amount);
        $this->coverRs($result);
    }

    /**
     * @param $oid int 订单id
     * 统一下单查询支付结果
     */
    public function searchUnfiedOrder(){
        $this->checkLogin();
        $http_request_mode = 'request';
        $oid = I($http_request_mode . '.oid', 0, 'intval');
        if(!$oid){
            $this->coverRs(array('status' => 0,'info' => '参数错误'));
        }
        $logic = new PayLogic();
        $result = $logic->searchOrder($oid);
        $this->coverRs($result);
    }

    public function test(){
        $logic = new PayLogic();
        $result = $logic->searchOrder(11);
        $this->coverRs($result);
    }
}

?>