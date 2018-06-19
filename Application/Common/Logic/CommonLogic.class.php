<?php

namespace Common\Logic;

/**
 * 通用逻辑
 */
class CommonLogic {

    /**
     * 搜索
     * @param type $param
     * @return type
     */
    public function search($param) {
        $arr = array('total' => 0, 'rows' => array());
        $check_rs = $this->search_ban_check($param['keyword']);
        if($check_rs){
            return array('status' => 1, 'data' => $arr);
        }
        
        if (in_array($param['where']['type'], array(0, 1, 2, 3))) {
            $sphinxModel = D('Sphinx');
            $res = $sphinxModel->search('search', $param['keyword'], $param);
            $arr['total'] = $res['total'];
            foreach ($res['list'] as $v) {
                if ($v['attrs']['type'] == 1) {
                    $row = LC('Common/Cx')->get_cx_info_by_id($v['attrs']['id'])['data'];
                    $row && $arr['rows'][] = array('type' => $v['attrs']['type'], 'id' => $row['id'], 'title' => $row['title'], 'icon' => $row['icon'], 'hits' => $row['hits'], 'static_cloud' => $row['static_cloud'], 'score' => $row['score']['score'], 'cat_name' => $row['cat_name']);
                }
                if ($v['attrs']['type'] == 2) {
                    $row = LC('Common/Lapp')->get_lapp_info_by_id($v['attrs']['id'])['data'];
                    $row && $arr['rows'][] = array('type' => $v['attrs']['type'], 'id' => $row['id'], 'title' => $row['title'], 'icon' => $row['icon'], 'hits' => $row['hits'], 'static_cloud' => $row['static_cloud'], 'score' => $row['score']['score'], 'cat_name' => $row['cat_name']);
                }
                if ($v['attrs']['type'] == 3) {
                    $row = LC('Common/Wp')->get_account($v['attrs']['id'])['data'];
                    $row && $arr['rows'][] = array('type' => $v['attrs']['type'], 'id' => $row['id'], 'title' => $row['title'], 'icon' => $row['icon'], 'hits' => $row['static_cloud'], 'static_cloud' => $row['static_cloud'], 'cat_name' => $row['cat_name']);
                }
            }
        } else if ($param['where']['type'] == 4) {
            $param['where']['title'] = array('like', "%" . $param['keyword'] . "%");
            unset($param['keyword'], $param['where']['type']);
            $param['where']['status'] = 1;
            $res = LC('Cx')->get_evaluate_list($param);
            $arr['total'] = $res['data']['total'];
            foreach ($res['data']['rows'] as $v) {
                $arr['rows'][] = array('type' => 4, 'id' => $v['id'], 'title' => $v['title'], 'thumb' => $v['thumb'], 'hits' => $v['hits'], 'score' => $v['score'], 'content' => $v['content'], 'time' => friendlyDate($v['create_time']));
            }
        } else if ($param['where']['type'] == 5) {
            $param['where']['title'] = array('like', "%" . $param['keyword'] . "%");
            unset($param['keyword'], $param['where']['type']);
            $param['where']['status'] = 1;
            $res = LC('Article')->get_list($param);
            $arr['total'] = $res['data']['total'];
            foreach ($res['data']['rows'] as $v) {
                $arr['rows'][] = array('type' => 5, 'id' => $v['id'], 'title' => $v['title'], 'thumb' => $v['thumb'], 'hits' => $v['hits'], 'content' => $v['content'], 'time' => friendlyDate($v['create_time']));
            }
        }


        return array('status' => 1, 'data' => $arr);
    }

    /**
     * 搜索
     * @param type $keyword
     * @return type
     */
    public function search_ban_check($keyword) {
        $check_rs = D('SearchBan')->where(array('title' => $keyword, 'status' => 1))->find();
        if ($check_rs) {
            return true;
        }
        return false;
    }

    /**
     * 获取导航信息
     * @param $param
     * @return type
     */
    public function get_nav_list($param) {
        !isset($param['where']) && $param['where'] = array();
        !isset($param['order']) && $param['order'] = 'sort ASC,id ASC';
        $rows = D('Common/Channel')->getListRows($param, FALSE);
        return array('status' => 1, 'data' => $rows);
    }

    /**
     * 获取导航信息
     * @param $param
     * @return type
     */
    public function get_link_list($param) {
        !isset($param['where']) && $param['where'] = array();
        !isset($param['order']) && $param['order'] = 'sort ASC,id ASC';
        $rows = D('Common/Link')->getListRows($param, FALSE);
        return array('status' => 1, 'data' => $rows);
    }

    /**
     * 获取下载地址
     * @return type
     */
    public function get_download_url($channel = '') {
        // 获取安卓下载信息
        $param = array();
        $param['limit'] = 1;
        $param['order'] = 'create_time DESC';
        $param['where'] = array('status' => 1, 'platform' => 1);
        $channel && $param['where']['channel'] = $channel;
        $android = D('AppInfo')->getListRows($param, FALSE);
        if (!$android) {
            unset($param['where']['channel']);
            $android = D('AppInfo')->getListRows($param, FALSE);
        }

        // 获取苹果下载信息
        $param = array();
        $param['where'] = array('status' => 1, 'platform' => 2);
        $ios = D('AppInfo')->getListRows($param, FALSE);

        $data = array();
        $data['android'] = $android ? $android[0]['download_url'] : '';
        $data['ios'] = $ios ? $ios[0]['download_url'] : '';

        return array('status' => 1, 'data' => $data);
    }

    /**
     * 提交反馈
     * @param type $param
     * @return type
     */
    public function add_feedback($param) {
        if ($param['type'] == 0) {
            if (!$param['contact'] || strlen($param['contact']) < 5) {
                return array('status' => 0, 'info' => '请填写联系方式');
            }
        }
        $param['ip'] = get_client_ip(1);
        $row = D('Common/Feedback')->where(array('ip' => $param['ip']))->order('id DESC')->find();
        if ($row && (time() - $row['create_time']) < 300) {
            return array('status' => 0, 'info' => '频繁请求，5分钟后在试');
        }

        $rs = D('Common/Feedback')->saveData($param);
        if ($rs) {
            return array('status' => 1, 'data' => $rs);
        }
        return array('status' => 0, 'info' => '提交失败', 'error' => D('Common/Feedback')->getError() . D('Common/Feedback')->getDbError());
    }

}
