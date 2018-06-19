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
class UnfiedOrderModel extends CommonModel {
    protected $_auto = array(
        array('create_time', NOW_TIME),
    );

    private $_cacheKey = 'order_{id}';
    private $_cacheKeyNo = 'order_on_{no}';

    public function getCacheKey($id) {
        return str_replace('{id}', $id, $this->_cacheKey);
    }

    public function getCacheKeyNo($id) {
        return str_replace('{no}', $id, $this->_cacheKeyNo);
    }

    /**
     * 根据ID获取订单信息
     * @param $id
     * @param string $fields
     * @return array|mixed
     */
    public function getById($id,$fields = '*'){
        $data = array();
        if ($id) {
            $data = S($this->getCacheKey($id));
            if (!$data) {
                $data = $this->field($fields)->where('id="' . $id . '"')->find();
                S($this->getCacheKey($id), $data, 60 * 60);
            }
        }
        return $data;
    }

    /**
     * 根据订单号获取订单信息
     * @param $id
     * @param string $fields
     * @return array|mixed
     */
    public function getByNo($orderNo,$fields = '*'){
        $data = array();
        if ($orderNo) {
            $data = S($this->getCacheKeyNo($orderNo));
            if (!$data) {
                $data = $this->field($fields)->where('order_no="' . $orderNo . '"')->find();
                S($this->getCacheKeyNo($orderNo), $data, 60 * 60);
            }
        }
        return $data;
    }

    public function getByUid($uid,$status = 0,$type = 1){
        return $this->where(array('user_id' => $uid,'type' => $type,'status' => $status))->field('id,order_no,wx_no')->order('id desc')->find();
    }

    /**
     * 保存
     * @param $data
     * @param array $where
     * @return bool|mixed|null
     */
    public function saveData($_data, $where = array()) {
        $data = $this->create($_data);
        if (!$data) {
            return false;
        }
        if ($data['id']) {
            $rs = $this->save($data);
            if (!$rs) {
                return false;
            }
            S($this->getCacheKey($data['id']), NULL);
        }else if($where){
            $rs = $this->where($where)->save($data);
            if (!$rs) {
                return false;
            }
            S($this->getCacheKey($data['id']), NULL);
        } else {
            $rs = $this->add($data);
            if (!$rs) {
                return false;
            }
        }
        return $rs;
    }

    /**
     * 删除
     */
    public function delById($id){
        if(!$id){
            return false;
        }
        if (is_array($id)) {
            $rs = $this->where(array('id' => array('in', $id)))->delete();
            foreach ($id as &$v) {
                S($this->getCacheKey($v), NULL);
            }
            unset($v);
        } else {
            $rs = $this->where(array('id' => $id))->delete();
            S($this->getCacheKey($id), NULL);
        }
        return $rs;
    }

    /**
     * 修改状态
     * @param $id
     * @param string $field
     * @param int $value
     * @return bool
     */
    public function changeById($id, $field = 'status', $value = 2) {
        if (!$id) {
            return false;
        }
        $rs = $this->where(array('id' => $id))->setField($field, $value);
        if (!$rs) {
            return false;
        }
        S($this->getCacheKey($id), NULL);
        return $rs;
    }
}
