<?php

class Collection {
    protected $url, $config, $upload_path;

    function __construct($conf = array()) {
        $this->upload_path = isset($conf['upload_path']) ? $conf['upload_path'] : 'Uploads';
    }

    /**
     * 采集内容
     * @param string $url    采集地址
     * @param array $config  配置参数
     * @param integer $test  采集测试
     */
    function get_content($url, $config, $test = FALSE) {
        $this->url = $url;
        $this->config = $config;
        set_time_limit(300);
        if ($html = $this->get_html($url, $config)) {
            //获取标题
            if ($config['title_rule']) {
                $title_rule = $this->replace_sg($config['title_rule']);
                $data['title'] = $this->replace_item($this->cut_html($html, $title_rule[0], $title_rule[1]), $config['title_html_rule']);
            } else {
                $data['title'] = '';
            }

            //获取作者
            if ($config['author_rule']) {
                $author_rule = $this->replace_sg($config['author_rule']);
                $data['author'] = $this->replace_item($this->cut_html($html, $author_rule[0], $author_rule[1]), $config['author_html_rule']);
            } else {
                $data['author'] = '';
            }

            //获取描述
            if ($config['key_rule']) {
                $key_rule = $this->replace_sg($config['key_rule']);
                $data['key'] = $this->replace_item($this->cut_html($html, $key_rule[0], $key_rule[1]), $config['key_html_rule']);
            } else {
                $data['key'] = '';
            }

            //获取描述
            if ($config['desc_rule']) {
                $desc_rule = $this->replace_sg($config['desc_rule']);
                $data['desc'] = $this->replace_item($this->cut_html($html, $desc_rule[0], $desc_rule[1]), $config['desc_html_rule']);
            } else {
                $data['desc'] = '';
            }

            //获取二维码
            if ($config['qrcode_rule']) {
                $qrcode_rule = $this->replace_sg($config['qrcode_rule']);
                $data['qrcode'] = $this->replace_item($this->replace_item($this->cut_html($html, $qrcode_rule[0], $qrcode_rule[1]), $config['qrcode_html_rule']));
            } else {
                $data['qrcode'] = '';
            }

            //获取ICON
            if ($config['icon_rule']) {
                $icon_rule = $this->replace_sg($config['icon_rule']);
                $data['icon'] = $this->replace_item($this->replace_item($this->cut_html($html, $icon_rule[0], $icon_rule[1]), $config['icon_html_rule']));
            } else {
                $data['icon'] = '';
            }

            //获取图片集
            if ($config['imgs_rule']) {
                $imgs_rule = $this->replace_sg($config['imgs_rule']);
                $data['imgs'] = $this->replace_item($this->replace_item($this->cut_html($html, $imgs_rule[0], $imgs_rule[1]), $config['imgs_html_rule']));
            } else {
                $data['imgs'] = '';
            }


            //获取内容
            if ($config['content_rule']) {
                $content_rule = $this->replace_sg($config['content_rule']);
                $data['content'] = $this->replace_item($this->cut_html($html, $content_rule[0], $content_rule[1]), $config['content_html_rule']);
            } else {
                $data['content'] = '';
            }

            if (!$test) {
                $down_imgs = array();
                isset($data['icon']) && $down_imgs[] = &$data['icon'];
                isset($data['imgs']) && $down_imgs[] = &$data['imgs'];
                isset($data['qrcode']) && $down_imgs[] = &$data['qrcode'];
                foreach ($down_imgs as &$v) {
                    $v = preg_replace_callback('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/i', 'download_img_callback', $v);

                    //下载内容中的图片到本地
                    if (!empty($v)) {
                        $info = $this->download($v, 0);
                        $v = '';
                        foreach ($info as $i) {
                            $v.=empty($v) ? $i['filepath'] : ',' . $i['filepath'];
                        }
                    }
                }
            }
            return $data;
        }
    }

    /**
     * 转换图片地址为绝对路径，为下载做准备。
     * @param array $out 图片地址
     */
    function download_img_callback($matches) {
        return $this->download_img($matches[0], $matches[1]);
    }

    function download_img($old, $out) {
        if (!empty($old) && !empty($out) && strpos($out, '://') === false) {
            return str_replace($out, $this->url_check($out, $this->url, $this->config), $old);
        } else {
            return $old;
        }
    }

    /**
     * 获取远程HTML
     * @param string $url    获取地址
     * @param array $config  配置
     */
    function get_html($url, &$config) {
        if (!empty($url) && $html = @file_get_contents($url)) {
            if ('utf-8' != strtolower($config['sourcecharset'])) {
                $html = iconv($config['sourcecharset'], 'UTF-8//TRANSLIT//IGNORE', $html);
            }
            return $html;
        } else {
            return false;
        }
    }

