<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/21 0021
 * Time: 15:24
 */
namespace Api\Logic;
use Think\Model;

class ApiLogic{
    //获取用户信息
    public function getUinfoFormat($uid,$strange){
        $isMyself = true;
        if($strange){//路人
            if($strange != $uid){
                $isMyself = false;
            }
            $u = $strange;
        }else{
            $u = $uid;
        }

        $res = D('Common/MemberInfo')->getById($u);

        if(!$res && $isMyself){
            $result = D('Common/Member')->getByUid($uid);
            $resultOpen = D('Common/MemberOpenid')->getOpenid($uid);
            $arr['uid'] = $uid;
            $arr['openid'] = $resultOpen['open_id'];
            $arr['thumb'] = $result['thumb'];
            $arr['gentle'] = $result['sex'];
            $arr['nickname'] = $result['nickname'];
            $res2 = D('Common/MemberInfo')->addData($arr);
            if(!$res2){
                $this->returnFormat('获取用户信息失败');
            }
            $res = D('Common/MemberInfo')->getById($uid);
        }
        $res['isMyself'] = $isMyself;
        return $this->returnFormat('成功',$res,1);
    }

    //保存用户信息
    public function saveUinfo($arr,$uid){
        $res = D('Common/MemberInfo')->getById($uid);
        $oldPath = $res['pic'];
        if(isset($res['nickname']) && $arr['nickname'] != $res['nickname']){
            if(mb_strlen($arr['nickname'],"utf-8") > 4){
                return $this->returnFormat('昵称不超过4个字');
            }
        }

        if($arr['mobile'] && !check_mobile($arr['mobile'])){
            return $this->returnFormat('手机号码格式错误');
        }
        $isSave = true;
        //没有记录新增
        if(!$res){
            $isSave = false;
            $result = D('Common/Member')->getByUid($uid);
            $resultOpen = D('Common/MemberOpenid')->getOpenid($uid);
            $arr['uid'] = $uid;
            $arr['openid'] = $resultOpen['open_id'];
            $arr['thumb'] = $result['thumb'];
            $arr['gentle'] = $result['sex'];
            $arr['nickname'] = $result['nickname'];
            if(!D('Common/MemberInfo')->addData($arr)){
                return $this->returnFormat('新增用户信息错误');
            }
        }

        $isUpPic = false;
        $arrKey = array('naughty','level','province','city','height','weight','chestline','waistline','hipline');
        foreach ($arr as $k => $v){
            if(in_array($k,$arrKey) && $v){
                if($v != $res[$k]){
                    $isUpPic = true;
                    break;
                }
            }
        }

        if($isUpPic){
            $ImgLogic = new MakeImageLogic();
            //修改模卡底部图片
            $path = $ImgLogic->createParam($arr);

            if(!$path){
                return $this->returnFormat('生成信息图失败');
            }
            $arr['pic'] = $path;
            D('Common/MemberInfo')->saveData($arr);
            if($isSave){
                $result = $ImgLogic->unionPicAgain($uid);
                if($result['status'] != 1) {
                    return $result;
                }
            }
        }
        $arrNiew = D('Common/MemberInfo')->getById($uid);
        return $this->returnFormat('成功',$arrNiew,1);
    }

    //获取我的模卡列表
    public  function getCardList($uid,$strange){
        $isMyself = true;
        if ($strange){//路人
            $isMyself = false;
            $uid = $strange;
        }

        $map = array(
            'where' => array(
                'uid' => $uid,
                'status' => 2
            ),
            'order' => 'id desc',
            'limit' => '0,3'
        );
        list($list,$totalInfo) = D('Common/Card')->getListRows($map,true);
        if(!$list){
            return array('info' => '成功','data' => array('list' => array(),'total' => 0,'isMyself' => $isMyself),'status' =>1);
        }
        $arrIndex = array(
            '模卡一',
            '模卡二',
            '模卡三',
        );
        $makeLogic = new MakeImageLogic();
        foreach ($list as $key => $val){
            $list[$key]['qr'] = $makeLogic->getImgParm($val['qr'],0,200,200);
            $picArr = json_decode($val['pic_json'],1);
            $temp = array();
            foreach($picArr as $ke => $va){
                $picArr[$ke] = $makeLogic->getImgParm($va,0,0,750);
                $temp[] = $makeLogic->getImgParm($va,0,300,300);
            }

            $list[$key]['pic_json_thumb'] = $temp;
            $list[$key]['pic_json'] = $picArr;
            $list[$key]['union_img_thumb'] = $makeLogic->getImgParm($val['union_img'],0,300,300);
            $list[$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);;
            $list[$key]['index'] = $arrIndex[$key];
        }

        return array('info' => '成功','data' => array('list' => $list,'total' => $totalInfo['c'],'isMyself' => $isMyself),'status' =>1);
    }

