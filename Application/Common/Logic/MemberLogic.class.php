<?php

namespace Common\Logic;

/**
 * 
 */
class MemberLogic {

    /**
     * 普通登录
     * @return number
     */
    public function login($map) {
        $account = isset($map['account']) ? $map['account'] : '';
        $password = isset($map['password']) ? $map['password'] : '';

        if (!check_mobile($account)) {
            return array('status' => 0, 'info' => '请输入账号');
        }

        if (!$password) {
            return array('status' => 0, 'info' => '请输入密码');
        }

        $api = new \User\Api\UserApi();
        $uid = $api->login($account, $password, 3);
        if ($uid == 0) {
            return array('status' => 0, 'info' => '登录失败');
        } elseif ($uid == -1) {
            return array('status' => 0, 'info' => '用户不存在或被禁用');
        } elseif ($uid == -2) {
            return array('status' => 0, 'info' => '密码错误');
        }

        $data = query_user(array('uid', 'thumb', 'nickname', 'mobile', 'username', 'sex'), $uid);
        //获取用户open_id和token
        $openId = $this->getOpenid($uid);
        $data['access_token'] = $openId['data']['access_token'];
        $data['open_id'] = $openId['data']['open_id'];
        return array('status' => 1, 'data' => $data);
        E();
    }

    /**
     * 获取开放OPENID
     * @param type $uid
     * @return type
     */
    public function getOpenid($uid) {
        $user = query_user(array('uid'), $uid);
        if (!$user) {
            return array('status' => 0, 'info' => '用户不存在', 'data' => (object) array());
        }
        $is_row = D('Common/MemberOpenid')->where(array('uid' => $uid))->count();
        if ($is_row > 0) {
            $res = D('Common/MemberOpenid')->updateToken($uid);
//            if (!$res) {
//                return array('status' => 0, 'info' => '获取失败请重试', 'data' => (object) array());
//            }
        }

        $data = D('Common/MemberOpenid')->getOpenid($uid);
        if (!$data) {
            return array('status' => 0, 'info' => '获取失败请重试');
        }
        return array('status' => 1, 'info' => '', 'data' => $data);
    }

    /**
     * 手机号校验
     * author lcc
     * type (register注册 findpassword 忘记密码 bind 绑定 weixin_register 微信注册 forget_paypwd 忘记交易密码)
     */
    public function check_mobile($mobile, $type) {
        if (!check_mobile($mobile)) {
            return array('status' => 0, 'info' => '手机格式不正确', 'data' => (object) array());
        }

        switch ($type) {
            case 'register':
                if (D('User/UcenterMember')->getUidByMobile($mobile)) {
                    return array('status' => 0, 'info' => '手机号已被注册', 'data' => (object) array());
                }
                break;
            case 'weixin_register':
                $memberid = D('User/UcenterMember')->getUidByMobile($mobile);
                if ($memberid) {
                    $weixin_info = D('SyncLogin')->field('uid')->where(array('uid' => $memberid, 'type' => 'weixin'))->find();
                    if ($weixin_info) {
                        return array('status' => 0, 'info' => '手机号已注册', 'data' => (object) array());
                    }
                }
                break;
            case 'findpassword':
                if (!D('User/UcenterMember')->getUidByMobile($mobile)) {
                    return array('status' => 0, 'info' => '手机号未注册', 'data' => (object) array());
                }
                break;
            case 'bind':
                if (D('User/UcenterMember')->getUidByMobile($mobile)) {
                    return array('status' => 0, 'info' => '手机号已被注册', 'data' => (object) array());
                }
                break;
            default:
                return array('status' => 0, 'info' => '非法请求', 'data' => (object) array());
        }

        return array('status' => 1, 'info' => '校验成功', 'data' => (object) array());
    }

