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
 * 文章管理控制器
 */
class ArticleController extends AdminController
{

    /**
     * 文章列表
     */
    public function index() {
        /* 查询条件初始化 */
        $map = array();
        // 状态
        if (isset($_GET['status'])) {
            $map['status'] = $_GET['status'];
            $this->assign('status',$_GET['status']);
        }
        // 文章类型
        if (isset($_GET['catid'])) {
            $map['catid'] = $_GET['catid'];
            $this->assign('catid',$_GET['catid']);
        }
        // 标题
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }

        $list = $this->lists('Article', $map, 'listorder DESC,id DESC');
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        
        $this->meta_title = '文章列表';
        $this->display();
    }

    /**
     * 新增文章
     */
    public function add() {
        if (IS_POST) {
            $model = D('Common/Article');
            $_POST['create_time'] = strtotime($_POST['create_time']);
            unset($_POST['parse']);

            $rs = $model->saveData();
            if ($rs) {
                $this->success('新增成功', U('index'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            $this->assign('info', null);
            $this->meta_title = '新增文章';

            $this->display('edit');
        }
    }

    /**
     * 编辑文章
     */
    public function edit($id = 0) {
        $model = D('Common/Article');
        if (IS_POST) {
            $_POST['create_time'] = strtotime($_POST['create_time']);
            unset($_POST['parse']);
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
            $info['create_time'] = date('Y/m/d h:i', $info['create_time']);
            $this->assign('info', $info);

            $this->meta_title = '编辑文章';

            $this->display();
        }
    }

    /**
     * 删除文章
     */
    public function del() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/Article')->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 文章分类
     */
    public function category() {
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }

        $list = $this->lists('ArticleCategory', $map, 'listorder DESC,id');

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->meta_title = '文章分类';
        $this->display();
    }

    /**
     * 文章分类排序
     */
    public function categorySort() {
        if (IS_GET) {
            $ids = I('get.ids');

            //获取排序的数据
            $map = array('status' => array('gt', -1));
            if (!empty($ids)) {
                $map['id'] = array('in', $ids);
            } elseif (I('group')) {
                $map['group'] = I('group');
            }
            $list = M('ArticleCategory')->where($map)->field('id,title')->order('listorder asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = 'SEO规则排序';
            $this->display('category_sort');
        } elseif (IS_POST) {
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key => $value) {
                $res = M('ArticleCategory')->where(array('id' => $value))->setField('listorder', $key + 1);
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
     * 新增文章
     */
    public function categoryAdd() {
        if (IS_POST) {
            $model = D('Common/ArticleCategory');
            $rs = $model->saveData();
            if ($rs) {
                $this->success('新增成功', U('category'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            $this->assign('info', null);

            $cat = D('Common/ArticleCategory')->getListRows(array('where' => array('status' => 1)), FALSE);
            $cat = field2array_key($cat, 'id');
            $this->assign('cat', $cat);

            $this->meta_title = '新增分类';

            $this->display('category_edit');
        }
    }

    /**
     * 编辑文章
     */
    public function categoryEdit($id = 0) {
        $model = D('Common/ArticleCategory');
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
            $info['create_time'] = date('Y/m/d h:i', $info['create_time']);
            $this->assign('info', $info);

            $cat = D('Common/ArticleCategory')->getListRows(array('where' => array('status' => 1)), FALSE);
            $cat = field2array_key($cat, 'id');
            $this->assign('cat', $cat);

            $this->meta_title = '编辑分类';

            $this->display('category_edit');
        }
    }

    /**
     * 删除文章
     */
    public function categoryDel() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/ArticleCategory')->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 福利列表
     */
    public function welfare() {
        /* 查询条件初始化 */
        $map = array();
        $map = array('status' => array('egt', 0));
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string) I('title') . '%');
        }

        $list = $this->lists('ArticleWelfare', $map, 'sort DESC,id DESC');
        if (is_array($list)) {
            // 配置列表参数
            foreach ($list as &$v) {
                $v['thumb'] = thumb($v['thumb'], 400);
                $v['create_time'] = friendlyDate($v['create_time'], 'ymd');
            }
            unset($v);
        }

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->meta_title = 'SEO规则管理';
        $this->display();
    }

    /**
     * 新增福利
     */
    public function addWelfare() {
        if (IS_POST) {
            $model = D('Common/ArticleWelfare');
            $_POST['create_time'] = strtotime($_POST['create_time']);
            unset($_POST['parse']);
            $rs = $model->saveData();
            if ($rs) {
                $this->success('新增成功', U('welfare'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            $this->assign('info', null);

            $this->meta_title = '新增文章';

            $this->display('welfare_edit');
        }
    }

    /**
     * 编辑福利
     */
    public function editWelfare($id = 0) {
        $model = D('Common/ArticleWelfare');
        if (IS_POST) {
            $_POST['create_time'] = strtotime($_POST['create_time']);
            unset($_POST['parse']);
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
            $info['create_time'] = date('Y/m/d h:i', $info['create_time']);
            $this->assign('info', $info);

            $this->meta_title = '编辑SEO规则';

            $this->display('welfare_edit');
        }
    }

    /**
     * 删除福利
     */
    public function delWelfare() {
        $id = array_unique((array) I('id', 0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/ArticleWelfare')->delById($id);
        if ($rs) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 禁用启用
     */
    public function changeField($id, $value = 1, $model = 'Article', $field = 'status') {
        $rs = D($model)->changeById($id, $field, $value);
        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }

}
