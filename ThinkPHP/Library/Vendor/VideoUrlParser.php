<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Vendor;

class VideoUrlParser {

    const USER_AGENT = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko)
        Chrome/8.0.552.224 Safari/534.10";
    const CHECK_URL_VALID = "/(youku\.com|tudou\.com|ku6\.com|56\.com|letv\.com|video\.sina\.com\.cn|(my\.)?tv\.sohu\.com|v\.qq\.com)/";

    /**
     * parse 
     * 
     * @param string $url 
     * @param mixed $createObject 
     * @static
     * @access public
     * @return void
     */
    static public function parse($url = '', $createObject = true) {
        $lowerurl = strtolower($url);
        preg_match(self::CHECK_URL_VALID, $lowerurl, $matches);
        if (!$matches)
            return false;
        switch ($matches[1]) {
            case 'youku.com':
                $data = self::_parseYouku($url);
                break;
            case 'tudou.com':                
                $data = self::_parseTudou($url);
                break;
            case 'ku6.com':
                $data = self::_parseKu6($url);
                break;
            case '56.com':
                $data = self::_parse56($url);
                break;
            case 'letv.com':
                $data = self::_parseLetv($url);
                break;
            case 'video.sina.com.cn':
                $data = self::_parseSina($url);
                break;
            case 'my.tv.sohu.com':
            case 'tv.sohu.com':
            case 'sohu.com':
                $data = self::_parseSohu($url);
                break;
            case 'v.qq.com':
                $data = self::_parseQq($url);
                break;
            default:
                $data = false;
        }
        if ($data && $createObject)
            $data['object'] = "<embed src=\"{$data['swf']}\" quality=\"high\" width=\"480\" height=\"400\" align=\"middle\" allowNetworking=\"all\" allowScriptAccess=\"always\" type=\"application/x-shockwave-flash\"></embed>";
        return $data;
    }

    /**
     * 腾讯视频 
     * http://v.qq.com/cover/o/o9tab7nuu0q3esh.html?vid=97abu74o4w3_0
     * http://v.qq.com/play/97abu74o4w3.html
     * http://v.qq.com/cover/d/dtdqyd8g7xvoj0o.html
     * http://v.qq.com/cover/d/dtdqyd8g7xvoj0o/9SfqULsrtSb.html
     * http://imgcache.qq.com/tencentvideo_v1/player/TencentPlayer.swf?_v=20110829&vid=97abu74o4w3&autoplay=1&list=2&showcfg=1&tpid=23&title=%E7%AC%AC%E4%B8%80%E7%8E%B0%E5%9C%BA&adplay=1&cid=o9tab7nuu0q3esh
     */
    private function _parseQq($url) {
        if (preg_match("/\/play\//", $url)) {
            $html = self::_fget($url);
            preg_match("/url=[^\"]+/", $html, $matches);
            if (!$matches)
                ;
            return false;
            $url = $matches[0];
        }
        preg_match("/vid=([^\_]+)/", $url, $matches);
        $vid = $matches[1];
        $html = self::_fget($url);
        // query
        preg_match("/flashvars\s=\s\"([^;]+)/s", $html, $matches);
        $query = $matches[1];
        if (!$vid) {
            preg_match("/vid\s?=\s?vid\s?\|\|\s?\"(\w+)\";/i", $html, $matches);
            $vid = $matches[1];
        }
        $query = str_replace('"+vid+"', $vid, $query);
        parse_str($query, $output);
        $data['img'] = "http://vpic.video.qq.com/{$$output['cid']}/{$vid}_1.jpg";
        $data['url'] = $url;
        $data['title'] = $output['title'];
        $data['swf'] = "http://imgcache.qq.com/tencentvideo_v1/player/TencentPlayer.swf?" . $query;
        return $data;
    }

    /**
     * 优酷网 
     * http://v.youku.com/v_show/id_XMjI4MDM4NDc2.html
     * http://player.youku.com/player.php/sid/XMjU0NjI2Njg4/v.swf
     */
    private function _parseYouku($url) {
        preg_match("#id\_(\w+)#", $url, $matches);
        if (empty($matches)) {
            preg_match("#v_playlist\/#", $url, $mat);
            if (!$mat)
                return false;
            $html = self::_fget($url);
            preg_match("#videoId2\s*=\s*\'(\w+)\'#", $html, $matches);
            if (!$matches)
                return false;
        }
        $link = "http://v.youku.com/player/getPlayList/VideoIDS/{$matches[1]}/timezone/+08/version/5/source/out?password=&ran=2513&n=3";
        $retval = self::_cget($link);
        if ($retval) {
            $json = json_decode($retval, true);
            $data['img'] = $json['data'][0]['logo'];
            $data['title'] = $json['data'][0]['title'];
            $data['url'] = $url;
            $data['swf'] = "http://player.youku.com/player.php/sid/{$matches[1]}/v.swf";
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 土豆网
     * http://www.tudou.com/programs/view/Wtt3FjiDxEE/
     * http://www.tudou.com/v/Wtt3FjiDxEE/v.swf
     * 
     * http://www.tudou.com/playlist/p/a65718.html?iid=74909603
     * http://www.tudou.com/l/G5BzgI4lAb8/&iid=74909603/v.swf
     */
    private function _parseTudou($url) {
        preg_match("#view/([-\w]+)/#", $url, $matches);
        if (empty($matches)) {
            if (strpos($url, "/playlist/") == false)
                return false;
            if (strpos($url, 'iid=') !== false) {
                $quarr = explode("iid=", $lowerurl);
                if (empty($quarr[1]))
                    return false;
            }elseif (preg_match("#p\/l(\d+).#", $lowerurl, $quarr)) {
                if (empty($quarr[1]))
                    return false;
            }
            $html = self::_fget($url);
            $html = iconv("GB2312", "UTF-8", $html);
            preg_match("/lid_code\s=\slcode\s=\s[\'\"]([^\'\"]+)/s", $html, $matches);
            $icode = $matches[1];
            preg_match("/iid\s=\s.*?\|\|\s(\d+)/sx", $html, $matches);
            $iid = $matches[1];
            preg_match("/listData\s=\s(\[\{.*\}\])/sx", $html, $matches);

            $find = array("/\n/", '/\s/', "/:[^\d\"]\w+[^\,]*,/i", "/(\{|,)(\w+):/");
            $replace = array("", "", ':"",', '\\1"\\2":');
            $str = preg_replace($find, $replace, $matches[1]);
            //var_dump($str);
            $json = json_decode($str);
            //var_dump($json);exit;
            if (is_array($json) || is_object($json) && !empty($json)) {
                foreach ($json as $val) {
                    if ($val->iid == $iid) {
                        break;
                    }
                }
            }
            $data['img'] = $val->pic;
            $data['title'] = $val->title;
            $data['url'] = $url;
            $data['swf'] = "http://www.tudou.com/l/{$icode}/&iid={$iid}/v.swf";
            return $data;
        }
        $host = "www.tudou.com";
        $path = "/v/{$matches[1]}/v.swf";
        $ret = self::_fsget($path, $host);
        if (preg_match("#\nLocation: (.*)\n#", $ret, $mat)) {
            parse_str(parse_url(urldecode($mat[1]), PHP_URL_QUERY));
            $data['img'] = $snap_pic;
            $data['title'] = $title;
            $data['url'] = $url;
            $data['swf'] = "http://www.tudou.com/v/{$matches[1]}/v.swf";
            return $data;
        }
        return false;
    }

    /**
     * 酷6网 
     * http://v.ku6.com/film/show_520/3X93vo4tIS7uotHg.html
     * http://v.ku6.com/special/show_4926690/Klze2mhMeSK6g05X.html
     * http://v.ku6.com/show/7US-kDXjyKyIInDevhpwHg...html
     * http://player.ku6.com/refer/3X93vo4tIS7uotHg/v.swf
     */
    private function _parseKu6($url) {
        if (preg_match("/show\_/", $url)) {
            preg_match("#/([-\w]+)\.html#", $url, $matches);
            $url = "http://v.ku6.com/fetchVideo4Player/{$matches[1]}.html";
            $html = self::_fget($url);
            if ($html) {
                $json = json_decode($html, true);
                if (!$json)
                    return false;

                $data['img'] = $json['data']['picpath'];
                $data['title'] = $json['data']['t'];
                $data['url'] = $url;
                $data['swf'] = "http://player.ku6.com/refer/{$matches[1]}/v.swf";
                return $data;
            } else {
                return false;
            }
        } elseif (preg_match("/show\//", $url, $matches)) {
            $html = self::_fget($url);
            preg_match("/ObjectInfo\s?=\s?([^\n]*)};/si", $html, $matches);
            $str = $matches[1];
            // img
            preg_match("/cover\s?:\s?\"([^\"]+)\"/", $str, $matches);
            $data['img'] = $matches[1];
            // title
            preg_match("/title\"?\s?:\s?\"([^\"]+)\"/", $str, $matches);
            $jsstr = "{\"title\":\"{$matches[1]}\"}";
            $json = json_decode($jsstr, true);
            $data['title'] = $json['title'];
            // url
            $data['url'] = $url;
            // query
            preg_match("/\"(vid=[^\"]+)\"\sname=\"flashVars\"/s", $html, $matches);
            $query = str_replace("&", '&', $matches[1]);
            preg_match("/\/\/player\.ku6cdn\.com[^\"\']+/", $html, $matches);
            $data['swf'] = 'http:' . $matches[0] . '?' . $query;

            return $data;
        }
    }

    /**
     * 56网
     * http://www.56.com/u73/v_NTkzMDcwNDY.html
     * http://player.56.com/v_NTkzMDcwNDY.swf
     */
    private function _parse56($url) {
        preg_match("#/v_(\w+)\.html#", $url, $matches);
        if (empty($matches))
            return false;
        $link = "http://vxml.56.com/json/{$matches[1]}/?src=out";
        $retval = self::_cget($link);
        if ($retval) {
            $json = json_decode($retval, true);
            $data['img'] = $json['info']['img'];
            $data['title'] = $json['info']['Subject'];
            $data['url'] = $url;
            $data['swf'] = "http://player.56.com/v_{$matches[1]}.swf";
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 乐视网 
     * http://www.letv.com/ptv/vplay/1168109.html
     * http://www.letv.com/player/x1168109.swf
     */
    private function _parseLetv($url) {
        $html = self::_fget($url);
        preg_match("#http://v.t.sina.com.cn/([^'\"]*)#", $html, $matches);
        parse_str(parse_url(urldecode($matches[0]), PHP_URL_QUERY));
        preg_match("#vplay/(\d+)#", $url, $matches);
        $data['img'] = $pic;
        $data['title'] = $title;
        $data['url'] = $url;
        $data['swf'] = "http://www.letv.com/player/x{$matches[1]}.swf";
        return $data;
    }

    // 搜狐TV http://my.tv.sohu.com/u/vw/5101536
    private function _parseSohu($url) {
        $html = self::_fget($url);
        $html = iconv("GB2312", "UTF-8", $html);
        preg_match_all("/og:(?:title|image|videosrc)\"\scontent=\"([^\"]+)\"/s", $html, $matches);
        $data['img'] = $matches[1][1];
        $data['title'] = $matches[1][0];
        $data['url'] = $url;
        $data['swf'] = $matches[1][2];
        return $data;
    }

    /*
     * 新浪播客
     * http://video.sina.com.cn/v/b/48717043-1290055681.html
     * http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=48717043_1290055681_PUzkSndrDzXK+l1lHz2stqkP7KQNt6nki2O0u1ehIwZYQ0/XM5GdatoG5ynSA9kEqDhAQJA4dPkm0x4/s.swf
     */

    private function _parseSina($url) {
        preg_match("/(\d+)(?:\-|\_)(\d+)/", $url, $matches);
        $url = "http://video.sina.com.cn/v/b/{$matches[1]}-{$matches[2]}.html";
        $html = self::_fget($url);
        preg_match("/video\s?:\s?([^<]+)}/", $html, $matches);
        $find = array("/\n/", "/\s*/", "/\'/", "/\{([^:,]+):/", "/,([^:]+):/", "/:[^\d\"]\w+[^\,]*,/i");
        $replace = array('', '', '"', '{"\\1":', ',"\\1":', ':"",');
        $str = preg_replace($find, $replace, $matches[1]);
        $arr = json_decode($str, true);
        $data['img'] = $arr['pic'];
        $data['title'] = $arr['title'];
        $data['url'] = $url;
        $data['swf'] = $arr['swfOutsideUrl'];

        return $data;
    }

    /*
     * 通过 file_get_contents 获取内容
     */

    private function _fget($url = '') {
        if (!$url)
            return false;
        $html = file_get_contents($url);
        // 判断是否gzip压缩
        if ($dehtml = self::_gzdecode($html))
            return $dehtml;
        else
            return $html;
    }

    /*
     * 通过 fsockopen 获取内容
     */

    private function _fsget($path = '/', $host = '', $user_agent = '') {
        if (!$path || !$host)
            return false;
        $user_agent = $user_agent ? $user_agent : self::USER_AGENT;
        $out = <<<HEADER
GET $path HTTP/1.1
Host: $host
User-Agent: $user_agent
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: zh-cn,zh;q=0.5
Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n\r\n
HEADER;
        $fp = @fsockopen($host, 80, $errno, $errstr, 10);
        if (!$fp)
            return false;
        if (!fputs($fp, $out))
            return false;
        while (!feof($fp)) {
            $html .= fgets($fp, 1024);
        }
        fclose($fp);
        // 判断是否gzip压缩
        if ($dehtml = self::_gzdecode($html))
            return $dehtml;
        else
            return $html;
    }

    /*
     * 通过 curl 获取内容
     */

    private function _cget($url = '', $user_agent = '') {
        if (!$url)
            return;
        $user_agent = $user_agent ? $user_agent : self::USER_AGENT;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if (strlen($user_agent))
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        ob_start();
        curl_exec($ch);
        $html = ob_get_contents();
        ob_end_clean();
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        if (!is_string($html) || !strlen($html)) {
            return false;
        }
        return $html;
        // 判断是否gzip压缩
        if ($dehtml = self::_gzdecode($html))
            return $dehtml;
        else
            return $html;
    }

    private function _gzdecode($data) {
        $len = strlen($data);
        if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
            return null; // Not GZIP format (See RFC 1952) 
        }
        $method = ord(substr($data, 2, 1)); // Compression method 
        $flags = ord(substr($data, 3, 1)); // Flags 
        if ($flags & 31 != $flags) {
            // Reserved bits are set -- NOT ALLOWED by RFC 1952 
            return null;
        }
        // NOTE: $mtime may be negative (PHP integer limitations) 
        $mtime = unpack("V", substr($data, 4, 4));
        $mtime = $mtime [1];
        $xfl = substr($data, 8, 1);
        $os = substr($data, 8, 1);
        $headerlen = 10;
        $extralen = 0;
        $extra = "";
        if ($flags & 4) {
            // 2-byte length prefixed EXTRA data in header 
            if ($len - $headerlen - 2 < 8) {
                return false; // Invalid format 
            }
            $extralen = unpack("v", substr($data, 8, 2));
            $extralen = $extralen [1];
            if ($len - $headerlen - 2 - $extralen < 8) {
                return false; // Invalid format 
            }
            $extra = substr($data, 10, $extralen);
            $headerlen += 2 + $extralen;
        }

        $filenamelen = 0;
        $filename = "";
        if ($flags & 8) {
            // C-style string file NAME data in header 
            if ($len - $headerlen - 1 < 8) {
                return false; // Invalid format 
            }
            $filenamelen = strpos(substr($data, 8 + $extralen), chr(0));
            if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
                return false; // Invalid format 
            }
            $filename = substr($data, $headerlen, $filenamelen);
            $headerlen += $filenamelen + 1;
        }

        $commentlen = 0;
        $comment = "";
        if ($flags & 16) {
            // C-style string COMMENT data in header 
            if ($len - $headerlen - 1 < 8) {
                return false; // Invalid format 
            }
            $commentlen = strpos(substr($data, 8 + $extralen + $filenamelen), chr(0));
            if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
                return false; // Invalid header format 
            }
            $comment = substr($data, $headerlen, $commentlen);
            $headerlen += $commentlen + 1;
        }

        $headercrc = "";
        if ($flags & 1) {
            // 2-bytes (lowest order) of CRC32 on header present 
            if ($len - $headerlen - 2 < 8) {
                return false; // Invalid format 
            }
            $calccrc = crc32(substr($data, 0, $headerlen)) & 0xffff;
            $headercrc = unpack("v", substr($data, $headerlen, 2));
            $headercrc = $headercrc [1];
            if ($headercrc != $calccrc) {
                return false; // Bad header CRC 
            }
            $headerlen += 2;
        }

        // GZIP FOOTER - These be negative due to PHP's limitations 
        $datacrc = unpack("V", substr($data, - 8, 4));
        $datacrc = $datacrc [1];
        $isize = unpack("V", substr($data, - 4));
        $isize = $isize [1];

        // Perform the decompression: 
        $bodylen = $len - $headerlen - 8;
        if ($bodylen < 1) {
            // This should never happen - IMPLEMENTATION BUG! 
            return null;
        }
        $body = substr($data, $headerlen, $bodylen);
        $data = "";
        if ($bodylen > 0) {
            switch ($method) {
                case 8 :
                    // Currently the only supported compression method: 
                    $data = gzinflate($body);
                    break;
                default :
                    // Unknown compression method 
                    return false;
            }
        } else {
            //...
        }

        if ($isize != strlen($data) || crc32($data) != $datacrc) {
            // Bad format!  Length or CRC doesn't match! 
            return false;
        }
        return $data;
    }

    static function parse_vedio($vedio_url = '') {
        $url = empty($vedio_url) ? $_GET ['vedio_url'] : $vedio_url;
        $data = array();
        $temp_data = array();
        $pic_url = '';
        $result = array();
        if (preg_match('/http:\/\/v.youku.com\/v_show.*id_(.*)\.html/U', $url, $data)) {
            if (!empty($data)) {
                $result ['real_url'] = ' http://player.youku.com/player.php/sid/' . $data [1] . '/v.swf';
                $result ['company'] = '优酷';
                $temp_data = file_get_contents($url);
                preg_match('/pic=(.*)" target="_blank"/U', $temp_data, $pic_url);
                if (!empty($pic_url)) {
                    $result ['pic_url'] = $pic_url [1];
                }
            }
        } elseif (preg_match('/http:\/\/www.tudou.com\/(.).*\/(.*)\.html/U', $url, $data)) {
            $new_data = explode('/', $data [2]);
            $temp = array();
            $html = file_get_contents($url);
            preg_match('/.*iid:\s?(\d+)[\s\S]*pic:\s?"(.*)"/', $html, $temp);
            if (!empty($data) && !empty($temp)) {
                $result ['real_url'] = 'http://www.tudou.com/' . $data [1] . '/' . $new_data [0] . '/&iid=' . $temp [1] . '/v.swf';
                $result ['company'] = '土豆';
                $result ['pic_url'] = $temp [2];
            }
        } elseif (preg_match('/http:\/\/(.*)tv.sohu.com\/.*\/(.*.shtml|.*\/(.*))/', $url, $temp_data)) {
            $html = file_get_contents($url);
            if (empty($temp_data [1])) {
                $temp = array();
                $temp_1 = array();
                preg_match('/[\s\S]*vid="(\d+)"[\s\S]*var cover="(.*)"/U', $html, $temp);
                preg_match('/[\s\S]*PLAYLIST_ID="(\d+)"[\s\S]*/', $html, $temp_1);
                $result ['pic_url'] = $temp [2];
                if (!empty($temp)) {
                    if (empty($temp_1)) {
                        $result ['real_url'] = 'http://share.vrs.sohu.com/' . $temp [1] . '/v.swf&autoplay=true&api_key=664cbe6f3376306fa6b3082c770989d0';
                    } else {
                        $result ['real_url'] = 'http://share.vrs.sohu.com/' . $temp [1] . '/v.swf&autoplay=true&api_key=664cbe6f3376306fa6b3082c770989d0&plid=' . $temp [1];
                    }
                    $result ['company'] = '搜狐';
                }
            } else {
                preg_match('/bCover: \'(.*)\'/U', $html, $temp);
                $result ['pic_url'] = $temp [1];
                $result ['real_url'] = 'http://share.vrs.sohu.com/' . rtrim($temp_data [1], '.') . '/v.swf&id=' . $temp_data [3] . '&topBar=1&autoplay=true&api_key=664cbe6f3376306fa6b3082c770989d0';
                $result ['company'] = '搜狐';
            }
        } elseif (preg_match('/http:\/\/v.ifeng.com\/.*.shtml/U', $url)) {
            preg_match('/http:\/\/v.ifeng.com\/.*\/((.*\/.*)|(.*))\/(.*).shtml$/U', $url, $data);
            $html = file_get_contents($url);
            if (!empty($data)) {
                preg_match('/"url": "' . addcslashes($url, '/') . '","img": "(.*)"/', $html, $temp);
                $result ['pic_url'] = $temp [1];
                $result ['real_url'] = 'http://v.ifeng.com/include/exterior.swf?guid=' . $data [4] . '&AutoPlay=true';
                $result ['company'] = '凤凰';
            } elseif (preg_match('/http:\/\/v.ifeng.com\/.*\/.*\/.*.shtml#(.*)/', $url, $data)) {
                preg_match('/<li name="' . $data [1] . '".*>[\s\S]*<img src="(.*)"/U', $html, $temp);
                $result ['pic_url'] = $temp [1];
                $result ['real_url'] = 'http://v.ifeng.com/include/exterior.swf?guid=' . $data [1] . '&AutoPlay=true';
                $result ['company'] = '凤凰';
            }
        } elseif (preg_match('/http:\/\/video.sina.com.cn.*/', $url)) {
            $flag = true;
            $html = file_get_contents($url);
            preg_match('/pic: \'(.*)\'[\s\S]*swfOutsideUrl:\'(.*)\'/U', $html, $data);
            if (!empty($data)) {
                $result ['pic_url'] = $data [1];
                $result ['real_url'] = $data [2];
                $result ['company'] = '新浪';
            }
        } elseif (preg_match('/http:\/\/(www|yule).iqiyi.com\/(.*).html/', $url, $data)) {
            if (!empty($data)) {
                $html = file_get_contents($url);
                preg_match('/"pid":"(.*)","ptype":"(.*)".*"videoId":"(.*)","albumId":"(.*)".*"tvId":"(.*)".*"qitanId":(.*),[\s\S]*<span id="imgPathData" style="display:none">(.*)<\/span>/U', $html, $temp_data);
                if (!empty($temp_data)) {
                    $result ['company'] = '爱奇艺';
                    $result ['pic_url'] = str_replace('_baidu', '', trim($temp_data [7]));
                    $result ['real_url'] = 'http://player.video.qiyi.com/' . $temp_data [3] . '/0/0/' . ($data [1] == 'www' ? $data [2] : $data [1] . '/' . $data [2]) . '.swf-pid=' . $temp_data [1] . '-ptype=' . $temp_data [2] . '-albumId=' . $temp_data [4] . '-tvId=' . $temp_data [5] . '-autoplay=1-qitanId=' . $temp_data [6] . '-isDrm=0-isPurchase=0';
                }
            }
        } elseif (preg_match('/http:\/\/v.163.com\/.*/', $url, $data)) {
            if (!empty($data)) {
                $html = file_get_contents($url);
                preg_match('/name="movie"><param value="(.*)"  name="flashvars">[\s\S]*moviePicture=\'(.*)\'/U', $html, $data);
                if (!empty($data)) {
                    $result ['company'] = '网易';
                    $result ['real_url'] = 'http://swf.ws.126.net/v/ljk/shareplayer/ShareFlvPlayer.swf?' . $data [1];
                    $result ['pic_url'] = $data [2];
                }
            }
        }
        $result['title'] = '';
        $result['url'] = $vedio_url;
        $result['swf'] = $result ['real_url'];
        unset($result ['real_url']);
        return $result;
    }

}