    /**
     * 重置密码
     * @param array $param 数组参数
     * @param bool  $is_verify 是否有短信验证码验证
     */
    public function reset_pwd($param, $is_verify = TRUE) {
        $user = query_user(array('uid', 'mobile', 'password'), $param['uid']);
        if (!isset($user['uid']) || !$user['uid']) {
            return array('status' => 0, 'info' => '用户错误');
        }
        if (!isset($param['password']) || empty($param['password'])) {
            return array('status' => 0, 'info' => '请输入密码');
        }
        if (strlen($param['password']) < 6) {
            return array('status' => 0, 'info' => '密码不能少于六位');
        }
        if ($is_verify) {
            if (!isset($param['verify']) || !D('Common/Verify')->checkVerify($user['mobile'], 'findpassword', $param['verify'])) {
                return array('status' => 0, 'info' => '验证码过期或错误');
            }
        }
        $api = new \User\Api\UserApi();
        $rs = $api->resetPwd($param['uid'], $param['password']);
        if ($rs === TRUE) {
            $is_verify && D('Common/Verify')->delVerify($user['mobile']);
            clean_query_user_cache($param['uid'], 'password');
            return array('status' => 1, 'info' => '修改成功');
        }
        return array('status' => 0, 'info' => $rs);
    }

    /**
     * 修改个人基本资料
     */
    public function edit_base_info($uid, $param) {
        $data = array();
        (isset($param['nickname']) && trim($param['nickname']) != '') && $data['nickname'] = $param['nickname'];
        (isset($param['sex']) && $param['sex']) && $data['sex'] = $param['sex'];
        (isset($param['thumb']) && trim($param['thumb']) != '') && $data['thumb'] = $param['thumb'];

        if (isset($data['nickname']) && trim($data['nickname']) == '') {
            return array('status' => 0, 'info' => '昵称为空');
        }

        if (isset($data['sex']) && empty($data['sex'])) {
            return array('status' => 0, 'info' => '请设置性别');
        }

        if (isset($data['thumb']) && trim($data['thumb']) == '') {
            return array('status' => 0, 'info' => '请上传头像');
        }


        if (!$data) {
            return array('status' => 0, 'info' => '未做任何更改');
        }
        $rs = D('Common/Member')->saveData($data, array('uid' => $uid));
        if (!$rs) {
            return array('status' => 0, 'info' => '更新失败');
        }
        clean_query_user_cache($uid, array('nickname', 'sex', 'thumb'));
        return array('status' => 1, 'info' => '更新成功');
    }

    /**
     * 普通注册
     * @param array $param
     * @param type $is_verify 是否有短信验证码验证
     * @return type
     */
    public function register($param, $is_verify = TRUE) {
        if (!check_mobile($param['mobile'])) {
            return array('status' => 0, 'info' => '手机号格式错误');
        }
        if (D('User/UcenterMember')->getUidByMobile($param['mobile'])) {
            return array('status' => 0, 'info' => '手机号已注册');
        }
        if (!isset($param['password']) || empty($param['password'])) {
            return array('status' => 0, 'info' => '请输入密码');
        }
        if (strlen($param['password']) < 6) {
            return array('status' => 0, 'info' => '密码不能少于六位');
        }
        if (!isset($param['nickname']) || empty($param['nickname'])) {
            return array('status' => 0, 'info' => '请输入昵称');
        }
        if (strlen($param['nickname']) > 16) {
            return array('status' => 0, 'info' => '昵称不能大于8个字');
        }

        if ($is_verify) {
            if (!isset($param['verify']) || !D('Common/Verify')->checkVerify($param['mobile'], 'register', $param['verify'])) {
                return array('status' => 0, 'info' => '验证码过期或错误');
            }
        }

        $param['username'] = '9cn_' . date('YmdHis') . rand('100', '999');
        $api = new \User\Api\UserApi();
        $uid = $api->register($param['username'], $param['password'], '', $param['mobile']);
        if (is_numeric($uid) && $uid > 0) {
            $is_verify && D('Common/Verify')->delVerify($param['mobile']);
            $user = D('Common/Member')->create(array('nickname' => $param['nickname'], 'status' => 1));
            $user['uid'] = $uid;
            if (!D('Common/Member')->add($user)) {
                return array('status' => 0, 'info' => '用户初始化失败', 'error' => D('Common/Member')->getError() . D('Common/Member')->getDbError());
            }
        } elseif (is_numeric($uid) && $uid == 0) {
            return array('status' => 0, 'info' => '注册失败');
        } else {
            return array('status' => 0, 'info' => $uid);
        }
        return array('status' => 1, 'data' => $uid);
    }

