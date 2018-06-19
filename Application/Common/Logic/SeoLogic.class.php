<?php

namespace Common\Logic;


/**
 * 众包平台逻辑
 */
class SeoLogic 
{

    /**
     * 添加小程序审核
     */
    public function get_seo_info($module = '', $controller = '', $action = '') {
        // 添加数据
        $rs = D('SeoRule')->where(array('mod' => $module, 'ctrl' => $controller, 'act' => $action))->find();
        if (!$rs) {
            return array('status' => 0, 'info' => '没有相关seo规则', 'data' => array());
        }
        return array('status' => 1, 'data' => $rs);
    }

}
