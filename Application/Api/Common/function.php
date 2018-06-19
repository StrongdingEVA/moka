<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 处理图片
 */
define('MOKA_WIDTH',750);
define('MOKA_BOTTOM_HEIGHT',200);
define('MOKA_QR_WIDTH',100);
define('MOKA_FONT_SIZE',18);

/**
 * @param $info
 * @return bool|string
 * 生成模卡底部 带有用户信息的图片
 */
function textWater($info){
//    $info = array(
//        array(
//            '淘宝等级：2',
//            '淘气值：3',
//        ),
//        array(
//            '地区：福建',
//            '厦门',
//        ),
//        array(
//            '身高：173cm',
//            '三围：80/166/154',
//            '体重：99kg'
//        ),
//    );
    //$qr = "Uploads/smallQr/small.jpg";
    //图片长
    $width = MOKA_WIDTH;
    $height = MOKA_BOTTOM_HEIGHT;

    $font_size = MOKA_FONT_SIZE;
    $font = "Uploads/fonts/simsun.ttc";
    $fdir = "Uploads/userBottom/";

    if(!is_dir($fdir)){
        @mkdir($fdir);
    }

    $fdir .= date('Y-m-d',time()) . '/';
    if(!is_dir($fdir)){
        @mkdir($fdir);
    }

    if(!is_file($font)){
        return false;
    }

    //创建一个空白的画布  往上面写字
    $image = createColora($width, $height);

    $black = imagecolorallocate($image, 101, 93, 93);//黑色的笔
    $x = $font_size;
    $y = $font_size;

    //在布上写字
    $t = $y;
    foreach ($info as $keyt => $val){
        $m = $x;
        $temp = $val;
        $t += $font_size + 20;
        foreach ($temp as $ke => $va){
            $result = imagefttext($image, $font_size, 0,$m, $font_size + $t, $black, $font,$va);
            $m = $result[2] + 2 * $font_size;
        }
    }

    $fname = uniqid() . '.jpg';
    $final = $fdir . $fname;
    $res = imagepng($image,$final);
    imagedestroy($image);
    if ($res){
        return $final;
    }
    return false;
}

/**
 * @param $bottomImg 底部
 * @param $qr 小程序码
 * 合并模卡底部图和小程序码
 */
function mergeBottomQr($bottomImg,$qr){
    $str = C('IMGDOMAIN');
    $res = strpos($qr,$str);
    if($res !== false){
        $data = downLoadTolocal(array($qr));
        $qr = $data[0];
    }

    $bottomImgHandle = getImgHandleInfo($bottomImg);
    $qrW = MOKA_QR_WIDTH; //设小程序码100px
    $width = MOKA_WIDTH; //模卡图片宽750
    $height = MOKA_BOTTOM_HEIGHT; //底部信息图片高200
    $font_size = MOKA_FONT_SIZE;//字体大小
    $qrx = $width - $qrW - $font_size;
    $qry = ($height - $qrW) / 2;
    $bottomImgHandle['source'] = mergeImg($bottomImgHandle['source'],$qr,$qrx,$qry,$qrW,$qrW);

    $fdir = "Uploads/userTemp/";

    if(!is_dir($fdir)){
        @mkdir($fdir);
    }

    $fdir .= date('Y-m-d',time()) . '/';
    if(!is_dir($fdir)){
        @mkdir($fdir);
    }
    $fname = $fdir . uniqid() . '.jpg';
    $res = imagepng($bottomImgHandle['source'],$fname);
    imagedestroy($bottomImgHandle['source']);
    if(!$res){
        return false;
    }
    return $fname;
}

/**
 * @param $arr
 * @param $styleIndex
 * @return bool|string
 * 合并用户上传的图片  模卡图的上部
 */
function mergePics($arr,$style){
    $str = C('IMGDOMAIN');
    $res = strpos($arr[0],$str);
    if($res !== false){
        $arr = downLoadTolocal($arr);
    }

    $points = $style['points'];
    if(!$arr || empty($arr)){
        return false;
    }

    $fdir = "Uploads/unionPics/";

    if(!is_dir($fdir)){
        @mkdir($fdir);
    }

    $fdir .= date('Y-m-d',time()) . '/';
    if(!is_dir($fdir)){
        @mkdir($fdir);
    }
    $fname = $fdir . uniqid() . '.jpg';

    //创建一个空白的画布  往上面写字
    $image = createColora($style['canvw'], $style['canvh']);
    foreach($points as $key => $val){
        $h = $val['h'] ? $val['h'] : $val['w'];
        mergeImg($image,$arr[$key],$val['x'],$val['y'],$val['w'],$h);
    }
    $res = imagepng($image,$fname);
    imagedestroy($image);
    if($res){
        $arr_ = array_unique($arr);
        foreach ($arr_ as $item){
            @unlink($item);
        }
        return $fname;
    }else{
        return false;
    }
}