    /**
     * 获取消息读取时间
     */
    public function get_message_info($uid) {
        if (empty($uid)) {
            return array('status' => 0, 'info' => '用户编号错误');
        }
        $readtime = D('CommentReadtime')->where(array('uid' => $uid))->find();
        $last_time = $readtime ? $readtime['last_time'] : 0;
        $count = D('Common/Comment')->getCountByMap(array('status' => 1, 'create_time' => array('egt', $last_time), 'touid' => $uid));          // 获取用户未读信息
        $data = array();
        $data['last_time'] = $last_time;
        $data['count'] = $count;
        return array('status' => 1, 'data' => $data);
    }

    /**
     * 消息中心
     */
    public function get_message_list($param) {
        !isset($param['page']) && $param['page'] = 1;
        !isset($param['page_size']) && $param['page_size'] = 20;
        !isset($param['last_time']) && $param['last_time'] = 0;

        $model = D('Common/Comment');

        $where['status'] = 1;
        $where['touid'] = $param['touid'];
        $where['pid'] = array('gt', 0);
        $order = 'id desc';

        $last_time = $param['last_time'];
        if ($param['last_time'] == 1) {
            $rs = D('CommentReadtime')->where(array('uid' => $param['touid']))->find();
            $last_time = $rs ? $rs['last_time'] : 0;
        }

        if ($param['page'] == 1) {
            $readtime = array();
            $readtime['uid'] = $where['touid'];
            $readtime['last_time'] = time();
            D('CommentReadtime')->add($readtime, array(), true);
        }

        $data = array();
        $data['last_time'] = $last_time;
        // 获取查询列表
        list($data['rows'], $data['total']) = $model->getListRows(array( 'where' => $where, 'page' => $param['page'], 'limit' => $param['page_size'], 'order' => $order ), TRUE);
        foreach ($data['rows'] as $k => &$v) {
            if (!$v) {
                unset($v);
                continue;
            }
            $v['user'] = query_user(array('nickname', 'thumb', 'uid'), $v['uid']);
            $v['read'] = ($v['create_time'] > $last_time) ? 0 : 1;  // 0未读 1已读
            if ($v['pid']) {
                $ref = $model->getById($v['pid'], $v['obj_id']);
                $refuser = query_user(array('nickname', 'thumb', 'uid'), $ref['uid']);
                $v['reply_nickname'] = $refuser['nickname'];
                $v['reply_thumb'] = $refuser['thumb'];
                $v['reply_content'] = $ref['content'];
                $v['reply_uid'] = $refuser['uid'];
            }
            unset($v['pid'], $v['ip'], $v['uid'], $v['status'], $v['reply']);
        }
        unset($v);

        return array('status' => 1, 'data' => $data);
    }

