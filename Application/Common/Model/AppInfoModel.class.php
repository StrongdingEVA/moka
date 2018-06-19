<?php

namespace Common\Model;

use Common\Model\CommonModel;

class AppInfoModel extends CommonModel
{

    // 自动验证设置
    protected $_validate = array(
        array('platform', 'require', '类型不能为空！', 1),
        array('channel', 'require', '渠道不能为空！', 1),
        array('download_url', 'require', '下载地址不能为空！', 1),
        array('version', 'require', '版本不能为空！', 1),
    );
    // 自动填充设置
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_BOTH, 'function'),
    );
    
    private $_cacheKey = 'app_info_{id}';
    
    public $applist = array( 1 => '模卡' );

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