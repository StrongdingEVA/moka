<?php

namespace Api\Controller;


use Admin\Controller\DatabaseController;

class MokaOldController extends BaseController {

    /**
     *  获取用户基本信息
     */
    public function getUinfo(){
        $this->checkLogin();
        $http_request_mode = 'request';

        $strange = I($http_request_mode . '.strange', 0, 'intval');
        $isMyself = true;
        if($strange){//路人
            if($strange != $this->uid){
                $isMyself = false;
            }
            $u = $strange;
        }else{
            $u = $this->uid;
        }

        $res = D('Common/MemberInfo')->getById($u);

        if(!$res && $isMyself){
            $result = D('Common/Member')->getByUid($this->uid);
            $resultOpen = D('Common/MemberOpenid')->getOpenid($this->uid);
            $arr['uid'] = $this->uid;
            $arr['openid'] = $resultOpen['open_id'];
            $arr['thumb'] = $result['thumb'];
            $arr['gentle'] = $result['sex'];
            $arr['nickname'] = $result['nickname'];
            $res2 = D('Common/MemberInfo')->addData($arr);
            if(!$res2){
                $this->coverRs(array('status' => 0,'info' => '获取用户信息失败'));exit;
            }
            $res = D('Common/MemberInfo')->getById($this->uid);
        }
        $res['isMyself'] = $isMyself;
        $this->coverRs(array('status' => 1,'data' => array('list' => $res,'isMyself' => $isMyself)));
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
        $arr['height'] = I($http_request_mode . '.height','intval');
        $arr['weight'] = I($http_request_mode . '.weight','intval');
        $arr['chestline'] = I($http_request_mode . '.chestline',0,'intval');//胸围
        $arr['waistline'] = I($http_request_mode . '.waistline',0,'intval');//腰围
        $arr['hipline'] = I($http_request_mode . '.hipline',0,'intval');//臀围
        $arr['thumb'] = I($http_request_mode . '.thumb');//头像
        $arr['mobile'] = I($http_request_mode . '.mobile');//手机号
        $arr['level'] = I($http_request_mode . '.level');//淘宝等级
        $arr['naughty'] = I($http_request_mode . '.naughty');//淘气值
        $arr['wechat'] = I($http_request_mode . '.wechat','','htmlspecialchars');//微信号
        $arr['uid'] = $this->uid;

        $res = D('Common/MemberInfo')->getById($this->uid);
        $oldPath = $res['pic'];
        if(isset($res['nickname']) && $arr['nickname'] != $res['nickname']){
            if(mb_strlen($arr['nickname'],"utf-8") > 4){
                $this->coverRs(array('status' => 0,'info' => '昵称不超过4个字'));exit;
            }
        }

        if($arr['mobile'] && !check_mobile($arr['mobile'])){
            $this->coverRs(array('status' => 0,'info' => '手机号码格式错误'));exit;
        }
        $isSave = true;
        if(!$res){
            $isSave = false;
            $result = D('Common/Member')->getByUid($this->uid);
            $resultOpen = D('Common/MemberOpenid')->getOpenid($this->uid);
            $arr['uid'] = $this->uid;
            $arr['openid'] = $resultOpen['open_id'];
            $arr['thumb'] = $result['thumb'];
            $arr['gentle'] = $result['sex'];
            $arr['nickname'] = $result['nickname'];
            D('Common/MemberInfo')->addData($arr);
        }
        D('Common/MemberInfo')->saveData($arr);

        $path = createParam($arr);
        $res2 = D('Common/MemberInfo')->saveData(array('pic' => $path,'uid' => $this->uid));
        if($res2){
            if($oldPath){//删除旧图片
                @unlink($oldPath);
            }

            $isUpPic = false;
            $arrKey = array('naughty','level','province','city','height','weight','chestline','waistline','hipline');
            foreach ($arr as $k => $v){
                if(in_array($k,$arrKey)){
                    if($v != $res[$k]){
                        $isUpPic = true;
                        break;
                    }
                }
            }
            if($isSave && $isUpPic){
                $this->unionPicAgain();
//                $list = D('Common/Card')->getList($this->uid);
//                foreach ($list as $item){
//                    $this->makeUnionPic($item['id'],true);
//                }
//                S('moka_card_list_' . $this->uid,null);
            }

            $arrNiew = D('Common/MemberInfo')->getById($this->uid);
            $this->coverRs(array('status' => 1,'info' => '保存成功','data' => $arrNiew));exit;
        }else{
            $this->coverRs(array('status' => 0,'info' => '保存失败'));exit;
        }
    }