    /**
     * 同步第三方注册
     */
    public function sync_oauth_register($param) {
        if (!isset($param['open_id']) || empty($param['open_id'])) {
            return array('status' => 0, 'info' => 'open_id错误');
        }

//        if (!isset($param['nickname']) || empty($param['nickname'])) {
//            return array('status' => 0, 'info' => '昵称错误');
//        }
//        if (!isset($param['sex']) || empty($param['sex'])) {
//            $param['sex'] = 1;
//        }

//        if (!isset($param['headimg']) || empty($param['headimg'])) {
//            // return array('status' => 0, 'info' => '头像错误');
//            $param['headimg'] = 'http://img.xmyunyou.com/user-pic-default.png';
//        }

        if (!isset($param['type']) || empty($param['type']) || !in_array(strtolower($param['type']), array('weixin', 'qq', 'sina', 'wxxcx'))) {
            return array('status' => 0, 'info' => '登录类型错误');
        }

        if (!isset($param['access_token'])) {
            $param['access_token'] = '';
        }

        $member = array('sex' => $param['sex'], 'thumb' => $param['headimg'], 'nickname' => $param['nickname']);

        // 第三方登录信息
        $sync = D('Common/SyncLogin')->where(array('type_uid' => $param['open_id'], 'type' => $param['type']))->find();
        
        // 更新第三方信息
        if($sync){
            $upparam = array();
            ($param['nickname'] && $sync['nick'] != $param['nickname']) && $upparam['nick'] = $param['nickname'];
            ($param['sex'] && $sync['sex'] != $param['sex']) && $upparam['sex'] = $param['sex'];
            ($param['headimg'] && $sync['avatar'] != $param['headimg']) && $upparam['avatar'] = $param['headimg'];
            (!$sync['unionid'] && $param['unionid']) && $upparam['unionid'] = $param['unionid'];
            
            // 同个微信号存在的情况下，检查小程序账号和微信账号是否同步，没有则同步小程序的uid为微信的uid
            if($param['type'] == 'wxxcx' && $param['unionid']){
                $weixin = D('Common/SyncLogin')->where(array('type' => 'weixin', 'unionid' => $param['unionid']))->find();
                if($weixin && $sync['uid'] != $weixin['uid']){
                    $upparam['uid'] = $weixin['uid'];
                }
            }
            
            if($upparam){
                $upparam['id'] = $sync['id'];
                if(D('Common/SyncLogin')->saveData($upparam) && $upparam['uid']){
                    $sync['uid'] = $upparam['uid'];
                }
            }
        }
        // 注册第三方登录
        if (!$sync) {
            $syncdata = array();
            $syncdata['type_uid'] = $param['open_id'];
            $syncdata['type'] = $param['type'];
            $syncdata['oauth_token_secret'] = $param['open_id'];
            $syncdata['oauth_token'] = $param['access_token'];
            $syncdata['unionid'] = $param['unionid'];
            $syncdata['nick'] = $param['nickname'];
            $syncdata['sex'] = $param['sex'];
            $syncdata['avatar'] = $param['headimg'];
            $param['uid'] && $syncdata['uid'] = $param['uid'];
            
            // 如果存在unionid记录，则uid取值存在的unionid对应的uid
            if(!$syncdata['uid'] && $param['unionid']){
                $has_unionid = D('Common/SyncLogin')->where(array('unionid' => $param['unionid']))->find();
                if($has_unionid['uid']){
                    $syncdata['uid'] = $has_unionid['uid'];
                }
            }
            
            $rs = D('Common/SyncLogin')->saveData($syncdata);
            if (!$rs) {
                return array('status' => 0, 'info' => '同步注册失败', 'error' => D('Common/SyncLogin')->getError() . D('Common/SyncLogin')->getDbError());
            }
            $sync = D('Common/SyncLogin')->where(array('id' => $rs))->find();
        }

        $uc = D('User/UcenterMember')->where(array('id' => (!$sync['uid'] ? '-1' : $sync['uid'])))->find();
        //如果不存在用户则注册一个新用户
        if (!$uc) {
            $rs = $this->init_register($member);
            if (!$rs['status']) {
                return $rs;
            }
            D('Common/SyncLogin')->saveData(array('uid' => $rs['data'], 'is_sync' => 1), array('id' => $sync['id']));
            $uc['id'] = $rs['data'];
        }
        // 执行登录
        $login_rs = D('Common/Member')->login($uc['id']);
        if (!$login_rs) {
            return array('status' => 0, 'info' => '同步登录失败',);
        }

        return array('status' => 1, 'data' => $uc['id']);
    }