    /**
     * 
     * HTML切取
     * @param string $html    要进入切取的HTML代码
     * @param string $start   开始
     * @param string $end     结束
     */
    function cut_html($html, $start, $end) {
        if (empty($html))
            return false;
        $html = str_replace(array("\r", "\n"), "", $html);
        $start = str_replace(array("\r", "\n"), "", $start);
        $end = str_replace(array("\r", "\n"), "", $end);
        $html = explode(trim($start), $html);

        if (is_array($html))
            $html = explode(trim($end), $html[1]);
        return trim($html[0]);
    }

    /**
     * 过滤代码
     * @param string $html  HTML代码
     * @param array $config 过滤配置
     */
    function replace_item($html, $config) {
        if (empty($config))
            return $html;

        $config = html_entity_decode($config);
        $config = explode("\n", $config);
        $patterns = $replace = array();
        $p = 0;
        foreach ($config as $k => $v) {
            if (empty($v))
                continue;
            $c = explode('[|]', $v);
            $patterns[$k] = '/' . str_replace('/', '\/', $c[0]) . '/i';
            $replace[$k] = $c[1];
            $p = 1;
        }
        return $p ? @preg_replace($patterns, $replace, $html) : false;
    }

    /**
     * 替换采集内容
     * @param $html 采集规则
     */
    function replace_sg($html) {
        $list = explode('[内容]', html_entity_decode($html));
        if (is_array($list))
            foreach ($list as $k => $v) {
                $list[$k] = str_replace(array("\r", "\n"), '', trim($v));
            }
        return $list;
    }

    /**
     * URL地址检查
     * @param string $url      需要检查的URL
     * @param string $baseurl  基本URL
     * @param array $config    配置信息
     */
    function url_check($url, $baseurl, $config) {
        $urlinfo = parse_url($baseurl);

        $baseurl = $urlinfo['scheme'] . '://' . $urlinfo['host'] . (substr($urlinfo['path'], -1, 1) === '/' ? substr($urlinfo['path'], 0, -1) : str_replace('\\', '/', dirname($urlinfo['path']))) . '/';
        if (strpos($url, '://') === false) {
            if ($url[0] == '/') {
                $url = $urlinfo['scheme'] . '://' . $urlinfo['host'] . $url;
            } else {
                if ($config['page_base']) {
                    $url = $config['page_base'] . $url;
                } else {
                    $url = $baseurl . $url;
                }
            }
        }
        return $url;
    }

