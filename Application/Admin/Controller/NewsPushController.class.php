<?php

namespace Admin\Controller;

class NewsPushController extends AdminController {

    function __construct() {
        parent::__construct('NewsPush');
    }

    //过滤查询字段
    function _filter(&$map) {
        if (!empty($_POST['title'])) {
            $map['title'] = array('like', "%" . $_POST['title'] . "%");
            $this->assign('title', $_POST['title']);
        }      
        if (!empty($_POST['rt'])) {
            $map['_string'] = 'release_time >=' . strtotime($_POST['rt']) . ' AND release_time <=' . strtotime($_POST['rt'].' 23:59:59');           
            $this->assign('rt', $_POST['rt']);
        }
    }

    public function _before_index() {
        
    }

    public function _before_add() {
        if (isset($_GET['id'])) {
            $this->catid = $_GET['id'];
        }
    }

    public function _before_edit() {
        
    }

    public function update() {
        parent::update();
    }

    public function insert() {
        parent::insert();
    }

    public function index() {
        $_REQUEST['_order'] = 'listorder desc,release_time desc';
        parent::index();
        $list = $this->get('list');
        foreach ($list as &$v) {
            $v['news'] = D('WpNews')->where(array('id' => $v['obj_id']))->find();
        }
        $this->assign('list', $list);
    }

    public function delete() {
        $id = I('get.id', 0, 'intval');
        $rs = D('NewsPush')->where(array('id' => $id))->delete();
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
     * 添加资讯热推
     */
    function newspushadd() {
        $sid = I('post.sid', 0, 'intval');
        $aid = I('post.cid', 0, 'intval');
        $row = D('WpNews')->where(array('id' => $aid))->find();
        if (!$row) {
            $this->error('资讯不存在');
            return;
        }
        $wp = D('WpAccount')->where(array('id' => $row['aid']))->find();
        $sapp = D('NewsPush')->where(array('id' => $aid, 'type' => $sid))->find();
        if ($sapp) {
            $this->error('该资讯已添加');
            return;
        }
        $arr = array();
        $arr['catid'] = isset($wp['catid']) ? $wp['catid'] : 0;
        $arr['obj_id'] = $aid;
        $arr['type'] = $sid;
        $arr['listorder'] = 0;
        $arr['create_time'] = time();
        $arr['release_time'] = time()+180;
        $arr['title'] = isset($row['title']) ? $row['title'] : '';
        $rs = D('NewsPush')->add($arr);
        if ($rs) {
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    function getnewslist() {
        $search = I('request.search', '');
        $map = array();
        $search && $map['title'] = array('like', "%" . urldecode($search) . "%");
        $rows = D('WpNews')->limit(50)->where($map)->select();
        foreach ($rows as &$v) {
            $v['wp_name'] = D('Common/WpAccount')->getById($v['aid'])['title'];
        }
        echo json_encode($rows);
    }

    function ajax_set_order() {
        $id = I('get.id', 0, 'intval');
        $order = I('get.order', 0, 'intval');
        $rs = D('NewsPush')->saveData(array('listorder' => $order), array('id' => $id));
        if ($rs) {
            //成功提示           
            $this->success('操作成功!');
        } else {
            //错误提示
            $this->error('操作失败!');
        }
    }

    function ajax_set_rt() {
        $id = I('post.id', 0, 'intval');
        $rt = I('post.rt', 0, 'op_t');
        $rt && $rt = strtotime($rt);
        $rs = D('NewsPush')->saveData(array('release_time' => $rt), array('id' => $id));
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
