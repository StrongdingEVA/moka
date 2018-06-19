<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Addons\EditorForAdmin\Controller;
use Home\Controller\AddonsController;
use Think\Upload;

class UploadController extends AddonsController{

	public $uploader = null;

	/* 上传图片 */
	public function upload(){
                session('upload_error', null);
                $info = array();
                $rs = LC('Common/File')->upload();
                if($rs['status'] == 1){
                        $info['imgFile'] = $rs['data']['imgFile'];
                        $info['fullpath'] = $rs['data']['imgFile']['url'];
                } else {
                        session('upload_error', '上传失败');
                }
		return $info;
	}

	//keditor编辑器上传图片处理
	public function ke_upimg(){
		/* 返回标准数据 */
		$return  = array('error' => 0, 'info' => '上传成功', 'data' => '');
		$img = $this->upload();
		/* 记录附件信息 */
		if($img){
			$return['url'] = $img['fullpath'];
			unset($return['info'], $return['data']);
		} else {
			$return['error'] = 1;
			$return['message']   = session('upload_error');
		}

		/* 返回JSON数据 */
		exit(json_encode($return));
	}

	//ueditor编辑器上传图片处理
	public function ue_upimg(){

		$img = $this->upload();
		$return = array();
		$return['url'] = $img['fullpath'];
		$title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
		$return['title'] = $title;
		$return['original'] = $img['imgFile']['name'];
		$return['state'] = ($img)? 'SUCCESS' : session('upload_error');
		/* 返回JSON数据 */
		$this->ajaxReturn($return);
	}

}