    /**
     * 补全网址
     *
     * @param	string	$surl		源地址
     * @param	string	$absurl		相对地址
     * @param	string	$basehref	网址
     * @return	string	网址
     */
    function fillurl($surl, $absurl, $basehref = '') {
        if ($basehref != '') {
            $preurl = strtolower(substr($surl, 0, 6));
            if ($preurl == 'http://' || $preurl == 'ftp://' || $preurl == 'mms://' || $preurl == 'rtsp://' || $preurl == 'thunde' || $preurl == 'emule://' || $preurl == 'ed2k://')
                return $surl;
            else
                return $basehref . '/' . $surl;
        }
        $i = 0;
        $dstr = '';
        $pstr = '';
        $okurl = '';
        $pathStep = 0;
        $surl = trim($surl);
        if ($surl == '')
            return '';
        $urls = @parse_url($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/');
        $HomeUrl = $urls['host'];
        $BaseUrlPath = $HomeUrl . $urls['path'];
        $BaseUrlPath = preg_replace("/\/([^\/]*)\.(.*)$/", '/', $BaseUrlPath);
        $BaseUrlPath = preg_replace("/\/$/", '', $BaseUrlPath);
        $pos = strpos($surl, '#');
        if ($pos > 0)
            $surl = substr($surl, 0, $pos);
        if ($surl[0] == '/') {
            $okurl = 'http://' . $HomeUrl . '/' . $surl;
        } elseif ($surl[0] == '.') {
            if (strlen($surl) <= 2)
                return '';
            elseif ($surl[0] == '/') {
                $okurl = 'http://' . $BaseUrlPath . '/' . substr($surl, 2, strlen($surl) - 2);
            } else {
                $urls = explode('/', $surl);
                foreach ($urls as $u) {
                    if ($u == "..")
                        $pathStep++;
                    else if ($i < count($urls) - 1)
                        $dstr .= $urls[$i] . '/';
                    else
                        $dstr .= $urls[$i];
                    $i++;
                }
                $urls = explode('/', $BaseUrlPath);
                if (count($urls) <= $pathStep)
                    return '';
                else {
                    $pstr = 'http://';
                    for ($i = 0; $i < count($urls) - $pathStep; $i++) {
                        $pstr .= $urls[$i] . '/';
                    }
                    $okurl = $pstr . $dstr;
                }
            }
        } else {
            $preurl = strtolower(substr($surl, 0, 6));
            if (strlen($surl) < 7)
                $okurl = 'http://' . $BaseUrlPath . '/' . $surl;
            elseif ($preurl == "http:/" || $preurl == 'ftp://' || $preurl == 'mms://' || $preurl == "rtsp://" || $preurl == 'thunde' || $preurl == 'emule:' || $preurl == 'ed2k:/')
                $okurl = $surl;
            else
                $okurl = 'http://' . $BaseUrlPath . '/' . $surl;
        }
        $preurl = strtolower(substr($okurl, 0, 6));
        if ($preurl == 'ftp://' || $preurl == 'mms://' || $preurl == 'rtsp://' || $preurl == 'thunde' || $preurl == 'emule:' || $preurl == 'ed2k:/') {
            return $okurl;
        } else {
            $okurl = preg_replace('/^(http:\/\/)/i', '', $okurl);
            $okurl = preg_replace('/\/{1,}/i', '/', $okurl);
            return 'http://' . $okurl;
        }
    }

    /**
     * 附件下载
     * Enter description here ...
     * @param $field 预留字段
     * @param $value 传入下载内容
     * @param $watermark 是否加入水印
     * @param $ext 下载扩展名
     * @param $absurl 绝对路径
     * @param $basehref 
     */
    function download($value, $watermark = '0', $ext = 'gif|jpg|jpeg|bmp|png', $absurl = '', $basehref = '') {
        $upload_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/';
        $dir = $this->upload_path . date('/Y/m/d/');
        $uploadpath = $upload_url . $dir;
        $uploaddir = dirname(THINK_PATH) . '/' . $dir;
        $string = $this->new_stripslashes($value);
        if (!preg_match_all("/(href|src)=([\"|']?)([^ \"'>]+\.($ext))\\2/i", $string, $matches))
            return $value;
        $remotefileurls = array();
        foreach ($matches[3] as $matche) {
            if (strpos($matche, '://') === false)
                continue;
            $this->dir_create($uploaddir);
            $remotefileurls[$matche] = $this->fillurl($matche, $absurl, $basehref);
        }
        unset($matches, $string);
        $remotefileurls = array_unique($remotefileurls);
        $oldpath = $newpath = array();
        $downloadedfiles = array();
        foreach ($remotefileurls as $k => $file) {
            if (strpos($file, '://') === false || strpos($file, $upload_url) !== false)
                continue;
            $filename = $this->fileext($file);
            $file_name = basename($file);
            $filename = date('Ymdhis') . rand(1000, 9999) . '.' . $filename;

            $newfile = $uploaddir . $filename;
            $upload_func = 'copy';
            if ($upload_func($file, $newfile)) {
                $oldpath[] = $k;
                @chmod($newfile, 0777);
                $fileext = $this->fileext($filename);
                $filepath = str_replace($this->upload_path, '', $dir) . $filename;

                $rs = upload_local_sea($newfile);             
                if (is_array($rs) || $rs['status']) {
                    unlink($newfile);
                    $filepath = $rs['data']['url'];
                }


                $downloadedfiles[] = array('filename' => $filename, 'filepath' => $filepath, 'filesize' => filesize($newfile), 'fileext' => $fileext, 'img' => str_replace($oldpath, $filepath, $value));
            }
        }

        return $downloadedfiles; //str_replace($oldpath, $newpath, $value);
    }

    /**
     * 返回经stripslashes处理过的字符串或数组
     * @param $string 需要处理的字符串或数组
     * @return mixed
     */
    function new_stripslashes($string) {
        if (!is_array($string))
            return stripslashes($string);
        foreach ($string as $key => $val)
            $string[$key] = $this->new_stripslashes($val);
        return $string;
    }

    /**
     * 创建目录
     * 
     * @param	string	$path	路径
     * @param	string	$mode	属性
     * @return	string	如果已经存在则返回true，否则为flase
     */
    function dir_create($path, $mode = 0777) {
        if (is_dir($path))
            return TRUE;
        $ftp_enable = 0;
        $path = $this->dir_path($path);
        $temp = explode('/', $path);
        $cur_dir = '';
        $max = count($temp) - 1;
        for ($i = 0; $i < $max; $i++) {
            $cur_dir .= $temp[$i] . '/';
            if (@is_dir($cur_dir))
                continue;
            @mkdir($cur_dir, 0777, true);
            @chmod($cur_dir, 0777);
        }
        return is_dir($path);
    }

    /**
     * 取得文件扩展
     *
     * @param $filename 文件名
     * @return 扩展名
     */
    function fileext($filename) {
        return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
    }

    function dir_path($path) {
        $path = str_replace('\\', '/', $path);
        if (substr($path, -1) != '/')
            $path = $path . '/';
        return $path;
    }

}
