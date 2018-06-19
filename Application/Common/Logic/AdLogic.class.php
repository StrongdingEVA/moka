<?php

namespace Common\Logic;


/**
 * 广告逻辑
 */
class AdLogic{
    public function get_advs_list($pos_name = '') {        
        $ad_pos = D('Common/AdvPos')->getListRows(array('where' => array('status' => 1, 'name' => array('in', $pos_name))), FALSE);
        $data = array();
        foreach ($ad_pos as $p) {
            $where = array();
            $where['pos_id'] = $p['id'];
            $where['status'] = 1;
            $adv = D('Common/Advs')->getListRows(array('where' => $where, 'order' => 'sort DESC'), FALSE);
           
            foreach ($adv as $v) {
                $data[$p['name']][] = array('title' => $v['title'], 'image' => $v['image'], 'url' => $v['url'], 'obj_id' => $v['obj_id'], 'target' => $v['target'], 'subtitle' => $v['subtitle'], 'description' => $v['description']);
            }
        }
        return array('status' => 1, 'data' => $data);
    }

}
