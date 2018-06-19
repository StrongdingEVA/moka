<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Common\Model;

use User\Api\UserApi;
use Common\Model\CommonModel;

/**
 * 文档基础模型
 */
class MemberInfoModel extends CommonModel {
    protected $connection = 'DB_UCENTER';
    /* 用户模型自动完成 */

    protected $_auto = array(
        array('create_time', NOW_TIME,self::MODEL_INSERT),
    );

    // 自动验证设置
    protected $_validate = array(
    );

    private $_cacheKey = ' member_info_{uid}';
    private $_cacheKeyUid = ' moka_uinfo_{uid}';

    public function getCacheKey($id) {
        return str_replace('{uid}', $id, $this->_cacheKey);
    }

    public function getCacheKeyUid($uid){
        return str_replace('{uid}', $uid, $this->_cacheKeyUid);
    }

    public function getById($uid,$fields = '*'){
        $data = array();
        if ($uid) {
            $data = S($this->getCacheKeyUid($uid));
            if (!$data) {
                $data = $this->field($fields)->where('uid="' . $uid . '"')->find();
                S($this->getCacheKeyUid($uid), $data, 60 * 60);
            }
        }
        return $data;
    }

    public function getByOpenId($openid,$fields = '*') {
        $data = array();
        if ($openid) {
            $data = S($this->getCacheKey($openid));
            if (!$data) {
                $data = $this->field($fields)->where('openid="' . $openid . '"')->find();
                S($this->getCacheKey($openid), $data, 60 * 60);
            }
        }
        $arr['data'] = $data;
        $arr['info'] = '成功';
        $arr['status'] = 0;
        return $arr;
    }

    public function getUserWechatInfo($openid,$accessToken){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='. $accessToken .'&openid='. $openid.'&lang=zh_CN';
        $result = file_get_contents($url);
        $result = json_decode($result,1);
        return $result;
    }

    public function addData($data){
        $data = $this->create($data);
        if (!$data) {
            return NULL;
        }
        $res = $this->add($data);
        return $res;
    }

    public function saveData($data, $where = array()) {
        $data = $data ? $data : $this->create();
        if ($data['uid']) {
            $where['uid'] = $data['uid'];
            unset($data['uid']);
        }

        foreach($data as $key => $val){
            if(empty($val) && $val !== 0){
                unset($data[$key]);
            }
        }

        if ($where) {
            $res = $this->where($where)->save($data);
        } else {
            $data = $this->create($data);
            if (!$data) {
                return NULL;
            }
            $res = $this->add($data);
        }

        //清缓存
        $where['uid'] && S($this->getCacheKeyUid($where['uid']), NULL);

        return $res;
    }

    public function getListRows($map, $return_total = TRUE) {
        !isset($map['field']) && $map['field'] = 'openid';
        $lists = $this->getList($map, TRUE);
        $data = array();
        if ($lists[1]) {
            $ids = array_filter(getSubByKey($lists[0], 'openid'));
            if($ids){
                $data = $this->field($map['field'])->where(array('openid' => array('in',$ids)))->select();
            }
        }
        if (!$data) {
            $data = NULL;
        }
        return $return_total ? array($data, $lists[1]) : $data;
    }

    public function changeById($id, $field = 'is_show', $value = 0) {
        if (is_array($id)) {
            $rs = $this->where(array('uid' => array('in', $id)))->setField($field, $value);
            foreach ($id as &$v) {
                S($this->getCacheKey($v), NULL);
            }
            unset($v);
        } else {
            $rs = $this->where(array('uid' => $id))->setField($field, $value);
            S($this->getCacheKey($id), NULL);
        }
        return $rs;
    }
}
