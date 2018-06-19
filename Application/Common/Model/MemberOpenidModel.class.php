<?php

namespace Common\Model;

use Think\Model;

class MemberOpenidModel extends Model {
    protected $connection = 'DB_UCENTER';
    /*
     * @param $uid 用户id或openid
     * */

    public function getCacheKey($uid) {
        $str = str_replace('{uid}', $uid, 'member_openid_{uid}');
        return $str;
    }

    public function getRowByOpenid($openid) {
        $res = S($this->getCacheKey($openid));
        if (empty($res)) {
            $res = $this->where(array('open_id' => $openid))->find();
            if (empty($res)) {
                return NULL;
            }
            S($this->getCacheKey($openid), $res);
            S($this->getCacheKey($res['uid']), $res);
        }
        return $res;
    }

    /**
     * 获取用户access_token和open_id
     * @param type $uid uid 
     * @return type
     */
    public function getOpenid($uid) {
        $res = S($this->getCacheKey($uid));
        if (empty($res)) {
            $res = $this->where(array('uid' => $uid))->find();
            if (!$res) {
                $res = array();
                $res['uid'] = $uid;
                $res['access_token'] = build_auth_key();
                $res['open_id'] = build_user_open_id();
                $res['update_time'] = time();
                $res['create_time'] = time();
                $res['update_time'] = time();
                $rs = $this->saveData($res); 
                if (!$rs) {
                    return NULL;
                }
            }
            S($this->getCacheKey($res['uid']), $res);
            S($this->getCacheKey($res['open_id']), $res);
        }
        return $res;
    }

    /*
     * 判断access_token是否过期
     * */

    public function checkAccessToken($time) {
        if (time() - $time >= 3600 * 24) {
            return false;
        }
        return true;
    }

    public function saveData($data = array()) {
        $data = $this->create($data);
        if (!$data) {
            return false;
        }
        $row = $this->where(array('uid' => $data['uid']))->find();
        if ($row) {
            //更新之前先清除OPENID缓存
            $openid = isset($data['open_id']) ? $data['open_id'] : $row['open_id'];
            $openid && S($this->getCacheKey($openid), null);

            $res = $this->save($data) ? $data['uid'] : 0;
          
            S($this->getCacheKey($data['uid']), null);
        } else {
            $data = $this->create($data);
            if (!$data) {
                return NULL;
            }
            $res = $this->add($data);
        }        
        return $res;
    }

    /*
     * 生成open_id
     * */

    public function addOpenid($data) {
        $rs = $this->saveData($data);
        if (!$rs) {
            return array('status' => 1, 'info' => '添加成功');
        }
        return array('status' => 0, 'info' => $this->getError());
    }

    /*
     * 更新token
     * @param $uid 用户id
     * */

    public function updateToken($uid) {
        $data = $this->where('uid=' . $uid)->find();
        if (!$data) {
            return false;
        }
        $data['access_token'] = build_auth_key();
        $data['update_time'] = time();
        $rs = $this->saveData($data);
        if (!$rs) {
            return array('status' => 0, 'info' => $this->getError());
        }
        return array('status' => 1, 'info' => '操作成功');
    }

}