    /**
     * 获取我的模卡列表
     * @param $page_no 页码
     * @param $page_size 页尺寸
     * @param $strange 获取路人模卡列表
     */
    public function getCardList(){$this->coverRs(array('data' => array(),'status' => 1,'info'=>111));exit;
        $this->checkLogin();
        $http_request_mode = 'request';
        $page = I($http_request_mode . '.page_no', 1, 'intval');
        $pageSize = I($http_request_mode . '.page_size', 10, 'intval');
        $pageSize = 5000;
        $strange = I($http_request_mode . '.strange', 0, 'intval');
        $isMyself = true;

        if ($strange){//路人
            $isMyself = false;
            $uid = $strange;
        }else{
            $uid = $this->uid;
        }
        $map = array(
            'where' => array(
                'uid' => $uid,
                'status' => 2
            ),
            'order' => 'id desc'
        );
        list($list,$totalInfo) = D('Common/Card')->getListRows($map,true);

        $arrIndex = array(
            '模卡一',
            '模卡二',
            '模卡三',
        );
        foreach ($list as $key => $val){
            $list[$key]['qr'] = $val['qr'] . getImgParm(0,200,200);
            $picArr = json_decode($val['pic_json'],1);
            foreach($picArr as $ke => $va){
                $picArr[$ke] = $va  . getImgParm(0,0,750);
                $temp[$ke] = $va  . getImgParm(0,300,300);
            }

            $list[$key]['pic_json_thumb'] = $temp;
            $list[$key]['pic_json'] = $picArr;
            $list[$key]['union_img_thumb'] = $val['union_img']  . getImgParm(0,300,300);
            $list[$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);;
            $list[$key]['index'] = $arrIndex[$key];
        }
        $this->coverRs(array('data' => array('list' => $list,'total' => $totalInfo,'isMyself' => $isMyself),'status' => 1));
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
        $model = D('Common/Card');

        $info = $model->getById($cid);
        $info['union_img_thumb'] = $info['union_img']  . getImgParm(0,300,300);
        $info['qr'] = $info['qr']  . getImgParm(0,200,200);
        $picArr = json_decode($info['pic_json'],1);
        $temp = array();
        foreach ($picArr as $key => $val){
            $picArr[$key] = $val  . getImgParm(0,0,750);
            $temp[$key] = $val  . getImgParm(0,300,300);
        }
        $info['pic_json'] = $picArr;
        $info['pic_json_thumb'] = $temp;
        $info['create_time'] = date('Y-m-d H:i:s',$info['create_time']);

        $isMyself = $info['uid'] == $this->uid ? true : false;

        $arrIndex = array(
            '模卡一',
            '模卡二',
            '模卡三',
        );
        $map = array(
            'where' => array(
                'uid' => $isMyself ? $this->uid : $info['uid'],
                'status' => 2
            ),
            'order' => 'id desc',
            'limit' => '0,3'
        );
        $list = D('Common/Card')->getListRows($map,false);
        $len = count($list) - 1;
        for($i = $len;$i >= 0;$i--){
            if($list[$i]['id'] == $cid){
                $info['index'] = $arrIndex[$i];
                break;
            }
        }
        $this->coverRs(array('status' => 1,'info' => '成功','data' => array('info' => $info,'isMyself' => $isMyself)));
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

        $model = D('Common/Card');
        $res = $model->changeById($cid,'status',1);
        if($res){
            S('moka_card_info_' . $cid,null);
            S('moka_card_list_' . $this->uid,null);
            $this->coverRs(array('info' => '操作成功','status' => 1));exit;
        }else{
            $this->coverRs(array('info' => '操作失败','status' => 0));exit;
        }
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
        if(!$picJson){
            $this->coverRs(array('info' => '缺少参数','status' => 0));exit;
        }
        /*
        $picArr = array(
            "Uploads/2018-04-04/5ac4d5e165158.png",
            "Uploads/2018-04-04/5ac4d5e169b91.png",
            "Uploads/2018-04-04/5ac4d5e17a14d.png",
            "Uploads/2018-04-04/5ac4d5e19c435.png",
            "Uploads/2018-04-04/5ac4d5e1858e7.png",
            "Uploads/2018-04-04/5ac4d5e190c9a.png",
            "Uploads/2018-04-04/5ac4d5e1cfc79.png",
            "Uploads/2018-04-04/5ac4d5e17bca5.png"
        );
        */

        $picArr = json_decode($picJson,1);
        if(empty($picArr)){
            $this->coverRs(array('info' => '参数格式错误','status' => 0));exit;
        }

        //获取模卡风格
        $styleInfo = D('Common/Style')->getById_($s_id);
        if(!$styleInfo){
            $this->coverRs(array('info' => '不存在此模卡风格','status' => 0));exit;
        }
        $info = json_decode($styleInfo['points'],1);
        $points = $info['points'];
        if(count($points) != count($picArr)){
            $this->coverRs(array('info' => '图片不齐全哦~','status' => 0));exit;
        }

        $model = D('Common/Card');
//        $list = S('moka_card_list_' . $this->uid);
//        if(!$list){
//            $list = $model->field('id')->where('status=2 and uid=' . $this->uid)->select();
//        }
//        if(count($list) >= 3){
//            $this->coverRs(array('info' => '每人最多只能拥有三张模卡哦~','status' => 0));exit;
//        }
        $model->startTrans();
        if($cid){//修改
            $model->saveData(array('id' => $cid,'pic_json' => json_encode($picArr)));
            $res = 1;
            $cardInfo = $model->getById($cid);
            if(!$cardInfo){
                $this->coverRs(array('info' => '不存在该模卡','status' => 0));exit;
            }
            $oldPath = $cardInfo['union_img'];
        }else{//添加新模卡
            $res = $model->saveData(array('pic_json' => json_encode($picArr),'s_id' => $s_id,'uid' => $this->uid));
            $cid = $res;
        }
        if($res){
            $result = $this->makeUnionPic($cid,true);
            if(!$result){
                $model->rollback();
                $this->coverRs(array('info' => '操作失败','status' => 1,'data' => $cid));exit;
            }else{
                if($oldPath){
                    @unlink($oldPath);
                }
                S('moka_card_list_' . $this->uid,null);
                $model->commit();
                $this->coverRs(array('info' => '操作成功','status' => 1,'data' => $cid));exit;
            }
        }else{
            $model->rollback();
            $this->coverRs(array('info' => '操作失败','status' => 0));exit;
        }

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
        $res = tencentCloundUpload();
        if($res){
            $this->coverRs(array('data' => $res,'info' => '上传成功', 'status' => 1));exit;
        }else{
            $this->coverRs(array('info' => '文件上传失败','status' => 0));exit;
        }
    }