/**
 * @param $qr 上部
 * @param $imgBottom 底部
 * @return bool|string
 * 合并模卡图  上部和下部
 */
function unionAll($qr,$imgBottom,$type = 1){
    if(!is_file($qr) || !is_file($imgBottom)){
        return false;
    }

    $fdir = "Uploads/unionPics/";

    if(!is_dir($fdir)){
        @mkdir($fdir);
    }

    $fdir .= date('Y-m-d',time()) . '/';
    if(!is_dir($fdir)){
        @mkdir($fdir);
    }
    $fname = $fdir . uniqid() . '.jpg';

    $img1 = getImgHandleInfo($qr);
    $img2 = getImgHandleInfo($imgBottom);
    $totalWidth = $img1['w'];
    if ($type == 1){
        $height1 = $img1['h'];
    }else{
        $height1 = $img1['h'] - $img2['h'];
    }

    $height2 = $img2['h'];
    $totalHeight = $height1 + $height2;
    $image = createColora($totalWidth,$totalHeight,0,0,0);

    imagecopymerge($image, $img1['source'], 0, 0, 0, 0, $totalWidth, $height1, 100);
    imagecopymerge($image, $img2['source'], 0, $height1, 0, 0, $totalWidth, $height2, 100);

    $res = imagepng($image,$fname);
    @unlink($qr);
    imagedestroy($image);
    imagedestroy($img1['source']);
    imagedestroy($img2['source']);
    if(!$res){
        return false;
    }
    return $fname;
}

/**
 * @param $width
 * @param $height
 * @param int $c1
 * @param int $c2
 * @param int $c3
 * @return resource
 * 创建一张带颜色的画布
 */
function createColora($width,$height,$c1 = 255,$c2 = 255,$c3 = 255){
    //创建一个空白的画布  往上面写字
    $image = imagecreatetruecolor($width, $height);
    $pen = imagecolorallocate($image, $c1,$c2,$c3);
    imagefill($image,0,0,$pen);
    return $image;
}

/**
 * @param $text
 * @return array
 * 获取生成文字所占的长宽
 */
function getTtfInfo($text){
    $font_size = "30";
    $font = "simsun.ttc";

    $image = imagecreatetruecolor(1, 1);
    $pen = imagecolorallocate($image, 255,255,255);//白色的画笔
    $result = imagefttext($image, $font_size, 0,0, 0, $pen, $font,$text);
    imagedestroy($image);
    return $result;
}

/**
 * @param $fHandle
 * @param $logo
 * @param $posionX
 * @param $posionY
 * @param $width
 * @param $height
 * @return bool
 * 合并图片
 */
function mergeImg($fHandle,$logo,$posionX,$posionY,$width,$height){
    if(!is_file($logo)){return false;}
    $file_hand = $fHandle;
    $logoInfo = getImgHandleInfo($logo);
    $logo_hand = $logoInfo["source"];
    $h = $logoInfo["h"];
    $w = $logoInfo["w"];

    $image = createColora($width, $height);//新建一个画布放缩小的图片

    if($width > $height){ //需要横图
        $h_ = $logoInfo["w"] / ($width / $height);
        $y_ = ($logoInfo['h'] - $h_) / 2;
        imagecopyresampled($image, $logo_hand, 0, 0, 0, $y_,$width,$height,$logoInfo["w"], $h_);
    }else if($width < $height){ //需要竖图
        if($h > $w){ //要合并的图是竖图
            $w_ = $logoInfo['h'] / ($height / $width); //真实宽度
            $x_ = ($logoInfo['w'] - $w_) / 2;
            imagecopyresampled($image, $logo_hand, 0, 0, $x_, 0,$width,$height,$w_, $logoInfo['h']);
        }else{ //要合并的图是横的
            $w_ = $logoInfo["h"] / ($height / $width);
            $x_ = ($logoInfo['w'] - $w_) / 2;
            imagecopyresampled($image, $logo_hand, 0, 0, $x_, 0,$width,$height,$w_, $logoInfo['h']);
        }
    }else{ //正方形
        $y_ = $x_ = 0;
        if($h > $w){
            $y_ = ($logoInfo['h'] - $w) / 2;
            $temp = $logoInfo["w"];
        }else{
            $x_ = ($logoInfo['w'] - $h) / 2;
            $temp = $logoInfo["h"];
        }
        imagecopyresampled($image, $logo_hand, 0, 0, $x_, $y_,$width,$height,$temp, $temp);
    }

    imagecopymerge($file_hand, $image, $posionX, $posionY, 0, 0, $width, $height, 100);
    imagedestroy($logo_hand);
    imagedestroy($image);
    return $file_hand;
}