    //获取模卡信息
    public function getCardInfo($cid,$uid){
        $makeLogic = new MakeImageLogic();
        $model = D('Common/Card');
        $info = $model->getById($cid);
        $info['union_img_thumb'] = $makeLogic->getImgParm($info['union_img'],0,300,300);
        $info['qr'] = $makeLogic->getImgParm($info['qr'],0,200,200);
        $picArr = json_decode($info['pic_json'],1);
        $temp = array();
        foreach ($picArr as $key => $val){
            $picArr[$key] = $makeLogic->getImgParm($val,0,0,750);
            $temp[$key] = $makeLogic->getImgParm($val,0,300,300);
        }
        $info['pic_json'] = $picArr;
        $info['pic_json_thumb'] = $temp;
        $info['create_time'] = date('Y-m-d H:i:s',$info['create_time']);

        $isMyself = $info['uid'] == $uid ? true : false;

        $arrIndex = array(
            '模卡一',
            '模卡二',
            '模卡三',
        );
        $map = array(
            'where' => array(
                'status' => 2
            ),
            'order' => 'id desc',
            'limit' => '0,3'
        );
        $map['where']['uid'] = $isMyself ? $uid : $info['uid'];
        $list = D('Common/Card')->getListRows($map,false);
        $len = count($list) - 1;
        for($i = $len;$i >= 0;$i--){
            if($list[$i]['id'] == $cid){
                $info['index'] = $arrIndex[$i];
                break;
            }
        }
        return $this->returnFormat(array('info' => $info,'isMyself' => $isMyself));
    }

    //删除模卡
    public function delCard($cid){
        $model = D('Common/Card');
        $res = $model->saveData(array('status' => 1,'id' => $cid));
        if($res){
            return $this->returnFormat('操作成功','',1);
        }else{
            return $this->returnFormat('操作失败');
        }
    }

    //修改模卡
    public function saveCardInfo($picJson,$uid,$s_id,$cid){
        if(!$picJson){
            return $this->returnFormat('缺少参数');
        }

        $picArr = json_decode($picJson,1);
        if(empty($picArr)){
            return $this->returnFormat('参数格式错误');
        }

        //获取模卡风格
        $styleInfo = D('Common/Style')->getById_($s_id);
        if(!$styleInfo){
            return $this->returnFormat('不存在此模卡风格');
        }
        $info = json_decode($styleInfo['points'],1);
        $points = $info['points'];
        if(count($points) != count($picArr)){
            return $this->returnFormat('图片不齐全哦~');
        }
        $model = D('Common/Card');
        $model->startTrans();
        if($cid){//修改 要把点赞次数清空
            $cardInfo = $model->getById($cid);
            if(!$cardInfo){
                return $this->returnFormat('不存在该模卡');
            }
            $oldPath = $cardInfo['union_img'];
            $model->saveData(array('id' => $cid,'pic_json' => json_encode($picArr),'great' => '','great_num' => 0));
            $res = 1;
        }else{//添加新模卡
            $res = $model->saveData(array('pic_json' => json_encode($picArr),'s_id' => $s_id,'uid' => $uid));
            $cid = $res;
        }
        if($res){
            $makeLogic = new MakeImageLogic();
            $result = $makeLogic->makeUnionPic($cid,$uid);
            if($result['status'] != 1){
                $model->rollback();
                return $result;
            }else{
                if($oldPath){
                    @unlink($oldPath);
                }
                $model->commit();
                return $this->returnFormat('操作成功',$cid,1);
            }
        }else{
            $model->rollback();
            return $this->returnFormat('操作失败');
        }
    }

