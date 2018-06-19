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
 * 后台频道控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ChannelController extends AdminController
{

    /**
     * 频道列表
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index() {
        $pid = I('get.pid', 0);
        /* 获取频道列表 */
        $map = array('status' => array('gt', -1), 'pid' => $pid);
        $list = M('Channel')->where($map)->order('sort asc,id asc')->select();

        $this->assign('list', $list);
        $this->assign('pid', $pid);
        $this->meta_title = '导航管理';

        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    /**
     * 添加频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function add() {
        if (IS_POST) {
            $model = D('Common/Channel');
            $rs = $model->saveData();
            if ($rs) {
                action_log('update_channel', 'channel', $rs, UID);
                $this->success('新增成功', U('index'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            $pid = I('get.pid', 0);
            //获取父导航
            if (!empty($pid)) {
                $parent = M('Channel')->where(array('id' => $pid))->field('title')->find();
                $this->assign('parent', $parent);
            }

            $this->assign('pid', $pid);
            $this->assign('info', null);
            $this->meta_title = '新增导航';
            $this->display('edit');
        }
    }

    /**
     * 编辑频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function edit($id = 0) {
        $model = D('Common/Channel');
        if (IS_POST) {
            $id = I('post.id', '', 'intval');
            $rs = $model->saveData();
            if ($rs) {
                action_log('update_channel', 'channel', $id, UID);
                $this->success('更新成功', cookie('__forward__'));
            } else {
                $this->error($model->getError() ? : '更新失败');
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = $model->getById($id);

            if (false === $info) {
                $this->error('获取导航信息错误');
            }

            $pid = I('get.pid', 0);
            //获取父导航
            if (!empty($pid)) {
                $parent = $model->getById($pid);
                $this->assign('parent', $parent);
            }

            $this->assign('pid', $pid);
            $this->assign('info', $info);
            $this->meta_title = '编辑导航';
            $this->display();
        }
    }

    /**
     * 删除频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function del() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/Channel')->delById($id);
        if ($rs) {
            action_log('update_channel', 'channel', $id, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 导航排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort() {
        if (IS_GET) {
            $ids = I('get.ids');
            $pid = I('get.pid');

            //获取排序的数据
            $map = array('status' => array('gt', -1));
            if (!empty($ids)) {
                $map['id'] = array('in', $ids);
            } else {
                if ($pid !== '') {
                    $map['pid'] = $pid;
                }
            }
            $list = M('Channel')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = '导航排序';
            $this->display();
        } elseif (IS_POST) {
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            $rs = D('Common/Channel')->sortById($ids);
            if ($rs !== false) {
                $this->success('排序成功！');
            } else {
                $this->error('排序失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }

}
