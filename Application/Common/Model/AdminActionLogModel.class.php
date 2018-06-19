<?php

namespace Common\Model;

use Common\Model\CommonModel;

class AdminActionLogModel extends CommonModel {

    protected $_validate = array(
    );
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function')       
    );
    private $_cacheKey = 'admin_action_log_{id}';

    public function getCacheKey($id) {
        return str_replace('{id}', $id, $this->_cacheKey);
    }

    public function getById($id) {
        if ($id > 0) {
            $data = S($this->getCacheKey($id));
            if (empty($data)) {
                $data = $this->find($id);
                S($this->getCacheKey($id), $data, 60 * 60);
            }
            return $data;
        }
        return null;
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

    public function getListRows($param, $return_total = TRUE) {
        !isset($param['field']) && $param['field'] = 'id';
        $lists = $this->getList($param, TRUE);
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