function getImgHandleInfo($fileName){
    if(!empty($fileName) && file_exists($fileName)){
        $ground_info = getimagesize($fileName);
        $ground_w = $ground_info[0];//取得背景图片的宽
        $ground_h = $ground_info[1];//取得背景图片的高
        switch($ground_info[2]){//取得背景图片的格式
            case 1:$ground_im = imagecreatefromgif($fileName);break;
            case 2:$ground_im = imagecreatefromjpeg($fileName);break;
            case 3:$ground_im = imagecreatefrompng($fileName);break;
            default:return false;
        }
        $arr["w"] = $ground_w;
        $arr["h"] = $ground_h;
        $arr["source"] = $ground_im;
        return $arr;
    }else{
        return false;
    }
}

/**
 * @param $fname
 * @param int $percentage
 * @return bool
 * 压缩图片
 */
function tarImg($fname,$percentage = 90){
    if(!is_file($fname)){
        return false;
    }
    if($percentage <= 0){
        return false;
    }
    $fHandle = getImgHandleInfo($fname);
    $readWidth = intval($fHandle['w'] * $percentage / 100);
    $readHeight = intval($fHandle['h'] * $percentage / 100);
    $image = createColora($readWidth, $readHeight);//新建一个画布放缩小的图片
    imagecopyresampled($image, $fHandle['source'], 0, 0, 0, 0,$readWidth,$readHeight,$fHandle["w"], $fHandle["h"]);
    imagedestroy($fHandle['source']);
    $res = imagepng($image,$fname);
    imagedestroy($image);
    if($res){
        return true;
    }
    return false;
}

//卡片id   获取小程序码
function getSmallQr($cid){
    $data = array();
    $data['scene'] = $cid;
    $data['page'] = 'pages/detail/detail';
    $token = getToken();
    $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$token;
    $jpg = https_curl_json($url, $data, 'json');
    if(empty($jpg) || json_decode($jpg)){ //获取失败使用默认图
        return 'Uploads/smallQr/small.jpg';
    }

    //生成图片
    $imgDir = 'Uploads/smallQr/';
    if(!is_dir($imgDir)){
        @mkdir($imgDir);
    }
    $imgDir .=  date('Y-m-d',time()) . '/';

    if(!is_dir($imgDir)){
        @mkdir($imgDir);
    }

    $filename = uniqid() . ".png";///要生成的图片名字
    $filePath = $imgDir . $filename;

    $file = fopen($filePath, "w");//打开文件准备写入
    fwrite($file, $jpg);//写入
    fclose($file);//关闭

    //图片是否存在
    if(!file_exists($filePath)){ // 图片保存失败则使用默认图
        return 'Uploads/smallQr/small.jpg';
    }
    return $filePath;
}

function getToken(){
    $access_token = S('access_token');
    if(!$access_token){
        $appid = C('WXXCX_CONFIG.appid');
        $secret = C('WXXCX_CONFIG.secret');
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
        $result = curl_get_content($url);
        $res = json_decode($result, true); // json字符串转数组

        if ($res) {
            $access_token = $res['access_token'];
            S('access_token',$access_token,7200);
        }
    }
    return $access_token;
}

