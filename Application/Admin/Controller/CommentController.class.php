<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 评论管理控制器
 */
class CommentController extends AdminController {

    /**
     * 文章列表
     */
    public function index() {
        $where = array();
        if (isset($_GET['content']) && $_GET['content']) {
            $where['content'] = array('like', '%' . (string) I('content') . '%');

            $user = D('Common/Member')->where(array('nickname'=>array('like', '%' . (string) I('content') . '%')))->select();
            if ($user) {
                $uids = getSubByKey($user, 'uid');
                $where['uid'] = array('in', $uids);
                $where['_logic'] = 'or';
            }
        }



        $map = array();
        $where && $map['_complex'] = $where;
        // 状态
        if (isset($_GET['status'])) {
            $map['status'] = $_GET['status'];
            $this->assign('status', $_GET['status']);
        }
        if (isset($_GET['type'])) {
            $map['type'] = $_GET['type'];
            $this->assign('type', $_GET['type']);
        }


        $list = $this->lists('ViewComment', $map, 'create_time DESC');
        foreach ($list as &$v) {
            $v['user'] = query_user(array('nickname'), $v['uid']);
        }


        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);

        $this->meta_title = '评论列表';
        $this->display();
    }

    /**
     * 删除文章
     */
    public function del() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        if (count($id) == 1) {
            $rs = D('Common/Comment')->delById($id[0]);
            if ($rs) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败！');
            }
            return;
        } else {
            foreach ($id as $v) {
                D('Common/Comment')->delById($v);
            }
        }
        $this->success('删除成功');
    }

    /**
     * 禁用启用
     */
    public function changeStatus() {
        $id = I('request.id', 0, 'intval');
        $sts = I('request.sts', 1, 'intval');
        $row = D('Common/Comment')->getById($id);
        if (!$row) {
            $this->error('操作失败！');
        }
        $rs = D('Common/Comment')->saveData(array('status' => $sts, 'obj_id' => $row['obj_id']), array('id' => $id));
        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }

}