    /**
     * 收藏列表
     * @param type $map
     * @return type
     */
    public function site_fav_list($map) {
        !isset($map['page']) && $map['page'] = 1;
        !isset($map['limit']) && $map['limit'] = 20;
        !isset($map['order']) && $map['order'] = 'id desc';
        list($list, $total) = D('Common/SiteFavorites')->getListRows($map);
        foreach ($list as &$v) {
            $cat = D('Common/SiteFavCategory')->getById($v['cid']);
            $v['cat_name'] = $cat ? $cat['title'] : '默认';
        }
        return array('status' => 1, 'data' => array('rows' => $list, 'total' => $total));
    }

    /**
     * 添加网站收藏
     */
    public function add_site_fav($param) {
        if (!isset($param['uid']) || empty($param['uid'])) {
            return array('status' => 0, 'info' => '请登录');
        }
        if (!isset($param['url']) || empty($param['url']) || !is_url($param['url'])) {
            return array('status' => 0, 'info' => 'url参数错误');
        }
        $param['url'] = trim($param['url']);
        $arr = parse_url($param['url']);
        $param['domain'] = $arr['host'];

        $row = D('Common/SiteFavorites')->where(array('uid' => $param['uid'], 'url' => $param['url']))->find();
        if ($row) {
            return array('status' => 0, 'info' => '该页面已收藏');
        }

        $rs = D('Common/SiteFavorites')->saveData($param);
        if ($rs) {
            D('Common/SiteFavCategory')->saveData(array('id' => $param['cid'], 'update_time' => time()));
            return array('status' => 1, 'info' => '收藏成功', 'data' => $rs);
        }
        return array('status' => 0, 'info' => '收藏失败', 'error' => D('Common/SiteFavorites')->getError() . D('Common/SiteFavorites')->getDbError());
    }

    /**
     * 删除收藏
     * @param type $uid
     * @param type $id
     * @return type
     */
    public function del_site_fav($uid, $id) {
        if (empty($uid)) {
            return array('status' => 0, 'info' => '用户错误');
        }
        if (empty($id)) {
            return array('status' => 0, 'info' => 'ID参数错误');
        }
        $rs = D('Common/SiteFavorites')->where(array('uid' => $uid, 'id' => $id))->delete();
        if ($rs) {
            return array('status' => 1, 'info' => '取消成功');
        }
        return array('status' => 0, 'info' => '取消失败');
    }

    /**
     * 收藏夹分类表
     * @param type $map
     * @return type
     */
    public function site_fav_cat_list($map, $show_count = FALSE) {
        !isset($map['page']) && $map['page'] = 1;
        !isset($map['limit']) && $map['limit'] = 100;
        !isset($map['order']) && $map['order'] = 'update_time desc';
        $uid = isset($map['where']['uid']) ? $map['where']['uid'] : 0;

        list($list, $total) = D('Common/SiteFavCategory')->getListRows($map);
        if ($map['page'] == 1 && $total == 0 && $uid > 0) {
            $user = query_user(array('nickname'), $uid);
            $data = array('uid' => $uid, 'update_time' => time(), 'title' => $user['nickname'] . '的收藏夹');
            $id = D('Common/SiteFavCategory')->saveData($data);
            $favs = D('Common/SiteFavorites')->where(array('uid' => $uid))->field('id')->select();
            foreach ($favs as $v) {
                S(D('Common/SiteFavorites')->getCacheKey($v['id']), NULL);
            }
            $id && D('Common/SiteFavorites')->saveData(array('cid' => $id), array('uid' => $uid));
        }

        list($list, $total) = D('Common/SiteFavCategory')->getListRows($map);
        if ($show_count) {
            foreach ($list as &$v) {
                $v['count'] = D('Common/SiteFavorites')->where(array('cid' => $v['id']))->count();
            }
        }
        return array('status' => 1, 'data' => array('rows' => $list, 'total' => $total));
    }

