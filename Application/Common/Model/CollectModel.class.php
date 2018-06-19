<?php

namespace Common\Model;

class CollectModel extends CommonModel
{

    // 自动验证设置
    protected $_validate = array(

    );
    // 自动填充设置
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
    );
    
    private $_cacheKey = 'moka_collect_{uid}';

    public function getCacheKey($uid) {
        return str_replace('{uid}', $uid, $this->_cacheKey);
    }


    public function getByuId($uid) {
        $data = array();
        if ($uid > 0) {
            $data = S($this->getCacheKey($uid));
            if (!$data) {
                $data = $this->field('*')->where(array('uid' => $uid))->find();
                if($data){
                    $data['collects'] = $data['collects'] ? json_decode($data['collects'],1) : array();
                    S($this->getCacheKey($uid), $data, 60 * 60);
                }
            }
        }
        return $data;
    }

    public function saveData($_data = '') {
        $data = $this->create($_data);
        if (!$data || !$data['uid']) {
            return false;
        }
        $result = $this->getByuId($data['uid']);
        if(!$result){
            $res = $this->add($data);
        }else{
            $where['uid'] = $data['uid'];
            unset($data['uid']);
            $res = $this->where($where)->save($data);
            S($this->getCacheKey($where['uid']), NULL);
        }
        return $res;
    }
}

?>