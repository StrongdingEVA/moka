<?php

namespace Api\Controller;

use Common\Logic\FileLogic;

class FileController extends BaseController {

    public function upload() {
        $logic = new FileLogic();
        $action = I('action', '', 'op_t');
        switch ($action) {
            case 'base64': // base64
                $base64 = I('base64');
                $path = I('path');
                $ret = $logic->uploadBase64($base64, $path);
                break;
            default: // $_FILE上传
                $rs = $logic->upload();
                if (isset($rs['status']) && !$rs['status']) {
                    $this->coverRs($rs);
                    exit;
                }
                $ret['status'] = 1;
                foreach ($rs['data'] as $k => $f) {
                    $ret['data'][] = array('url' => $f['url'], 'thumb' => $f['thumb']);
                }
                break;
        }
        $this->coverRs($ret);
    }

}
