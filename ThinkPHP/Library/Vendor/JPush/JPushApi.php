<?php

/**
 * JPush极光推送
 * platform	必填	推送平台设置 ，JPush 当前支持 Android, iOS, Windows Phone 三个平台的推送。其关键字分别为："android", "ios", "winphone"。
 * audience	必填	推送设备指定 ，JPush 提供了多种方式，比如：别名、标签、注册ID、分群、广播等。
 * notification	可选	通知（客户端显示在通知栏）内容体。是被推送到客户端的内容。与 message 一起二者必须有其一，可以二者并存
 * message	可选	消息（客户端不显示）内容体。是被推送到客户端的内容。与 notification 一起二者必须有其一，可以二者并存
 * options	可选	推送参数
 */

namespace Vendor\JPush;

require_once("JPush.php");

class JPushApi {

    private $app_key = '';            //待发送的应用程序(appKey)，只能填一个。
    private $master_secret = '';      //主密码
    private $url = "https://api.jpush.cn/v3/push";            //推送的地址
    private $jpush;
    private $product = true;          //是否生产环境

    public function getJPush() {
        return $this->jpush;
    }

    //若实例化的时候传入相应的值则按新的相应值进行

    public function __construct($app_key = null, $master_secret = null, $url = null) {
        $app_key && $this->app_key = $app_key;
        $master_secret && $this->master_secret = $master_secret;
        $url && $this->url = $url;
        $this->jpush = new \JPush($this->app_key, $this->master_secret);
    }

    public function setAppKey($key) {
        $this->app_key = $key;
        return $this;
    }

    public function setMasterSecret($secret) {
        $this->master_secret = $secret;
        return $this;
    }

    /**
     * 推送通知
     * @param string $receiver
     * @param string $title
     * @param string $content
     * @param string $m_time
     */
    public function pushNoticeComm($receiver = 'all', $title = '', $content = '', $extras, $m_time = 86400) {
        try {
            $result = $this->jpush->push()
                    ->setPlatform(array('ios', 'android'))                   
                    //->addAlias('alias1')
                    //->addTag(array('tag1', 'tag2'))
                    ->setNotificationAlert($content,'default')
                    ->addAllAudience()
                    //->setMessage("msg content", 'msg title', 'type', array("key1"=>"value1", "key2"=>"value2"))
                    ->setOptions(null, $m_time, null, $this->product)
                    ->send();
        } catch (\APIRequestException $e) {
            return;
        }
        return;
    }

    /**
     * 广播推送自定义消息
     * @param string $receiver
     * @param string $title
     * @param string $content
     * @param unknown $extras
     * @param string $m_time
     */
    public function pushMess($receiver = 'all', $title = '', $content = '', $extras, $m_time = 86400) {
        try {
            $result = $this->jpush->push()
                    ->setPlatform(array('ios', 'android'))
                    ->addAllAudience()
                    ->setMessage($content, $title, 'type', $extras)
                    ->setOptions(null, $m_time, null, $this->product)                   
                    ->send();
        } catch (\APIRequestException $e) {
            return;
        }
        return;
    }

    /**
     * 别名推送自定义消息
     * @param string $alias 字符串或数组
     * @param string $title
     * @param string $content
     * @param unknown $extras
     * @param string $m_time
     */
    public function pushMessAlias($alias, $title = '', $content = '', $extras, $m_time = 86400) {
        try {
            if (is_array($alias)) {
                foreach ($alias as &$v) {
                    $v = (string) ('' . $v);
                }
            } else {
                $alias = (string) $alias;
            }
            $result = $this->jpush->push()
                    ->setPlatform(array('ios', 'android'))
                    ->addAlias($alias)
                    ->setMessage($content, $title, 'type', $extras)
                    ->setOptions(null, $m_time, null, $this->product)
                    ->send();
            return $result;
        } catch (\APIRequestException $e) {
            print_r($e);
            return;
        }
    }
    
