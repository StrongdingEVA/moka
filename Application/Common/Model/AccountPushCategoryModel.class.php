<?php

namespace Common\Model;

use Common\Model\CommonModel;

class AccountPushCategoryModel extends CommonModel {
    // 自动验证设置
    protected $_validate = array(
        array('title', 'require', '分类名称不能为空！', 1),
        array('title', '', '分类已存在', 0, 'unique', self::MODEL_INSERT),      
    );
    // 自动填充设置
    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
    );
    private $_cacheKey = 'account_push_category_{id}';

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

    /**
     * 子集数据
     * @param number $pid
     */
    public function getByPid($pid = 0, $tree = FALSE) {
        $map['where']['status'] = 1;
        $map['where']['pid'] = $pid;
        $map['field'] = 'id';
        $data = $this->getListRows($map, false);
        if ($tree) {
            foreach ($data as &$v) {
                $v['child'] = $this->getByPid($v['id'], $tree);
                if (!$v['child']) {
                    unset($v['child']);
                }
            }
            unset($v);
        }
        return $data;
    }

}

?>