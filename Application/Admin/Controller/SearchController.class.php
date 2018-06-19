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
 * 热搜管理控制器
 */
class SearchController extends AdminController {

    /**
     * 搜索词 列表
     */
    public function index() {
        // 获取请求参数
        $title = I('get.title', '', 'op_t');
        $status = I('get.status', -1, 'intval');
        $recommend = I('get.recommend', -1, 'intval');
        $sort = I('get.sort', '', 'op_t');
        
        $this->assign('status', $status);
        $this->assign('recommend', $recommend);
        $this->assign('sort', $sort);
        // 配置查询条件
        $map = array();
        if ($title) {
            $map['search'] = array('like', '%' . $title . '%');
        }
        if ($status != -1) {
            $map['status'] = $status;
        }
        if ($recommend != -1) {
            $map['recommend'] = $recommend;
        }

        $order = 'create_time DESC,id DESC';
        if ($sort) {
            $order = $sort . ' DESC,id DESC';
        }

        $list = $this->lists('Search', $map, $order);

        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);

        $this->meta_title = '热搜列表';
        $this->display();
    }

    /**
     * 搜索词 禁用/启用/推荐
     */
    public function searchChange($id, $value = 1, $field = 'status') {
        $rs = D('Search')->changeById($id, $field, $value);
        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }
    
    /**
     * 搜索词 删除
     */
    public function searchDel() {
        $model = D('Common/Search');
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
     * 搜索词 添加/编辑
     */
    public function searchEdit() {
        if (IS_POST) {
            $rs = D('Common/Search')->saveData();
            if (!$rs) {
                $this->error('操作失败');
            }
            $this->success('操作成功', Cookie('__forward__'));
        }
        $info = array();
        $id = I('get.id', 0, 'intval');
        if ($id) {
            $info = D('Common/Search')->getById($id);
            if (false === $info) {
                $this->error('参数错误');
            }
        }
        $this->assign('info', $info);

        $this->display('search_edit');
    }

    /**
     * 搜索词 导出
     */
    public function searchExport() {
        /* 查询条件初始化 */
        $map = array();

        $status = I('get.status', -1, 'intval');
        $recommend = I('get.recommend', -1, 'intval');
        if ($status != -1) {
            $map['status'] = $status;
        }
        if ($recommend != -1) {
            $map['recommend'] = $recommend;
        }

        $sort = I('get.sort', '', 'op_t');
        $order = 'create_time DESC,id DESC';
        if ($sort) {
            $order = $sort . ' DESC,id DESC';
            $this->assign('sort', $sort);
        }

        $data = array();
        $list = D('Common/Search')->getListRows(array('where' => $map, $order, 'limit' => 99999), FALSE);
        if (is_array($list)) {
            foreach ($list as &$v) {
                $arr = array();
                $arr[] = $v['id'];
                $arr[] = $v['search'];
                $arr[] = $v['hits'];
                $arr[] = $v['recommend'] == 1 ? '已推荐' : '未推荐';
                $arr[] = $v['status'] == 1 ? '显示' : '隐藏';
                $data[] = $arr;
            }
            unset($v);
        }
        LC('Common/Excel')->exportExcel('热词搜索表', array('ID', '关键词', '热度', '推荐', '状态'), $data);
        exit;
    }

    /**
     * 禁用词 列表
     */
    public function ban() {
        $map = array();
        // 搜索名称
        $search = I('get.title', '', 'op_t');
        $search && $map['title'] = array('like', '%' . $search . '%');

        $list = $this->lists('SearchBan', $map, 'id DESC');


        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);

        $this->meta_title = '禁用词列表';
        $this->display();
    }

    /**
     * 禁用词 删除
     */
    public function banDel() {
        $model = D('Common/SearchBan');
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
     * 禁用词 添加/编辑
     */
    public function banEdit() {
        if (IS_POST) {
            $data = array();
            $data['id'] = I('post.id', 0, 'intval');
            $data['title'] = I('post.title', '', 'op_t');
            $data['status'] = I('post.status', 1, 'intval');
            if (!$data['id']) {
                unset($data['id']);
            }
            $rs = D('Common/SearchBan')->saveData($data);
            if (!$rs) {
                $this->error('操作失败');
            }
            $this->success('操作成功', Cookie('__forward__'));
        }
        $info = array();
        $id = I('get.id', 0, 'intval');
        if ($id) {
            $info = D('Common/SearchBan')->getById($id);
            if (false === $info) {
                $this->error('参数错误');
            }
        }
        $this->assign('info', $info);

        $this->display('ban_edit');
    }

    /**
     * 禁用词 禁用/启用
     */
    public function banStatus() {
        $id = I('request.id', 0, 'intval');
        $sts = I('request.status', 1, 'intval');
        $rs = D('SearchBan')->saveData(array('id' => $id, 'status' => $sts));
        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }

}
