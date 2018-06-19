<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Api\Logic\MakeImageLogic;
use Think\Controller;
/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */
class FileController extends Controller
{

    /**
     * 文件上传
     */
    public function upload() {
        $rs = LC('Common/File')->upload();

        if($rs['status'] == 1){
            $temp = array();
            $ImgLogic = new MakeImageLogic();
            $files = $rs['data'];
            foreach ($files as $item){
                $url = $item['url'];
                $temp[] = $ImgLogic->tencentCloundUpload($url);
            }
            $this->ajaxReturn(array('status' => 1,'data' => $temp));
        }
        $this->ajaxReturn($rs);
    }

    /**
     *  下载文件 
     */
    public function download($id = null) {
        if (empty($id) || !is_numeric($id)) {
            $this->error('参数错误！');
        }

        $logic = D('Download', 'Logic');
        if (!$logic->download($id)) {
            $this->error($logic->getError());
        }
    }

    /**
     * 上传图片
     * @author huajie <banhuajie@163.com>
     */
    public function uploadPicture() {
        $rs = LC('Common/File')->upload();
        $this->ajaxReturn($rs);
    }

    /**
     * 后台uploadify上传文件
     * @author huajie <banhuajie@163.com>
     */
    public function uploadify() {
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
        
        $info = LC('Common/File')->upload();
        if($info['status'] == 1){
            $return['status'] = 1;
            $return = array_merge($info['data']['download'], $return);
        } else {
            $return['status'] = 0;
            $return['info']   = '上传失败';
        }
        
        $this->ajaxReturn($return);
    }

}
