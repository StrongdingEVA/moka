<?php

namespace Common\Logic;

/**
 * 文件上传逻辑
 */
class FileLogic {

    /**
     * 上传文件
     * @param type $files 文件数组
     * @return type
     */
    public function upload($files = array()) {
        $return = array('status' => 1, 'info' => '上传成功', 'data' => '');
        $files = $files ? $files : $_FILES;
        $File = D('Common/File');
        $file_driver = C('DOWNLOAD_UPLOAD_DRIVER');
        $setting = C('DOWNLOAD_UPLOAD');
        $drive_config = C("UPLOAD_" . strtoupper($file_driver) . "_CONFIG");
        $info = $File->upload($files, $setting, $file_driver, $drive_config);
        /* 记录附件信息 */
        if ($info) {
            $return['data'] = $info;
            foreach ($return['data'] as &$v){
                $v['thumb']=thumb($v['url'], 400);
            }
        } else {
            $return['status'] = 0;
            $return['info'] = $File->getError();
        }
        return $return;
    }

    /**
     * 图片上传
     * @param type $files
     * @return type
     */
    public function uploadPicture($files = array()) {
        $info = $this->upload($files);
        return $info;
    }

    /**
     * 图片上传url模式-七牛
     * @param string $url
     * @param type $savePath
     * @return boolean
     */
    public function uploadRemote($url, $savePath = '') {
        !$savePath && $savePath = date('Y-m-d') . '_' . uniqid() . '.png';
        $savePath = str_replace('/', '_', $savePath);
        $config = C("UPLOAD_QINIU_CONFIG");
        $access_key = $config['accessKey'];
        $secret_key = $config['secretKey'];

        $fetch = $this->urlsafe_base64_encode($url);
        $to = $this->urlsafe_base64_encode($config['bucket'] . ':' . $savePath);

        $url = 'http://iovip.qbox.me/fetch/' . $fetch . '/to/' . $to;
        $access_token = $this->generate_access_token($access_key, $secret_key, $url);

        $header[] = 'Content-Type: application/json';
        $header[] = 'Authorization: QBox ' . $access_token;
        $curl = curl_init('http://iovip.qbox.me/fetch/' . $fetch . '/to/' . $to);
        curl_setopt($curl, CURLOPT_POST, 1);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_TIMEOUT, $config['timeout']);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_NOBODY, 1);
        $con = curl_exec($curl);
        if ($con === false) {
            return false;
            // echo 'CURL ERROR: ' . curl_error($curl);
        } else {
            return "http://{$config['domain']}/{$savePath}";
        }
    }

    public function uploadPictureBase64($base64 = '') {
        $return = array('status' => 1, 'info' => '上传成功！', 'data' => '');

        !$base64 && $base64 = I('post.base64', '', 'trim');
        $base64 = preg_replace('/^data:image\/(jpeg|png|gif);base64,/', '', $base64);
        if (!$base64 || base64_encode(base64_decode($base64)) != $base64) {
            $ret['info'] = '图片不正确';
            $this->ajaxReturn($ret);
        }

        $file_driver = C('DOWNLOAD_UPLOAD_DRIVER');
        $setting = C('DOWNLOAD_UPLOAD');
        $config = C("UPLOAD_" . strtoupper($file_driver) . "_CONFIG");
        $savePath = (isset($config['file_prefix']) ? $config['file_prefix'] : '') . date('Y-m-d') . uniqid() . '.jpg';
        $access_key = $config['accessKey'];
        $secret_key = $config['secretKey'];

        $access['scope'] = $config['bucket'];
        $access['saveKey'] = $savePath;
        $access['deadline'] = time() + 3600;
        $json = json_encode($access);
        $b = $this->urlsafe_base64_encode($json);
        $sign = hash_hmac('sha1', $b, $secret_key, true);
        $encodedSign = $this->urlsafe_base64_encode($sign);
        $uploadToken = $access_key . ':' . $encodedSign . ':' . $b;

        $url = 'http://up.qiniu.com/putb64/-1';
        $header[] = 'Content-Type: application/octet-stream';
        $header[] = 'Authorization: UpToken ' . $uploadToken;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_TIMEOUT, $config['timeout']);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($curl, CURLOPT_POSTFIELDS, $base64);
        $con = curl_exec($curl);
        if ($con === false) {
            $return['status'] = 0;
            $return['info'] = '上传失败';
        } else {
            $data = array();
            $data['url'] = "http://{$config['domain']}/{$savePath}";
            $data['thumb'] = thumb($data['url'], 400);
            $return['data'] = $data;
        }
        return $return;
    }

    /**
     * 用于兼容UM编辑器的图片上传方法
     */
    public function uploadPictureUM() {
        $return = array('status' => 1, 'info' => '上传成功！', 'data' => '');

        //实际有用的数据只有name和state，这边伪造一堆数据保证格式正确
        $originalName = 'u=2830036734,2219770442&fm=21&gp=0.jpg';
        $newFilename = '14035912861705.jpg';
        $filePath = 'upload\/20140624\/14035912861705.jpg';
        $size = '7446';
        $type = '.jpg';
        $status = 'success';
        $rs = array(
            "originalName" => $originalName,
            'name' => $newFilename,
            'url' => $filePath,
            'size' => $size,
            'type' => $type,
            'state' => $status,
            'original' => $_FILES['upfile']['name']
        );

        $setting['exts'] = 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,xlsx,xls,ppt,pptx,pdf';
        $setting['rootPath'] = './Uploads/Editor/Picture/';

        $File = D('Common/File');
        $file_driver = C('DOWNLOAD_UPLOAD_DRIVER', 'local', 'config');
        $info = $File->upload($_FILES, $setting, C('DOWNLOAD_UPLOAD_DRIVER'), C("UPLOAD_" . strtoupper($file_driver) . "_CONFIG"));
        $info = array_values($info);
        isset($info['data'][0]) && $info = $info['data'][0];

        /* 记录图片信息 */
        if ($info) {
            $return['status'] = 1;
            $rs['state'] = 'SUCCESS';
            $rs['url'] = $info['url'];
            return $rs;
        } else {
            $return['state'] = 0;
            $return['info'] = $info['info'];
        }

        return $return;
    }

    public function uploadFileUE() {
        $return = array('status' => 1, 'info' => '上传成功！', 'data' => '');

        //实际有用的数据只有name和state，这边伪造一堆数据保证格式正确
        $originalName = 'u=2830036734,2219770442&fm=21&gp=0.jpg';
        $newFilename = '14035912861705.jpg';
        $filePath = 'upload\/20140624\/14035912861705.jpg';
        $size = '7446';
        $type = '.jpg';
        $status = 'success';
        $rs = array(
            'name' => $newFilename,
            'url' => $filePath,
            'size' => $size,
            'type' => $type,
            'state' => $status
        );

        $File = D('Common/File');
        $file_driver = C('DOWNLOAD_UPLOAD_DRIVER', 'local', 'config');
        $setting['exts'] = 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,xlsx,xls,ppt,pptx,pdf';
        $info = $File->upload($_FILES, $setting, C('DOWNLOAD_UPLOAD_DRIVER'), C("UPLOAD_" . strtoupper($file_driver) . "_CONFIG"));
        $info = array_values($info);
        isset($info['data'][0]) && $info = $info['data'][0];


        /* 记录附件信息 */
        if ($info) {
            $return['data'] = $info;
            $rs['original'] = $info['name'];
            $rs['state'] = 'SUCCESS';
            $rs['url'] = $info['url'];
            $rs['size'] = $info['size'];
            $rs['title'] = $info['savename'];
            return $rs;
        } else {
            $return['status'] = 0;
            $return['info'] = $info['info'];
        }

        return $return;
    }

    /**
     * base64上传
     * @param string $base64 可以为base64字符串或者多个base64数组jason字符串
     * @param string $savePath
     */
    public function uploadBase64($base64, $savePath = '') {       
        /*
          // json示例数据
          $base64 = array(
          array('name'=>'1.jpg','base64'=>'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJAQMAAADaX5RTAAAAA3NCSVQICAjb4U/gAAAABlBMVEX///+ZmZmOUEqyAAAAAnRSTlMA/1uRIrUAAAAJcEhZcwAACusAAArrAYKLDVoAAAAWdEVYdENyZWF0aW9uIFRpbWUAMDkvMjAvMTIGkKG+AAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAAB1JREFUCJljONjA8LiBoZyBwY6BQQZMAtlAkYMNAF1fBs/zPvcnAAAAAElFTkSuQmCC'),
          array('name'=>'1.jpg','base64'=>'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJAQMAAADaX5RTAAAAA3NCSVQICAjb4U/gAAAABlBMVEX///+ZmZmOUEqyAAAAAnRSTlMA/1uRIrUAAAAJcEhZcwAACusAAArrAYKLDVoAAAAWdEVYdENyZWF0aW9uIFRpbWUAMDkvMjAvMTIGkKG+AAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAAB1JREFUCJljONjA8LiBoZyBwY6BQQZMAtlAkYMNAF1fBs/zPvcnAAAAAElFTkSuQmCC'),
          array('name'=>'1.jpg','base64'=>'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJAQMAAADaX5RTAAAAA3NCSVQICAjb4U/gAAAABlBMVEX///+ZmZmOUEqyAAAAAnRSTlMA/1uRIrUAAAAJcEhZcwAACusAAArrAYKLDVoAAAAWdEVYdENyZWF0aW9uIFRpbWUAMDkvMjAvMTIGkKG+AAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAAB1JREFUCJljONjA8LiBoZyBwY6BQQZMAtlAkYMNAF1fBs/zPvcnAAAAAElFTkSuQmCC')
          );
          $base64 = json_encode($base64); */
        $files = json_decode($base64, true);
        if ($files) {
            foreach ($files as $v) {
                $data[] = array('base64' => $v['base64'], 'savePath' => $v['name']);
            }
        } else {
            $data[] = array('base64' => $base64, 'savePath' => $savePath);
        }

        $return['status'] = 0;
        $return['data'] = array();
        $msg = '';
        

        if ($data) {
            if (count($data) > 1) {
                foreach ($data as $k => $v) {
                    $res = $this->_base64($v['base64'], $v['savePath']);
                    if ($res['status']) {
                        $return['status'] = 1;
                    } else {
                        $msg .= $k . ':' . $res['info'] . ',';
                    }
                    $return['data'][] = $res['data'];
                }
            } else {
                $res = $this->_base64($data[0]['base64'], $data[0]['savePath']);
                if ($res['status']) {
                    $return['status'] = 1;
                    $return['data'][] = $res['data'];
                } else {
                    $msg .= $k . ':' . $res['info'] . ',';
                }
            }
        }
        $msg = trim($msg, ',');
        if (!$msg) {
            $msg = $return['status'] ? '上传成功' : '上传失败';
        }
        $return['info'] = trim($msg, ',');

        return $return;
    }

    /**
     * 处理上传base64
     * @param string $base64
     * @param string $savePath
     */
    private function _base64($base64, $savePath = '') {
        preg_match('/^data:image\/(jpeg|png|gif);base64,(.*)$/', $base64, $match);
        if ($match) {
            $base64 = $match[2];
        }
        if (!$base64 || $base64 == 'null' || !$base64 == base64_encode(base64_decode($base64))) {
            $res['status'] = 0;
            $res['info'] = '无效的base64编码';
            return $res;
        }


        $file_driver = C('DOWNLOAD_UPLOAD_DRIVER');
        $setting = C('DOWNLOAD_UPLOAD');
        $config = C("UPLOAD_" . strtoupper($file_driver) . "_CONFIG");
        $savePath = (isset($config['file_prefix']) ? $config['file_prefix'] : '') . date('Y-m-d') . uniqid() . '.jpg';
//        $savePath = ltrim($savePath, '/');
//        $savePath = str_replace('/', '_', $savePath);
        $access_key = $config['accessKey'];
        $secret_key = $config['secretKey'];

        $access['scope'] = $config['bucket'];
        $access['saveKey'] = $savePath;
        $access['deadline'] = time() + 3600;
        $json = json_encode($access);
        $b = $this->urlsafe_base64_encode($json);
        $sign = hash_hmac('sha1', $b, $secret_key, true);
        $encodedSign = $this->urlsafe_base64_encode($sign);
        $uploadToken = $access_key . ':' . $encodedSign . ':' . $b;

        $url = 'http://up.qiniu.com/putb64/-1';
        $header[] = 'Content-Type: application/octet-stream';
        $header[] = 'Authorization: UpToken ' . $uploadToken;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_TIMEOUT, $config['timeout']);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($curl, CURLOPT_POSTFIELDS, $base64);
        $con = curl_exec($curl);

        if ($con == false) {
            $res['status'] = 0;
            $res['info'] = curl_error($curl);
        } else {
            $res = json_decode($con, true);
            if (!$res || $res['error']) {
                $res['status'] = 0;
                $res['info'] = $res['error'];
            } else {
                $data = array();
                $data['url'] = "http://{$config['domain']}/{$savePath}";
                $data['thumb'] = thumb($data['url'], 400);
                $res['data'] = $data;
                $res['status'] = 1;               
            }
        }
        return $res;
    }

    private function urlsafe_base64_encode($str) {
        $find = array("+", "/");
        $replace = array("-", "_");
        return str_replace($find, $replace, base64_encode($str));
    }

    private function generate_access_token($access_key, $secret_key, $url, $params = '') {
        $parsed_url = parse_url($url);
        $path = $parsed_url['path'];
        $access = $path;
        if (isset($parsed_url['query'])) {
            $access .= "?" . $parsed_url['query'];
        }
        $access .= "\n";
        if ($params) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            $access .= $params;
        }
        $digest = hash_hmac('sha1', $access, $secret_key, true);
        return $access_key . ':' . $this->urlsafe_base64_encode($digest);
    }

}
