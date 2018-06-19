<?php

namespace Common\Model;

class ShareModel extends CommonModel {

    protected $_validate = array(
    );
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function')
    );
    private $_cacheKey = 'moka_share_{uid}';

    public function getCacheKey($uid) {
        return str_replace('{uid}', $uid, $this->_cacheKey);
    }

    public function getByUid($uid) {
        if(!$uid){
            return array();
        }

        $data = S($this->getCacheKey($uid));
        if (!$data) {
            $data = $this->field('*')->where(array('uid' => $uid))->select();
            S($this->getCacheKey($uid), $data, 60 * 60);
        }
        return $data;
    }


    public function saveData($data = '') {
        $data = $this->create($data);
        $rs = $this->add($data);
        S($this->getCacheKey($data['uid']), NULL);
        return $rs;
    }

    public function getListByPage($map, $page = 1, $order = 'create_time desc', $field = '*', $r = 20) {
        $params = array();
        $params['where'] = $map;
        $params['order'] = $order;
        $params['field'] = $field;
        $params['page'] = $page;
        $params['limit'] = $r;

        $rs = $this->getListRows($params);

        return $rs;
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
}

?>