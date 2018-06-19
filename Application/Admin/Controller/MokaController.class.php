<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Api\Logic\MakeImageLogic;
use Common\Model\MemberInfoModel;
use Common\Model\StyleModel;
use Think\Template\TagLib\Html;

/**
 * 广告管理控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class MokaController extends AdminController
{
    /**
     * 风格列表
     */
    public function index(){
        $name = I("name");

        $list = $this->lists('Style', array("name" => $name), 'id');

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign("list",$list);
        $this->assign("name",$name);
        $this->display();
    }

    /**
     * @param int $id
     * 分割编辑
     */
    public function editStyle($id = 0){
        $model = D('Common/Style');
        if (IS_POST) {
            $rs = $model->saveData();
            if ($rs) {
                $this->success('更新成功', Cookie('__forward__'));
            } else {
                $this->error($model->getError() ? : '更新失败');
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = $model->getById($id);

            if (false === $info) {
                $this->error('获取模卡风格信息错误');
            }
            $this->assign('info', $info);

            $this->meta_title = '编辑模卡风格';

            $this->display('edit_style');
        }
    }

    /**
     * 模卡风格删除
     */
    public function delStyle() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/Style')->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 模卡列表
     */
    public function cardList(){
        //查询所有风格
        $cat = $this->lists('Style', array(), 'id');

        //生成条件
        $where = array();
        $status = I("status");
        $s_id = I("s_id");
        $nickname = trim(I("nickname"));

        if($status){
            $where['status'] = $status;
        }
        if($s_id){
            $where['s_id'] = $s_id;
        }
        if($nickname){
            $where['nickname'] = $nickname;
        }

        $list = $this->lists('Card',$where,'id desc','');

        $modelMember = new MemberInfoModel();
        $modelStyle = new StyleModel();
        foreach ($list as $key => $val){
            $memberInfo = $modelMember->where(array('uid' => $val['uid']))->find();
            $styleInfo = $modelStyle->where(array('id' => $val['s_id']))->find();
            $list[$key]['nickname'] = $memberInfo['nickname'];
            $list[$key]['s_name'] = $styleInfo['name'];
            $picArr = json_decode($val['pic_json'],1);
            foreach ($picArr as $k => $v){
                $picArr[$k] = $this->getImgParm($v,0,100,100);
            }
            $list[$key]['pic_json'] = $picArr;
        }

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->assign('cat', $cat);
        $this->assign('status', $status);
        $this->assign('s_id', $s_id);
        $this->assign('nickname', $nickname);
        $this->display('card_list');
    }

    /**
     * @param int $cid
     * 编辑模卡
     */
    public function editCard($cid = 0){
        if(!$cid){
            $this->error('操作失败！');
        }

        //查询所有风格
        $cat = $this->lists('Style', array(), 'id');

        $model = D('Common/Card');
        if (IS_POST) {
            $rs = $model->saveData();
            if ($rs) {
                $this->success('更新成功', Cookie('__forward__'));
            } else {
                $this->error($model->getError() ? : '更新失败');
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = $model->getById($cid);
            if (false === $info) {
                $this->error('获取模卡信息错误');
            }
            $info['pic_json'] = json_decode($info['pic_json'],1);
            foreach ($info['pic_json'] as $k => $v){
                $info['pic_json'][$k] = $this->getImgParm($v,0,500,500);
            }

            //获取用户信息
            $uModel = D('Common/Member');
            $uInfo = $uModel->getById($info['uid']);

            $this->meta_title = '编辑模卡';
            $this->assign('info', $info);
            $this->assign('cat', $cat);
            $this->assign('uInfo', $uInfo);
            $this->display('edit_card');
        }
    }

    /**
     * 删除模卡
     */
    public function delCard(){
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/Card')->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 禁用启用
     */
    public function changeCard($id, $value = 1, $model = 'Card', $field = 'status') {
        $rs = D($model)->changeById($id, $field, $value);
        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }

    public function modelCon(){
        if(IS_POST){
            $status = I('status');
            $str = '<?php return $modelArr=array("modelController"=>'. $status .')?>';
            $hand = fopen('static/modelController.php','w');
            fwrite($hand,$str);
            fclose($hand);
            $this->success('操作成功');
        }else{
            require_once 'static/modelController.php';
            $this->assign('modelController',$modelArr['modelController']);
        }
        $this->display('model_con');
    }

    //模卡榜单
    public function order(){
        $map = array();
        $type = I('type',0);
        $name = I('name','');
        if($type){
            $map['type'] = $type;
        }
        if($name){
            $name = trim($name);
            $map['name'] = array('like', '%' . (string) $name . '%');
        }
        $list = $this->lists('Order', $map, 'create_time DESC', '');

        $this->assign('list', $list);
        $this->assign('name', $name);
        $this->assign('type', $type);

        $this->meta_title = '模卡榜单';
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display('order');
    }

    //编辑订单
    public function orderEdit(){
        if (IS_POST) {
            $rs = D('Common/Order')->saveData();
            if (!$rs) {
                $this->error('操作失败');
            }
            $this->success('操作成功', Cookie('__forward__'));
        }
        $info = array();
        $id = I('get.id', 0, 'intval');
        if ($id) {
            $info = D('Common/Order')->getById($id);
            if (false === $info) {
                $this->error('参数错误');
            }
        }
        $this->assign('info', $info);
        $this->display('order_edit');
    }

    //删除订单
    public function orderDel($id){
        $rs = D('Order')->delById($id);
        if (!$rs) {
            $this->error('删除失败');
        }
        $this->success('删除成功', Cookie('__forward__'));
    }

    //模卡用户
    public function member(){
        $map = array();
        $type = I('type',0);
        $name = I('nickname','');
        if($name){
            $name = trim($name);
            $map['nickname'] = array('like', '%' . (string) $name . '%');
        }
        $list = $this->lists('MemberInfo', $map, 'create_time DESC', '');

        $this->assign('list', $list);
        $this->assign('nickname', $name);
        $this->assign('type', $type);

        $this->meta_title = '用户列表';
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

    public function memberEdit(){
        if (IS_POST) {
            $uid = $_POST['uid'];
            $info = D('Common/MemberInfo')->getById($uid);
            $rs = D('Common/MemberInfo')->saveData($_POST);
            if (!$rs) {
                $this->error('操作失败');
            }

            //修改模卡底部图片
            $arr = $_POST;
            $isUpPic = false;
            $arrKey = array('naughty','level','province','city','height','weight','chestline','waistline','hipline');
            foreach ($arrKey as $k => $v){
                if($arr[$v] != $info[$v]){
                    $isUpPic = true;
                    break;
                }
            }

            if($isUpPic) {
                //修改用户的模卡
                $ImgLogic = new MakeImageLogic();
                $path = $ImgLogic->createParam($_POST);
                if(!$path){
                    $this->error("生成信息图失败");
                }
                $arr['pic'] = $path;
                D('Common/MemberInfo')->saveData($arr);
                $result = $ImgLogic->unionPicAgain($uid);
                if($result['status'] != 1){
                    $this->error($result['info']);
                }
            }
            $this->success('操作成功', Cookie('__forward__'));
        }
        $info = array();
        $id = I('get.uid', 0, 'intval');
        if ($id) {
            $info = D('Common/MemberInfo')->getById($id);
            if (false === $info) {
                $this->error('参数错误');
            }
        }
        $this->assign('info', $info);
        $this->display('member_edit');
    }

    public function getImgParm($path,$model = 0,$w = 300,$h = 300){
        if(!$path){
            return '';
        }
        $index = strpos($path,'imageView2');
        if($index !== false){
            return substr($path,0,$index) . 'imageView2/'. $model .'/w/'. $w .'/h/' . $h;
        }elseif(strpos($path,'guimizone') !== false){
            return $path . '?imageView2/'. $model .'/w/'. $w .'/h/' . $h;
        }
        return $path;
    }

    public function reward(){
        //生成条件
        $where = array();
        $status = I("status",-1,'intval');
        $nickname = trim(I("nickname"));

        if($status != -1){
            $where['status'] = $status;
        }
        $modelMember = new MemberInfoModel();
        if($nickname){
            $map = array(
                'nickname' => array('like' , '%' . $nickname .'%')
            );
            $uList = $modelMember->where($map)->select();
            $uidArr = array();
            foreach ($uList as $k => $v){
                $uidArr[] = $v['uid'];
            }
            $ids = implode(',',$uidArr);
            if(!$ids){
                $list = array();
                $this->assign('list', $list);
                $this->assign('status', $status);
                $this->display('reward');
            }else{
                $where['uid'] = array('in',$ids);
            }
        }
        $list = $this->lists('UnfiedOrder',$where,'id desc','');

        foreach ($list as $key => $val){
            $memberInfo = $modelMember->where(array('uid' => $val['uid']))->find();
            $memberInfoRe = $modelMember->where(array('uid' => $val['rewarder']))->find();
            $list[$key]['u_nickname'] = $memberInfo['nickname'];
            $list[$key]['r_nickname'] = $memberInfoRe['nickname'];
        }

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->assign('status', $status);
        $this->display('reward');
    }

    public function delOrder($id){
        $rs = D('UnfiedOrder')->delById($id);
        if (!$rs) {
            $this->error('删除失败');
        }
        $this->success('删除成功', Cookie('__forward__'));
    }

    /**
     * 编辑文章
     */
    public function edit($id = 0) {
        $model = D('Common/Article');
        if (IS_POST) {
            $_POST['create_time'] = strtotime($_POST['create_time']);
            unset($_POST['parse']);
            $rs = $model->saveData();
            if ($rs) {
                $this->success('更新成功', Cookie('__forward__'));
            } else {
                $this->error($model->getError() ? : '更新失败');
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = $model->getById($id);
            if (false === $info) {
                $this->error('获取配置信息错误');
            }
            $info['create_time'] = date('Y/m/d h:i', $info['create_time']);
            $this->assign('info', $info);

            $this->meta_title = '编辑文章';

            $this->display('Article/edit');
        }
    }

    /**
     * 删除文章
     */
    public function del() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/Article')->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
}

?>