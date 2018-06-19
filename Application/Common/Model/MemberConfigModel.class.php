<?php
namespace Common\Model;

use Common\Model\CommonModel;

/**
 * 用户配置模型
 */
class MemberConfigModel extends CommonModel {

    protected $_validate = array(
        array('name', 'check_name_value', '键值不合法', self::MODEL_INSERT, 'function'),
        array('uid', 'require', '用户UID必填',self::MODEL_INSERT)
    );
    /* 用户模型自动完成 */
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME),
        array('value', 0, self::MODEL_INSERT),
    );
    private $_cacheKey = 'member_config_{uid}_{name}';

    public function getCacheKey($uid, $name) {
        $rs = str_replace('{uid}', $uid, $this->_cacheKey);
        return str_replace('{name}', $name, $rs);
    }

    public function getByUidAndName($uid, $name) {
        $data = S($this->getCacheKey($uid, $name));
        if (!$data) {
            $data = $this->field('*')->where(array('uid' => $uid, 'name' => $name))->find();
            S($this->getCacheKey($uid, $name), $data, 60 * 60);
        }

        return $data;
    }

    public function saveData($data, $where = array()) {
        if ($data['uid'] && $data['name']) {
            $where['uid'] = $data['uid'];
            $where['name'] = $data['name'];
        }
        $row = $this->where($where)->find();              
        if ($row) {
            unset($data['uid'], $data['name']);
            $res = $this->where($where)->data($data)->save();
        } else {
            $data = $this->create($data);
            if (!$data) {
                return NULL;
            }
            $res = $this->add($data);
        }     
        //清缓存
        ($where['uid'] & $where['name']) && S($this->getCacheKey($where['uid'], $where['name']), NULL);

        return $res;
    }

    public function getListRows($map, $return_total = TRUE) {
        !isset($map['field']) && $map['field'] = 'uid,name';
        $lists = $this->getList($map, TRUE);
        $data = array();
        if ($lists[1]) {
            foreach ($lists[0] as $v) {
                $data[] = $this->getByUidAndName($v['uid'], $v['name']);
            }
        }
        if (!$data) {
            $data = NULL;
        }
        return $return_total ? array($data, $lists[1]) : $data;
    }

    public function delByUidAndName($uid, $name) {
        $rs = $this->where(array('uid' => $uid, 'name' => $name))->delete();
        S($this->getCacheKey($uid, $name), NULL);
        return $rs;
    }

    /**
     * 检查键值是否合法
     * @param type $name
     */
    public function check_name_value($name) {
        $names = array('push_play_sound', 'no_picture_model', 'wp_show_headline');
        $is = in_array(trim(strtolower($name)), $names);
        if (!$is) {
            return false;
        }
        return true;
    }

}