    /**
     * 添加编辑收藏夹
     */
    public function add_site_fav_cat($param) {
        if (!isset($param['uid']) || empty($param['uid'])) {
            return array('status' => 0, 'info' => '请登录');
        }
        if (!isset($param['title']) || empty($param['title'])) {
            return array('status' => 0, 'info' => 'title参数错误');
        }

        $row = D('Common/SiteFavCategory')->where(array('uid' => $param['uid'], 'title' => trim($param['title'])))->find();
        if ($row) {
            return array('status' => 0, 'info' => '收藏夹已存在');
        }

        $rs = D('Common/SiteFavCategory')->saveData($param);
        if ($rs) {
            return array('status' => 1, 'info' => '创建成功', 'data' => $rs);
        }
        return array('status' => 0, 'info' => '创建失败', 'error' => D('Common/SiteFavCategory')->getError() . D('Common/SiteFavCategory')->getDbError());
    }

    /**
     * 编辑收藏夹
     * @param type $param
     * @return type
     */
    public function edit_site_fav_cat($param) {
        if (!isset($param['uid']) || empty($param['uid'])) {
            return array('status' => 0, 'info' => '请登录');
        }
        if (!isset($param['id']) || empty($param['id'])) {
            return array('status' => 0, 'info' => 'id参数错误');
        }
        if (isset($param['title']) && empty($param['title'])) {
            return array('status' => 0, 'info' => 'title参数错误');
        }

        $row = D('Common/SiteFavCategory')->where(array('id' => array('NEQ', $param['id']), 'uid' => $param['uid'], 'title' => trim($param['title'])))->find();
        if ($row) {
            return array('status' => 0, 'info' => '收藏夹重名');
        }

        $rs = D('Common/SiteFavCategory')->saveData($param);
        if ($rs) {
            return array('status' => 1, 'info' => '操作成功', 'data' => $rs);
        }
        return array('status' => 0, 'info' => '操作失败', 'error' => D('Common/SiteFavCategory')->getError() . D('Common/SiteFavCategory')->getDbError());
    }

    /**
     * 删除收藏
     * @param type $uid
     * @param type $id
     * @return type
     */
    public function del_site_fav_cat($uid, $id, $del_all_fav = FALSE) {
        if (empty($uid)) {
            return array('status' => 0, 'info' => '用户错误');
        }
        if (empty($id)) {
            return array('status' => 0, 'info' => 'ID参数错误');
        }
        if (!$del_all_fav) {
            $count = D('Common/SiteFavorites')->where(array('cid' => $id))->count();
            if ($count) {
                return array('status' => 0, 'info' => '错误!收藏夹下存在收藏项目');
            }
        } else {
            $rows = D('Common/SiteFavorites')->where(array('cid' => $id, 'uid' => $uid))->field('id')->select();
            foreach ($rows as $v) {
                D('Common/SiteFavorites')->delById($v['id']);
            }
        }
        $rs = D('Common/SiteFavCategory')->where(array('uid' => $uid, 'id' => $id))->delete();
        if ($rs) {
            return array('status' => 1, 'info' => '删除成功');
        }
        return array('status' => 0, 'info' => '删除失败');
    }

    /**
     * 取得收藏ID
     * @param type $uid
     * @param type $url
     * @return type
     */
    public function get_site_fav_id($uid, $url) {
        if (empty($uid)) {
            return array('status' => 0, 'info' => '用户错误', 'data' => '0');
        }
        if (empty($url)) {
            return array('status' => 0, 'info' => 'URL参数错误', 'data' => '0');
        }
        $rs = D('Common/SiteFavorites')->where(array('uid' => $uid, 'url' => trim($url)))->find();
        return array('status' => 1, 'data' => ((isset($rs['id']) & $rs['id'] > 0) ? $rs['id'] : '0'));
    }

