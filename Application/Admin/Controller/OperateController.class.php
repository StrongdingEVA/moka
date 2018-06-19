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
 * 运营控制器
 */
class OperateController extends AdminController
{
    
    /**
     * 帮助列表
     */
    public function help() {
        /* 查询条件初始化 */
        $map = array();
        // 状态筛选
        $status = I('get.status', -1, 'intval');
        if ($status != -1) {
            $map['status'] = $status;
        }
        $this->assign('status', $status);
        // 分类筛选
        $cat = I('get.cat', -1, 'intval');
        if ($cat != -1) {
            $map['cid'] = array('eq', $cat);
        }
        $this->assign('cat', $cat);
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }
        
        // 获取分类列表
        $catlist = D('Common/HelpCategory')->getListRows(array('where' => array('status' => 1)), FALSE);
        $catlist = field2array_key($catlist, 'id');
        $this->assign('catlist', $catlist);

        $list = $this->lists('Help', $map, 'sort DESC');
        if (is_array($list)) {
            foreach ($list as &$v) {
                $v['cat_name'] = $catlist[$v['cid']]['title'];
                $v['create_time'] = date("Y-m-d", $v['create_time']);
                $v['update_time'] = date("Y-m-d", $v['update_time']);
            }
            unset($v);
        }
        
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->meta_title = '帮助列表';
        
        $this->display();
    }

    /**
     * 新增帮助
     */
    public function helpAdd() {
        $model = D('Common/Help');
        if (IS_POST) {
            $_POST['uid'] = UID;
            $rs = $model->saveData();
            if ($rs) {
                $this->success('新增成功', U('help'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            $this->assign('info', null);
            
            $catlist = D('Common/HelpCategory')->getListRows(array('where' => array('status' => 1)), FALSE);
            $catlist = field2array_key($catlist, 'id');
            $this->assign('catlist', $catlist);

            $this->meta_title = '新增帮助';

            $this->display('help_edit');
        }
    }

    /**
     * 编辑帮助
     */
    public function helpEdit($id = 0) {
        $model = D('Common/Help');
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
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);
            
            $catlist = D('Common/HelpCategory')->getListRows(array('where' => array('status' => 1)), FALSE);
            $catlist = field2array_key($catlist, 'id');
            $this->assign('catlist', $catlist);

            $this->meta_title = '编辑帮助';

            $this->display('help_edit');
        }
    }
    
    /**
     * 删除帮助
     */
    public function helpDel() {
        $model = D('Common/Help');
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
     * 帮助分类
     */
    public function helpCategory() {
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }

        $list = $this->lists('HelpCategory', $map, 'sort DESC');
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->meta_title = '帮助分类列表';
        $this->display('help_category');
    }

    /**
     * 新增帮助分类
     */
    public function helpCategoryAdd() {
        $model = D('Common/HelpCategory');
        if (IS_POST) {
            $rs = $model->saveData();
            if ($rs) {
                $this->success('新增成功', U('helpCategory'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            $this->assign('info', null);

            $this->meta_title = '新增帮助分类';

            $this->display('help_category_edit');
        }
    }

    /**
     * 编辑帮助分类
     */
    public function helpCategoryEdit($id = 0) {
        $model = D('Common/HelpCategory');
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
                $this->error('获取信息错误');
            }
            $this->assign('info', $info);

            $this->meta_title = '编辑帮助分类';

            $this->display('help_category_edit');
        }
    }
    
    /**
     * 删除帮助分类
     */
    public function helpCategoryDel() {
        $model = D('Common/HelpCategory');
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
     * 禁用启用
     */
    public function changeField($id, $value = 1, $model = 'Help', $field = 'status') {
        $rs = D($model)->changeById($id, $field, $value);
        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }

}
