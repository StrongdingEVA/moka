<?php

namespace Common\Model;

use Common\Model\CommonModel;

class StyleModel extends CommonModel
{

    // 自动验证设置
    protected $_validate = array(
        array('name', 'require', '模卡类型名称不能为空！', 1),
        array('pic_num', 'require', '模卡图片数量不能为空', 1),
        array('pics', 'require', '模卡图片数量不能为空', 1),
    );
    // 自动填充设置
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
    );
    
    private $_cacheKey = 'style_{id}';

    public function getCacheKey($id) {
        return str_replace('{id}', $id, C('DATA_CACHE_TWO_PREFIX').$this->_cacheKey);
    }

    public function getById($id) {
        $data = array();
        if ($id > 0) {
            $data = S($this->getCacheKey($id));
            if (!$data) {
                $data = $this->field('*')->find($id);
                if(!$data){
                    return array();
                }
                S($this->getCacheKey($id), $data, 60 * 60);
            }
        }
        $temp = explode(',',$data['pics']);
        $data['pics'] = empty($temp[0]) ? '' : $temp;
        $data['ext_info'] = json_decode($data['ext_info'],1);
        return $data;
    }

    public function getById_($id) {
        $data = array();
        if ($id > 0) {
            $data = $this->field('*')->where('id_a=' . $id)->find();
        }
        $temp = explode(',',$data['pics']);
        $data['pics'] = empty($temp[0]) ? '' : $temp;
        $data['ext_info'] = json_decode($data['ext_info'],1);
        return $data;
    }

    public function saveData($_data = '') {
        $data = $this->create($_data);
        if (!$data) {
            return false;
        }
        $data['ext_info'] = json_encode(I('ext'));
        if ($data['id']) {
            $rs = $this->save($data);
            if (!$rs) {
                return false;
            }
            S('moka_style',null);
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

    public function getList($fileds = '*'){
        $list = S('moka_style');
        if(!$list){
            $list = $this->field($fileds)->where('status=1')->order(array('sort'=>'asc'))->select();
        }
        S('moka_style',$list,3600);
        foreach($list as $key => $val){
            $temp = explode(',',$val['pics']);
            $temp = empty($temp[0]) ? '' : $temp;
            if($temp){
                foreach ($temp as $k => $v){
                    $temp[$k] = 'https://www.' . $_SERVER['SERVER_NAME'] . '/' . $v;
                }
            }
            $list[$key]['ext_info'] = json_decode($val['ext_info'],1);
            $list[$key]['pics'] = $temp;
        }
        return $list;
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