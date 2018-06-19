<?php

function pic($cover_id) {
    return get_cover($cover_id, 'path');
}

/**
 * @param        $filename
 * @param int $width
 * @param string $height
 * @param int $type
 * @param bool $replace
 * @return mixed|string
 * 
 */
function getThumbImage($filename, $width = 100, $height = 'auto', $type = 0, $replace = false) {

    $UPLOAD_URL = '';
    $UPLOAD_PATH = '';
    $filename = str_ireplace($UPLOAD_URL, '', $filename); //将URL转化为本地地址
    $info = pathinfo($filename);
    $oldFile = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . ($info['extension'] ? '.' . $info['extension'] : '');
    $thumbFile = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '_' . $width . '_' . $height . '.' . ($info['extension'] ? '.' . $info['extension'] : '');

    $oldFile = str_replace('\\', '/', $oldFile);
    $thumbFile = str_replace('\\', '/', $thumbFile);

    $filename = ltrim($filename, '/');
    $oldFile = ltrim($oldFile, '/');
    $thumbFile = ltrim($thumbFile, '/');

    if (!file_exists($UPLOAD_PATH . $oldFile)) {
        //原图不存在直接返回
        @unlink($UPLOAD_PATH . $thumbFile);
        $info['src'] = $oldFile;
        $info['width'] = intval($width);
        $info['height'] = intval($height);
        return $info;
    } elseif (file_exists($UPLOAD_PATH . $thumbFile) && !$replace) {
        //缩图已存在并且  replace替换为false
        $imageinfo = getimagesize($UPLOAD_PATH . $thumbFile);
        $info['src'] = $thumbFile;
        $info['width'] = intval($imageinfo[0]);
        $info['height'] = intval($imageinfo[1]);
        return $info;
    } else {
        //执行缩图操作
        $oldimageinfo = getimagesize($UPLOAD_PATH . $oldFile);
        $old_image_width = intval($oldimageinfo[0]);
        $old_image_height = intval($oldimageinfo[1]);
        if ($old_image_width <= $width && $old_image_height <= $height) {
            @unlink($UPLOAD_PATH . $thumbFile);
            @copy($UPLOAD_PATH . $oldFile, $UPLOAD_PATH . $thumbFile);
            $info['src'] = $thumbFile;
            $info['width'] = $old_image_width;
            $info['height'] = $old_image_height;
            return $info;
        } else {
            if ($height == "auto")
                $height = $old_image_height * $width / $old_image_width;
            if ($width == "auto")
                $width = $old_image_width * $width / $old_image_height;
            if (intval($height) == 0 || intval($width) == 0) {
                return 0;
            }
            require_once('ThinkPHP/Library/Vendor/phpthumb/PhpThumbFactory.class.php');
            $thumb = PhpThumbFactory::create($UPLOAD_PATH . $filename);
            if ($type == 0) {
                $thumb->adaptiveResize($width, $height);
            } else {
                $thumb->resize($width, $height);
            }
            $res = $thumb->save($UPLOAD_PATH . $thumbFile);
            $info['src'] = $UPLOAD_PATH . $thumbFile;
            $info['width'] = $old_image_width;
            $info['height'] = $old_image_height;
            return $info;
        }
    }
}

/* * 获取网站的根Url
 * @return string
 * 
 */

function getRootUrl() {
    if (__ROOT__ != '') {
        return __ROOT__ . '/';
    }
    if (C('URL_MODEL') == 2)
        return __ROOT__ . '/';
    return __ROOT__;
}

/* * 简写函数，等同于getThumbImageById（）
 * @param $cover_id 图片id
 * @param int $width 宽度(数字、small、large、square、auto)
 * @param string $height 高度
 * @param int $type 裁剪类型，0居中裁剪
 * @param bool $replace 裁剪
 * @return string
 */

function thumb($filename, $width = 120, $height = 'auto', $type = 0, $replace = false) {
    //处理七牛云存储
    if(strpos(strtolower($filename), 'sinaimg.cn') ){ // 微博图片处理
        if( $width == 'small' || ($width > 0 && $width <= 300) ){
            return str_replace(array('thumbnail', 'large', 'wap180', 'square', 'mw690'), 'orj360', $filename);
        }elseif( $width == 'large' || $width > 300 ){
            return str_replace(array('thumbnail', 'large', 'wap180', 'square', 'orj360'), 'mw690', $filename);
        }else{
            return $filename;
        }
    }elseif (is_url($filename) && strpos(strtolower($filename), 'clouddn.com') !== false) {
        //$conf = C('UPLOAD_QINIU_CONFIG');
        //$Upload = new Think\Upload(array(), $conf['driver'], $conf['driverConfig']);
        if (strpos($filename, '?') === false) {
            if($width == 'small'){
                $width = 240;
                $height = 'auto';
            }elseif($width == 'large'){
                $width = 800;
                $height = 'auto';
            }elseif($width == 'square'){
                $width = $height = 150;
            }
            $width = ($width == 'auto') ? '' : '/w/' . $width;
            $height = ($height == 'auto') ? '' : '/h/' . $height;
            if ($type == 1) {
                $filename = $filename . '?imageView2/1' . $width . $height;
            } else {
                $filename = $filename . '?imageView2/2' . $width . $height;
            }
        } else {
            $filename = $filename . '/thumbnail/' . $width . 'x' . $height . '!';
        }
        return $filename;
    } elseif (is_url($filename)) { //如果是其他网络图片原址返回
        if(empty($filename)){
            return C('BASE_URL').'static/common/images/none.png';
        }
    }
    
    return $filename;

    //$info = getThumbImage($filename, $width, $height, $type, $replace);
    //return $info['src'];
}

/* * 获取第一张图
 * @param $str_img
 * @return mixed
 */

function get_pic($str_img) {
    preg_match_all("/<img.*\>/isU", $str_img, $ereg); //正则表达式把图片的整个都获取出来了
    $img = $ereg[0][0]; //图片
    $p = "#src=('|\")(.*)('|\")#isU"; //正则表达式
    preg_match_all($p, $img, $img1);
    $img_path = $img1[2][0]; //获取第一张图片路径
    return $img_path;
}

/**
 * get_pic_src   渲染图片链接
 * @param $path
 * @return mixed
 * 
 */
function get_pic_src($path) {
    //不存在http://
    $not_http_remote = (strpos($path, 'http://') === false);
    //不存在https://
    $not_https_remote = (strpos($path, 'https://') === false);
    if ($not_http_remote && $not_https_remote) {
        //本地url
        return 'http://' . $_SERVER['SERVER_NAME'] . str_replace('//', '/', '/' . str_replace('//', '/', getRootUrl() . $path)); //防止双斜杠的出现
    } else {
        //远端url
        return $path;
    }
}

/**
 * 网络图片转换成BASE64
 * @param type $url
 * @return type
 */
function net_img2base64($url) {
    if (!is_url($url)) {
        return NULL;
    }

    $ext = get_url_ext($url);

    //试用curl下载文件
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($curl);
    $content = curl_multi_getcontent($curl);
    curl_close($curl);

    if (!$content) {
        return NULL;
    }

    return 'data:image/' . strtolower($ext) . ';base64,' . base64_encode($content);
}
