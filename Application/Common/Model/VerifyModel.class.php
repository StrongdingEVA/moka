<?php
/**
 * 验证码模型
 */

namespace Common\Model;

use Common\Model\CommonModel;

class VerifyModel extends CommonModel
{
    protected $tableName = 'verify';
    protected $_cacheKey = 'verify_{id}';
    protected $_auto = array(array('create_time', NOW_TIME, self::MODEL_INSERT));
    
     public function getCacheKey($id) {
        return str_replace('{id}', $id, C('DATA_CACHE_TWO_PREFIX').$this->_cacheKey);
    }

    /**
     * 生成验证码
     * @param string $account 账户（手机、邮箱。。。）
     * @param string $type 类型（手机、邮箱）
     * @param number $uid
     */
    public function addVerify($account,$type,$uid=0)
    {
        //$uid = $uid?$uid:is_login();
        if ($type == 'mobile') {
            $verify = create_rand(6, 'num');
        } else {
            $verify = create_rand(32);
        }
        /* $this->where(array('account'=>$account,'type'=>$type))->delete();
        $data['verify'] = $verify;
        $data['account'] = $account;
        $data['type'] = $type;
        $data['uid'] = $uid;
        $data['code'] = rand(1111, 9999); //短信编号
        $data = $this->create($data);
        $res = $this->add($data);
        if(!$res){
            return false;
        } */
        /**
         * 验证码改用缓存过期方式
         */
        $data['verify'] = $verify;
        $data['account'] = $account;
        $data['code'] = rand(1111, 9999); //短信编号
        
        S($this->getCacheKey($account), $data['verify'], 600); //设置缓存10分钟过期
        
        if($type == 'mobile'){
            return array('verify'=>$data['verify'], 'code'=>$data['code']);
        }
        return $verify;
    }

    /**
     * 获取缓存
     * @param string $account
     */
    public function getVerify($account){
        /* $verify = $this->where(array('id'=>$id))->getField('verify');
        return $verify; */
        return S($this->getCacheKey($account));
    }
    
    /**
     * 验证
     * @param string $account
     * @param string $type
     * @param string $verify
     * @param string $uid
     * @return boolean
     */
    public function checkVerify($account,$type,$verify,$uid){
        /* $verify = $this->where(array('account'=>$account,'type'=>$type,'verify'=>$verify,'uid'=>$uid))->find();
        if(!$verify){
            return false;
        }
        $this->where(array('account'=>$account,'type'=>$type))->delete(); */
        //$this->where('create_time <= '.get_some_day(1))->delete();
        
        if(!$verify || S($this->getCacheKey($account)) != $verify){
            return false;
        }

        return true;
    }
    
    /**
     * 设置验证码失效
     * @param string $account
     * @return Ambigous <mixed, object>
     */
    public function delVerify($account){
        return S($this->getCacheKey($account), null);
    }

}