    public function downloadImage($cid,$uid){
        $model = D('Common/Card');
        $cardInfo = $model->getById($cid);
        if(!$cardInfo){
            return $this->returnFormat('不存在该模卡');
        }
        if($cardInfo['union_img']){
            return $this->returnFormat('成功',$cardInfo['union_img'],1);
        }
        $makeLogic = new MakeImageLogic();
        $result = $makeLogic->makeUnionPic($cid,$uid);
        if($result['status'] != 1){
            return $result;
        }else{
            return $this->returnFormat('成功',$result['data'],1);
        }
    }

    //获取人气模特
    public function getHotModel($param = array()){
        $start = ($param['page'] - 1) * $param['pageSize'];
        $where = array(
            'is_show' => 1
        );
        if(isset($param['province']) && $param['province']){
            $where['province'] = $param['province'];
        }
        if(isset($param['city']) && $param['city']){
            $where['city'] = $param['city'];
        }
        if(isset($param['area']) && $param['area']){
            $where['area'] = $param['area'];
        }

        if(isset($param['naughty']) && $param['naughty']){
            list($min,$max) = explode(',',$param['naughty']);
            $min = $min ? $min : 0;
            $max = $max ? $max : 0;
            if($min && $max){
                $where['naughty'] = array('BETWEEN',array($min,$max));
            }elseif($min){
                $where['naughty'] = array('egt',$min);
            }else{
                $where['naughty'] = array('elt',$max);
            }
        }
        if(isset($param['level']) && $param['level']){
            $where['level'] = $param['level'];
        }
        $order = 'clicks desc,uid asc';
        if(isset($param['order']) && $param['order']){
            $order = $param['order'];
        }


        $map = array(
            'field' => 'uid,nickname,wechat,thumb,openid,height,weight,chestline,waistline,hipline,clicks',
            'order' => $order,
            'limit' => "{$start},{$param['pageSize']}"
        );
        if(!empty($where)){
            $map['where'] = $where;
        }
        list($result,$total) = D('Common/MemberInfo')->getListRows($map,true);
        return array('info' => '成功','data' => array('list' => $result,'total' => $total),'status' =>1);
    }

    //获取人气模特 点赞次数最多的模卡
    public function hotModelFormat($list,$uid,$order = 0){
        $cardModel = D('Common/Card');
        $styleModel = D('Common/Style');
        $memberModel = D('Common/Collect');
        $rewardModel = D('Common/Reward');
        $sort = $order ? 'id asc' : 'great_num desc,id desc';
        foreach($list as $key => $val){
            $result = $cardModel->where(array('uid' => $val['uid'],'status' => 2))->order($sort)->field('s_id,great_num,uid,id,great,pic_json,create_time')->find();
            $result['pic_json'] = $result['pic_json'] ? json_decode($result['pic_json'],1) : array();
            $result['great'] = $result['great'] ? json_decode($result['great'],1) : array();
            //判断当前用户是否点赞了
            $list[$key]['great'] = in_array($uid,$result['great']) ? true : false;
            //判断当前用户是否收藏了
            $memberInfo = $memberModel->getByUid($uid);
            $collectList = $memberInfo['collects'];
            $list[$key]['collect'] = in_array($result['id'],$collectList) ? true : false;
            //判断当前用户是否打赏过
            $list[$key]['isReward'] = $rewardModel->getByUid($uid,$val['uid']);
            //获取当前模卡样式
            $styleInfo = $styleModel->getById($result['s_id']);
            $result['styleId'] = $styleInfo['id_a'];
            unset($result['s_id']);
            unset($list[$key]['openid']);
            $list[$key]['cardInfo'] = $result;
        }
       return $list;
    }

    public function hotModelFormatOther($list){
        $temp = array();
        foreach($list as $key => $val){
            $temp[$key]['uid'] = $val['uid'];
            $temp[$key]['wechat'] = $val['wechat'];
            $temp[$key]['thumb'] = $val['thumb'];
            $temp[$key]['nickname'] = $val['nickname'];
        }
       return $temp;
    }



    //按模卡生成顺序正序
    public function modelSortByCreateTime($list){
        $temp = array();
        foreach($list as $key => $val){
            $cardInfo = $val['cardInfo'];
            $temp[$cardInfo['id']] = $val;
        }
        krsort($temp,SORT_NUMERIC);
        $final = array();
        foreach($temp as $item){
            $final[] = $item;
        }
        return $final;
    }

