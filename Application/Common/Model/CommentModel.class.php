<?php

namespace Common\Model;

use Think\Model\AdvModel;

class CommentModel extends AdvModel {

    protected $partition = array(
        'field' => 'obj_id', // 要分表的字段 通常数据会根据某个字段的值按照规则进行分表
        'type' => 'mod', // 分表的规则 包括id year mod md5 函数 和首字母
            //'expr' => 'name',// 分表辅助表达式 可选 配合不同的分表规则
            //'num' => 'name',// 分表的数目 可选 实际分表的数量
    );
    protected $autoCheckFields = false;
    // 自动验证设置
    protected $_validate = array(
    );
    // 自动填充设置
    protected $_auto = array(
    );
    protected $_skey_main = 'comment_{id}'; // 主键

    // 评论类型

    const TYPE_CX = 1; // 小程序
    const TYPE_CX_ARTICLE = 2; // 小程序文章
    const TYPE_CX_EVALUATE = 3; // 小程序测评

    public function getCacheKey($id) {
        return str_replace('{id}', $id, C('DATA_CACHE_TWO_PREFIX').$this->_skey_main);
    }

    /**
     * 数据访问对象
     * @param unknown $data
     */
    public function getDao($data = array()) {
        $data = empty($data) ? $_POST : $data;
        $table = $this->getPartitionTableName($data);
        return $this->table($table);
    }

    /**
     * 获取主键自增ID值
     */
    public function getFileId() {
        return D('Common/Autoincrement')->getAutoincrementId('Comment');
    }

    /**
     * 获取单条评论内容
     * @param number $id
     * @param number $obj_id
     */
    public function getById($id, $obj_id = 0) {
        if ($id > 0) {
            $data = S($this->getCacheKey($id));
            if (!empty($data))
                return $data;

            $dao = D("ViewComment");
            if ($obj_id) {

                $dao = $this->getDao(array('obj_id' => $obj_id));
            }
            $data = $dao->find($id);
            S($this->getCacheKey($id), $data, 60 * 60);

            return $data;
        }
        return null;
    }

    /**
     * 添加评论
     * @param array $data
     * @return type
     */
    public function addComment($data) {
        if (!$data['obj_id']) {
            return false;
        }
        return $this->saveData($data);
    }

    public function saveData($data, $where) {
        if ($data['id']) {
            $where['id'] = $data['id'];
            unset($data['id']);
        }
        if ($where) {
            $res = $this->getDao(array('obj_id' => $data['obj_id']))->where($where)->save($data);
        } else {
            $data['id'] = $this->getFileId();
            $res = $this->getDao(array('obj_id' => $data['obj_id']))->add($data);
        }

        //清缓存
        $where['id'] && S($this->getCacheKey($where['id']), NULL);

        return $res;
    }

    /**
     * 回复累加
     * @param type $id
     * @param type $obj_id
     * @return boolean
     */
    public function setIncReply($id, $obj_id = 0) {
        if (!$obj_id) {
            return false;
        }
        $rs = $this->getDao(array('obj_id' => $obj_id))->where(array('id' => $id))->setInc('reply', 1);
        S($this->getCacheKey($id), NULL);
        return $rs;
    }

    public function getCount($obj_id, $type) {
        if (!$obj_id) {
            return 0;
        }
        $dao = $this->getDao(array('obj_id' => $obj_id));
        return $dao->where(array('obj_id' => $obj_id, 'type' => $type))->count();
    }

    public function getCountByMap($map) {
        if ($map['obj_id'] && $map['type']) {
            $dao = $this->getDao(array('obj_id' => $map['obj_id']));
        } else {
            $dao = D("ViewComment");
        }
        return $dao->where($map)->count();
    }

    public function getListRows($map, $return_total = TRUE) {
        !isset($map['field']) && $map['field'] = 'id,obj_id';
        $lists = $this->getList($map, TRUE);
        $data = array();
        if ($lists[1]) {
            foreach ($lists[0] as $v) {
                $data[] = $this->getById($v['id'], $v['obj_id']);
            }
        }
        if (!$data) {
            $data = NULL;
        }
        return $return_total ? array($data, $lists[1]) : $data;
    }

    public function delById($id) {
        $row = $this->getById($id);
        if (!$row) {
            return 0;
        }       
        $rs = $this->getDao(array('obj_id' => $row['obj_id']))->where(array('id'=>$id))->delete();
    
        S($this->getCacheKey($id), NULL);
        return $rs;
    }

    /**
     * 获取列表
     * @param type $param
     * @return type
     */
    public function getList($param, $return_total = FALSE) {
        if ($param['where']['obj_id']) {
            $dao = $this->getDao(array('obj_id' => $param['where']['obj_id']));
        } else {
            $dao = D("ViewComment");
        }
        !empty($param['where']) && $dao->where($param['where']);
        !empty($param['limit']) && $dao->limit($param['limit']);
        !empty($param['page']) && $dao->page($param['page']);
        !empty($param['order']) && $dao->order($param['order']);
        $dao->field(empty($param['field']) ? '*' : $param['field']);

        $list = $dao->select();

        //在设置一次不然取不到数据
        if ($param['where']['obj_id']) {
            $dao = $this->getDao(array('obj_id' => $param['where']['obj_id']));
        } else {
            $dao = D("ViewComment");
        }
        $total = $dao->where($param['where'])->count();
        return $return_total ? array($list, $total) : $list;
    }

}

?>