<?php

namespace Common\Model;

use Common\Model\CommonModel;

class SyncLoginModel extends CommonModel {

    protected $connection = 'DB_UCENTER';
    // 自动填充设置
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
    );
    private $_cacheKey = 'sync_login_{id}';

    public function getCacheKey($id) {
        return str_replace('{id}', $id, $this->_cacheKey);
    }

    public function getById($id) {
        $data = array();
        if ($id > 0) {
            $data = S($this->getCacheKey($id));
            if (!$data) {
                $data = $this->field('*')->find($id);
                S($this->getCacheKey($id), $data, 60 * 60);
            }
        }
        return $data;
    }

    public function getOpenIdByUidAndType($uid, $type) {
        $data = array();
        $cachekey = 'openid_'.$uid.'_'.$type;
        if ($uid > 0) {
            $data = S($cachekey);
            if (!$data) {
                $data = $this->where(array('uid'=>$uid, 'type'=>$type))->getField('type_uid');
                S($cachekey, $data, 24 * 3600);
            }
        }
        return $data;
    }

    public function saveData($data, $where) {
        if ($data['id']) {
            $where['id'] = $data['id'];
            unset($data['id']);
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
        $where['id'] && S($this->getCacheKey($where['id']), NULL);

        return $res;
    }

    public function getListRows($map, $return_total = TRUE) {
        !isset($map['field']) && $map['field'] = 'id';
        $lists = $this->getList($map, TRUE);
        $data = array();
        if ($lists[1]) {
            $ids = array_filter(getSubByKey($lists[0], 'id'));
            foreach ($ids as $v) {
                $data[] = $this->getById($v);
            }
        }
        if (!$data) {
            $data = NULL;
        }
        return $return_total ? array($data, $lists[1]) : $data;
    }

    public function delById($id) {
        $rs = $this->where(array('id' => $id))->delete();
        S($this->getCacheKey($id), NULL);
        return $rs;
    }

}

?>