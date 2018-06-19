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
 * 内容管理控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ContentController extends AdminController
{

    public function index() {
        // 获取请求参数

        $get = I('get.', '', 'trim');

        $this->assign($get);

        //获取国籍列表
        $countryModel = D('VideoCountry');
        $country = $countryModel->getField('id,title');
        $this->assign('countries', $country);

        //获取类型列表
        $typeModel = D('VideoType');
        $type = $typeModel->getField('id,title');
        $this->assign('types', $type);

        // 配置查询条件
        $map = array();
        if ($get['status']) {
            $map['status'] = $get['status'] < 2 ? 1 : 0;
        }
        if ($get['title']) {
            $map['title'] = array('like', '%' . $get['title'] . '%');
        }
        if ($get['year']) {
            $map['year'] = $get['year'];
        }

        if ($get['state']){
            $map['state'] = $get['state'] > 1 ? 1 : 0;
        }
        if ($get['country']) {
            $map['country'] = $get['country'];
        }
        if ($get['status']) {
            $map['status'] = $get['status'];
        }
        if ($get['type']) {
            $map['type'] = $get['type'];
        }
        $sort = '';
        if (!empty($get['tsort'])) {
            $get['tsort'] == 1 && $sort = 'follow_num DESC';
            $get['tsort'] == 2 && $sort = 'follow_num ASC';
        }else{
            $sort = 'follow_num DESC';
        }
        $list = $this->lists('Video', $map, $sort);

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);

        $this->meta_title = '栏目分类列表';

        $this->display();
    }

    /**
     *剧集资源列表
     */
    public function getItem(){
        $id = I('get.id',0,'intval');

        $itemModel = D('VideoItem');
        $map =array();
        $sort = 'num DESC';
        if (!$id){
            $this->error($itemModel->getError() ? : '操作失败');
        }else{
            $map['video_id'] = $id;
        }
        $itemlist = $this->lists($itemModel, $map,$sort);
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('itemlist', $itemlist);


        $this->meta_title = '影视资源列表';

        $this->display();

    }
    /**
     *合集资源列表
     */
    public function getCollection(){
        $id = I('get.id',0,'intval');

        $conModel = D('VideoCollection');
        if (!$id){
            $this->error($conModel->getError() ? : '操作失败');
        }else{
            $map['video_id'] = $id;
        }
        $conlist = $this->lists($conModel, $map);
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('conlist', $conlist);

        $this->meta_title = '影视资源列表';

        $this->display();

    }
    /**
     * 新增影视资源
     */
    public function add() {
        $http_request_method = 'request';
        if (IS_POST) {
            $model = D('Common/Video');
            $data['thumb']= $this->_upload();
            $data['title'] = I($http_request_method.'.title','','trim');
            $data['catid'] = I($http_request_method.'.catid',0,'intval');
            $data['follow_num'] = I($http_request_method.'.follow_num',0,'intval');
            $data['status'] = I($http_request_method.'.status',0,'intval');
            $rs = $model->saveData($data);
            if ($rs) {
                $this->success('新增成功', U('index'));
            } else {
                $this->error($model->getError() ? : '新增失败');
            }
        } else {
            // 获取类型
            $catlst= D('Common/VideoCategory')->getAll();
            $catlst = field2array_key($catlst, 'id');
            $this->assign('catlist', $catlst);

            $this->meta_title = '新增影视资源';

            $this->display();
        }
    }

    /**
     * 编辑配置
     */
    public function edit($id = 0) {
        $model = D('Common/Video');
        if (IS_POST) {
            $rs = $model->saveData();
            if ($rs) {
                $this->success('更新成功', Cookie('__forward__'));
            } else {
                $this->error($model->getError() ? : '更新失败');
            }
        } else {
            /* 获取数据 */
            $video = $model->getById($id);
            if (false === $video) {
                $this->error('获取影视信息错误');
            }
            $this->assign('video', $video);

            $countryModel = D('VideoCountry');
            $countries =$countryModel->field('id,title')->select();
            $this->assign('countries',$countries);

            $typeModel = D('VideoType');
            $types =$typeModel->field('id,title')->select();
            $this->assign('types',$types);
            
            $this->meta_title = '编辑影视';

            $this->display();
        }
    }
    /**
     * 修改影视内容
     */
    public function update() {
        $data['thumb'] = $this->_upload();
        $data = I('post.', '', 'trim');
        $rs = D('Video')->saveData($data);
        if ($rs) {
//            add_admin_action_log($this->uid, 1, 2, $data['id'], $data['title']);
//            ($data['id'] && isset($data['status']) && $data['status'] == 2) && LC('Common/Topic')->clear_topic_related_info($data['id']);
            //成功提示
            $this->assign('jumpUrl', cookie('__forward__'));
            $this->success('编辑成功!');
        } else {
            //错误提示
            $this->error('编辑失败!' . D('Video')->getError());
        }
    }


    /**
     * 删除影视内容
     */
    public function del() {
        $id = I('get.id',0,'intval');
        $video_id =$id;
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $rs = D('Common/Video')->delById($id);
        if ($rs) {
            D('Common/VideoItem')->delById($video_id);
            D('Common/VideoCollection')->delById($video_id);
            $this->success('删除成功',cookie('__forward__'));
        } else {
            $this->error('删除失败！');
        }
    }


    /**
     * 禁用启用
     */
    public function videoChange($id, $value = 1, $model = 'Video', $field = 'status') {
        $rs = D($model)->changeById($id, $field, $value);
        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }

    /***
     *
     * 改变订阅量
    **/
    public function videoFollowNum(){
        $id = I('get.id',0,'intval');
        $follow_num = I('get.follow_num',0,'intval');
        $re = D('Video')->saveData(array('id'=>$id,'follow_num'=>$follow_num));
        if ($re){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

}

?>