    /**
     * 初始化一个新注册用户
     */
    private function init_register($member_field = array()) {
        $username = '9cn_' . date('YmdHis') . rand('100', '999');
        $password = substr(md5(time()), 2, 6);

        $member = array();
        $member['sex'] = isset($member_field['sex']) ? $member_field['sex'] : 1;
        $member['thumb'] = isset($member_field['thumb']) ? $member_field['thumb'] : '';
        $member['nickname'] = isset($member_field['nickname']) ? $member_field['nickname'] : $username;

        $api = new \User\Api\UserApi();
        $uid = $api->register($username, $password);
        if (is_numeric($uid) && $uid > 0) {
            $member['uid'] = $uid;
            $member['status'] = 1;
            $member['reg_time'] = time();
            $rs = D('Common/Member')->add($member);
            if (!$rs) {
                D('User/UcenterMember')->where(array('id' => $uid))->delete();
                return array('status' => 0, 'info' => '用户初始化失败', 'error' => D('Common/Member')->getError() . D('Common/Member')->getDbError());
            }
        } elseif (is_numeric($uid) && $uid == 0) {
            return array('status' => 0, 'info' => '注册失败' . $api->getError());
        }
        return array('status' => 1, 'data' => $uid);
    }

    /**
     * 获取用户配置
     */
    public function get_member_config($uid) {
        $rs = D('Common/MemberConfig')->getListRows(array('where' => array('uid' => $uid)), FALSE);
        $data = array();
        foreach ($rs as $v) {
            $data[$v['name']] = $v['value'];
        }
        return array('status' => 1, 'data' => $data);
    }

    /**
     * 修改用户配置
     * @param type $uid 用户id
     * @param type $name 唯一键值
     * @param type $param 
     * @return type
     */
    public function set_member_config($uid, $name, $param) {
        !isset($param['value']) && $param['value'] = 0;
        $param['value'] = $param['value'] ? 1 : 0;
        $param['uid'] = $uid;
        $param['name'] = trim(strtolower($name));
        $param['update_time'] = time();
        if ($uid < 1) {
            return array('status' => 0, 'info' => 'UID不合法');
        }

        if (!D('Common/MemberConfig')->check_name_value($name)) {
            return array('status' => 0, 'info' => 'name参数错误');
        }
        $rs = D('Common/MemberConfig')->saveData($param);
        if (!$rs) {
            return array('status' => 0, 'info' => D('Common/MemberConfig')->getError(), 'error' => D('Common/MemberConfig')->getError() . D('Common/MemberConfig')->getDbError());
        }
        return array('status' => 1, 'data' => $rs);
    }

    /**
     * 批量编辑收藏
     * @param type $param
     * @return type
     */
    public function batch_eidt_site_fav($param) {
        if (!isset($param['uid']) || empty($param['uid'])) {
            return array('status' => 0, 'info' => '请登录');
        }
        if (!isset($param['id']) || empty($param['id'])) {
            return array('status' => 0, 'info' => '数据错误');
        }
        if (isset($param['url']) && ( empty($param['url']) || !is_url($param['url']))) {
            return array('status' => 0, 'info' => 'url参数错误');
        }
        isset($param['url']) && $param['url'] = trim($param['url']);
        isset($param['url']) && $arr = parse_url($param['url']);
        isset($param['url']) && $param['domain'] = $arr['host'];


        $rs = D('Common/SiteFavorites')->saveData($param);
        if ($rs) {
            return array('status' => 1, 'info' => '收藏成功', 'data' => $rs);
        }
        return array('status' => 0, 'info' => '收藏失败', 'error' => D('Common/SiteFavorites')->getError() . D('Common/SiteFavorites')->getDbError());
    }

}