    /**
     * 别名推送自定义消息
     * @param string $alias 字符串或数组
     * @param string $title
     * @param string $content
     * @param unknown $extras
     * @param string $m_time
     */
    public function pushIosMessAlias($alias, $title = '', $content = '', $extras, $m_time = 86400) {
        try {
            if (is_array($alias)) {
                foreach ($alias as &$v) {
                    $v = (string) ('' . $v);
                }
            } else {
                $alias = (string) $alias;
            }
            $result = $this->jpush->push()
                    ->setPlatform(array('ios'))
                    ->addAlias($alias)
                    ->setMessage($content, $title, 'type', $extras)
                    ->setOptions(null, $m_time, null, $this->product)
                    ->send();
            return $result;
        } catch (\APIRequestException $e) {
            //print_r($e);
            return;
        }
    }
    
    /**
     * 别名推送自定义消息
     * @param string $alias 字符串或数组
     * @param string $title
     * @param string $content
     * @param unknown $extras
     * @param string $m_time
     */
    public function pushAndroidMessAlias($alias, $title = '', $content = '', $extras, $m_time = 86400) {
        try {
            if (is_array($alias)) {
                foreach ($alias as &$v) {
                    $v = (string) ('' . $v);
                }
            } else {
                $alias = (string) $alias;
            }
            $result = $this->jpush->push()
                    ->setPlatform(array('android'))
                    ->addAlias($alias)
                    ->setMessage($content, $title, 'type', $extras)
                    ->setOptions(null, $m_time, null, $this->product)
                    ->send();
            return $result;
        } catch (\APIRequestException $e) {
            //print_r($e);
            return;
        }
    }
    

    /**
     * 通知栏有声音
     * @param type $alias
     * @param type $content
     * @param type $extras
     * @param type $m_time
     * @return type
     */
    public function pushNotice($alias, $content = '', $extras = array(), $m_time = 86400) {
        try {
            if (is_array($alias)) {
                foreach ($alias as &$v) {
                    $v = (string) ('' . $v);
                }
            }
            $result = $this->jpush->push()
                    ->setPlatform(array('ios'))
                    ->addAlias($alias)
                    ->setOptions(null, $m_time, null, $this->product)
                    //->addTag(['tag1', 'tag2'])                  
                    //->addAndroidNotification($content, $content, null, $extras)
                    ->addIosNotification(array('body'=>$extras['data']['msg']?$extras['data']['msg']:$content,'title'=>$content), 'default', null, null, null, $extras)
                    ->send();
            return $result;
        } catch (\APIRequestException $e) {
            return;
        }
    }
    /**
     * 通知栏没有声音
     * @param type $alias
     * @param type $content
     * @param type $extras
     * @param type $m_time
     * @return type
     */
    public function pushNoticeNoSound($alias, $content = '', $extras = array(), $m_time = 86400) {
        try {
            if (is_array($alias)) {
                foreach ($alias as &$v) {
                    $v = (string) ('' . $v);
                }
            }
            $result = $this->jpush->push()
                    ->setPlatform(array('ios'))
                    ->addAlias($alias)
                    ->setOptions(null, $m_time, null, $this->product)
                    //->addTag(['tag1', 'tag2'])                  
                    //->addAndroidNotification($content, $content, null, $extras)
                    ->addIosNotification(array('body'=>$extras['data']['msg']?$extras['data']['msg']:$content,'title'=>$content), '', null, null, null, $extras)
                    ->send();
            return $result;
        } catch (\APIRequestException $e) {
            return;
        }
    }

    /**
     * 
     * @param type $alias
     * @param type $title
     * @param type $content
     * @param type $extras
     * @param type $m_time
     */
    public function pushMessAliasAndNotice($alias, $title = '', $content = '', $extras, $m_time = 86400) {
        $rs[] = $this->pushMessAlias($alias, $title, $content, $extras, $m_time);
        $rs[] = $this->pushNotice($alias, $title, $extras, $m_time);
        return $rs;
    }
    
    public function deleteAlias($alias){
        $rs =  $this->jpush->device()->deleteAlias($alias);
    }

}
