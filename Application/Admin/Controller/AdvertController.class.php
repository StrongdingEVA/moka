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
 * 广告管理控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class AdvertController extends AdminController
{

    public function index() {
       
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));
        
        if (isset($_GET['pid'])) {
            $map['pos_id'] = $_GET['pid'];
            $this->assign('pid', $_GET['pid']);
        }
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }
        // 广告列表
        $list = $this->lists('Advs', $map, 'sort,id');
        // 广告位
        $pos = D('Common/AdvPos')->getListRows(array(), FALSE);
        $pos = field2array_key($pos, 'id');
        if(is_array($list)){
            // 配置广告
            foreach ($list as &$v){
                $v['thumb'] = thumb($v['image'],400);
                $v['pos_id'] = $pos[$v['pos_id']]['title'];
            }
            unset($v);
        }
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->assign('pos', $pos);
        
        $this->meta_title = 'SEO规则管理';
        $this->display();
    }

    /**
     * 新增广告位
     */
    public function add() {
        if (IS_POST) {
            $model = D('Common/Advs');
            $rs = $model->saveData();
            if ($rs) {
                $this->success('新增成功', U('index'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            $this->assign('info', null);
            
            // 获取广告位
            $pos = D('Common/AdvPos')->getListRows(array('where' => array('status' => 1)), FALSE);
            $pos = field2array_key($pos, 'id');
            $this->assign('poslist', $pos);
            
            $this->meta_title = '新增广告';

            $this->display('edit');
        }
    }

    /**
     * 编辑配置
     */
    public function edit($id = 0) {
        $model = D('Common/Advs');
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

            // 获取广告位
            $pos = D('Common/AdvPos')->getListRows(array('where' => array('status' => 1)), FALSE);
            $pos = field2array_key($pos, 'id');
            $this->assign('poslist', $pos);
            
            $this->meta_title = '编辑广告';

            $this->display();
        }
    }

    /**
     * 删除配置
     */
    public function del() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/Advs')->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    
    /**
     * 广告位
     */
    public function pos() {
         $this->class=I('request.sel_filter',-1,'intval');
       
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));
         $this->class!=-1 && $map['class']=$this->class;
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }
        $list = $this->lists('AdvPos', $map, 'id');
        
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->meta_title = '广告位';
        $this->display();
    }

    /**
     * 新增广告位
     */
    public function addPos() {  
        if (IS_POST) {          
            $model = D('Common/AdvPos'); 
            $rs = $model->saveData();
            if ($rs) {
                $this->success('新增成功', U('index'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            $this->meta_title = '新增广告位';
            $this->assign('info', null);
            $this->display('pos_edit');
        }
    }

    /**
     * 编辑配置
     */
    public function editPos($id = 0) {
       
        $model = D('Common/AdvPos');
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

            $this->meta_title = '编辑广告位';

            $this->display('pos_edit');
        }
    }

    /**
     * 删除配置
     */
    public function delPos() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/AdvPos')->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 禁用启用
     */
    public function changeField($id, $value = 1, $model = 'Advs', $field = 'status') {
        $rs = D($model)->changeById($id, $field, $value);
        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }

}

?>