    /**
     * 如果是在点下载的时候在合成图片就用此接口
     */
    public function makeUnionPic($id = 0,$isEdit = false){
        $this->checkLogin();
        $http_request_mode = 'request';
        $cid = $id ? $id : I($http_request_mode . '.cid', 0, 'intval');

        $model = D('Common/Card');
        $cardInfo = $model->getById($cid);
        if(!$cardInfo){
            $this->coverRs(array('info' => '不存在该模卡','status' => 0));exit;
        }

//        if (!$id && $cardInfo['union_img']){
//            $this->coverRs(array('info' => '操作成功','status' => 1,'data' => $cardInfo['union_img']));exit;
//        }

        //生成模卡图
        $styleInfo = D('Common/Style')->getById_($cardInfo['s_id']);
        $style = json_decode($styleInfo['points'],1);
        $unionPic = mergePics(json_decode($cardInfo['pic_json'],1),$style);
        if(!$unionPic){
            $this->coverRs(array('info' => '生成模卡图失败','status' => 0));exit;
        }

        $memberInfo = D('Common/MemberInfo')->getById($this->uid);

        if(!$memberInfo['pic']){//如果没生成模卡底部图片 现在生成
            $path = createParam($memberInfo);
            if(!$path){
                $this->coverRs(array('info' => '生成模卡用户信息图失败','status' => 0));exit;
            }
            D('Common/MemberInfo')->saveData(array('pic' => $path,'uid' => $this->uid));
        }else{
            $path = $memberInfo['pic'];
        }
        //获取模卡的小程序码  一个模卡一张码
        $qrPath = $cardInfo['qr'] ? $cardInfo['qr'] : getSmallQr($cid);
//        //小程序吗和模卡图的底部合并
        $tempImg = mergeBottomQr($path,$qrPath);

        if(!$tempImg){
            $this->coverRs(array('info' => '生成模卡用户信息图失败','status' => 0));exit;
        }
        //上传小程序码
        $str = C('IMGDOMAIN');
        if(strpos($qrPath,$str) === false){
            $resQr = tencentCloundUpload($qrPath);
            if($resQr){
                @unlink($qrPath);
            }
            $qrPath = $resQr;
        }

        $resUnion = unionAll($unionPic,$tempImg);
        @unlink($tempImg);
        if(!$resUnion){
            $this->coverRs(array('info' => '合并模卡图失败','status' => 0));exit;
        }else{
            /*
            if($result){
                @unlink($cardInfo['union_img']);
                if($isEdit){
                    return true;
                }else{
                    $path = C('IMGDOMAIN') . $resUnion;
                    $this->coverRs(array('info' => '操作成功','status' => 1,'data' => $path));exit;
                }
            }else{
                if($isEdit){
                    return false;
                }else{
                    $this->coverRs(array('info' => '云同步失败','status' => 0));exit;
                }
            }
            */

            @unlink($cardInfo['union_img']);
            //删除云上的图
            //TODO
            $upRes = tencentCloundUpload($resUnion);
            if($upRes){
                //替换域名
                $model->saveData(array('id' => $cid,'union_img' => $upRes,'qr' => $qrPath));
                @unlink($resUnion);
                if($isEdit){
                    return true;
                }else{
                    $this->coverRs(array('info' => '操作成功','status' => 1,'data' => $upRes));exit;
                }
            }else{
                if($isEdit){
                    return false;
                }else{
                    $this->coverRs(array('info' => '云同步失败','status' => 0));exit;
                }
            }
        }
    }

