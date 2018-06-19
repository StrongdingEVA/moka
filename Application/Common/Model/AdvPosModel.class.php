<?php

namespace Common\Model;

use Common\Model\CommonModel;

class AdvPosModel extends CommonModel
{

    // 自动验证设置
    protected $_validate = array(
        array('name', 'require', '广告名称必填'),
        array('title', 'require', '广告标签必填'),
        array('title', '', '标题已经存在', 0, 'unique', self::MODEL_INSERT),
    );
    // 自动填充设置
    protected $_auto = array(
        
    );
    private $_cacheKey = 'adv_pos_{id}';

    public function getCacheKey($id) {
        return str_replace('{id}', $id, C('DATA_CACHE_TWO_PREFIX').$this->_cacheKey);
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

    public function saveData($_data = '') {
        $data = $this->create($_data);
        if (!$data) {
            return false;
        }
        if ($data['id']) {
            $rs = $this->save($data);
            if (!$rs) {
                return false;
            }
            action_log('update_adv_pos', 'advert', $data['id'], UID);
            S($this->getCacheKey($data['id']), NULL);
        } else {
            $rs = $this->add($data);
            if (!$rs) {
                return false;
            }
        }
        return $rs;
    }

    public function getListByPage($map, $page = 1, $order = 'update_time desc', $field = '*', $r = 20) {
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
        action_log('del_adv_pos', 'advert', $id, UID);
        return $rs;
    }

    public function changeById($id, $field = 'status', $value = 0) {
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

?>