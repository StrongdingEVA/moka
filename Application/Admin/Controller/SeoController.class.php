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
 * SEO规则控制器
 */
class SeoController extends AdminController
{

    /**
     * SEO规则列表
     */
    public function index() {
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }

        $list = $this->lists('SeoRule', $map, 'sort,id');
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->meta_title = 'SEO规则管理';
        $this->display();
    }

    /**
     * 新增SEO规则
     */
    public function add() {
        if (IS_POST) {
            $Seo = D('SeoRule');
            $data = $Seo->create();
            if ($data) {
                if ($Seo->add()) {
                    $this->success('新增成功', U('index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Seo->getError());
            }
        } else {
            $this->meta_title = '新增配置';
            $this->assign('info', null);
            $this->display('edit');
        }
    }

    /**
     * 编辑SEO规则
     */
    public function edit($id = 0) {
        if (IS_POST) {
            $Seo = D('SeoRule');
            $data = $Seo->create();
            if ($data) {
                if ($Seo->save()) {
                    //记录行为
                    action_log('update_seo', 'seo', $data['id'], UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($Seo->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('SeoRule')->field(true)->find($id);

            if (false === $info) {
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);
            $this->meta_title = '编辑SEO规则';
            $this->display();
        }
    }
    
    /**
     * 删除SEO规则
     */
    public function del() {
        $id = array_unique((array) I('id', 0));

        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id));
        if (M('SeoRule')->where($map)->delete()) {
            //记录行为
            action_log('update_seo', 'Seo', $id, UID);
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
            $list = M('SeoRule')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = 'SEO规则排序';
            $this->display();
        } elseif (IS_POST) {
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key => $value) {
                $res = M('SeoRule')->where(array('id' => $value))->setField('sort', $key + 1);
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
    public function changeStatus($id, $value = 1) {
        $this->editRow('SeoRule', array('status' => $value), array('id' => $id));
    }

}
