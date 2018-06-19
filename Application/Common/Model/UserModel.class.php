<?php

namespace Common\Model;

class UserModel {
    protected $connection = 'DB_UCENTER';

    private $member_fields = array('uid', 'nickname', 'sex', 'login', 'reg_ip', 'reg_time', 'last_login_ip', 'last_login_time', 'status');
    private $ucenter_member_fields = array('id', 'username', 'password', 'mobile', 'reg_time', 'reg_ip', 'last_login_time', 'last_login_ip', 'update_time', 'status');

    /**
     * 获取用户信息
     * @param string $fields 字段
     * @param int $uid   = follow_who 关注谁
     * @return array|mixed|null
     */
    function query_user($fields = null, $uid = 0) {
        $result = array();
        //默认赋值
        if ($fields === null) {
            $fields = array('uid', 'username', 'nickname', 'sex', 'thumb');
        }
        //如果fields不是数组，直接返回需要的值
        if (!is_array($fields)) {
            $result = query_user(array($fields), $uid);
            return $result[$fields];
        }


        //默认获取自己的资料
        $uid = (intval($uid) != 0 ? $uid : 0);
        if (!$uid) {
            return array();
        }


        //获取缓存过的字段
        list($cacheResult, $field, $fields) = $this->getCachedFields($fields, $uid);

        //获取各个表的字段值
        list($avatarFields, $homeResult, $ucenterResult) = $this->getSplittedFieldsValue($fields, $uid);

        // 头像
        if (in_array('thumb', $fields)) {
            if (is_url($homeResult['thumb'])) {
                $homeResult['thumb'] = thumb($homeResult['thumb'], 300);
            } elseif(!$homeResult['thumb']){
                $homeResult['thumb'] = C('BASE_URL').'static/common/images/default_avatar.jpg'; // 默认头像
            } else {
                $homeResult['thumb'] = C('BASE_URL').str_replace('//', '/', 'Uploads/' . $homeResult['thumb']);
            }
        }

        //TODO 在此加入扩展字段的处理钩子
        //↑↑↑ 新增字段应该写在在这行注释以上 ↑↑↑
        //合并结果，不包括缓存
//         $result = array_merge($ucenterResult, $homeResult, $spaceUrlResult, $result, $spaceMobUrlResult);
        $result = array_merge($ucenterResult, $homeResult, $result);
        //写缓存
        $result = $this->writeCache($uid, $result);
        //合并结果，包括缓存
        $result = array_merge($result, $cacheResult);




        return $result;
    }

    function read_query_user_cache($uid, $field) {
        return S("query_user_{$uid}_{$field}");
    }

    function write_query_user_cache($uid, $field, $value) {
        return S("query_user_{$uid}_{$field}", $value);
    }

    /**
     * 清理用户数据缓存，即时更新query_user返回结果。
     * @param $uid
     * @param $field
     * 
     */
    function clean_query_user_cache($uid, $field) {
        if (is_array($field)) {
            foreach ($field as $field_item) {
                S("query_user_{$uid}_{$field_item}", null);
            }
        } else {
            S("query_user_{$uid}_{$field}", null);
        }
    }

    /**
     * 查询缓存，过滤掉已缓存的字段
     * @param $fields
     * @param $uid
     * @return array
     */
    public function getCachedFields($fields, $uid) {
        //查询缓存，过滤掉已缓存的字段
        $cachedFields = array();
        $cacheResult = array();
        foreach ($fields as $field) {
            $cache = $this->read_query_user_cache($uid, $field);
            if ($cache !== false) {
                $cacheResult[$field] = $cache;
                $cachedFields[] = $field;
            }
        }
        //去除已经缓存的字段
        $fields = array_diff($fields, $cachedFields);
        return array($cacheResult, $field, $fields);
    }

    /**
     * @param $fields
     * @param $homeFields
     * @param $ucenterFields
     * @return array
     */
    public function getSplittedFields($fields, $homeFields, $ucenterFields) {
        $avatarFields = array('avatar', 'avatar32', 'avatar64', 'avatar128', 'avatar256', 'avatar512');
        $avatarFields = array_intersect($avatarFields, $fields);
        $homeFields = array_intersect($homeFields, $fields);
        $ucenterFields = array_intersect($ucenterFields, $fields);
        return array($avatarFields, $homeFields, $ucenterFields);
    }

    /**
     * @param $fields
     * @param $uid
     * @return array
     */
    public function getSplittedFieldsValue($fields, $uid) {
        //获取两张用户表格中的所有字段
        $homeFields = M('Member','','DB_UCENTER')->getDBFields();
        $ucenterFields = M('UcenterMember','','DB_UCENTER')->getDBFields();

        //分析每个表格分别要读取哪些字段
        list($avatarFields, $homeFields, $ucenterFields) = $this->getSplittedFields($fields, $homeFields, $ucenterFields);


        //查询需要的字段
        $homeResult = array();
        $ucenterResult = array();
        if ($homeFields) {
            $homeResult = M('Member','','DB_UCENTER')->where(array('uid' => $uid))->field($homeFields)->find();
        }
        if ($ucenterFields) {
            $model = M('UcenterMember','','DB_UCENTER');
            $ucenterResult = $model->where(array('id' => $uid))->field($ucenterFields)->find();
            return array($avatarFields, $homeResult, $ucenterResult);
        }
        return array($avatarFields, $homeResult, $ucenterResult);
    }

    /**
     * 读取用户名拼音
     * @param $fields
     * @param $result
     * @return mixed
     */
    public function getPinyin($fields, $result) {
        if (in_array('pinyin', $fields)) {

            $result['pinyin'] = D('Pinyin')->pinYin($result['nickname']);
            return $result;
        }
        return $result;
    }

    /**
     * 写入缓存
     * @param $uid
     * @param $result
     * @return mixed
     */
    public function writeCache($uid, $result) {
        foreach ($result as $field => $value) {
            if (!in_array($field, array('rank_link', 'space_link', 'expand_info'))) {
                $value = str_replace('"', '', text($value));
            }

            $result[$field] = $value;
            $this->write_query_user_cache($uid, $field, str_replace('"', '', $value));
        }
        return $result;
    }

}
