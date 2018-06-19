<?php

namespace Common\Model;

use Common\Model\CommonModel;

class NewsPushModel extends CommonModel {   
    // 自动验证设置
    protected $_validate = array(
    );
    // 自动填充设置
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_BOTH, 'function'),      
    );
    private $_cacheKey = 'news_push_{id}';

    public function getCacheKey($id) {
        return str_replace('{id}', $id, C('DATA_CACHE_TWO_PREFIX') . $this->_cacheKey);
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