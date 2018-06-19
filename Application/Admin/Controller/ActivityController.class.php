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
 * 活动控制器
 */
class ActivityController extends AdminController {

    /**
     * 活动列表
     */
    public function activity() {
        /* 查询条件初始化 */
        $map = array();
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }

        $list = $this->lists('Activity', $map);
        if (is_array($list)) {
            foreach ($list as &$v) {
                $v['start_time'] = date("Y-m-d H:i", $v['start_time']);
                $v['end_time'] = date("Y-m-d H:i", $v['end_time']);
            }
            unset($v);
        }

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);

        $this->meta_title = '活动列表';

        $this->display();
    }

    /**
     * 编辑活动
     */
    public function activityEdit($id = 0) {
        $model = D('Common/Activity');
        if (IS_POST) {
            $_POST['start_time'] = strtotime($_POST['start_time']);
            $_POST['end_time'] = strtotime($_POST['end_time']);
            $rs = $model->saveData();
            if ($rs) {
                $this->success('更新成功', Cookie('__forward__'));
            } else {
                $this->error($model->getError() ? : '更新失败');
            }
        }
        $info = array();
        /* 获取数据 */
        if ($id) {
            $info = $model->getById($id);
            if (false === $info) {
                $this->error('获取活动信息错误');
            }
            $info['start_time'] = date('Y/m/d H:i', $info['start_time']);
            $info['end_time'] = date('Y/m/d H:i', $info['end_time']);
        }

        $this->assign('info', $info);

        $this->meta_title = '编辑活动';

        $this->display('activity_edit');
    }

    /**
     * 删除活动
     */
    public function activityDel() {
        $model = D('Common/Activity');
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = $model->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 抽奖奖品
     */
    public function drawPrize() {
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }

        $list = $this->lists('ActivityDrawPrize', $map);

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);

        $this->meta_title = '奖品列表';

        $this->display('draw_prize');
    }

    /**
     * 编辑奖品
     */
    public function drawPrizeEdit($id = 0) {
        $model = D('Common/ActivityDrawPrize');
        if (IS_POST) {
            $rs = $model->saveData();
            if ($rs) {
                $this->success('更新成功', Cookie('__forward__'));
            } else {
                $this->error($model->getError() ? : '更新失败');
            }
        }
        $info = array();
        /* 获取数据 */
        if ($id) {
            $info = $model->getById($id);
            if (false === $info) {
                $this->error('获取奖品信息错误');
            }
        }

        $this->assign('info', $info);

        $this->meta_title = '编辑奖品';

        $this->display('draw_prize_edit');
    }

    /**
     * 删除活动
     */
    public function drawPrizeDel() {
        $this->error('敲代码的不让你删！');
        $model = D('Common/ActivityDrawPrize');
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = $model->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 抽奖用户
     */
    public function drawUser() {
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));
        if (isset($_GET['title'])) {
            $map['username'] = array('like', '%' . (string) I('title') . '%');
        }

        $list = $this->lists('ActivityDrawUser', $map);
        if (is_array($list)) {
            foreach ($list as &$v) {
                $v['create_time'] = date("Y-m-d H H:i", $v['create_time']);
            }
            unset($v);
        }

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);

        $this->meta_title = '用户列表';

        $this->display('draw_user');
    }

    /**
     * 编辑奖品
     */
    public function drawUserEdit($id = 0) {
        $model = D('Common/ActivityDrawUser');
        if (IS_POST) {
            $rs = $model->saveData();
            if ($rs) {
                $this->success('更新成功', Cookie('__forward__'));
            } else {
                $this->error($model->getError() ? : '更新失败');
            }
        }
        $info = array();
        /* 获取数据 */
        if ($id) {
            $info = $model->getById($id);
            if (false === $info) {
                $this->error('获取奖品信息错误');
            }
        }

        $this->assign('info', $info);

        $this->meta_title = '编辑奖品';

        $this->display('draw_user_edit');
    }

    /**
     * 抽奖记录
     */
    public function drawLog() {
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));

        $status = I('get.status', -1, 'intval');
        $mobile = I('get.mobile', '', 'op_t');
        $username = I('get.username', '', 'op_t');
        if ($mobile || $username) {
            $user_map = array();
            $mobile && $user_map['mobile'] = $mobile;
            $username && $user_map['username'] = array('like', '%' . $username . '%');
            $user = D('ActivityDrawUser')->where($user_map)->find();
            if ($user) {
                $map['uid'] = $user['id'];
            } else {
                $map['uid'] = 0;
            }
        }
        if ($status == 1) {
            $map['pid'] = array('neq', 1);
        } else if ($status == 0) {
            $map['pid'] = array('eq', 1);
        }
        $this->assign('status', $status);

        $list = $this->lists('ActivityDrawLog', $map, 'status DESC,create_time ASC');
        if (is_array($list)) {
            foreach ($list as &$v) {
                $v['user'] = D('ActivityDrawUser')->getById($v['uid']);
                $v['prize'] = D('ActivityDrawPrize')->getById($v['pid']);
                $v['create_time'] = date("Y-m-d H H:i", $v['create_time']);
            }
            unset($v);
        }

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);

        $this->meta_title = '抽奖记录';

        $this->display('draw_log');
    }

    public function drawLogExport() {
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));

        $status = I('get.status', -1, 'intval');
        $mobile = I('get.mobile', '', 'op_t');
        $username = I('get.username', '', 'op_t');
        if ($mobile || $username) {
            $user_map = array();
            $mobile && $user_map['mobile'] = $mobile;
            $username && $user_map['username'] = array('like', '%' . $username . '%');
            $user = D('ActivityDrawUser')->where($user_map)->find();
            if ($user) {
                $map['uid'] = $user['id'];
            } else {
                $map['uid'] = 0;
            }
        }
        if ($status == 1) {
            $map['pid'] = array('neq', 1);
        } else if ($status == 0) {
            $map['pid'] = array('eq', 1);
        }

        $data = array();
        $list = D('Common/ActivityDrawLog')->getListRows(array('where' => $map, 'order' => 'status DESC,create_time ASC', 'limit' => 99999), FALSE);
        if (is_array($list)) {
            foreach ($list as &$v) {
                $arr = array();
                $user = D('ActivityDrawUser')->getById($v['uid']);
                $prize = D('ActivityDrawPrize')->getById($v['pid']);
                $arr[] = $v['id'];
                $arr[] = $user['username'];
                $arr[] = $user['mobile'];
                $arr[] = $prize['title'];
                $arr[] = date("Y-m-d H H:i", $v['create_time']);
                $data[] = $arr;
            }
            unset($v);
        }
        LC('Common/Excel')->exportExcel('中奖记录表', array('ID', '用户名', '手机号', '奖品名称', '抽奖时间'), $data);
    }

}
