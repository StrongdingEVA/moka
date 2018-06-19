<?php

namespace Common\Model;

use Common\Model\CommonModel;

class SearchLogModel extends CommonModel
{

    // 自动验证设置
    protected $_validate = array(
            array('keyword', 'require', '关键词必填', 1),
    );
    
    // 自动填充设置
    protected $_auto = array(
            array('create_time', 'time', self::MODEL_INSERT, 'function'),
    );

    /**
     * 添加/修改数据
     * @param array $data
     * @return boolean|Ambigous <boolean, mixed, unknown>
     */
    public function saveData($data = array())
    {
        $data = $this->create($data);
        if(!$data){
            return false;
        }
        if ($data['id']) {
            $res = $this->where(array('id' => $data['id']))->save($data);
        } else {
            $res = $this->add($data);
        }
        return $res;
    }
    
    /**
     * 获取数据
     * @param array $map
     */
    public function getList($map){
        
    }

}