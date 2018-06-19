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
 * APP管理控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class AppController extends AdminController
{

    public function info() {
        $appid = I('appid', 0, 'intval');
        
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));
        $appid && $map['appid'] = $appid;
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }

        $list = $this->lists('AppInfo', $map, 'id DESC');
        
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->assign('applist', D('AppInfo')->applist);
        $this->assign('appid', $appid);

        $this->meta_title = 'App版本管理';
        $this->display();
    }

    /**
     * 新增广告位
     */
    public function addInfo() {
        if (IS_POST) {
            $model = D('Common/AppInfo');
            $rs = $model->saveData();
            if ($rs) {
                $this->success('新增成功', U('info'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            $this->assign('info', null);
            $this->assign('applist', D('Common/AppInfo')->applist);

            $this->meta_title = '新增App版本';

            $this->display('info_edit');
        }
    }

    /**
     * 编辑App版本
     */
    public function editInfo($id = 0) {
        $model = D('Common/AppInfo');
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
            $this->assign('applist', $model->applist);

            $this->meta_title = '编辑App版本';

            $this->display('info_edit');
        }
    }

    /**
     * 删除配置
     */
    public function delInfo() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/AppInfo')->delById($id);
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