<?php

namespace Cli\Controller;

use Think\Cache\Driver\Redis;
use Think\Controller;

/**
 * redis队列入库本地运行脚本
 */
class RedisController extends Controller {

    var $redis;

    function __construct() {
        parent::__construct();
        $this->redis = new Redis();
    }
    
    /**
     * 发送影视更新推送
     */
    public function sendVideoUpdateNotify(){
        $name = 'queue_video_update';
        $count = 0;
        $max = 100; // 循环发送每次读取一百条记录
        while ($count < $max) {
            $video_id = $this->redis->rPop($name);
            if (!$video_id) {
                break;
            }
            $count++;
        
            $video = D('Common/Video')->getById($video_id);
            if (!$video) {
                continue;
            }
        
            //分页发送，每次限制发送一千个用户
            $page = 1;
            while ($page > 0) {
                $list = D('Common/VideoFollow')->where(array( 'obj_id' => $video['id'], 'notify' => 1))->page($page, 1000)->field('uid')->select();
                if (!empty($list)) {
                    $page++;
                    foreach ($list as $v){
                        $this->send_msg($v['uid'], $video);
                    }
                } else {
                    $page = 0;
                }
            }
        }
        $this->redis->close();
        exit;
    }
    
    /**
     * 发送小程序推送
     * @param number $uid
     * @param number $video_id
     * @param string $content
     */
    protected function send_msg($uid, $video){
        if(!$uid || !$video){
            return false;
        }
        
        $formid = $this->get_video_formid($uid);

        if(!$formid){
            return false;
        }
        
        $openid = D('SyncLogin')->getOpenIdByUidAndType($uid, 'wxxcx');
        if(!$openid){
            return false;
        }
        
        $value = array(
                "keyword1"=>array( // 番剧标题
                        "value" => $video['title'],
                        "color" => "#4a4a4a"
                ),
                "keyword2"=>array( // 更新内容
                        "value" => $video['type'] == 3 ? "电影" : "第{$video['num']}集",
                        "color" => "#9b9b9b"
                ),
                "keyword3"=>array( // 更新时间
                        "value" => date('Y-m-d H:i', $video['update_time']),
                        "color" => "#9b9b9b"
                )
        );
        
        $access_token = $this->get_access_token();
        if(!$access_token){
            return false;
        }
        
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$access_token;
        
        $data = array();
        //$data['access_token']=$access_token;
        $data['touser'] = $openid;
        $data['template_id'] = $video['type'] == 3 ? 'Uxs-KUg5YZREBxCuEH8PvIYn508sJ4YzESl3ljUxNUk' : '_V-zWx32gNyfD57CcAkw5NGOJBxHZ21m-MXQTgOcpNw';
        $data['page'] = 'pages/detail/detail?id='.$video['id'];  //点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,该字段不填则模板无跳转。
        $data['form_id'] = $formid;
        
        $data['data'] = $value;                        //模板内容，不填则下发空模板
        
        //$data['color'] = '';                        //模板内容字体的颜色，不填默认黑色
        //$data['color']='#ccc';
        //$data['emphasis_keyword'] = '';    //模板需要放大的关键词，不填则默认无放大
        //$data['emphasis_keyword']='keyword1.DATA';
        
        //$send = json_encode($data);   //二维数组转换成json对象
        
        /* curl_post()进行POST方式调用api： api.weixin.qq.com*/
        $result = $this->https_curl_json($url, $data, 'json');
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    public function testXcxNotify(){
        $video_id = 775;
        $uid = 6;

        $formid = $this->get_video_formid($uid);
        var_dump($formid);
        if(!$formid){
            return false;
        }
        
        $openid = D('SyncLogin')->getOpenIdByUidAndType($uid, 'wxxcx');
        if(!$openid){
            return false;
        }
        
        $video = D('Video')->getById($video_id);
        if(!$video){
            return false;
        }
        
        $value = array(
                "keyword1"=>array( // 番剧标题
                        "value" => $video['title'],
                        "color" => "#4a4a4a"
                ),
                "keyword2"=>array( // 更新内容
                        "value" => "第{$video['num']}集",
                        "color" => "#9b9b9b"
                ),
                "keyword3"=>array( // 更新时间
                        "value" => date('Y-m-d H:i', $video['update_time']),
                        "color" => "#9b9b9b"
                )
        );
        
        $access_token = $this->get_access_token();
        var_dump($access_token);
        if(!$access_token){
            return false;
        }
        
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$access_token;
        
        $data = array();
        //$data['access_token']=$access_token;
        $data['touser'] = $openid;
        $data['template_id'] = '_V-zWx32gNyfD57CcAkw5NGOJBxHZ21m-MXQTgOcpNw';
        $data['page'] = 'pages/detail/detail?id='.$video_id;  //点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,该字段不填则模板无跳转。
        $data['form_id'] = $formid;
        
        $data['data'] = $value;                        //模板内容，不填则下发空模板
        
        $data['color'] = '';                        //模板内容字体的颜色，不填默认黑色
        //$data['color']='#ccc';
        $data['emphasis_keyword'] = '';    //模板需要放大的关键词，不填则默认无放大
        //$data['emphasis_keyword']='keyword1.DATA';
        
        //$send = json_encode($data);   //二维数组转换成json对象
        
        /* curl_post()进行POST方式调用api： api.weixin.qq.com*/
        print_r($data);
        $result = $this->https_curl_json($url, $data, 'json');
        var_dump($result);
    }

    protected function get_access_token(){
        $xcx_config = C('WXXCX_CONFIG');
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$xcx_config['appid'].'&secret='.$xcx_config['secret'];
        $res = file_get_contents($url);// 异常{"errcode":40163,"errmsg":"code been used, hints: [ req_id: MYgAsA0978th48 ]"}
        $res = json_decode($res, true);
        
        if(!$res['access_token']){
            return false;
        }
        return $res['access_token'];
    }

    protected function https_curl_json($url,$data,$type){
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
            return false;
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl);
        return $output;
    }
    
    protected function get_video_formid($uid){
        return LC('Common/Redis')->get_queue_video_formid($uid)['formid'];
    }

    public function addTask($cid = 0,$name){
        if(!$cid){
            return false;
        }
        return $this->redis->setqueue($name,$cid);
    }

    public function useTask($name = ''){
        if(!$name){
            return false;
        }
        return $this->redis->rPop($name);
    }
}