function https_curl_json($url, $data, $type = 'json'){
    if($type=='json'){//json $_POST=json_decode(file_get_contents('php://input'), TRUE);
        $headers = array("Content-type: application/json;charset=UTF-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
        $data=json_encode($data);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
    $output = curl_exec($curl);
    if (curl_errno($curl)) {
        echo 'Errno'.curl_error($curl);
    }
    curl_close($curl);
    return $output;
}

function createParam($arrNiew){
    $info = array(
        array(
            '淘宝等级：' . $arrNiew['level'],
            '淘气值：' . $arrNiew['naughty'],
        ),
        array(
            '地区：' . $arrNiew['province'],
            $arrNiew['city'],
        ),
        array(
            '身高：' . $arrNiew['height'] . 'cm',
            '三围：' . "{$arrNiew['chestline']}/{$arrNiew['waistline']}/{$arrNiew['hipline']}",
            '体重：' . $arrNiew['weight'] . 'Kg'
        ),
    );
    $path = textWater($info); //生成每张模卡底部带有用户信息的图片
    return $path;
}

function downLoadTolocal($picArr,$isTar = true){
    $date = date('Y-m-d',time());
    $basePath = 'Uploads/' . $date;

    if(!is_dir($basePath)){
        @mkdir($basePath);
    }

    $arr_ = array();
    $temp = array();
    foreach ($picArr as $key => $val){
        $fname = $basePath . '/' . uniqid() . '.jpg';
        if(!in_array($val,$temp)){
            $res = strpos($val,'imageView2');
            if ($isTar){
                if($res === false){
                    $source = file_get_contents($val . getImgParm(0,300,300));
                }else{
                    $source = file_get_contents($val);
                }
            }else{
                $source = file_get_contents($val);
            }

            $hand = fopen($fname,'w');
            fwrite($hand,$source);
            fclose($hand);
            $temp[] = $val;
        }
        $arr_[] = $fname;
    }
    return $arr_;
}

function tencentCloundUpload($file = ''){
    $dar = Date('Y').'/'.Date('m'); //以年月为目录
    $path = "$dar/";
    if($file){
        $tempPath = $file;
        $dstPath = $path . substr($file,strrpos($file,'/') + 1);
    }else{
        $tempPath = $_FILES['file']['tmp_name'];
        $dstPath = $path . uniqid() . substr($_FILES['file']['name'],strrpos($_FILES['file']['name'],'.'));
    }
    Vendor('TencentClound.TencentClound');
    $obj = new \TencentClound();
    $data = $obj->uploadFile($dstPath,file_get_contents($tempPath));

    if($data['code'] == 0){
        $path = $data['data']['ObjectURL'];
        $reps = C('IMGDOMAIN');
        $result = preg_replace('/http.*myqcloud.com/',$reps,$path);
        return $result;
    }else{
        return false;
    }
}

function getImgParm($model = 0,$w = 300,$h = 300){
    return  '?imageView2/'. $model .'/w/'. $w .'/h/' . $h;
}

function getStyle(){
    return array(
        array(
            'id' => 1,
            'name' => '8张 两行布局',
            'border' => 10,
            'canvw' => 750,
            'canvh' => 370,
            'points' => array(
                array(
                    'x' => 0,
                    'y' => 0,
                    'w' => 180,
                ),
                array(
                    'x' => 190,
                    'y' => 0,
                    'w' => 180,
                ),
                array(
                    'x' => 380,
                    'y' => 0,
                    'w' => 180,
                ),
                array(
                    'x' => 570,
                    'y' => 0,
                    'w' => 180,
                ),
                array(
                    'x' => 0,
                    'y' => 190,
                    'w' => 180,
                ),
                array(
                    'x' => 190,
                    'y' => 190,
                    'w' => 180,
                ),
                array(
                    'x' => 380,
                    'y' => 190,
                    'w' => 180,
                ),
                array(
                    'x' => 570,
                    'y' => 190,
                    'w' => 180,
                ),
            ),
        ),
        array(
            'id' => 2,
            'name' => '1+8 两行布局',
            'border' => 10,
            'canvw' => 750,
            'canvh' => 294,
            'points' => array(
                array(
                    'x' => 0,
                    'y' => 0,
                    'w' => 142,
                    'h' => 294
                ),
                array(
                    'x' => 152,
                    'y' => 0,
                    'w' => 142,
                ),
                array(
                    'x' => 304,
                    'y' => 0,
                    'w' => 142,
                ),
                array(
                    'x' => 456,
                    'y' => 0,
                    'w' => 142,
                ),
                array(
                    'x' => 608,
                    'y' => 0,
                    'w' => 142,
                ),
                array(
                    'x' => 152,
                    'y' => 152,
                    'w' => 142,
                ),
                array(
                    'x' => 304,
                    'y' => 152,
                    'w' => 142,
                ),
                array(
                    'x' => 456,
                    'y' => 152,
                    'w' => 142,
                ),
                array(
                    'x' => 608,
                    'y' => 152,
                    'w' => 142,
                ),
            ),
        ),
        array(
            'id' => 3,
            'name' => '九宫格 三行布局',
            'border' => 10,
            'canvw' => 750,
            'canvh' => 756,
            'points' => array(
                array(
                    'x' => 0,
                    'y' => 0,
                    'w' => 242,
                ),
                array(
                    'x' => 252,
                    'y' => 0,
                    'w' => 242,
                ),
                array(
                    'x' => 504,
                    'y' => 0,
                    'w' => 242,
                ),
                array(
                    'x' => 0,
                    'y' => 252,
                    'w' => 242,
                ),
                array(
                    'x' => 252,
                    'y' => 252,
                    'w' => 242,
                ),
                array(
                    'x' => 504,
                    'y' => 252,
                    'w' => 242,
                ),
                array(
                    'x' => 0,
                    'y' => 504,
                    'w' => 242,
                ),
                array(
                    'x' => 252,
                    'y' => 504,
                    'w' => 242,
                ),
                array(
                    'x' => 504,
                    'y' => 504,
                    'w' => 242,
                ),
            ),
        ),
    );
}