    public function unionPicAgain(){
        $map = array(
            'where' => array(
                'uid' => $this->uid,
                'status' => 2
            ),
            'order' => 'id desc',
            'limit' => '0,3'
        );
        $model = D('Common/Card');
        $list = $model->getListRows($map,false);
        //没有发布模卡 不需要修改
        if(!$list){
            return true;
        }

        $memberInfo = D('Common/MemberInfo')->getById($this->uid);
        if(!$memberInfo['pic']){//如果没生成模卡底部图片 现在生成
            $path = createParam($memberInfo);
            if(!$path){
                $this->coverRs(array('info' => '生成模卡用户信息图失败','status' => 0));exit;
            }
            D('Common/MemberInfo')->saveData(array('pic' => $path,'uid' => $this->uid));
        }else{
            $path = $memberInfo['pic'];
        }

        foreach ($list as $item){
            //获取模卡的小程序码  一个模卡一张码
            $qrPath = $item['qr'];
            //小程序吗和模卡图的底部合并
            $tempImg = mergeBottomQr($path,$qrPath);
            if(!$tempImg){
                $this->coverRs(array('info' => '生成模卡用户信息图失败','status' => 0));exit;
            }

            $unionPic = downLoadTolocal(array($item['union_img']),false);
            $resUnion = unionAll($unionPic[0],$tempImg,2);
            @unlink($tempImg);
            if(!$resUnion){
                $this->coverRs(array('info' => '合并模卡图失败','status' => 0));exit;
            }else {
                //删除云上的图
                //TODO
                $upRes = tencentCloundUpload($resUnion);
                if($upRes){
                    //替换域名
                    $model->saveData(array('id' => $item['id'],'union_img' => $upRes,'qr' => $qrPath));
                    @unlink($resUnion);
                }

                S('moka_card_info_' . $item['id'],null);
            }
        }
        S('moka_card_list_' . $this->uid,null);
    }

    public function clearScache(){
        S('moka_uinfo_' . $this->uid,null);
        S('moka_card_list_' . $this->uid,null);
        S('moka_style',null);
    }
}

?>