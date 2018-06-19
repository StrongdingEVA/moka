<?php

namespace Admin\Controller;

class FeedbackController extends AdminController {

    function __construct() {
        parent::__construct('Feedback');
    }

    //过滤查询字段
    function _filter(&$map) {   
       
        
        if (!empty($_GET['title'])) {
            $map['title'] = array('like', "%" . $_GET['title'] . "%");
            $this->assign('title', $_GET['title']);
        }

//        $this->assign('type', $_GET['type']);
//        $this->assign('status', $_GET['status']);
    }

    public function _before_index() {
        if (isset($_REQUEST['type'])) {
            $this->type = $_GET['type'];
        }
        if (isset($_REQUEST['status'])) {
            $this->status = $_GET['status'];
        }
    }
 

    public function index() {      
        $map = $this->_search();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        if($map['type']<0){
            unset($map['type']);
        }
        if($map['status']<0){
            unset($map['status']);
        }      

        $model = D('Feedback');
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
        $row = D('Feedback')->where(array('cid' => $id))->count();
        if ($row) {
            $this->error('分类下存在数据无法删除!');
        }

        $rs = D('Feedback')->where(array('id' => $id))->delete();
        if ($rs) {
            //成功提示
            $this->assign('jumpUrl', cookie('_currentUrl_'));
            $this->success('操作成功!');
        } else {
            //错误提示
            $this->error('操作失败!');
        }
    }

    function ajax_set_sts() {
        $id = I('get.id', 0, 'intval');      
        $rs = D('Common/Feedback')->saveData(array('status' => 1), array('id' => $id));       
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
