<?php

namespace Common\Model;

use Think\Model;

class CommonModel extends Model {

    /**
      +----------------------------------------------------------
     * 根据条件禁用表数据
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @param array $options 条件
      +----------------------------------------------------------
     * @return boolen
      +----------------------------------------------------------
     */
    public function forbid($options, $field = 'status') {

        if (FALSE === $this->where($options)->setField($field, 0)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    /**
      +----------------------------------------------------------
     * 根据条件批准表数据
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @param array $options 条件
      +----------------------------------------------------------
     * @return boolen
      +----------------------------------------------------------
     */
    public function checkPass($options, $field = 'status') {
        if (FALSE === $this->where($options)->setField($field, 1)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    /**
      +----------------------------------------------------------
     * 根据条件恢复表数据
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @param array $options 条件
      +----------------------------------------------------------
     * @return boolen
      +----------------------------------------------------------
     */
    public function resume($options, $field = 'status') {
        if (FALSE === $this->where($options)->setField($field, 1)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    /**
      +----------------------------------------------------------
     * 根据条件恢复表数据
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @param array $options 条件
      +----------------------------------------------------------
     * @return boolen
      +----------------------------------------------------------
     */
    public function recycle($options, $field = 'status') {
        if (FALSE === $this->where($options)->setField($field, 0)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    public function recommend($options, $field = 'is_recommend') {
        if (FALSE === $this->where($options)->setField($field, 1)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    public function unrecommend($options, $field = 'is_recommend') {
        if (FALSE === $this->where($options)->setField($field, 0)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    /**
      +----------------------------------------------------------
     * 根据条件配置表数据
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @param array $options 条件
     *         string $field 字段名
     *         int $value 值
      +----------------------------------------------------------
     * @return boolen
      +----------------------------------------------------------
     */
    public function change($options, $field = 'status', $value = 0) {

        if (FALSE === $this->where($options)->setField($field, $value)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    public function getList($param, $return_total = FALSE) {
        !empty($param['field']) && $this->field($param['field']);
        !empty($param['where']) && $this->where($param['where']);
        !empty($param['limit']) && $this->limit($param['limit']);
        !empty($param['order']) && $this->order($param['order']);
        isset($param['distinct']) && $this->distinct($param['order']);
        !empty($param['page']) && $this->page($param['page'], empty($param['limit']) ? 10 : $param['limit']);
       
        $list = $this->select(); 
        $total = $this->where($param['where'])->count();       
        return $return_total ? array($list, $total) : $list;
    }


}

?>