    //收藏用户的模卡
    public function collectMoka($cid,$uid){
        if(!$uid || !$cid){
            return $this->returnFormat('参数错误');
        }
        //查询用户收藏的模卡 判断是否收藏
        $collectModel = D('Common/Collect');
        $collectList = $collectModel->getByuId($uid);

        if($collectList || $collectList['collects']){
            $temArr = $collectList['collects'] ? $collectList['collects'] : array();
            if(!in_array($cid,$temArr)){
                $collectList['collects'][] = $cid;
                $collectList['collects'] = json_encode($collectList['collects']);
            }else{
                return $this->returnFormat('您已经收藏过了','',0);
            }
        }else{
            //直接收藏
            $collectList = array('uid' => $uid,'collects' => json_encode(array($cid)));
        }
        if($collectModel->saveData($collectList)){
            return $this->returnFormat('收藏成功','',1);
        }
        return $this->returnFormat('收藏失败');
    }

    //取消收藏
    public function collectMokaCancel($cid,$uid){
        if(!$uid || !$cid){
            return $this->returnFormat('参数错误');
        }
        //查询用户收藏的模卡 判断是否收藏
        $collectModel = D('Common/Collect');
        $collectList = $collectModel->getByuId($uid);
        if(!$collectList || !$collectList['collects']){
            return $this->returnFormat('还未收藏，无法取消');
        }
        if(!in_array($cid,$collectList['collects'])){
            return $this->returnFormat('还未收藏，无法取消');
        }
        $temp = $collectList['collects'];
        unset($temp[array_search($cid,$temp)]);
        $collectList['collects'] = json_encode($temp);
        if($collectModel->saveData($collectList)){
            return $this->returnFormat('取消收藏成功','',1);
        }
        return $this->returnFormat('取消收藏失败');
    }

    //点赞模卡
    public function giveMeFive($cid,$uid){
        if(!$uid || !$cid){
            return $this->returnFormat('参数错误');
        }

        $cardModel = D('Common/Card');
        $cardInfo = $cardModel->getById($cid);
        if(!$cardInfo){
            return $this->returnFormat('不存在此模卡');
        }

        $greatList = $cardInfo['great'] ? json_decode($cardInfo['great'],1) : array();
        $greatNum = $cardInfo['great_num'] ? $cardInfo['great_num'] : 0;

        $isKey = array_search($uid,$greatList);
        if($isKey === false){
            $greatList[] = $uid;
            $greatNum += 1;
            $temp = '点赞成功';
        }else{//已经点过赞
            unset($greatList[$isKey]);
            $greatNum -= 1;
            $temp = '取消点赞成功';
        }
        if($cardModel->saveData(array('id' => $cardInfo['id'],'great' => json_encode($greatList),'great_num' => $greatNum))){
            return $this->returnFormat($temp,'',1);
        }
        return $this->returnFormat('操作失败');
    }

    //设置主页点击率
    public function setClicks($uid){
        if(!$uid){
            return $this->returnFormat('参数错误');
        }
        $memberModel = D('Common/MemberInfo');
        $memberInfo = $memberModel->getByUid($uid);
        if(!$memberInfo){
            return $this->returnFormat('该用户不存在');
        }
        $arr = array('clicks' => $memberInfo['clicks'] + 1,'uid' => $uid);
        if($memberModel->saveData($arr)){
            return $this->returnFormat('操作成功','',1);
        }
        return $this->returnFormat('操作失败');
    }

