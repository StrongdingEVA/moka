<?php

namespace Common\Model;

use Common\Model\CommonModel;

class ArticleModel extends CommonModel
{

    protected $connection = 'DB_9CNV2_XCX';
    // 自动验证设置
    protected $_validate = array(
        array('title', 'require', '标题必填'),
        array('content', 'require', '内容必填'),
        array('title', '', '标题已经存在', 0, 'unique', self::MODEL_INSERT),
    );
    // 自动填充设置
    protected $_auto = array(
        array('sitetitle', '-', self::MODEL_INSERT),
        array('description', '-', self::MODEL_INSERT),
        array('iscomment', 0, self::MODEL_INSERT),
        array('ip', 'get_client_ip', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_BOTH, 'function'),
    );
    private $_cacheKey = 'article_{id}';

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
            S($this->getCacheKey($data['id']), NULL);
        } else {
            $rs = $this->add($data);
            if (!$rs) {
                return false;
            }
        }
        return $rs;
    }

    public function getListRows($map, $return_total = TRUE) {
        !isset($map['field']) && $map['field'] = 'id';
        $lists = $this->getList($map, $return_total);
        return $return_total ? array($lists[0], $lists[1]) : $lists;
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

    /**
     * 更新点击量
     * @param number $id
     */
    public function updateHits($id) {
        if ($this->where(array('id' => $id))->setInc('hits', 1)) {
            // 更新缓存
            $info = $this->getById($id);
            if (!$info) {
                return false;
            }
            $info['hits'] += 1;
            S($this->getCacheKey($id), $info, 60 * 60);
        }
    }

}

?>