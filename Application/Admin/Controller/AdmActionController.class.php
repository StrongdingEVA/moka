<?php

namespace Admin\Controller;

class AdmActionController extends AdminController {

    function __construct() {
        parent::__construct('Common/AdminActionLog');
        $type = array(1 => '主题', 2 => '主题内容源', 3 => '主题资讯', 4 => '看看分类', 5 => '看看主题');
        $sts = array(1 => '新增', 2 => '编辑', 3 => '删除', 4 => '禁用', 5 => '启用', 6 => '隐藏', 7 => '显示');
        $this->assign('g_type', $type);
        $this->assign('g_status', $sts);
    }

    //过滤查询字段
    function _filter(&$map) {
        $type = I('request.type', 0, 'intval');
        if ($type <= 0) {
            unset($map['type']);
        }

        $status = I('request.status', 0, 'intval');
        if ($status <= 0) {
            unset($map['status']);
        }

        $aid = I('request.aid', 0, 'intval');
        if ($aid <= 0) {
            unset($map['aid']);
        }

        $time = I('request.create_time', '', 'op_t');
        $time = $time ? strtotime($time) : 0;
        if ($time <= 0) {
            unset($map['create_time']);
        } else {
            $map['create_time'] = array('egt', $time);
        }


        $this->type = urldecode($_REQUEST['type']);
        $this->status = urldecode($_REQUEST['status']);
        $this->aid = urldecode($_REQUEST['aid']);
        $this->create_time = urldecode($_REQUEST['create_time']);

        $this->request_params['create_time'] = urldecode($_REQUEST['create_time']);
        $this->request_params['status'] = urldecode($_REQUEST['status']);
        $this->request_params['type'] = urldecode($_REQUEST['type']);
        $this->request_params['aid'] = urldecode($_REQUEST['aid']);
    }

    public function index() {

        $map = $this->_search();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }

        $model = D('AdminActionLog');
        if (!empty($model)) {
            $this->_list($model, $map);
        }

        $list = $this->get('list');
        foreach ($list as &$v) {
            $v['user'] = query_user(array('uid', 'username', 'nickname'), $v['aid']);
        }

        $users = D('AdminActionLog')->distinct(true)->field('aid')->select();
        foreach ($users as &$v) {
            $v = query_user(array('uid', 'nickname', 'username'), $v['aid']);
        }

        $this->assign('list', $list);
        $this->assign('users', $users);

        $this->display();
    }

}

?>
