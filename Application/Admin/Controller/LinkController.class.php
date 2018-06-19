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
 * 友情链接管理控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class LinkController extends AdminController
{
    /**
     *  友链列表
     */
    public function index() {
        $map = array();
        $map = array('status' => array('egt', 0));
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }

        $list = $this->lists('Link', $map, 'sort DESC,id DESC');
        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);

        $this->meta_title = '友链列表';
        $this->display();
    }

    /**
     * 新增
     */
    public function add() {
        if (IS_POST) {
            $model = D('Common/Link');
            $rs = $model->saveData();
            if ($rs) {
                $this->success('新增成功', U('index'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            $this->assign('info', null);

            $this->meta_title = '新增';

            $this->display('edit');
        }
    }

    /**
     * 编辑
     */
    public function edit($id = 0) {
        $model = D('Common/Link');
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
                $this->error('获取友情链接信息错误');
            }
            $this->assign('info', $info);

            $this->meta_title = '编辑';

            $this->display('edit');
        }
    }

    /**
     * 删除
     */
    public function del() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/Link')->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    
    /**
     * 配置排序
     */
    public function sort() {
        if (IS_GET) {
            $ids = I('get.ids');

            //获取排序的数据
            $map = array('status' => array('gt', -1));
            if (!empty($ids)) {
                $map['id'] = array('in', $ids);
            } elseif (I('group')) {
                $map['group'] = I('group');
            }
            $list = M('Link')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = 'SEO规则排序';
            $this->display();
        } elseif (IS_POST) {
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key => $value) {
                $res = M('Link')->where(array('id' => $value))->setField('sort', $key + 1);
            }
            if ($res !== false) {
                $this->success('排序成功！', Cookie('__forward__'));
            } else {
                $this->error('排序失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }

    /**
     * 禁用启用
     */
    public function changeField($id, $value = 1, $model = 'Link', $field = 'status') {
        $rs = D($model)->changeById($id, $field, $value);
        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }

}

?>