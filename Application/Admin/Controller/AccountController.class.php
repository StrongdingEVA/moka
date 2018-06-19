<?php

namespace Admin\Controller;

class AccountController extends AdminController {

    function __construct() {
        parent::__construct('AccountPushCategory');
    }

    //过滤查询字段
    function _filter(&$map) {
        if (!empty($_POST['catid'])) {
            $map['catid'] = array('eq', $_POST['catid']);
            $this->assign('catid', $_POST['catid']);
        }
        if (!empty($_POST['title'])) {
            $map['title'] = array('like', "%" . $_POST['title'] . "%");
            $this->assign('title', $_POST['title']);
        }
    }

    public function _before_index() {
        if (isset($_REQUEST['catid'])) {
            $this->catid = $_GET['catid'];
        }
    }

    public function _before_add() {
        if (isset($_GET['id'])) {
            $this->catid = $_GET['id'];
        }
    }

    public function _before_edit() {
        
    }

    public function update() {
        $_POST['create_time'] = strtotime($_POST['create_time']);
        parent::update();
    }

    public function insert() {
        $_POST['create_time'] = strtotime($_POST['create_time']);
        parent::insert();
    }

    public function index() {
        //列表过滤器，生成查询Map对象
        $map = $this->_search();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }      

        $model = D('AccountPushCategory');
        if (!empty($model)) {
            $this->_list($model, $map);
        }

        $list = $this->get('list');
        foreach ($list as &$v) {
            $v['count'] = D('AccountPush')->where(array('cid' => $v['id']))->count();
        }

        $this->assign('list', $list);
        $this->display();
    }

    public function delete() {
        $id = I('get.id', 0, 'intval');
        $row = D('AccountPush')->where(array('cid' => $id))->count();
        if($row){
           $this->error('分类下存在数据无法删除!'); 
        }
         
        $rs = D('AccountPushCategory')->where(array('id' => $id))->delete();
        if ($rs) {
            //成功提示
            $this->assign('jumpUrl', cookie('_currentUrl_'));
            $this->success('操作成功!');
        } else {
            //错误提示
            $this->error('操作失败!');
        }
    }

    /**
     * 添加小程序到专题
     */
    function appadd() {
        $aid = I('post.aid', 0, 'intval');
        $cid = I('post.cid', 0, 'intval');
        $row = D('AccountPushCategory')->find($cid);
        if (!$row) {
            $this->error('分类不存在');
            return;
        }
        $sapp = D('AccountPush')->where(array('aid' => $aid, 'cid' => $cid))->find();
        if ($sapp) {
            $this->error('该账号已添加到分类');
            return;
        }
        $arr = array();
        $arr['aid'] = $aid;
        $arr['cid'] = $cid;  
        $arr['status'] = 1;
        $rs = D('AccountPush')->add($arr);
        if ($rs) {
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    function getlist() {
        $search = I('request.search', '');
        $map = array();
        $search && $map['title'] = array('like', "%" . urldecode($search) . "%");
        $rows = D('AccountPushCategory')->where($map)->select();
        echo json_encode($rows);
    }

    function getapplist() {
        $search = I('request.search', '');
        $map = array();
        $search && $map['title'] = array('like', "%" . urldecode($search) . "%");
        $rows = D('WpAccount')->where($map)->select();
        echo json_encode($rows);
    }

    function applist() {
        $id = I('get.id', 0, 'intval');
        $model = D('AccountPush');
        if (!empty($model)) {
            $_REQUEST ['_order']='listorder desc,id desc';
            $this->_list($model, array('cid' => $id));
        }
        $list = $this->get('list');
        foreach ($list as &$v) {
            $v['app'] = D('WpAccount')->where(array('id' => $v['aid']))->find();
            $s = D('AccountPushCategory')->find($v['cid']);
            $v['title'] = $s ? $s['title'] : '';
        }
        $this->assign('list', $list);

        $this->assign('cid', $id);
        $this->display();
    }

    function appdel() {
        $id = I('get.id', 0, 'intval');
        $rs = D('AccountPush')->where(array('id' => $id))->delete();
        if ($rs) {
            //成功提示
            $this->assign('jumpUrl', cookie('_currentUrl_'));
            $this->success('操作成功!');
        } else {
            //错误提示
            $this->error('操作失败!');
        }
    }
    
    function ajax_app_order() {
        $id = I('get.id', 0, 'intval');
        $order = I('get.order', 0, 'intval');
        $rs = D('AccountPush')->saveData(array('listorder' => $order), array('id' => $id));
        if ($rs) {
            //成功提示           
            $this->success('操作成功!');
        } else {
            //错误提示
            $this->error('操作失败!');
        }
    }

}

?>