    //获取用户的收藏的模卡
    public function getUserCollect($uid,$page = 1,$pageSize = 5){
        if(!$uid){
            return $this->returnFormat('参数错误');
        }
        $memberModel = D('Common/Collect');
        $memberInfo = $memberModel->getByUid($uid);
        if(!$memberInfo){
            return array('info' => '您什么都没收藏哦','data' => array(),'status' =>0);
        }
        if($memberInfo['collects']){
            $start = ($page - 1) * $pageSize;
            $temp = array_slice($memberInfo['collects'],$start,$pageSize);
            $ids = implode(',',$temp);
            if(!$ids){
                return array('info' => '您什么都没收藏哦','data' => array(),'status' =>0);
            }
            $model = new Model();
            $makeLogic = new MakeImageLogic();
            $result = $model->query('select m.uid,m.thumb,m.nickname,m.height,m.weight,m.chestline,m.waistline,m.hipline,c.pic_json,c.id as cid,s.id_a as sid from mk_card as c left join mk_member_info as m on c.uid=m.uid left join mk_style as s on s.id=c.s_id where c.id in('. $ids .')');
            foreach ($result as $key => $val){
                $picArr = $val['pic_json'] ? json_decode($val['pic_json'],1) : array();
                $temp = array();
                foreach ($picArr as $k => $v){
                    $temp[$k] = $makeLogic->getImgParm($v,0,300,300);
                    $picArr[$k] = $makeLogic->getImgParm($v,0,0,750);
                }
                $result[$key]['pic_json'] = $picArr;
                $result[$key]['pic_json_thumb'] = $temp;
            }
            return $this->returnFormat('成功',array('list' => $result,'total' => count($memberInfo['collects'])),1);
        }else{
            return $this->returnFormat('您什么都没收藏哦~',array(),0);
        }
    }

    //放单大厅
    public function getOrder($param){
        $map = array(
            'where' => array(
                'status' => 1,
            ),
        );
        if($param['order'] == 0){
            $map['order'] = 'id desc';
        }else if($param['order'] == 1){
            $map['order'] = 'num desc';
        }else if($param['order'] == 2){
            $map['order'] = 'price desc';
        }else{
            return $this->returnFormat('排序类型错误');
        }

        if($param['page'] && $param['pageSize']){
            $start = ($param['page'] - 1) * $param['pageSize'];
            $map['limit'] = "{$start},{$param['pageSize']}";
        }

        list($list,$total) = D('Common/Order')->getListRows($map,true);
        return array('info' => '成功','data' => array('list' => $list,'total' => $total),'status' =>1);
    }

    //订单详情
    public function orderDetail($oid,$uid){
        if(!$oid){
            return $this->returnFormat('参数错误');
        }
        $orderInfo = D('Common/Order')->getById($oid);
        if(!$orderInfo || $orderInfo['status'] != 1){
            return $this->returnFormat('不存在此订单');
        }
        //获取该用户所有分享订单
        $ids = $this->getUserShare($uid);
        $orderInfo['isShare'] = false;
        if(in_array($oid,$ids)){
            $orderInfo['isShare'] = true;
        }
        return $this->returnFormat('成功',$orderInfo,1);
    }

    public function orderFormat($list,$uid){
        //获取该用户所有分享订单
        $ids = $this->getUserShare($uid);
        foreach($list as $key => $val){
            $list[$key]['create_time'] = date('m-d H:i',$val['create_time']);
            //判断该用户是否分享过该订单
            if(in_array($val['id'],$ids)){
                $list[$key]['isShare'] = true;
            }else{
                $list[$key]['isShare'] = false;
            }
            unset($list[$key]['status']);
        }
        return $list;
    }

    //获取用户所有分享 并返回id数组
    public function getUserShare($uid){
        $shareList = D('Common/Share')->getByUid($uid);
        $ids = array();
        foreach ($shareList as $item){
            $ids[] = $item['oid'];
        }
        return $ids;
    }

    //设置分享
    public function setShare($oid,$uid){
        if(!$oid || !$uid){
            return false;
        }

        //判断是否存在订单
        $oInfo = D('Common/Order')->getById($oid);
        if(!$oInfo){
            return $this->returnFormat('订单并不存在');
        }

        //获取该用户所有分享订单
        $ids = $this->getUserShare($uid);
        if(in_array($oid,$ids)){
            return $this->returnFormat('已经分享过了');
        }
        if(D('Common/Share')->saveData(array('uid' => $uid,'oid' => $oid))){
            return $this->returnFormat('设置分享成功','',1);
        }
        return $this->returnFormat('设置分享失败');
    }

    //帮助列表
    public function helps($page = 1,$pageSize){
        $start = ($page - 1) * $pageSize;
        $map = array(
            'where' => array(
                'status' => 1
            ),
            'order' => 'listorder asc',
            'field' => 'title,content',
            'limit' => "{$start},{$pageSize}",
        );
        list($list,$total) = D('Common/Article')->getListRows($map);
        return $this->returnFormat('成功',array('list' => $list,'total' => $total),1);
    }

    public function returnFormat($info = '',$data = array(),$status = 0){
        return array('status' => $status,'info' => $info,'data' => $data);
    }
}