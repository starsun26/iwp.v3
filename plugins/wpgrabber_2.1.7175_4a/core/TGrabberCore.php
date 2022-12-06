<?php

#ini_set('display_errors', true);
#error_reporting(E_ALL);

/**
 * TGrabberCore
 *
 * @version 1.1
 * @author GrabTeam <gfk@mail.ru>, <Motohiro.Ooshima@gmail.com>
 * @copyright 2009-2016 GrabTeam (closed)
 * @link http://wpgrabber-tune.blogspot.com/
 */
class TGrabberCore
{

    var $config;

    var $feed;

    var $content;

    var $currentUrl;

    var $picToIntro;

    var $intro_pic_on;

    var $testOn;

    var $titles;

    var $baseHrefs;

    var $onLog;

    var $imageDir = '';

    var $db;

    var $introTexts;

    var $currentTitle;

    var $imagesContent = array();

    var $requestMethod;

    var $rssDescs;

    var $imagesContentNoSave;

    var $filterWordsSave;

    var $updateFeedData = array();

    var $rootPath;

    var $tmpDir;

    var $cookieFile;

    var $textNoTranslate = array();

    var $titleNoTranslate = array();

    protected $_is_transaction_model = false;

    protected $_start_import = false;

    protected $_current_link = null;

    protected $_links_list = array();

    // режим автообновления
    var $autoUpdateMode = 0;


    function __construct()
    {
        if ((int)$this->config->get('phpTimeLimit'))
            set_time_limit($this->config->get('phpTimeLimit'));
        $this->tmpDir = $this->rootPath . $this->config->get('testPath');
        $this->cookieFile = $this->tmpDir . 'cookies.txt';
        if ($this->config->get('curlCookiesClean'))
            $this->write_string($this->cookieFile, "", "w");
    }


    public function write_string($filename, $record, $type = "w")
    {

        $logFile = fopen($filename, $type);
        fwrite($logFile, $record);
        fclose($logFile);
    }



    public function setTransactionModel()
    {
        $this->_is_transaction_model = true;
    }

    protected function _isTransactionModel()
    {
        return $this->_is_transaction_model;
    }

    public function __sleep()
    {
        return array_keys(get_object_vars($this));
    }

    public function __wakeup()
    {

    }

    /**
     * Test mode On
     *
     */
    function setTest()
    {
        $this->testOn = 1;
    }

    /**
     * Display messages off
     *
     */
    function onLog()
    {
        $this->onLog = 1;
    }

    /**
     * Display message
     *
     * @param mixed $mess
     */
    function _echo($mess)
    {
    }


    function _echoMessage($message)
    {
        $this->_echo("\n<br />".$message.'');
    }

    function _echoWarning($message)
    {
        $this->_echo("\n<br /><i>".$message.'</i>');
    }

    function _echoError($message)
    {
        $this->_echo("\n<br /><font color=\"red\"><b>".$message.'</b></font>');
    }

    /**
     * Charset convert
     *
     * @param mixed $out
     * @param mixed $inCharset
     * @param mixed $outCharset
     * @return string
     */
    function utf($out, $inCharset, $outCharset = 'UTF-8')
    {
        if ($inCharset == 'исходная')
            return $out;
        return mb_convert_encoding($out, $outCharset, $inCharset);
        #return iconv($inCharset, $outCharset, $out);
    }

    public function cp1251_to_uft8($v)
    {
        return $this->utf($v, 'CP1251');
    }


    /**
     * Получение контента ссылке с помощью fsockopen()
     *
     * @param mixed $url
     * @return mixed
     */
    private function getContentUrlSockOpen($url)
    {
        $urlParse = parse_url($url);
        $requestUrl = trim(str_replace($urlParse['scheme'] . '://' . $urlParse['host'], '', $url));
        $requestUrl = $requestUrl == '' ? '/' : $requestUrl;
        $fp = fsockopen($urlParse['host'], 80, $errno, $errstr, 30);
        if (!$fp)
            return false;
        $headers = "GET " . $requestUrl . " HTTP/1.1\r\n";
        $headers .= "Host: " . $urlParse['host'] . "\r\n";
        $headers .= "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
        $headers .= "Connection: close\r\n\r\n";
        fwrite($fp, $headers);
        $out = '';
        while (!feof($fp)) {
            $out .= fgets($fp, 4096);
        }
        $out = preg_replace("|.*?\r\n\r\n|is", '', $out, 1);
        return $out;
    }

    /**
     * Получение контента по ссылке
     *
     * @param mixed $url
     * @return mixed
     */
    function getContent($url)
    {
        #$this->_echo('<br /><b>tmpDir</b>: <a target="_blank" href="' . $this-> tmpDir . '">' . $this-> tmpDir . '</a> <br>');
        #var_export($this->config);
        $this->currentUrl = $url;
        if (!$this->requestMethod) // CURL
        {
            $ch = curl_init();
            $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
            $headers[] = "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8,cs;q=0.7";
            $headers[] = "Connection: keep-alive";
            #$headers[] = "DNT: 1";
            if ($this->config->get('curlGzipOn'))
                $headers[] = "Accept-Encoding: gzip";
            if ($this->config->get('userAgent'))
                $headers[] = "User-Agent: " . $this->config->get('userAgent');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($ch, CURLOPT_URL, $this->_rawurlencode($url));
            #$this->_echo('_rawurlencode($url): <b>'.$this->_rawurlencode($url).'</b><br />');

            curl_setopt($ch, CURLOPT_VERBOSE, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            #curl_setopt( $ch, CURLOPT_SSLVERSION, 6);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);

            if ($this->config->get('curlHeaderOn'))
                curl_setopt($ch, CURLOPT_HEADER, true);
            if ($this->config->get('requestTime'))
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->config->get('requestTime'));
            if ($this->config->get('curlGzipOn'))
                curl_setopt($ch, CURLOPT_ENCODING, "gzip");
            if ($this->config->get('curlRedirectOn'))
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            if ($this->config->get('curlCookiesOn')) {
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
            }

            if ($this->config->get('curlProxyOn')) {
                // Берём из списка
                if($this->config->get('curlProxyListOn'))
                {
                    // ставим прокси
                    if ($this->config->get('curlProxyHostPort_List')) {
                        $this->_echo('getContent->curlProxyHostPort_List: <b>'.$this->config->get('curlProxyHostPort_List').'</b><br />');
                        $proxy_array = explode("\r", trim($this->config->get('curlProxyHostPort_List')));
                        shuffle($proxy_array);
                        $proxy = array_pop($proxy_array);
                        $this->_echo('getContent->proxy: <b>'.$proxy.'</b><br />');
                        curl_setopt($ch, CURLOPT_PROXY, trim($proxy));
                        $this->_echo('<br /><b>TGrabberCore::getContent CURLOPT_PROXY</b>: ' . $proxy . ' <br>');
                    }
                }
                else
                {
                    // ставим прокси
                    if ($this->config->get('curlProxyHostPort')) {
                        curl_setopt($ch, CURLOPT_PROXY, $this->config->get('curlProxyHostPort'));
                        $this->_echo('<br /><b>TGrabberCore::getContent CURLOPT_PROXY</b>: ' . $this->config->get('curlProxyHostPort') . ' <br>');
                    }
                }


                // ставим тип прокси
                #  array('0'=>'CURLPROXY_HTTP','1'=>'CURLPROXY_SOCKS5','2'=>'CURLPROXY_SOCKS4A','3'=>'CURLPROXY_SOCKS5_HOSTNAME'), get_option('wpg_' .'curlProxyType'), 1);

                if ($this->config->get('curlProxyType')) {
                    switch ($this->config->get('curlProxyType')) {
                        case 1:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                            break;
                        case 2:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4A);
                            break;
                        case 3:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
                            break;
                        default:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    }
                }
                // авторизация
                if ($this->config->get('curlProxyUserPwd')) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->config->get('curlProxyUserPwd'));    // Стока с именем пользователя и паролем к HTTP прокси-серверу в виде [username]:[password].
                }
            }
            if($this->config->get('userAgent'))
            {
                curl_setopt($ch, CURLOPT_USERAGENT, $this->config->get('userAgent'));
            }else
            {
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36");
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            $out = curl_exec($ch);

            $this->currentUrl = $this->_rawurldecode(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
            if ($this->config->get('getContentWriteLogsOn')) {
                #$this->curlGetInfo2File($this->tmpDir . "curlGetInfo2File" . md5($url) . ".txt", $ch);
            }

            if ($this->config->get('curlinfoHeaderOutOn')) {
                $INFO_HEADER_OUT = preg_replace('~[\r\n]{1,}~', '<br>', curl_getinfo($ch, CURLINFO_HEADER_OUT));
                $this->_echo('<br /><b>CURLINFO_HEADER_OUT</b>:<br><i>' . $INFO_HEADER_OUT . '</i><hr>');
                file_put_contents($this->tmpDir . "curlinfo_header_out_" . md5($url) . ".txt", curl_getinfo($ch, CURLINFO_HEADER_OUT));
                $this->_echo('<br /><b>curl_error</b>: <br><i>' . curl_error($ch) . '</i><hr>');
                file_put_contents($this->tmpDir . "curl_error.txt", curl_error($ch));
                $this->curlGetInfo2File($this->tmpDir . "curlGetInfo2File" . md5($url) . ".txt", $ch);

            }

            curl_close($ch);

        } elseif ($this->requestMethod == 1) // file_get_contents
        {
            $out = file_get_contents($this->_rawurlencode($url));
        } else // fsockopen
        {
            $out = $this->getContentUrlSockOpen($this->_rawurlencode($url));
        }
        if ($this->config->get('getContentWriteLogsOn'))  file_put_contents($this->tmpDir . md5($url) . ".html", $out);
        if ($this->config->get('stopTime')) sleep($this->config->get('stopTime'));
        return $out;
    }


    function curlGetInfo2File($target_file, $ch)
    {

        ob_start();
        print_r(curl_getinfo($ch));
        $info = ob_get_contents();
        ob_end_clean();

        $fp = fopen($target_file, "w+");
        fwrite($fp, strip_tags($info));
        fclose($fp);
    }


    function PHPInfo2File($target_file)
    {

        ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_end_clean();

        $fp = fopen($target_file, "w+");
        fwrite($fp, strip_tags($info));
        fclose($fp);
    }


    /**
     * Скачивание файла по URL-ссылке
     *
     * @param mixed $url
     * @param mixed $file
     * @return bool
     */
    function copyUrlFile($url, $file)
    {
        // если файл по пути сохранения уже существует, то удаляем его
        if (is_file($file))
            @unlink($file);

        // для файлов доступных по https-протоколу или если выбран метод CURL
        if (substr_count($url, 'https://') or $this->config->get('saveFileUrlMethod') == '1') {
            if ($this->config->get('curlinfoHeaderOutOn')) {
                $this->_echo('<br /><b>saveFileUrlMethod</b><i>1 (curl) </i> <br />');
                $this->_echo("<br><b>copyUrlFile::url</b> " . $url);
                $this->_echo("<br><b>copyUrlFile::file</b> " . $file);
                $this->_echo("<br><b>copyUrlFile::parse_url(\$url, PHP_URL_PATH)</b> " . parse_url($url, PHP_URL_PATH));
                $this->_echo("<br><b>copyUrlFile::rawurlencode(parse_url(\$url, PHP_URL_PATH))</b> " . rawurlencode(parse_url($url, PHP_URL_PATH)));
            }
            #$url = str_replace('%20', ' ', $url);
            #if (preg_match('/(%[A-Fa-f0-9]{2})+/',$url))  $url=rawurldecode($url);
            #$url = str_replace(parse_url($url, PHP_URL_PATH), rawurlencode(parse_url($url, PHP_URL_PATH)), $url);
            #$url = str_replace(array('%2F','%2C'), array('/',','), $url);
            #$url = str_replace('%2C', ',', $url);
            #$url = str_replace(basename($url), rawurlencode(basename($url)), $url );
            if ($this->config->get('curlinfoHeaderOutOn')) {
                $this->_echo("<br><b>curl_init(\$url)</b> " . $url);
            }
            #$ch = curl_init($url);
            $ch = curl_init($this->_rawurlencode($url));
            $headers[] = "Accept: image/png,image/*;q=0.8,*/*;q=0.5";
            $headers[] = "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8,cs;q=0.7";
            $headers[] = "Connection: keep-alive";
            $headers[] = "Content-Type: image/png";
            #$headers[] = "DNT: 1";
            if ($this->config->get('userAgent'))
                $headers[] = "User-Agent: " . $this->config->get('userAgent');
            if ($this->config->get('curlGzipOn'))
                $headers[] = "Accept-Encoding: gzip";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if ($this->config->get('curlGzipOn'))
                curl_setopt($ch, CURLOPT_ENCODING, "gzip");
            if($this->config->get('userAgent'))
            {
                curl_setopt($ch, CURLOPT_USERAGENT, $this->config->get('userAgent'));
            }else
            {
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36");
            }
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            #curl_setopt($ch, CURLOPT_DEFAULT_PROTOCOL, "https");
            #curl_setopt( $ch, CURLOPT_SSLVERSION, 6);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            if ($this->config->get('curlRedirectOn'))
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            if ($this->config->get('getCopyUrlFileWriteLogsOn')) {
                file_put_contents($this->tmpDir . "curlinfoFile_header_out_" . md5($url) . ".txt", curl_getinfo($ch, CURLINFO_HEADER_OUT));
                #file_put_contents($this->tmpDir . "curlinfo_http_connectcode_" . md5($url) . ".txt", curl_getinfo($ch,  CURLINFO_HTTP_CONNECTCODE));
                #file_put_contents($this->tmpDir . "curlinfo_http_code_" . md5($url) . ".txt", curl_getinfo($ch,   CURLINFO_HTTP_CODE));
                file_put_contents($this->tmpDir . "curl_errorFile.txt", curl_error($ch));
                #file_put_contents($this->tmpDir . "curl_error" . md5($url) . ".txt", curl_error($ch));
                #file_put_contents($this->tmpDir . "curl_getinfoFile" . md5($url) . ".txt", implode("\n", curl_getinfo($ch)));
                $this->curlGetInfo2File($this->tmpDir . "curlGetInfo2File" . md5($url) . ".txt", $ch);

                #$this-> PHPInfo2File($this->tmpDir . "phpinfo.htm");
            }

            if ($this->config->get('curlCookiesOn')) {
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
            }

            if ($this->config->get('curlProxyOn')) {
                // Берём из списка
                if($this->config->get('curlProxyListOn'))
                {
                    // ставим прокси
                    if ($this->config->get('curlProxyHostPort_List')) {
                        $this->_echo('getContent->curlProxyHostPort_List: <b>'.$this->config->get('curlProxyHostPort_List').'</b><br />');
                        $proxy_array = explode("\r", trim($this->config->get('curlProxyHostPort_List')));
                        shuffle($proxy_array);
                        $proxy = array_pop($proxy_array);
                        $this->_echo('getContent->proxy: <b>'.$proxy.'</b><br />');
                        curl_setopt($ch, CURLOPT_PROXY, trim($proxy));
                    }
                }
                else
                {
                    // ставим прокси
                    if ($this->config->get('curlProxyHostPort')) {
                        curl_setopt($ch, CURLOPT_PROXY, $this->config->get('curlProxyHostPort'));
                    }
                }

                // ставим тип прокси
                #  array('0'=>'CURLPROXY_HTTP','1'=>'CURLPROXY_SOCKS5','2'=>'CURLPROXY_SOCKS4A','3'=>'CURLPROXY_SOCKS5_HOSTNAME'), get_option('wpg_' .'curlProxyType'), 1);

                if ($this->config->get('curlProxyType')) {
                    switch ($this->config->get('curlProxyType')) {
                        case 1:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                            break;
                        case 2:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4A);
                            break;
                        case 3:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
                            break;
                        default:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    }
                }
                // авторизация
                if ($this->config->get('curlProxyUserPwd')) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->config->get('curlProxyUserPwd'));    // Стока с именем пользователя и паролем к HTTP прокси-серверу в виде [username]:[password].
                }
            }

            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            #curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 60);
            $contentFile = curl_exec($ch);
            if ($this->config->get('getCopyUrlFileWriteLogsOn'))
                $this-> write_string($this->tmpDir . "jpg_". md5($url).".jpg", $contentFile, "wb");

            if ($this->config->get('curlinfoHeaderOutOn')) {
                $INFO_HEADER_OUT = preg_replace('~[\r\n]{1,}~', '<br>', curl_getinfo($ch, CURLINFO_HEADER_OUT));
                $this->_echo('<br /><b>CURLINFO_HEADER_OUT</b>:<br><i>' . $INFO_HEADER_OUT . '</i><hr>');
            }
            curl_close($ch);
            $fp = fopen($file, 'x');
            fwrite($fp, $contentFile);
            fclose($fp);
        } elseif ($this->config->get('saveFileUrlMethod') == '2') // file_get_contents + file_put_contents
        {
            if ($this->config->get('curlinfoHeaderOutOn')) $this->_echo('<br /><b>saveFileUrlMethod</b>:<i>2</i><br />');
            $contentFile = file_get_contents($url);
            file_put_contents($file, $contentFile);
        } else {
            if ($this->config->get('curlinfoHeaderOutOn')) $this->_echo('<br /><b>saveFileUrlMethod</b>:<i>copy</i><br />');
            if (!copy($url, $file)) {
                // способ 2: сохранение при помощи file_get_contents/file_put_contents
                $contentFile = file_get_contents($url);
                file_put_contents($file, $contentFile);
            }
        }
        return is_file($file);
    }










    /**
     * Get array new links
     *
     * @param array $links
     * @return array
     */
    function getLinks($links, $exists)
    {
        if (!$this->testOn)
            $links = array_diff((array)$links, $exists);
        if (!$this->feed['params']['start_top'])
            $links = array_reverse((array)$links);
        if ($this->feed['params']['start_link'])
            $links = array_slice((array)$links, $this->feed['params']['start_link']);
        if ($this->feed['params']['max_items'])
            $links = array_slice((array)$links, 0, $this->feed['params']['max_items']);
        return $links;
    }

    /**
     * Get URL
     *
     * @param mixed $url
     * @return string
     */
    function getUrl($url)
    {
        if (!substr_count($url, 'http://') and !substr_count($url, 'https://')) {
            $page = $this->currentUrl;
            if ($this->baseHrefs[$page]) {
                $url = rtrim($this->baseHrefs[$page], '/') . '/' . ltrim($url, '/');
            } else {
                $page = 'http://' . parse_url($page, PHP_URL_HOST);
                $url = rtrim($page, '/') . '/' . ltrim($url, '/');
            }
        }
        $url = html_entity_decode($url);
        return $url;
    }





    // Кодирует русские символы и пробел  согласно RFC 3986
    private function _rawurlencode($url)
    {
        static $search, $replace;

        if(mb_detect_encoding($url) == 'UTF-8')
        {
            $r = range(161, 255);
            foreach($r as $s) $search[] = mb_chr($s, 'utf8');
            $replace = array_map('rawurlencode', $search);
        }

        if (!isset($search, $replace)) {
            $search = range(chr(192), chr(255));
            $search[] = chr(184);
            $search[] = chr(168);
            $search[] = ' ';
            $search = array_map(array($this, 'cp1251_to_uft8'), $search);
            $replace = array_map('rawurlencode', $search);
        }
        $url = str_replace($search, $replace, $url);
        return $url;
    }




    // Обратная функция для _rawurlencode()
    private function _rawurldecode($url)
    {
        static $search, $replace;
        if(mb_detect_encoding($url) == 'UTF-8')
        {
            $r = range(161, 255);
            foreach($r as $s) $search[] = rawurlencode(mb_chr($s, 'utf8'));
            $replace = array_map('rawurldecode', $search);
        }

        if (!isset($search, $replace)) {
            $search = range(chr(192), chr(255));
            $search[] = chr(184);
            $search[] = chr(168);
            $search[] = ' ';
            $search = array_map(array($this, 'cp1251_to_uft8'), $search);
            foreach($search as $s) $search_U[] = rawurlencode($s);
            $replace = array_map('rawurldecode', $search_U);
        }
        $url = str_replace($replace, $search, $url);
        return $url;
    }




    /**
     * Get URL for pictures
     *
     * @param mixed $url
     * @return string
     */
    function getImageUrl($url)
    {
        if (substr_count($url, 'http://') or substr_count($url, 'https://')) {
            //
        } else {
            $page = $this->currentUrl;
            if ($this->baseHrefs[$page]) {
                $page = rtrim($this->baseHrefs[$page], '/');
            } else {
                $page = dirname($page);
            }
            if (!substr_count($url, '/'))
                return $page . '/' . $url;
            $page = 'http://' . parse_url($page, PHP_URL_HOST);
            $url = $page . '/' . ltrim($url, '/');
        }
        $url = html_entity_decode($url);
        $url = str_replace('\'', '%27', $url);
        $url = str_replace(' ', '%20', $url);
        #$url = str_replace('é', '%C3%A9', $url);


        if ($this->config->get('curlinfoHeaderOutOn')) {
            $this->_echo("\n<br>" . 'getImageUrl - <a href="' . $url . '" style="color:green; font-weight: bold">' . $url . '</a>');
        }
        return $url;
    }


    /**
     * Search base tag on page
     *
     * @param mixed $url
     * @param mixed $source
     */
    function setBaseHref($url, $html)
    {
        if (preg_match_all('|<base[^>]*href[\s]*=[\s\'\"]*(.*?)[\'\"\s>]|is', $html, $matches, 0, 1)) {
            $this->baseHrefs[$url] = $matches[1][0];
        }
    }

    /**
     * Main process import
     *
     */
    private function _import()
    {
        if ($this->_isTransactionModel() and $this->_current_link !== null) {
            $result = $this->_saveLink($this->_links_list[$this->_current_link]);
            if ($result === null) {
                $this->_saveEmptyRecord($this->_links_list[$this->_current_link]);
            }
            $this->_current_link++;
            if (isset($this->_links_list[$this->_current_link])) {
                return $this;
            } else {
                return true;
            }
        }

        $index = $this->getContent(urldecode($this->feed['url']));

        if (trim($index) == '') {
            $this->_echo('Пустой контент RSS-ленты или индексной HTML-страницы): ' . $this->feed['url'], 2);
            return true;
        }

        $encoding = $this->feed['type'] == 'html' ? $this->feed['html_encoding'] : $this->feed['rss_encoding'];

        // html импорт
        if ($this->feed['type'] == 'html') {

            $index = $this->utf($index, $encoding);

            // обработка пользовательскими шаблонами
            $index = $this->userReplace('index', $index);

            $this->setBaseHref($this->feed['url'], $index);
            $this->currentUrl = $this->feed['url'];

            // поиск ссылок
            if ($this->feed['params']['autoIntroOn'] == 1) {
                // ручной поиск ссылок и анонсов в тексте индексной страницы
                preg_match_all($this->feed['params']['introLinkTempl'], $index, $matches, PREG_SET_ORDER);
                if (!count((array)$matches)) {
                    $this->_echo('Ссылки не найдены!', 1);
                    return true;
                }
                if ($this->feed['params']['orderLinkIntro']) // порядок: анонс, ссылка
                {
                    for ($k = 0; $k < count((array)$matches); $k++) {
                        $this->introTexts[$this->getUrl($matches[$k][2])] = $matches[$k][1];
                    }
                    $numArray = 2;
                } else // порядок: ссылка, анонс
                {
                    for ($k = 0; $k < count((array)$matches); $k++) {
                        $this->introTexts[$this->getUrl($matches[$k][1])] = $matches[$k][2];
                    }
                    $numArray = 1;
                }
            } else {
                # russian /[:alpha:\w-_\/]{16,} and ~isu
                #$this-> _echo($this->feed['links']);
                preg_match_all('~' . $this->feed['links'] . '~is', $index, $matches, PREG_SET_ORDER);
                $numArray = 0;
            }

            if (!count((array)$matches)) {
                $this->_echo('Найдено ссылок: 0', 2);
                return true;
            }
            // + удаляются дубли
            foreach ($matches as $v) {
                $__url = $this->getUrl($v[$numArray]);
                $links[$__url] = $__url;
            }
            $this->_echo('Найдено ссылок: <font color="green"><b>' . count((array)$links) . '</b></font><br />' . implode('<br />', $links) . '<br />');
            $this->feed['link_count'] = count((array)$links);
            $links = $this->getLinks($links);
            $this->_echo('<br /><b>Из них ссылок для текущего импорта: </b><font color="green"><b>' . count((array)$links) . '</b></font><br />' . implode('<br />', $links) . '<br />');
        }
        elseif ($this->feed['type'] == 'rss') // rss
        {
            $index = $this->userReplace('index', $index);
            $xml = simplexml_load_string($index);
            foreach ($xml->channel->item as $item) {
                $title = $this->utf((string)$item->title, $this->feed['rss_encoding']);
                $link = $this->utf((string)$item->link, $this->feed['rss_encoding']);
                $this->rssDescs[$link] = $this->utf((string)$item->description, $this->feed['rss_encoding']);
                $links[$link] = $link;
                $this->titles[$link] = $title;
            }
            $this->_echo('Найдено ссылок: <font color="green"><b>' . count($links) . '</b></font><br />' . implode('<br />', $links) . '<br />');
            $this->feed['link_count'] = count($links);
            $links = $this->getLinks($links);
            $this->_echo('<br /><b>Из них ссылок для текущего импорта: </b><font color="green"><b>' . count($links) . '</b></font><br />' . implode('<br />', $links) . '<br />');
        }
        elseif ($this->feed['type'] == 'vk') // vk
        {
            $index = $this->utf($index, 'windows-1251');
            // обработка пользовательскими шаблонами
            $index = $this->userReplace('index', $index);
            #file_put_contents($this->tmpDir . "_indexVK_.html", $index);
            preg_match_all('~<div class="post_date"><a  class="post_link"  href="/(wall-\d+_\d+)".*?<div class="wall_text">(.*?)<div class="like_wrap _like_wall-\d+_\d+ ">~is', $index, $matches);
            if (!count($matches)) {
                $this->_echo('Найдено постов: 0', 2);
                return true;
            }
            foreach ($matches[1] as $_k => $v) {
                $__url = $this->feed['url'] . '?w=' . $v;
                $links[$__url] = $__url;
                $_buffVK[$__url] = $matches[2][$_k];
            }
            $this->_echo('Найдено постов: <b>' . count($links) . '</b><br />' . implode('<br />', $links) . '<br />');
            $this->feed['link_count'] = count($links);
            $links = $this->getLinks($links);
            $this->_echo('Из них постов для текущего импорта: <b>' . count($links) . '</b><br />' . implode('<br />', $links) . '<br />');
            foreach ($links as $link) {
                $this->content[$link]['text'] = $_buffVK[$link];
                file_put_contents($this->tmpDir . "_buffVK_" . md5($link) . ".html", $_buffVK[$link]);

                $this->content[$link]['tagsScrape'] = '';
                $this->content[$link]['post_date_scrape'] = current_time('mysql');
                   /*
                if($this->feed['params']['post_date_on']) {

                   $this->content[$link]['post_date_scrape'] = strip_tags(preg_replace('~.*?<a class="published_by_date"  class="post_link" .*?>(.*?)</a>.*~is', '$1', $this->content[$link]['text']));

                }else{
                    if ($this->feed['params']['post_date_type'] == 'runtime')  $this->content[$link]['post_date_scrape'] = current_time('mysql');
                }
                                   */
                $this->content[$link]['text'] = preg_replace('~src="/images/~is', ' src="https://vk.com/images/', $this->content[$link]['text']);
                $this->content[$link]['text'] = preg_replace('~src="/emoji~is', ' src="https://vk.com/emoji', $this->content[$link]['text']);
                $this->content[$link]['text'] = preg_replace('~<div id="page_avatar" class="page_avatar">.*?</div>~is', '', $this->content[$link]['text']);
                $this->content[$link]['text'] = preg_replace('~<button .*?</button>~is', '', $this->content[$link]['text']);

                $this->content[$link]['text'] = preg_replace_callback('~<a  aria-label=".*?" onclick="return showPhoto\(\'(\-?\d+_\d+)\', \'wall-\d+_\d+\', (\{.*?;:1\}), event\)" style="width: \d+px; height: \d+px;background-image: url\((.*?)\);" class="page_post_thumb_wrap image_cover.*?" data-photo-id="-\d+_\d+"></a>~is', array($this, "vkImages"), $this->content[$link]['text']);
                $this->content[$link]['text'] = preg_replace('~<a  href="/page(.*?)" onclick="return showWiki\(.*?, false, event, \{queue: 1\}\);" style="width: \d+px; height: \d+px;background-image: url\((.*?)\);" class="page_post_thumb_wrap image_cover .*?></a>~is', '<img src="$2" alt="$1"/>', $this->content[$link]['text']);
                $this->content[$link]['text'] = preg_replace('~<a href="/video-.*? onclick="return showInlineVideo\(\'(-?\d+_\d+)\', \'\w+\', \{.*?\}, event, this\);" style="width: \d+px; height: \d+px;background-image: url\((.*?)\);" class="page_post_thumb_wrap image_cover .*?">.*?</a>~is', '<img src="$2" alt="$1"/>', $this->content[$link]['text']);
                $this->content[$link]['text'] = preg_replace('~<a class="wall_post_more" onclick="hide.*?">Expand text…</a><span style="display: none">~is', '', $this->content[$link]['text']);
                $this->content[$link]['text'] = preg_replace('~<div class="post_video_views_count">.*?</div>~is', '', $this->content[$link]['text']);
                $this->content[$link]['title'] = $this->feed['title'];
                if (trim($this->feed['title']) != '') {
                    if (preg_match('~' . $this->feed['title'] . '~is', $this->content[$link]['text'], $buff)) {
                        if (count($buff) == 2) {
                            $this->content[$link]['title'] = $buff[1];
                        } elseif (count($buff) == 1) {
                            $this->content[$link]['title'] = $buff[0];
                        } else {
                            $this->content[$link]['title'] = $this->getTitleFromVKText($this->content[$link]['text']);
                        }
                    } else {
                        $this->content[$link]['title'] = $this->getTitleFromVKText($this->content[$link]['text']);
                    }
                } else {
                    $this->content[$link]['title'] = $this->getTitleFromVKText($this->content[$link]['text']);
                }
                $this->content[$link]['title'] = strip_tags($this->content[$link]['title']);



                // И сразу сохраняем
                $this->beforeSaveLoop($link);
                $result = $this->save($link);
                if (!$result) {
                    $this->cleanImages();
                    $this->content[$link] = null;
                    if ($result === null) {
                        $this->_saveEmptyRecord($link);
                    }
                }
            }
            return true;
        }
        if (count($links) > 0) {
            $this->_echo('<br><b>Загрузка страниц:</b>');
            if ($this->_isTransactionModel())
            {
                $this->_current_link = 0;
                $this->_links_list = array_values($links);
                return $this;
            }
            else
            {
                foreach ($links as $link)
                {
                    $result = $this->_saveLink($link);
                    if ($result === null)
                    {
                        $this->_saveEmptyRecord($link);
                    }
                }
            }
        }
        return true;
    }




/*
Array
(
    [temp] => Array
        (
            [x] => https://sun9-51.userapi.com/c850236/v850236930/1c16c1/F4jyV0L85xE.jpg
            [y] => https://sun9-49.userapi.com/c850236/v850236930/1c16c2/IEhRfSIhsMs.jpg
            [z] => https://sun9-25.userapi.com/c850236/v850236930/1c16c3/pZPF0S3YQsg.jpg
            [x_] => Array
                (
                    [0] => c850236/v850236930/1c16c1/F4jyV0L85xE
                    [1] => 467
                    [2] => 604
                )

            [y_] => Array
                (
                    [0] => https://sun9-49.userapi.com/c850236/v850236930/1c16c2/IEhRfSIhsMs
                    [1] => 624
                    [2] => 807
                )

            [z_] => Array
                (
                    [0] => https://sun9-25.userapi.com/c850236/v850236930/1c16c3/pZPF0S3YQsg
                    [1] => 650
                    [2] => 841
                )

            [base] => https://sun9-51.userapi.com/
        )

    [queue] => 1
)

*/
    function vkImages($m)
    {
        #var_export($m);
        $js = htmlspecialchars_decode($m[2]);
        $imgs=json_decode($js, true);
        #var_dump($imgs);
        if($imgs['temp']['z'])
        {
            $out = '<img src="'.$imgs['temp']['z'].'" alt="'.$m[1].'"/>';
        }
        elseif($imgs['temp']['y'])
        {
            $out = '<img src="'.$imgs['temp']['y'].'" alt="'.$m[1].'"/>';
        }else
        {
            $out = '<img src="'.$imgs['temp']['x'].'" alt="'.$m[1].'"/>';
        }

        #var_export($out);
        return $out;
    }


    /**
     * Парсит данные по отдельной ссылке и сохраняет в БД
     * Возращает
     * True - если успешно сохранено
     * False - если не удалось сохранить
     * Null - если не найден контент для сохранения
     * @param string $link
     */
    protected function _saveLink($link)
    {
        if ($this->feed['type'] == 'rss' && $this->feed['params']['rss_textmod']) {
            $this->_echo('<br />RSS description tag');
            $page = $this->rssDescs[$link];
        } else {
            $this->_echo('<br />link: <a target="_blank" href="' . $link . '">' . $link . '</a>');
            $page = $this->getContent($link);
            $page = $this->userReplace('page', $page);
            $this->content[$link]['location'] = $this->currentUrl;
            $page = $this->utf($page, $this->feed['html_encoding']);
        }
        if (trim($page) == '') {
            $this->_echo('<font color="red"> пустая страница!</font>');
            $this->_echo(' <font color="red">(' . mb_strlen($page, 'utf-8') . ' Байт)</font>');
            return null;
        } else {
            $this->_echo(' <font color="green">(' . mb_strlen($page, 'utf-8') . ' Байт)</font>');
        }
        //$this->currentUrl = $link;
        $this->setBaseHref($this->currentUrl, $page);
        if ($this->feed['type'] == 'rss' and trim($this->titles[$link]) != '') {
            $this->content[$link]['title'] = $this->titles[$link];
        } else {
            // поиск заголовка
            preg_match('~' . $this->feed['title'] . '~is', $page, $title_matches);
            if (count($title_matches) == 0) {
                $this->_echo('<font color="red"> Заголовок не найден! </font>');
                return null;
            }
            $this->content[$link]['title'] = $title_matches[1];
        }
        if ($this->feed['type'] == 'rss' && $this->feed['params']['rss_textmod']) {
            $text_matches[1] = $this->rssDescs[$link];
        } else {
            // поиск текста
            preg_match('~' . addcslashes($this->feed['text_start'], '&|') . '(.*?)' . addcslashes($this->feed['text_end'], '&|') . '~is', $page, $text_matches);
            #preg_match('~' . addcslashes($this->feed['text_start'], '&|{}[]') . '(.*?)' . addcslashes($this->feed['text_end'], '&|{}[]') . '~is', $page, $text_matches);
            if (count($text_matches) == 0) {
                $this->_echo('<font color="red"> текст не найден!</font>');
                return null;
            }
        }
        $this->content[$link]['text'] = $text_matches[1];
        #$this->_echo('<font color="green"> текст htmlspecialchars: '.htmlspecialchars($text_matches[1]). '</font>');

        // Tags
        if($this->feed['params']['post_tags_on'] AND !$this->feed['params']['tags_mode'])
        {
            #file_put_contents(ABSPATH.'page.htm', $page);
            #file_put_contents(ABSPATH.'page_stripcslashes.htm', stripcslashes($page));
            preg_match_all('~' . addcslashes($this->feed['params']['tagsScrape'], '&|') . '~is', $page, $tagsScrape);
            if (count($tagsScrape) == 0) {
                $this->_echo('<font color="red"> tags не найден! </font>');
                #return null;
            }
            #var_export($tagsScrape);
            $post_tags = $tagsScrape[1];
            if (!is_array($post_tags) || count($post_tags) == 0) {
                $tag_separator = "";
                if(isset($this->feed['params']['tagsScrapeSeparator'])) {
                    $tag_separator = $this->feed['params']['tagsScrapeSeparator'];
                    if ($tag_separator != "" && !empty($post_tags)) {
                        $post_tags = str_replace("\xc2\xa0", ' ', $post_tags);
                        $post_tags = explode($tag_separator, $post_tags);
                        $post_tags = array_map("trim", $post_tags);
                    }
                }
            }
            #var_export($post_tags);
            $this->_echo("<br> tags count: ".count($post_tags));
            $post_tags = array_slice($post_tags, 0, $this->feed['params']['tagsScrapeCount']);
            if (count($post_tags) > 0) {
                $this->content[$link]['tagsScrape'] = $post_tags;
                #var_export($post_tags);
            }
        }elseif($this->feed['params']['tags_mode'])
        {
            $this->_echo('<font color="blue"> Берём теги из файла</font>');
            if($this->feed['params']['tags_file']){
                $tags_file = file($this->feed['params']['tags_file']);
            }else{
                $tags_file = file(wp_upload_dir()['basedir'] . "/post_tags.txt");
            }
            shuffle($tags_file);shuffle($tags_file);shuffle($tags_file);
            $post_tags = explode(",", trim($tags_file[0]) );
            $this->_echo("<br> tags count: ".count($post_tags));
            $post_tags = array_slice($post_tags, 0, $this->feed['params']['tagsScrapeCount']);
            if (count($post_tags) > 0) {
                $this->content[$link]['tagsScrape'] = $post_tags;
            }
        }else
        {
            $this->content[$link]['tagsScrape'] = '';
        }





        // Дата
        if($this->feed['params']['post_date_on'])
        {
            #$this-> _echo ("<br> post_date: " .$this->feed['params']['post_date_scrape']);
            #file_put_contents(ABSPATH.'page.htm', var_export($page, true));
            preg_match('~' . addcslashes($this->feed['params']['post_date_scrape'], '&|') . '~is', $page, $post_date_scrape);
            if (count($post_date_scrape) == 0) {
                $this->_echo('<font color="red"> post_date не найден! </font>');
                return null;
            }
            $post_date = $post_date_scrape[1];
            #var_export($post_date);
            $tmp_post_date = $post_date;
            $post_date = date_parse($post_date);
            if (!is_integer($post_date['year']) || !is_integer(($post_date['month'])) || !is_integer($post_date['day'])) {
                $this->_echo("<br>date can not be parsed correctly. trying translations");
                $this->_echo('<font color="red"> post_date не найден! </font>');
                return null;

                $post_date = $tmp_post_date;
                $post_date = $this->translate_months($post_date);
                $this->_echo("<br>date value: " . $post_date);
                $post_date = date_parse($post_date);
                if (!is_integer($post_date['year']) || !is_integer(($post_date['month'])) || !is_integer($post_date['day']))
                {
                    $this->_echo("<br>translation is not accepted valid");
                    $post_date = '';
                    $this->_echo('<font color="red"> post_date не найден! </font>');
                    return null;
                }
                else
                {
                    $this->_echo("<br>translation is accepted valid");
                    $post_date = date("Y-m-d H:i:s", mktime($post_date['hour'], $post_date['minute'], $post_date['second'], $post_date['month'], $post_date['day'], $post_date['year']));
                }
            } else {
                $this->_echo("<br>date parsed correctly");
                $post_date = date("Y-m-d H:i:s", mktime($post_date['hour'], $post_date['minute'], $post_date['second'], $post_date['month'], $post_date['day'], $post_date['year']));
            }
        } else {
            #$this-> _echo ("<br>post_date_on::runtime<br>");
            #var_export($this->feed['params']['post_date_type']);
            if ($this->feed['params']['post_date_type'] == 'runtime') {
                #$this-> _echo ("<br> post_date: " .$this->feed['params']['post_date_type']);
                $post_date = current_time('mysql');
            } else {
                if ($this->feed['params']['post_date_type'] == 'custom') {
                    $post_date = $meta_vals['scrape_date_custom'][0];
                } else {
                    if ($this->feed['params']['post_date_type'] == 'feed') {
                        $post_date = $rss_item['post_date'];
                    } else {
                        $post_date = '';
                    }
                }
            }
        }
        $this->content[$link]['post_date_scrape'] = $post_date;

        // И сразу сохраняем
        $this->beforeSaveLoop($link);
        $result = $this->save($link);
        if (!$result) {
            $this->cleanImages();
            $this->content[$link] = null;
            return $result;
        }
        return true;
    }


	public function translate_months($str) {
		$languages = array(
			"en" => array(
				"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
			), "de" => array(
				"Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"
			), "fr" => array(
				"Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
			), "tr" => array(
				"Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"
			), "nl" => array(
				"Januari", "Februari", "Maart", "April", "Mei", "Juni", "Juli", "Augustus", "September", "Oktober", "November", "December"
			), "id" => array(
				"Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"
			), "pt-br" => array(
				"Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
			)
		);

		$languages_abbr = $languages;

		foreach ($languages_abbr as $locale => $months) {
			$languages_abbr[$locale] = array_map(array($this, 'month_abbr'), $months);
		}

		foreach ($languages as $locale => $months) {
			$str = str_ireplace($months, $languages["en"], $str);
		}
		foreach ($languages_abbr as $locale => $months) {
			$str = str_ireplace($months, $languages_abbr["en"], $str);
		}

		return $str;
	}


    protected function _saveEmptyRecord($url)
    {
        return true;
    }


    /**
     * Get unique name for file in folder
     *
     * @param mixed $file
     */
    function getMDNameFile($file, $ext)
    {
        if($this->feed['params']['image_name_from_title_on']){
            $file = $this-> mso_slug($file);
        }else
        {
            $file = rawurlencode($file);
        }

        if ($this->config->get('curlinfoHeaderOutOn')) {
            $this->_echo('<br />getMDNameFile file: <b>' . $file . '</b>, file_ext: <b>' . $ext . '</b>');
        }
        #if (!$ext) $ext = 'jpg';
        #if (preg_match('~\?~', $ext)) $ext = 'jpg';
        if($this->feed['params']['image_name_from_title_on']){
            $file = $this->rootPath .
                    $this->config->get('imgPath') .
                    $this->imageDir .
                    #$this-> mso_slug($this->currentTitle)
                    substr($this-> mso_slug($this->currentTitle),0,145)
                    .'-'.
                    substr(md5(microtime() + mt_rand(1, 100)), 0, 7) .
                    ".$ext";
        }else{
            $file = $this->rootPath . $this->config->get('imgPath') . $this->imageDir . md5(microtime() . strval(mt_rand(1, 100))) . ".$ext";
        }
        if (is_file($file)) {
            if ($this->config->get('curlinfoHeaderOutOn')) {
                $this->_echo('<br />IS_FILE: <b>' . $file . '</b>, file_ext: <b>' . $ext . '</b> or [No preg_replace]: <b>' . pathinfo($file, PATHINFO_EXTENSION) . '</b><br/> ');
            }
            return $this->getMDNameFile($file, $ext);
        }
        return $file;
    }



    /**
     * Resize image
     *
     * @param mixed $input
     * @param mixed $output
     * @param mixed $width
     * @param mixed $height
     * @param mixed $quality
     */
    function imageResize($input, $output, $width, $height = 0, $quality = 100)
    {
        $input_size = getimagesize($input);
        // only width
        if ($height == 0) {
            $input_ratio = $input_size[0] / $input_size[1];
            $height = $width / $input_ratio;
            if ($input_size[0] < $width) {
                if ($input != $output)
                    copy($input, $output);
                return true;
            }
        } else {
            $input_ratio = $input_size[0] / ($input_size[1] ? :1);

            $ratio = $width / $height;
            if ($ratio < $input_ratio) {
                $height = $width / $input_ratio;
            } else {
                $width = $height * $input_ratio;
            }
            if (($input_size[0] < $width) && ($input_size[1] < $height)) {
                if ($input != $output)
                    copy($input, $output);
                return true;
            }
        }
        // create empty picture
        $dest_image = imagecreatetruecolor($width, $height);

        if ($input_size[2] == 1)
            $i_image = imagecreatefromgif($input);
        if ($input_size[2] == 2)
            $i_image = imagecreatefromjpeg($input);
        if ($input_size[2] == 3)
            $i_image = imagecreatefrompng($input);
        if ($input_size[2] == 18)
            $i_image = imagecreatefromwebp($input);

        if (!imagecopyresampled($dest_image, $i_image, 0, 0, 0, 0, $width, $height, $input_size[0], $input_size[1])) {
            return false;
        }
        if (file_exists($output))
            unlink($output);
        if ($input_size[2] == 1)
            imagegif($dest_image, $output);
        if ($input_size[2] == 2)
            imagejpeg($dest_image, $output, $quality);
        if ($input_size[2] == 3)
            imagepng($dest_image, $output);
        if ($input_size[2] == 18)
            imagejpeg($dest_image, $output);

        imagedestroy($dest_image);
        imagedestroy($i_image);
        return true;
    }

    /**
     * Crop image
     *
     * @param mixed $input
     * @param mixed $output
     * @param mixed $width
     * @param mixed $height
     * @param mixed $quality
     */
    function imageCrop($input, $output, $width, $height, $quality = 100)
    {
        $input_size = getimagesize($input);
        if ($input_size[2] == 1)
            $image = imagecreatefromgif($input);
        if ($input_size[2] == 2)
            $image = imagecreatefromjpeg($input);
        if ($input_size[2] == 3)
            $image = imagecreatefrompng($input);
        if ($input_size[2] == 18)
            $i_image = imagecreatefromwebp($input);

        $image_width = imagesx($image);
        $image_height = imagesy($image);
        if ($image_width / $image_height > $width / $height) {
            $thumb_width = $image_width * ($height / $image_height);
            $thumb_height = $height;
        } else {
            $thumb_width = $width;
            $thumb_height = $image_height * ($width / $image_width);
        }

        $thumb_image = imagecreatetruecolor($thumb_width, $thumb_height);
        imagecopyresampled($thumb_image, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $image_width, $image_height);
        #imagecopyresampled($thumb_image, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $image_width, $image_height);
        /*
        bool imagecopyresampled ( $dst_image , $src_image , $dst_x , $dst_y , $src_x , $src_y , $dst_w , $dst_h , $src_w , $src_h )

        imagecopyresampled() копирует прямоугольную часть одного изображения на другое изображение, интерполируя значения пикселов таким образом, чтобы уменьшение размера изображения не уменьшало его четкости.

         Другими словами, imagecopyresampled() берет прямоугольный участок из src_image с шириной src_w и высотой src_h на координатах src_x,src_y и помещает его в прямоугольный участок изображения dst_image шириной dst_w и высотой dst_h на координатах dst_x,dst_y.
         */

        $crop_image = imagecreatetruecolor($width, $height);
        imagecopy($crop_image, $thumb_image, 0, 0, intval(($thumb_width - $width) / 2), intval(($thumb_height - $height) / 2), $width, $height);
        if (is_file($output))
            unlink($output);
        if ($input_size[2] == 1)
            imagegif($crop_image, $output);
        if ($input_size[2] == 2)
            imagejpeg($crop_image, $output, $quality);
        if ($input_size[2] == 3)
            imagepng($crop_image, $output);
        if ($input_size[2] == 18)
            imagejpeg($dest_image, $output);
        imagedestroy($crop_image);
        imagedestroy($image);
        return true;
    }

    /**
     * Generate img tag for images
     *
     * @param mixed $image
     * @param mixed $width
     * @param mixed $height
     * @param mixed $attr
     */
    function getImageResize($image, $width, $height = 0, $adds)
    {
        $imageinfo = getimagesize($image);
        if (!$imageinfo[0] and !$imageinfo[1]) {
            // TO DO***** copy file to server, get size, than delete...
        }
        $out['w'] = $imageinfo[0];
        $out['h'] = $imageinfo[1];
        if ($height == 0) {
            $input_ratio = $imageinfo[0] / $imageinfo[1];
            $height = $width / $input_ratio;
            if ($imageinfo[0] < $width) {
                $width = $imageinfo[0];
                $height = $imageinfo[1];
            }
        } else {
            $input_ratio = $imageinfo[0] / $imageinfo[1];
            $ratio = $width / $height;
            if ($ratio < $input_ratio) {
                $height = $width / $input_ratio;
            } else {
                $width = $height * $input_ratio;
            }
            if (($imageinfo[0] < $width) && ($imageinfo[1] < $height)) {
                $width = $imageinfo[0];
                $height = $imageinfo[1];
            }
        }
        $attr = ' height="' . floor($height) . '" width="' . floor($width) . '"';
        return $this->imageHtmlCode($image, $adds, $attr);
    }

    /**
     *  processing of images from a template
     *
     */
    function imageHtmlCode($url, $adds = '', $attr = '')
    {
        if($this->config->get('imageHtmlCodeLogsOn'))
        {
            $this->_echo('<br>imageHtmlCode %ADDS%: <b style="color:grey;">' . $adds .'</b>');
            $this->_echo('<br>imageHtmlCode %ATTR%: <i>' . $attr . '</i>');
        }

        $this->imagesContentNoSave = $this->feed['params']['no_save_without_pic'] ? true : false;

        if ($this->feed['params']['image_save'] || $this->feed['params']['img_path_method'])
        #if (!$this->testOn && ($this->feed['params']['image_save'] || $this->feed['params']['img_path_method']))
        {
            if ($this->feed['params']['img_path_method'] == '1')
                $url = ltrim($url, '/');
            if ($this->feed['params']['img_path_method'] == '2')
                $url = rtrim(site_url(), '/') . $url;

            if($this->config->get('imageHtmlCodeLogsOn'))
            {
                $this->_echo('<br>imageHtmlCode img_path_method: <i>' . $this->feed['params']['img_path_method'] . '</i>');
                $this->_echo('<br>imageHtmlCode url: <i>' . $url . '</i>');
            }
        }
        return strtr($this->feed['params']['imageHtmlCode'], array(
            '%TITLE%' => htmlentities($this->currentTitle, ENT_COMPAT, 'UTF-8'),
            '%PATH%' => $url,
            '%ADDS%' => $adds,
            '%ATTR%' => $attr
        ));
    }


    /**
     * process the first picture in the anounce
     *
     */
    function introPicOn($file, $save = 0, $adds = '')
    {
        $this->intro_pic_on = 0;
        // saving images on the server
        if ($save) {
            $imageFileInto = $this->getMDNameFile(basename($file), $this-> imageGetExt($file));
            if ($this->copyUrlFile($file, $imageFileInto)) {
                $this->picToIntro = $this->imageHtmlCode($this->config->get('imgPath') . $this->imageDir . basename($imageFileInto), $adds);
                $this->imagesContent[] = $this->config->get('imgPath') . $this->imageDir . basename($imageFileInto);
                if ($this->feed['params']['image_resize']) // resizing
                {
                    if ($this->feed['params']['img_intro_crop']) {
                        $this->imageCrop($imageFileInto, $imageFileInto, $this->feed['params']['intro_pic_width'], $this->feed['params']['intro_pic_height'], $this->feed['params']['intro_pic_quality']);
                        $this->_echo('<br />imageCrop: ' . $this->feed['params']['intro_pic_width'] . ' x ' . $this->feed['params']['intro_pic_height']);
                    } else {
                        $this->imageResize($imageFileInto, $imageFileInto, $this->feed['params']['intro_pic_width'], $this->feed['params']['intro_pic_height'], $this->feed['params']['intro_pic_quality']);
                    }
                }
            }
        } else // without saving
        {
            if ($this->feed['params']['image_resize']) // resizing
            {
                $this->picToIntro = $this->getImageResize($file, $this->feed['params']['intro_pic_width'], $adds, $this->feed['params']['intro_pic_height']);
            } else {
                $this->picToIntro = $this->imageHtmlCode($file, $adds);
            }
        }
    }





	/**
	 * Returns first matched extension from Mime-type,
	 * as mapped from wp_get_mime_types()
	 *
	 * @since 3.5.0
	 *
	 * @param string $mime_type
	 * @return string|false
	 */
	function get_extension( $mime_type = null ) {
		$extensions = explode( '|', array_search( $mime_type, wp_get_mime_types() ) );

		if ( empty( $extensions[0] ) ) {
			return false;
		}

		return $extensions[0];
	}



    /**
     * Get mime_type
     *
     * @param string $file
     * @return ext
     */
    function imageGetExt($file)
    {
        $type = '';
        $size = wp_get_image_mime($file);
        #var_export($size);
        $type = $this-> get_extension($size);
        #var_export($this-> get_extension($size));
        #var_export(wp_get_mime_types());

        if (!$type) $type = 'jpg';

        if ($this->config->get('curlinfoHeaderOutOn'))
        {
            $this->_echo('<br>imageGetExt file: '. $file);
            $this->_echo('<br>imageGetExt type_image: '. $type);
        }

        return $type;

    }

    /**
     * Parsing images in the text
     *
     * @param string $matches
     * @return mixed
     */
    function imageParser($matches)
    {
        $matches[3] = $this->getImageUrl($matches[3]);
        #file_put_contents(ABSPATH.'matches'.md5($matches[3]).'.TXT', var_export($matches, true));
        $this->_echo('<br>imageParser src: <a target="_blank" href="' . $matches[3] . '">' . $matches[3] . '</a> ');
        #$this->_echo('<br />currentTitle: '. $this->currentTitle);
        #var_export(getimagesize($matches[3]));

        // image processing
        if ($this->feed['params']['image_save']) // saving images on the server
        {
            $imageFile = $this->getMDNameFile(basename($matches[3]), $this-> imageGetExt($matches[3]));
            #$this->_echo('<br />imageParser imageFile: <a target="_blank" href="' . $matches[3] . '">' . $imageFile . '</a> ');

            if ($this->copyUrlFile($matches[3], $imageFile))
            {
                // the first picture in the preview
                if ($this->intro_pic_on and ($this->feed['params']['intro_pic_on'] or @$this->feed['params']['image_intro_on'])) $this->introPicOn($imageFile, 1, "{$matches[1]} {$matches[4]}");

                $matches[3] = $this->config->get('imgPath') . $this->imageDir . basename($imageFile);
                $this->imagesContent[] = $matches[3];
                $this->_echo('<a href="' . site_url() . $matches[3] . '" style="color:green; font-weight: bold">OK</a>' . ' <br>imageParser <i style="color:Gold;background-color: black;"><b>newfilename</b></i>:<b> '.basename($imageFile). '</b>');

                // resizing
                if ($this->feed['params']['image_resize'])
                {
                    if ($this->feed['params']['img_text_crop'])
                    {
                        $this->imageCrop($imageFile, $imageFile, $this->feed['params']['text_pic_width'], $this->feed['params']['text_pic_height'], $this->feed['params']['text_pic_quality']);
                        $this->_echo('<br />imageCrop: ' . $this->feed['params']['text_pic_width'] . ' x ' . $this->feed['params']['text_pic_height']);
                    } else {
                        $this->imageResize($imageFile, $imageFile, $this->feed['params']['text_pic_width'], $this->feed['params']['text_pic_height'], $this->feed['params']['text_pic_quality']);

                    }
                }
                #var_export($this->imagesContent);
                return $this->imageHtmlCode($matches[3], "{$matches[1]} {$matches[4]}");
            } else {
                $this->_echo(' - <b style="color:red">Ошибка сохранения файла картинки!</b>');
            }
        }
        else // without saving
        {
            if ($this->feed['params']['image_resize']) // resizing
            {
                if ($this->intro_pic_on and $this->feed['params']['intro_pic_on'])
                    $this->introPicOn($matches[3], 0, "{$matches[1]} {$matches[4]}");
                return $this->getImageResize($matches[3], $this->feed['params']['text_pic_width'], "{$matches[1]} {$matches[4]}", $this->feed['params']['text_pic_height']);
            } else // without resizing
            {
                if ($this->intro_pic_on and $this->feed['params']['intro_pic_on'])
                    $this->introPicOn($matches[3], 0, "{$matches[1]} {$matches[4]}");
                return $this->imageHtmlCode($matches[3], "{$matches[1]} {$matches[4]}");
            }
        }
    }




    function genALT($alt, $title, $n_pic)
    {
        # из атрибута title
        if($this->feed['params']['image_alt_from_attr_title'])
        {
            if(!empty($title) )
            {
                #$alt = 'NEW_ALT_FROM_attr_TITLE_'. $title . $n_pic;
                $alt = ''. $title . $n_pic;
            }
            else
            {
                #$alt = 'NEW_ALT_from_current_title_'. $this->currentTitle . $n_pic;
                $alt = ''. $this->currentTitle . $n_pic;
            }
        }else
        {
            #$alt = 'NEW_ALT_from_current_title_'. $this->currentTitle . $n_pic;
            $alt = ''. $this->currentTitle . $n_pic;
        }
        return $alt;
    }


    /**
     * Search for images in the text
     * Переделан разбор simplehtmldom parser
     *
     * @param mixed $text
     * @return mixed
     */

    function imageProcessor($text)
    {
        $html = str_get_html($text);

        if(!$html){
            $this->_echo('<br>imageProcessor::str_get_html false<b>'. $html . '</b>'."");
            return false;
        }

        foreach($html->find('img') as $n_pic => $img)
        {
            # ограничить вывод картинок
            if ($this->feed['params']['limit_image_output_on'] AND $n_pic != 0) {
                $img->outertext = '';
                continue;
            }

            $img->getAllAttributes();

            # class
            if($this->feed['params']['image_class_name_on'] and $this->feed['params']['image_class_name_custom'])
            {
               $img->attr['class'] = $this->feed['params']['image_class_name_custom'];
            }

            # Сгенерировать alt
            if($this->feed['params']['image_alt_make_on'])
            {
                $alt = $img->attr['alt'];
                $title = $img->attr['title'];

                # Заменять alt
                if($this->feed['params']['image_alt_replace'] )
                {
                  #  попытаться сгенерить
                  $alt = $this-> genALT($alt, $title, $n_pic);
                }
                # Не заменять
                else
                {
                    # Если пустой попытаться сгенерить
                    if(empty($alt))
                    {
                        $alt = $this-> genALT($alt, $title, $n_pic);
                    }else
                    {
                     # всё оставить как есть
                    }
                }
                $img->attr['alt'] = $alt;
            }

            /*
            # Сгенерировать title
            if($this->feed['params']['image_title_make_on'])
            {
                if( empty($img->attr['title']) and !empty($img->attr['alt']) )
                {
                    $img->attr['title'] = 'NEW_title_FROM_ALT_'. $img->attr['alt'] . $n_pic;
                }
                elseif( empty($img->attr['title']) )
                {
                    $img->attr['title'] = 'NEW_TITLE_'. $this->currentTitle . $n_pic;
                }
            }
            */



            #$img->getAllAttributes();

            /*
            if($this->config->get('imageProcessorLogsOn'))
            {

                $this->_echo('<br>imageProcessor src: <b>'. $img->src . '</b>'."");
                $this->_echo('<br>imageProcessor class: <b>'. $img->attr['class'] . '</b>'."");
                $this->_echo('<br>imageProcessor alt: <b>'. $img->attr['alt'] . '</b>'."");
            }
            */

            //testing that it worked
            #file_put_contents(ABSPATH.'getAllAttributes_'.md5($img->src).'.TXT', var_export($img->attr, true));
            #file_put_contents(ABSPATH.'outertext_'.md5($img->src).'.TXT', var_export($img->outertext, true));

            $ADDS = array();
            $ATTR = array();

            /*
            # lazy
            if(preg_match ('~data:image~', $img->src) )
            {
                $img->lazy = true;
            }
            */
            /*
            if(preg_match ('~data:image~', $img->src) )
            {
                $last = '';

                if($this->config->get('imageProcessorLogsOn')){
                    $this->_echo('<br>imageProcessor src lazy: '. $img->src . ''."\n<br>");
                    #var_export($img->attr);
                }
                foreach($img->attr as $attr => $val)
                {
                    #$this->_echo('attr: ' . $attr .'=>'. $val ."<br>\n" );
                    if(preg_match ('~srcset~', $attr) )
                    {
                        $this->_echo('<b>attr</b>: ' . $attr .'=>'. $val ."<br>\n" );
                        $srcset_pics = explode(",", $val);
                        #var_export($pics);
                        $last = array_pop($srcset_pics);
                        #var_export($last);
                        if($last)
                        {
                            $img->src = trim($last);
                        }
                    }
                }
            }
            */


            # Удаление не нужных атрибутов
            if ($this->feed['params']['image_attr_delete']) {
                $attr_delete = explode(',',$this->feed['params']['image_attr_delete']);
                $attr_delete[] = 'src';
                #file_put_contents(ABSPATH.'attr_delete_'.md5($img->src).'.TXT', var_export($attr_delete, true));
                #var_export($attr_delete, true);
            }else
            {
                $attr_delete[] = 'src';
            }

            foreach($img->attr as $attr => $val)
            {
                # %ATTR% - дополнительные атрибуты картинок
                if(in_array($attr, array('align','alt','border','hspace','ismap','longdesc','lowsrc','vspace','usemap')))
                {
                   $ATTR[] = $attr.'="'.$val.'"';
                }
                elseif(in_array($attr, $attr_delete))
                #elseif(in_array($attr, array('src','srcset','data-original','data-src','data-srcset', 'data-lazy-type', 'sizes')))
                {
                    if($this->config->get('imageProcessorLogsOn'))
                        $this->_echo('<br>imageProcessor attr_delete: '. $attr . ''."\n<br>");

                }
                else
                {
                   # Универсальные атрибуты %ADDS% - атрибуты элемента IMG из исходника
                   $ADDS[] = $attr.'="'.$val.'"';
                }
            }
            $m[0]= preg_replace('~[\n\r\t]+~is', ' ', $img->outertext);
            $m[1]= implode(' ',$ADDS);
            $m[2]= $n_pic;
            $m[3]= trim($img->src);
            $m[4]= implode(' ',$ATTR);

            /*
            if(preg_match ('~data:image~', $img->src) )
            {
               $img->outertext = '';
            }else
            {
                $img->outertext = $this-> imageParser($m);
            }
            */

            $img->outertext = $this-> imageParser($m);

            # где больше 1 фото, вставляем <!--nextpage-->
            if ($this->feed['params']['image_nextpage_quan'] > 1 and  $n_pic > 1) {
                if($n_pic % $this->feed['params']['image_nextpage_quan'] == 0)
                {
                    $this->_echo(' где больше '.$this->feed['params']['image_nextpage_quan'].' img, вставляем &#60;!--nextpage--&#62;<br>');
                    $img->outertext .= '<!--nextpage-->';
                }
            }

            if($this->config->get('imageProcessorLogsOn'))
            {

                $this->_echo('<br>imageProcessor src: <b>'. $img->src . '</b>'."");
                $this->_echo('<br>imageProcessor class: <b>'. $img->attr['class'] . '</b>'."");
                $this->_echo('<br>imageProcessor alt: <b>'. $img->attr['alt'] . '</b>'."");
            }

            $this->_echo('<br>');

        }
        $text = $html->save();
        $html->clear();// подчищаем за собой
        return $text;
    }






    /**
     * Search for images in the text
     *
     * @param mixed $text
     * @return mixed
     */
    function imageProcessor_old($text)
    {
        #$this->images = array();
        // если включена обработка пробелов в путях картинок
        if ($this->feed['params']['image_space_on']) {
            $text = preg_replace_callback('|<img(.*?)src(.*?)=[\s\'\"]*(.*?)[\'\"](.*?)>|is', array(
                &$this,
                'imageParser'
            ), $text);
        } else {
            $text = preg_replace_callback('|<img(.*?)src(.*?)=[\s\'\"]*(.*?)[\'\"\s](.*?)>|is', array(
                &$this,
                'imageParser'
            ), $text);
        }
        return $text;
    }




    # функция преобразует русские и украинские буквы в английские
    # также удаляются все служебные символы
    function mso_slug($slug)
    {
        // таблица замены
        $repl = array(
        "А"=>"a", "Б"=>"b",  "В"=>"v",  "Г"=>"g",   "Д"=>"d",
        "Е"=>"e", "Ё"=>"jo", "Ж"=>"zh",
        "З"=>"z", "И"=>"i",  "Й"=>"j",  "К"=>"k",   "Л"=>"l",
        "М"=>"m", "Н"=>"n",  "О"=>"o",  "П"=>"p",   "Р"=>"r",
        "С"=>"s", "Т"=>"t",  "У"=>"u",  "Ф"=>"f",   "Х"=>"h",
        "Ц"=>"c", "Ч"=>"ch", "Ш"=>"sh", "Щ"=>"shh", "Ъ"=>"",
        "Ы"=>"y", "Ь"=>"",   "Э"=>"e",  "Ю"=>"ju", "Я"=>"ja",

        "а"=>"a", "б"=>"b",  "в"=>"v",  "г"=>"g",   "д"=>"d",
        "е"=>"e", "ё"=>"jo", "ж"=>"zh",
        "з"=>"z", "и"=>"i",  "й"=>"j",  "к"=>"k",   "л"=>"l",
        "м"=>"m", "н"=>"n",  "о"=>"o",  "п"=>"p",   "р"=>"r",
        "с"=>"s", "т"=>"t",  "у"=>"u",  "ф"=>"f",   "х"=>"h",
        "ц"=>"c", "ч"=>"ch", "ш"=>"sh", "щ"=>"shh", "ъ"=>"",
        "ы"=>"y", "ь"=>"",   "э"=>"e",  "ю"=>"ju",  "я"=>"ja",

        # украина
        "Є" => "ye", "є" => "ye", "І" => "i", "і" => "i",
        "Ї" => "yi", "ї" => "yi", "Ґ" => "g", "ґ" => "g",

        # беларусь
        "Ў"=>"u", "ў"=>"u", "'"=>"",

        # румынский
        "ă"=>'a', "î"=>'i', "ş"=>'sh', "ţ"=>'ts', "â"=>'a',

        "«"=>"", "»"=>"", "—"=>"-", "`"=>"", " "=>"-",
        "["=>"", "]"=>"", "{"=>"", "}"=>"", "<"=>"", ">"=>"",

        "?"=>"", ","=>"", "*"=>"", "%"=>"", "$"=>"",

        "@"=>"", "!"=>"", ";"=>"", ":"=>"", "^"=>"", "\""=>"",
        "&"=>"", "="=>"", "№"=>"", "\\"=>"", "/"=>"", "#"=>"",
        "("=>"", ")"=>"", "~"=>"", "|"=>"", "+"=>"", "”"=>"", "“"=>"",
        "'"=>"",

        "’"=>"",
        "—"=>"-", // mdash (длинное тире)
        "–"=>"-", // ndash (короткое тире)
        "™"=>"tm", // tm (торговая марка)
        "©"=>"c", // (c) (копирайт)
        "®"=>"r", // (R) (зарегистрированная марка)
        "…"=>"", // (многоточие)
        "“"=>"",
        "”"=>"",
        "„"=>"",

        );

        $slug = strtr(trim($slug), $repl);
        $slug = htmlentities($slug); // если есть что-то из юникода
        $slug = strtr(trim($slug), $repl);
        $slug = strtolower($slug);

        # разрешим расширение .html
        $slug = str_replace('.htm', '@HTM@', $slug);
        $slug = str_replace('.', '', $slug);
        $slug = str_replace('@HTM@', '.htm', $slug);

        $slug = str_replace('---', '-', $slug);
        $slug = str_replace('--', '-', $slug);

        $slug = str_replace('-', ' ', $slug);
        $slug = str_replace(' ', '-', trim($slug));
        return $slug;
    }

    /**
     * Create a directory for images
     *
     */
    function mkImageDir()
    {
        $this->imageDir = date('Ymd') . '/';
        $imageDirPath = $this->rootPath . $this->config->get('imgPath') . $this->imageDir;
        if (file_exists($imageDirPath))
            return;
        if (!file_exists($this->rootPath . $this->config->get('imgPath')))
            mkdir($this->rootPath . $this->config->get('imgPath'), 0777);
        mkdir($imageDirPath, 0777);
    }

    /**
     *
     *
     * @param mixed $id
     * @param mixed $url
     */
     /*
    function saveContentRecord($id, $url)
    {
    }
    */

    function getTitleFromVKText($text)
    {
        $text = strip_tags($text);
        $__introtext = preg_replace('/\s{2,}/', ' ', trim($text));
        $__introtext = explode(' ', $__introtext);
        $__introtext = array_slice($__introtext, 0, $this->feed['params']['title_words_count']);
        return implode(' ', $__introtext);
    }





    /**
     * Saving content
     * Возращает
     * True - если успешно сохранено
     * False - если не удалось сохранить
     * Null - если не найден контент для сохранения
     * @param mixed $url
     */
    function save($url)
    {
        $record =& $this->content[$url];

        if ($this->config->get('getContentWriteLogsOn')) {
            #file_put_contents($this->tmpDir . "content_url_before_" . md5($url) . ".html", var_export($record, true));
        }

        // если определение анонса в ручную:
        if ($this->feed['params']['autoIntroOn'] == 1) {
            $this->introTexts[$url] = $this->userReplace('intro', $this->introTexts[$url]);
            $record['text'] = $this->introTexts[$url] . '{{{MORE}}}' . $record['text'];
        }

        $this->_echo('<br />title: <a target="blank" href="' . $url . '">' . $record['title'] . '</a>');

        // обработка фильтр-слов
        if ($this->feed['params']['filter_words_on']) {
            if ($this->feed['params']['filter_words_where'] == 'title') {
                $filter_words_text = $record['title'];
            } elseif ($this->feed['params']['filter_words_where'] == 'text') {
                $filter_words_text = $record['text'];
            } elseif ($this->feed['params']['filter_words_where'] == 'title+text') {
                $filter_words_text = "{$record['title']} {$record['text']}";
            }

            preg_match_all("/(" . $this->filterWordsSave . ")/is", $filter_words_text, $_word_search);
            // не сохранять материалы
            if ($this->feed['params']['filter_words_save']) {
                if (count($_word_search[1])) {
                    $this->_echo('<br /><i>Материал будет не сохранен по причине наличия следующих фильтр-слов в нем: ' . implode(', ', $_word_search[1]) . '</i>');
                    return null;
                } else {
                    $this->_echo('<br /><i>Материал будет сохранен по причине отсутствия фильтр-слов в нем' . '</i>');
                }
            } elseif (!$this->feed['params']['filter_words_save']) {
                if (count($_word_search[1])) {
                    $this->_echo('<br /><i>Материал будет сохранен по причине наличия следующих фильтр-слов в нем: ' . implode(', ', $_word_search[1]) . '</i>');
                } else {
                    $this->_echo('<br /><i>Материал будет не сохранен по причине отсутствия фильтр-слов в нем' . '</i>');
                    return null;
                }
            }
        }

        // отображает то что был редирект
        //if ($url != $record['location']) $this->_echo(" redirect to --> <a href=\"{$record['location']}\">{$record['location']}</a>");
        $this->currentUrl = isset($record['location']) ? $record['location'] : '';

        // удаление script и style


        if (!$this->feed['params']['js_script_no_del'])
            $record['text'] = preg_replace('|<script.*?</script>|is', '', $record['text']);

        if (!$this->feed['params']['css_no_del'])
            $record['text'] = preg_replace('|<style.*?</style>|is', '', $record['text']);

        // обработка пользовательскими шаблонами текста страницы
        $record['text'] = $this->userReplace('text', $record['text']);



        // обработка пользовательскими шаблонами заголовка
        $record['title'] = $this->userReplace('title', $record['title']);

        /*
        Это может быть одна из констант MB_CASE_UPPER, MB_CASE_LOWER, MB_CASE_TITLE, MB_CASE_FOLD, MB_CASE_LOWER_SIMPLE, MB_CASE_UPPER_SIMPLE, MB_CASE_TITLE_SIMPLE или MB_CASE_FOLD_SIMPLE.
        */

        if ($this->feed['params']['case_title'] == 1) $record['title'] = mb_convert_case( $record['title'], MB_CASE_TITLE);
        if ($this->feed['params']['case_title'] == 2) $record['title'] = mb_convert_case( $record['title'], MB_CASE_UPPER);


        // удаление HTML тегов из заголовка
        $record['title'] = trim(strip_tags(html_entity_decode($record['title'], ENT_QUOTES, 'utf-8')));


        $this->currentTitle = $record['title'];

        // удаление HTML тегов из текста
        if ($this->feed['params']['strip_tags']) {
            $record['text'] = strip_tags($record['text'], $this->feed['params']['allowed_tags']);
            #$record['text'] = trim(strip_tags($record['text'], $this->feed['params']['allowed_tags']));
        }

        // Обработка изображений
        $this->_echo('<br /><b>Обработка изображений в тексте:</b>');
        if ($this->imagesContentNoSave)
        {
            if (preg_match_all('~<img[^>]+>~i', $record['text']))
            #if (preg_match_all("~\.(?:jp(?:e?g|e|2)|gif|png|tiff?|bmp|ico)~i", $record['text']))
            {
                $this->_echo("<i><b>Изображение найдено.</b></i>");
            }
            else
            {
                $this->_echo("<i>Материл не будет сохранен по причине отсутсвия в нем картинок! (см. опцию: Не сохранять материал без картинок)</i><hr>");
                return null;
            }
        }

        $this->intro_pic_on = 1;
        if (!$this->testOn and $this->feed['params']['image_save'])
            $this->mkImageDir();
        $record['text'] = $this->imageProcessor($record['text']);

        if ($this->feed['params']['fulltext_size_on'] == 1) {
            $record['text'] = $this->postFullTextSize($record['text'], $url);
        }



        if ($this->config->get('getContentWriteLogsOn')) {
            file_put_contents($this->tmpDir . "txt_" . md5($url) . ".html", var_export($record, true));
        }
        #file_put_contents(ABSPATH.'content_'.md5($url).'.txt', var_export($record, true));


        // перевод
        $this->_pluginTranslate($record);
        $record['text'] = $this->userReplace('text_after_translate', $record['text']);

        if ($this->config->get('getContentWriteLogsOn')) {
            #file_put_contents($this->tmpDir . "txt_aft_trans_" . md5($url) . ".html", var_export($record, true));
        }

        $this->_pluginSynonymize($record);
        $record['text'] = $this->userReplace('text_after_synonymize', $record['text']);
        if ($this->config->get('getContentWriteLogsOn')) {
            #file_put_contents($this->tmpDir . "txt_aft_syn_" . md5($url) . ".html", var_export($record, true));
        }

        if (empty($record['text'])) {
            $this->_echo('<br /><i>Материл не будет сохранен по причине отсутствия в нем контента</i>');
            return null;
        }
        return true;
    }








    function postFullTextSize($text, $url)
    {
        $postFulltextSymbolEnd = trim($this->feed['params']['postFulltextSymbolEnd']) == '' ? ' ' : $this->feed['params']['postFulltextSymbolEnd'];
        $fulltext = preg_replace('~<.*?>~is', " $0 \n", $text);

        $fulltext = str_replace(array("\n", "\r", "\t", "\0", "\x0B"), '', trim(strip_tags($fulltext)));
        #$fulltext = preg_replace("~[\n\r\t]+~", ' ', trim(strip_tags($fulltext)));
        $fulltext = preg_replace("~[\s]{2,}~", ' ', $fulltext);
        $fulltext = str_ireplace('&nbsp;', ' ', $fulltext);

        $encoding = mb_detect_encoding($text, "auto");
        $h = mb_substr($fulltext, 0, $this->feed['params']['post_full_size'], $encoding);
        $substr = mb_strripos($h, $postFulltextSymbolEnd, 0, $encoding);
        $fulltext = mb_substr($fulltext, 0, $substr, $encoding);
        preg_match('|(\S{1,})\s{1,}(\S{1,})\s{1,}(\S{1,})\s{0,}$|is', $fulltext, $buff);

        preg_match('|.*?' . $buff[1] . '.*?' . $buff[2] . '.*?' . $buff[3] . '|is', $text, $buff);
        #var_export($buff);
        $fulltext = $buff[0];
        if ($this->config->get('getContentWriteLogsOn')) {
            #$this->_echo('<br /><b>text: </b>' . strip_tags($text));
            #$this->_echo('<br /><b>postFullTextSize: </b> <i>' . $text . '</i> ');
            file_put_contents($this->tmpDir . "fulltext" . md5($url) .".html", $fulltext);
        }
        $fulltext = force_balance_tags( $fulltext . $postFulltextSymbolEnd);
        return $fulltext ;
    }




    /**
     * Processing of user templates
     *
     * @param mixed $key
     * @param mixed $text
     * @return mixed
     */
    function userReplace($key, $text)
    {
        #$this->_echo('<br>key:  <b>' . htmlspecialchars($key, ENT_QUOTES) . '</b>, ');
        if (!$this->feed['params']['user_replace_on'])
            return $text;
        if (!is_array($this->feed['params']['replace']))
            return $text;
        if (isset($this->feed['params']['replace'][$key]) AND is_array($this->feed['params']['replace'][$key])) {

            #var_export($this->feed['params']['replace'][$key]);

            foreach ($this->feed['params']['replace'][$key] as $k=>$v) {
                if ($v['limit'] == '')   $v['limit'] = -1;
                #var_export($k);
                #var_export($v['replace']);
                if($v['replace'] === '###newline###')
                {
                    $v['replace'] = "\n";
                }
                $text = preg_replace($v['search'], $v['replace'], $text, $v['limit']);
                if ($this->config->get('getContentWriteLogsOn') and $this->config->get('curlinfoHeaderOutOn')) {

                    $this->_echo('<br>type:  <b>' . htmlspecialchars($key, ENT_QUOTES) . '</b>, ');
                    $this->_echo('№:  <b>' . htmlspecialchars($k, ENT_QUOTES) . '</b>, ');
                    $this->_echo('search:  <b>' . htmlspecialchars($v['search'], ENT_QUOTES) . '</b>, ');
                    $this->_echo('replace:  <b>' . htmlspecialchars($v['replace'], ENT_QUOTES) . '</b> <br>');
                    file_put_contents($this->tmpDir . "userReplace_" . $key ."_". md5($text) . ".html", $text);

                }
            }
        }
        return $text;
    }

    /**
     * Clearing the text
     *
     * @param mixed $text
     * @return string
     */
    function textClean($text)
    {
        $text = preg_replace('~<script[^>]*?>.*?</script>~si', ' ', $text);
        $text = preg_replace('~<style[^>]*?>.*?</style>~si', ' ', $text);
        $cleanSymbol = array(
            "\n",
            "\r",
            "\t",
            '`',
            '"',
            '>',
            '<'
        );
        $text = html_entity_decode($text);
        $text = str_replace($cleanSymbol, ' ', strip_tags($text));
        //$text = preg_replace('|[\s]{1,}|si', ' ', $text);
        return trim($text);
    }

    /**
     * Transliteration of text
     *
     * @param mixed $str
     * @return string
     */
    function translit($str)
    {
        $trans = array(
            'а' => 'a',
            'А' => 'A',
            'б' => 'b',
            'Б' => 'B',
            'в' => 'v',
            'В' => 'V',
            'г' => 'g',
            'Г' => 'G',
            'д' => 'd',
            'Д' => 'D',
            'е' => 'e',
            'Е' => 'E',
            'ё' => 'e',
            'Ё' => 'E',
            'ж' => 'j',
            'Ж' => 'J',
            'з' => 'z',
            'З' => 'Z',
            'и' => 'i',
            'И' => 'I',
            'й' => 'i',
            'Й' => 'I',
            'к' => 'k',
            'К' => 'K',
            'л' => 'l',
            'Л' => 'L',
            'м' => 'm',
            'М' => 'M',
            'н' => 'n',
            'Н' => 'N',
            'о' => 'o',
            'О' => 'O',
            'п' => 'p',
            'П' => 'P',
            'р' => 'r',
            'Р' => 'R',
            'с' => 's',
            'С' => 'S',
            'т' => 't',
            'Т' => 'T',
            'у' => 'y',
            'У' => 'Y',
            'ф' => 'f',
            'Ф' => 'F',
            'х' => 'h',
            'Х' => 'H',
            'ц' => 'c',
            'Ц' => 'C',
            'ч' => 'ch',
            'Ч' => 'CH',
            'ш' => 'sh',
            'Ш' => 'SH',
            'щ' => 'sh',
            'Щ' => 'SH',
            'ъ' => '',
            'Ъ' => '',
            'ы' => 'y',
            'Ы' => 'Y',
            'ь' => '',
            'Ь' => '',
            'э' => 'e',
            'Э' => 'E',
            'ю' => 'u',
            'Ю' => 'U',
            'я' => 'ia',
            'Я' => 'IA',
            ' ' => '-'
        );
        return strtr($str, $trans);
    }

    /**
     * Generate keywords
     *
     * @param string $content
     * @return string
     */
    function genTagKeywords($content)
    {
        $content = $this->textClean($content);
        if (function_exists('mb_strtolower')) {
            $content = mb_strtolower($content, 'utf-8');
        } else {
            $content = strtolower($content);
        }
        preg_match_all('|[a-zA-Zа-яА-Я]{3,}|ui', $content, $buff);
        $buff = $buff[0];
        if (!count($buff))
            return '';
        array_unique($buff);
        $words = array_count_values($buff);
        $words = array_keys($words);
        $keyWordsStopList = str_replace(array(
            "\t",
            "\n",
            "\r"
        ), '', $this->feed['params']['metaKeysStopList']);

        $keyWordsStopList = str_replace(array(
            ', ',
            ' ,'
        ), ',', $keyWordsStopList);

        $keyWordsStopList = explode(',', $keyWordsStopList);
        if (count($keyWordsStopList))
            $words = array_diff($words, $keyWordsStopList);
        $words = array_slice($words, 0, $this->feed['params']['metaKeysSize']);
        if (count($words) > 0) {
            return implode(', ', $words);
        }
    }

    /**
     * Generate description
     *
     * @param string $content
     * @return string
     */
    function genTagDescription($content)
    {
        $content = $this->textClean($content);
        if (function_exists('mb_substr')) {
            $length = strripos(mb_substr($content, 0, $this->feed['params']['metaDescSize'], 'utf-8'), ' ');
            return mb_substr($content, 0, $length, 'utf-8');
        } else {
            $length = strripos(substr($content, 0, $this->feed['params']['metaDescSize']), ' ');
            return substr($content, 0, $length);
        }
    }

    /**
     * put your comment there...
     *
     */
    function cleanImages()
    {
        if (!count($this->imagesContent))
            return true;
        $this->_echo('<br>Очистка не используемых файлов картинок...');
        foreach ($this->imagesContent as $file) {
            @unlink($this->rootPath . $file);
        }
    }

    /**
     * put your comment there...
     *
     */
    function beforeSaveLoop($links)
    {
        #file_put_contents(ABSPATH.'links.txt', var_export($links, true));
    }

    /**
     * Основной процесс граббинга ленты с ID = $id
     * При транзакционной модели вернет объект если импорт не завершен,
     * true - когда завершен, false - в случае ошибки
     * @param mixed $id
     */
    final public function execute($id)
    {
        if ($this->_start_import === false) {
            $this->feed = $this->_getFeed($id);
            if (empty($this->feed)) {
                $this->_echo('<b>Лента ID: </b>' . $id . ' не найдена<br />');
            }else
            {
                $this->_echo('<b>Лента ID: </b>' . $id . ' <br />');
            }
            $this->_beforeExecute($id);
        }

        $result = $this->_import();
        #var_export($result);

        if ($this->config->get('getContentWriteLogsOn')) {
            #$this->write_string("execute_import.txt", var_export($result, true), "a");
        }




        if ($this->_isTransactionModel() and $result !== true) {
            return $result;
        }

        $this->_afterExecute($id);

        return true;
    }



    protected function _getFeed($id)
    {
        return array();
    }



    protected function _beforeExecute($id)
    {

        $this->_start_import = (int) current_time('timestamp', 1);
        #$this->_start_import = time();
        #var_export($this->feed);
        $this->_echo('<b>Импорт ленты: <a target="_blank" href="' . $this->feed['url'] . '">' . $this->feed['name'] . '</a> - ' . date('H:i:s Y-m-d', $this->_start_import) . '</b><br />');
        $this->feed['params'] = unserialize(base64_decode($this->feed['params']));

        if (trim($this->feed['params']['imageHtmlCode']) == '')
            $this->feed['params']['imageHtmlCode'] = '<img src="%PATH%" %ATTR% />';


        $this->requestMethod = $this->feed['params']['requestMethod'] == '0' ? $this->config->get('getContentMethod') : (int)($this->feed['params']['requestMethod'] - 1);


        if ($this->feed['params']['image_path'] and !$this->testOn) {
            $this->config->set('imgPath', $this->feed['params']['image_path']);
        }

        if ($this->feed['params']['filter_words_on']) {
            $this->filterWordsSave = '';
            $filter_words_list = @explode(',', $this->feed['params']['filter_words_list']);
            if (count($filter_words_list)) {
                #array_walk($filter_words_list, create_function('&$val', '$val = trim($val);'));
                array_walk($filter_words_list, function() use (&$val) { return trim($val);} );
                $filter_words_list = array_filter($filter_words_list);
                $this->filterWordsSave = implode('|', $filter_words_list);
            }
            if (trim($this->filterWordsSave) == '') {
                $this->feed['params']['filter_words_on'] = 0;
                $this->_echo('<br /><br><b>Список фильтр-слов пуст! Обработка фильтр слов отключена для данного процесса импорта.</b><br />');
            }
        }
        $this->imagesContentNoSave = $this->feed['params']['no_save_without_pic'] ? true : false;
    }




    protected function _afterExecute($id)
    {
        if(is_iterable($this->content))
        {
            $last_count = count($this->content);
        }else
        {
            $last_count = 0;
        }


        if ($last_count > 0) {
            $this->updateFeedData['last_url'] = "'" . (key($this->content)) . "'";
        }
        if ($this->testOn) {
            $this->_echo('<br /><br><b>Тестовый импорт ленты: <a target="_blank" href="' . $this->feed['url'] . '">' . $this->feed['name'] . '</a> - ' . date('H:i:s Y-m-d', (int) current_time('timestamp',1)) . ' - завершен!</b><br /><br />');
        } else {
            $end = (int) current_time('timestamp',1) - $this->_start_import;
	    #$end = time() - $this->_start_import;
            $this->updateFeedData['last_update'] = (int) current_time('timestamp',1);
            $this->updateFeedData['work_time'] = (int)$end;
            $this->updateFeedData['last_count'] = (int)$last_count;
            $this->updateFeedData['link_count'] = (int)$this->feed['link_count'];
            // режим отключения неработающих лент
            if ($this->config->get('offFeedsModeOn'))
                $this->updateFeedData['published'] = 1;
        }
        $this->_start_import = false;
    }



    protected function _pluginSynonymize(&$record)
    {
        $this->_echo('<br /><b>Синонимизация:</b>');
        $textorobotEnabled = $this
            ->config
            ->get('textorobotEnabled');
        $synonymizeEnabled = $this->feed['params']['synonymizeEnabled'];
        if (!$textorobotEnabled || !$synonymizeEnabled) return null;

        $textorobotApiKey = $this->feed['params']['textorobotApiKey'];
        if (!$textorobotApiKey)
        {
            $textorobotApiKey = $this
                ->config
                ->get('textorobotApiKey');
        }

        if (!$textorobotApiKey)
        {
            $this->_echoError('Не задан API-ключ для синонимизации');
            return false;
        }

        $text  = $record['text'];
        $title = $record['title'];

        if (!$text)
        {
            $this->_echoError('Не задан текст для синонимизации');
            return false;
        }

        if (!$this->textNoTranslate[$this
            ->currentUrl])
        {
            $this->textNoTranslate[$this->currentUrl]                        = $text;
        }

        if (!$this->titleNoTranslate[$this
            ->currentUrl])
        {
            $this->titleNoTranslate[$this->currentUrl]                        = $title;
        }

        $minSynonymPercentage   = (int)$this->feed['params']['minSynonymPercentage'];
        $ignoreOnError          = $this->feed['params']['ignoreRecordOnSynonymizeError'];
        $record['percent_syn']                        = 0;

        if ($title)
        {
            $synonymizeTitleResult  = $this->_synonymizeTextorobot($title, $textorobotApiKey);
            $processedTitle         = $synonymizeTitleResult->processedText;
            if ($processedTitle)
            {
                $record['title']                        = $processedTitle;
                $synonymTitlePercentage = (int)$synonymizeTitleResult->synonymPercentage;
                $this->_echoMessage('Заголовок синонимизирован на ' . $synonymTitlePercentage . '%, символы с баланса <b>списаны</b>.');
            }
            //            else if ($ignoreOnError) {
            //                $record['title'] = '';
            //                $this->_echoWarning('Заголовок не был синонимизирован! Включена опция не сохранять записи без синонимизации!');
            //            }
            else
            {
                $this->_echoWarning('Синонимизация заголовка завершилась ошибкой. Символы с баланса не списаны.');
            }
        }

        $synonymizeResult  = $this->_synonymizeTextorobot($text, $textorobotApiKey);
        $processedText     = $synonymizeResult->processedText;
        $synonymPercentage = (int)$synonymizeResult->synonymPercentage;

        if ($processedText)
        {
            $this->_echoMessage('Синонимизация произведена, символы с баланса <b>списаны</b>.');
            $this->_echoMessage('Процент синонимизации = ' . $synonymPercentage . '%, заданный лимит ' . $minSynonymPercentage . '%');
        }
        else
        {
            $this->_echoWarning('Синонимизация текста завершилась ошибкой. Символы с баланса <b>не списаны</b>.');
        }

        if ($synonymPercentage < $minSynonymPercentage)
        {
            $processedText = false;
        }

        if ($processedText)
        {
            $record['text']               = $processedText;
            $record['percent_syn']               = $synonymPercentage;
            $this->_echoMessage('В соответствии с настройками ленты: <b>Сохранён синонимизированный текст</b>!');
        }
        else if ($ignoreOnError)
        {
            $record['text'] = '';
            $this->_echoWarning('В соответствии с настройками ленты: <b>Текст не будет сохранён!</b>!');
        }
        else
        {
            $this->_echoWarning('В соответствии с настройками ленты: <b>Будет сохранён исходный текст</b>!');
        }
        $this->_echoMessage('Остаток символов на балансе: ' . $synonymizeResult->synonymSymbolBalance . '<br />');

        return true;
    }



    public function _textorobotErrorHandler($errorText, $errorType)
    {
        if ($errorType == 'warning')
        {
            $this->_echoWarning('Textorobot.ru:' . $errorText);
        }
        else
        {
            $this->_echoError('Textorobot.ru:' . $errorText);
        }
    }



    protected function _synonymizeTextorobot($text, $textorobotApiKey)
    {
        include_once (WPGRABBER_PLUGIN_DIR . 'textorobot/textorobotApi.php');
        $api    = new TextorobotApi($textorobotApiKey, array(
            $this,
            '_textorobotErrorHandler'
        ));
        $text_full = $text;
        if($this->feed['params']['SynonymStrimWidth'] )
        {
            $text = mb_strimwidth ( $text , 0 , $this->feed['params']['SynonymStrimWidth']);
            $text_end = str_replace($text, '', $text_full);
        }

        $result = $api->synonymize($text);
        if (!$result->processedText)
        {
            return false;
        }
        if($this->feed['params']['SynonymStrimWidth'] )
        {
            $result->processedText .= $text_end;
        }
        return $result;
    }




    protected function _pluginTranslate(&$record)
    {
        $errors = array();
        if ($this->feed['params']['translate_on']) {
            $provider = (int)$this->feed['params']['translate_method'];
            $params = array();
            if ($provider == 0) {
                // API Яндекс.Перевода
                $provider = 'Yandex';
                $params['lang'] = $this->feed['params']['translate_lang'];
                if ($this->config->get('multiKeyTRNSL') == true) {
                    if($this->config->get('curlinfoHeaderOutOn')) $this->_echo('<br /><b>режим multiKeyTRNSL ON!</b><hr>');
                    $yandexApiKey_array = explode("\n", $this->config->get('yandexApiKey'));
                    shuffle($yandexApiKey_array); shuffle($yandexApiKey_array);
                    $yandexApiKey = array_pop($yandexApiKey_array);
                } else {
                    if($this->config->get('curlinfoHeaderOutOn')) $this->_echo('<br /><b>режим multiKeyTRNSL OFF!</i><hr>');
                    $yandexApiKey = $this->config->get('yandexApiKey');
                }
                if($this->config->get('curlinfoHeaderOutOn')) $this->_echo('<br /><b>Использую yandexApiKey: </b><i>' . $yandexApiKey . '</i><hr>');
                $params['key'] = !empty($this->feed['params']['yandex_api_key']) ? $this->feed['params']['yandex_api_key'] : $yandexApiKey;

            } elseif ($provider == 2) {
                // Яндекс.Облако Translate
                $provider = 'YandexCloud';
                $params['lang'] = $this->feed['params']['translate_lang'];
                $params['key'] = !empty($this->feed['params']['yandexOauth']) ? $this->feed['params']['yandexOauth'] : $this->config->get('yandexOauth');
                $params['folder_id'] = !empty($this->feed['params']['yandexFolderId']) ? $this->feed['params']['yandexFolderId'] : $this->config->get('yandexFolderId');
                $params['yandex_glossary_pairs'] = $this->feed['params']['yandex_glossary_pairs'];

            } elseif ($provider == 3) {
                // Google Cloud Translation API
                $provider = 'GoogleCloud';
                $params['lang'] = $this->feed['params']['translate_lang'];
                $params['key'] = !empty($this->feed['params']['google_translate_api_key']) ? $this->feed['params']['google_translate_api_key'] : $this->config->get('google_translate_api_key');
                #$params['folder_id'] = !empty($this->feed['params']['google_translate_project_id']) ? $this->feed['params']['google_translate_project_id'] : $this->config->get('google_translate_project_id');



            } elseif ($provider == 1) {
                //API Bing Переводчика
                $provider = 'Bing';
                $lang = explode('-', $this->feed['params']['translate_lang']);
                $params['from'] = str_replace('_', '-', $lang[0]);
                $params['to'] = isset($lang[1]) ? str_replace('_', '-', $lang[1]) : $lang[1];
                $params['key'] = $this->config->get('bingApiKey');
            } else {
                $errors[] = 'Ошибка первого перевода. Неправильно указана система перевода.';
            }
            if (!sizeof((array)$errors)) {
                $this->textNoTranslate[$this->currentUrl] = $record['text'];
                // первый перевод текста
                if (($text = $this->_translate($record['text'], $provider, $params, $e)) !== false) {
                    // не сохранять запись если не перевели текст
                    if ($this->feed['params']['nosave_if_not_translate']) {
                        if (md5($text) == md5($record['text'])) {
                            $record['text'] = '';
                            $errors[] = 'Текст не был переведен! Включена опция не сохранять записи без перевода!';
                        } else {
                            $record['text'] = $text;
                        }
                    } else // сохранять даже если не перевели текст
                    {
                        $record['text'] = $text;
                    }
                } else {
                    $errors[] = 'Ошибка первого перевода текста. ' . current($e);
                    // не сохранять запись если не перевели текст
                    if ($this->feed['params']['nosave_if_not_translate']) {
                        $record['text'] = '';
                        $errors[] = 'Текст не был переведен! Включена опция не сохранять записи без перевода!';
                    }
                }

                $this->titleNoTranslate[$this->currentUrl] = $record['title'];
                // первый перевод заголовка
                if (($title = $this->_translate($record['title'], $provider, $params, $e)) !== false) {
                    // не сохранять запись если не перевели заголовок
                    if ($this->feed['params']['nosave_if_not_translate']) {
                        if (md5($title) == md5($record['title'])) {
                            $record['title'] = '';
                            $errors[] = 'Заголовок не был переведен! Включена опция не сохранять записи без перевода!';
                        } else // сохрянить запись, даже если не перевели заголовок
                        {
                            $record['title'] = $title;
                        }
                    } else {
                        $record['title'] = $title;
                    }
                } else {
                    $errors[] = 'Ошибка первого перевода заголовка. ' . current($e);
                    // не сохранять запись если не перевели заголовок
                    if ($this->feed['params']['nosave_if_not_translate']) {
                        $record['title'] = '';
                        $errors[] = 'Заголовок не был переведен! Включена опция не сохранять записи без перевода!';
                    }
                }
            }
        }
        if (!sizeof((array)$errors)) {
            if ($this->feed['params']['translate2_on']) {
                $provider = (int)$this->feed['params']['translate2_method'];
                $params = array();
                if ($provider == 0) {
                    // API Яндекс.Перевода
                    $provider = 'Yandex';
                    $params['lang'] = $this->feed['params']['translate2_lang'];
                    if ($this->config->get('multiKeyTRNSL') == true) {
                        $this->_echo('<br /><b>режим multiKeyTRNSL ON!</b><hr>');
                        $yandexApiKey_array = explode("\n", $this->config->get('yandexApiKey'));
                        shuffle($yandexApiKey_array);
                        #shuffle($yandexApiKey_array);shuffle($yandexApiKey_array);
                        $yandexApiKey = array_pop($yandexApiKey_array);
                    } else {
                        $this->_echo('<br /><b>режим multiKeyTRNSL OFF!</i><hr>');
                        $yandexApiKey = $this->config->get('yandexApiKey');
                    }
                    #$this->_echo($yandexApiKey);
                    $this->_echo('<br /><b>Использую yandexApiKey: </b><i>' . $yandexApiKey . '</i><hr>');
                    $params['key'] = !empty($this->feed['params']['yandex_api_key2']) ? $this->feed['params']['yandex_api_key2'] : $yandexApiKey;
                    #$params['key']  = !empty($this->feed['params']['yandex_api_key2']) ? #$this->feed['params']['yandex_api_key2'] : $this->config->get('yandexApiKey');

                } elseif ($provider == 2) {
                    // Яндекс.Облако Translate
                    $provider = 'YandexCloud';
                    $params['lang'] = $this->feed['params']['translate2_lang'];
                    $params['key'] = !empty($this->feed['params']['yandexOauth']) ? $this->feed['params']['yandexOauth'] : $this->config->get('yandexOauth');
                    $params['folder_id'] = !empty($this->feed['params']['yandexFolderId']) ? $this->feed['params']['yandexFolderId'] : $this->config->get('yandexFolderId');
                    $params['yandex_glossary_pairs'] = $this->feed['params']['yandex_glossary_pairs2'];

                } elseif ($provider == 3) {
                    // Google Cloud Translation API
                    $provider = 'GoogleCloud';
                    $params['lang'] = $this->feed['params']['translate2_lang'];
                    $params['key'] = !empty($this->feed['params']['google_translate_api_key']) ? $this->feed['params']['google_translate_api_key'] : $this->config->get('google_translate_api_key');
                    #$params['folder_id'] = !empty($this->feed['params']['google_translate_project_id']) ? $this->feed['params']['google_translate_project_id'] : $this->config->get('google_translate_project_id');

                } elseif ($provider == 1) {
                    //API Bing Переводчика
                    $provider = 'Bing';
                    $lang = explode('-', $this->feed['params']['translate2_lang']);
                    $params['from'] = str_replace('_', '-', $lang[0]);
                    $params['to'] = isset($lang[1]) ? str_replace('_', '-', $lang[1]) : $lang[1];
                    $params['key'] = $this->config->get('bingApiKey');
                } else {
                    $errors[] = 'Ошибка второго перевода. Неправильно указана система перевода.';
                }
                if (!sizeof((array)$errors)) {

                    // второй перевод текста
                    if (($text = $this->_translate($record['text'], $provider, $params, $e)) !== false) {
                        // не сохранять запись если не перевели текст
                        if ($this->feed['params']['nosave_if_not_translate']) {
                            if (md5($text) == md5($record['text'])) {
                                $record['text'] = '';
                                $errors[] = 'Текст не был переведен во втором переводе! Включена опция не сохранять записи без перевода!';
                            } else {
                                $record['text'] = $text;
                            }
                        } else // сохранять даже если не перевели текст
                        {
                            $record['text'] = $text;
                        }
                    } else {
                        $errors[] = 'Ошибка второго перевода текста. ' . current($e);
                        // не сохранять запись если не перевели текст
                        if ($this->feed['params']['nosave_if_not_translate']) {
                            $record['text'] = '';
                            $errors[] = 'Текст не был переведен во втором переводе! Включена опция не сохранять записи без перевода!';
                        }
                    }

                    // второй перевод заголовка
                    if (($title = $this->_translate($record['title'], $provider, $params, $e)) !== false) {
                        // не сохранять запись если не перевели заголовок
                        if ($this->feed['params']['nosave_if_not_translate']) {
                            if (md5($title) == md5($record['title'])) {
                                $record['title'] = '';
                                $errors[] = 'Заголовок не был переведен! Включена опция не сохранять записи без перевода!';
                            } else // сохрянить запись, даже если не перевели заголовок
                            {
                                $record['title'] = $title;
                            }
                        } else {
                            $record['title'] = $title;
                        }
                    } else {
                        $errors[] = 'Ошибка второго перевода заголовка. ' . current($e);
                        if ($this->feed['params']['nosave_if_not_translate']) {
                            $record['title'] = '';
                            $errors[] = 'Заголовок не был переведен во втором переводе! Включена опция не сохранять записи без перевода!';
                        }
                    }
                }
            }
        }
        if (sizeof((array)$errors)) {
            foreach ($errors as $e) {
                $this->_echo('<br /><i>' . $e . '</i>');
            }
        }
    }

    protected function _translate($text, $provider, $params, &$errors)
    {
        if ($provider !== '') {
            $method = '_translate' . $provider;
            if (method_exists($this, $method)) {
                return $this->$method($text, $params, $errors);
            }
        }
        $errors[] = 'Система перевода не найдена.';
        return false;
    }


    /*
    $yandexPassportOauthToken = "<OAuth-Token>"
    $Body = @{ yandexPassportOauthToken = "$yandexPassportOauthToken" } | ConvertTo-Json -Compress
    Invoke-RestMethod -Method 'POST' -Uri 'https://iam.api.cloud.yandex.net/iam/v1/tokens' -Body $Body -ContentType 'Application/json' | Select-Object -ExpandProperty iamToken
    */

    public function getYandexPassportOauthToken($yandexPassportOauthToken)
    {
        if (empty($yandexPassportOauthToken))
        {
            $errors[] = '<p>Получите OAuth-токен в сервисе Яндекс.OAuth. Для этого перейдите по <a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=1a6990aa636648e9b2ef855fa7bec2fb
            " target="_blank" rel="noreferrer noopener">ссылке</a>, нажмите <strong>Разрешить</strong> и скопируйте полученный OAuth-токен.</p>';
        }
        # https://oauth.yandex.ru/verification_code#access_token=AgAAAAANa6SCAATuwfyK1u4z-kXbhLittg_TqSM&token_type=bearer&expires_in=31533723
        if (!sizeof((array)$errors))
        {
            $post_data['yandexPassportOauthToken']    = $yandexPassportOauthToken;
            /*
            curl -d "{\"yandexPassportOauthToken\":\"<OAuth-token>\"}" "https://iam.api.cloud.yandex.net/iam/v1/tokens"
            */
            $query               = json_encode($post_data);
            #$query               = http_build_query($post_data);

            # https://cloud.yandex.ru/docs/translate/api-ref/Translation/translate
            $url                 = 'https://iam.api.cloud.yandex.net/iam/v1/tokens';
            $ch                  = curl_init();

            #$headers[] = "Authorization: Bearer" .$params['key'];
            #$headers[] = "X-Client-Request-ID: 0da512b9-27b4-4b9d-9133-a02d6b7a8879";
            #$headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
            #$headers[] = "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8,cs;q=0.7";
            #$headers[] = "Connection: keep-alive";
            #$headers[] = "DNT: 1";
            $headers[] = "ContentType: Application/json";

            if ($this->config->get('curlGzipOn'))
                $headers[] = "Accept-Encoding: gzip";
            if ($this->config->get('userAgent'))
                $headers[] = "User-Agent: " . $this->config->get('userAgent');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if($this->config->get('userAgent'))
            {
                curl_setopt($ch, CURLOPT_USERAGENT, $this->config->get('userAgent'));
            }else
            {
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36");
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            if ($this->config->get('curlProxyOn'))
            {
                // Берём из списка
                if ($this->config->get('curlProxyListOn'))
                {
                    // прокси из списка
                    if ($this->config->get('curlProxyHostPort_List'))
                    {
                        $this->_echo('getContent->curlProxyHostPort_List: <b>' . $this->config->get('curlProxyHostPort_List') . '</b><br />');
                        $proxy_array = explode("\r", trim($this->config->get('curlProxyHostPort_List')));
                        shuffle($proxy_array);  shuffle($proxy_array);  shuffle($proxy_array);
                        $proxy = array_pop($proxy_array);
                        $this->_echo('getContent->proxy: <b>' . $proxy . '</b><br />');
                        curl_setopt($ch, CURLOPT_PROXY, trim($proxy));
                        $this->_echo('<br /><b>TGrabberCore::getContent CURLOPT_PROXY</b>: ' . $proxy . ' <br>');
                    }
                }
                else
                {
                    // прокси
                    if ($this->config->get('curlProxyHostPort'))
                    {
                        curl_setopt($ch, CURLOPT_PROXY, $this->config->get('curlProxyHostPort'));
                        $this->_echo('<br /><b>TGrabberCore::getContent CURLOPT_PROXY</b>: ' . $this->config->get('curlProxyHostPort') . ' <br>');
                    }
                }
                // тип прокси
                if ($this->config->get('curlProxyType'))
                {
                    switch ($this->config->get('curlProxyType'))
                    {
                        case 1:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                            break;
                        case 2:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4A);
                            break;
                        case 3:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
                            break;
                        default:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    }
                }
                // авторизация
                if ($this->config->get('curlProxyUserPwd'))
                {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->config->get('curlProxyUserPwd')); // Стока с именем пользователя и паролем к HTTP прокси-серверу в виде [username]:[password].
                }
            }


            $out    = curl_exec($ch);
            $result = curl_getinfo($ch);
            curl_close($ch);
            #var_export($result);
            #var_export($out);
            $js = json_decode($out, true);
            #var_export($js);
            if ($result['http_code'] == 200)
            {
                if (!empty($js['iamToken']))
                {
                    return $js['iamToken'];
                }
                else
                {
                    $errors[] = 'iamToken отсутствует!';
                }
            }
            else
            {
                $errors[] = 'Ошибочный ответ сервер iam.api.cloud.yandex.net: ' . $result['http_code'];
            }
        }
        $errors[] = 'Сбой сервиса iam.api.cloud.yandex.net';
        return false;
    }










    /*
    Метод translate
    Переводит текст на указанный язык.

    # https://cloud.google.com/translate/docs/basic/translating-text
    # http://googleapis.github.io/google-cloud-php/#/docs/cloud-translate/v1.7.4/translate/v2/translateclient?method=translate
    # https://github.com/googleapis/google-cloud-php-translate

    */

    protected function _translateGoogleCloud($text, $params, &$errors)
    {
        #var_export($params);
        $this->_echo('<br /><b>TGrabberCore::translateGoogleCloud</b><br>');
        if (empty($text))
        {
            $errors[] = 'Нет данных для перевода';
        }
        if (empty($params['lang']))
        {
            $errors[] = 'Не задан язык перевода';
        }
        /*if (empty($params['folder_id']))
        {
            $errors[] = 'Не задан идентификатор каталога';
        } */
        if (empty($params['key']))
        {
            $errors[] = 'Не задан API key GoogleCloud';
        }
        if (!sizeof((array)$errors))
        {
            list($sourceLanguageCode, $targetLanguageCode) = explode('-',$params['lang']);

            $post_data['q']   = array(preg_replace('~[\t\n\r]+~',' ',$text));
            $post_data['source']   = $sourceLanguageCode;
            $post_data['target']   = $targetLanguageCode;
            $post_data['format'] = 'html';

            #$post_data['folder_id'] = $params['folder_id'];
            #var_export($post_data);
            $query               = json_encode($post_data);
            #var_export($query);
            $url                 = 'https://translation.googleapis.com/language/translate/v2?key='.$params['key'];
            $ch                  = curl_init();
            $headers[] = "Content-Type: application/json";
            $headers[] = "x-goog-api-client: gl-php/7.2.0 gccl/1.5.0";
            $headers[] = "Accept-Encoding: gzip";
            if ($this->config->get('curlGzipOn'))
                $headers[] = "Accept-Encoding: gzip";
            #if ($this->config->get('userAgent'))
                #$headers[] = "User-Agent: " . $this->config->get('userAgent');
            #var_export($headers);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            #curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:68.0) Gecko/20100101 Firefox/68.0");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            if ($this->config->get('curlGzipOn'))
                curl_setopt($ch, CURLOPT_ENCODING, "gzip");

            if ($this->config->get('curlProxyOn'))
            {
                // Берём из списка
                if ($this->config->get('curlProxyListOn'))
                {
                    // прокси из списка
                    if ($this->config->get('curlProxyHostPort_List'))
                    {
                        $this->_echo('getContent->curlProxyHostPort_List: <b>' . $this->config->get('curlProxyHostPort_List') . '</b><br />');
                        $proxy_array = explode("\r", trim($this->config->get('curlProxyHostPort_List')));
                        shuffle($proxy_array);  shuffle($proxy_array);  shuffle($proxy_array);
                        $proxy = array_pop($proxy_array);
                        $this->_echo('getContent->proxy: <b>' . $proxy . '</b><br />');
                        curl_setopt($ch, CURLOPT_PROXY, trim($proxy));
                        $this->_echo('<br /><b>TGrabberCore::getContent CURLOPT_PROXY</b>: ' . $proxy . ' <br>');
                    }
                }
                else
                {
                    // прокси
                    if ($this->config->get('curlProxyHostPort'))
                    {
                        curl_setopt($ch, CURLOPT_PROXY, $this->config->get('curlProxyHostPort'));
                        $this->_echo('<br /><b>TGrabberCore::getContent CURLOPT_PROXY</b>: ' . $this->config->get('curlProxyHostPort') . ' <br>');
                    }
                }
                // тип прокси
                if ($this->config->get('curlProxyType'))
                {
                    switch ($this->config->get('curlProxyType'))
                    {
                        case 1:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                            break;
                        case 2:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4A);
                            break;
                        case 3:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
                            break;
                        default:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    }
                }
                // авторизация
                if ($this->config->get('curlProxyUserPwd'))
                {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->config->get('curlProxyUserPwd')); // Стока с именем пользователя и паролем к HTTP прокси-серверу в виде [username]:[password].
                }
            }


            $out    = curl_exec($ch);
            $result = curl_getinfo($ch);
            curl_close($ch);
            #var_export($result);
            #var_export(json_decode($out, true));
            $buff = json_decode($out, true);
            #var_export($buff);
            /*
            '{
              "data": {
                "translations": [
                  {
                    "translatedText": "text"
                  }
                ]
              }
            }
            '
            'array (
              'data' =>
              array (
                'translations' =>
                array (
                  0 =>
                  array (
                    'translatedText' => 'text',
                  ),
                ),
              ),
            )

            */
            if ($result['http_code'] == 200)
            {
                if (!empty($buff['data']['translations'][0]['translatedText']))
                {
                    return html_entity_decode($buff['data']['translations'][0]['translatedText'], ENT_COMPAT, 'utf-8');
                }
                else
                {
                    $errors[] = 'Перевод отсутсвует!';
                }
            }
            else
            {
                $errors[] = 'Ошибочный ответ сервер Google Cloud Translation API: ' . $result['http_code'];
            }
        }
        $errors[] = 'Сбой сервиса';
        return false;
    }


    function getGlossaryConfig($yandex_glossary_pairs)
    {
        if($yandex_glossary_pairs)
        {
            $pairs = trim($yandex_glossary_pairs);
        }else
        {
            $pairs = '';
        }
        if($pairs == '')
        {
            return false;
        }
        $t = explode('|', $pairs);
        #var_export($t);
        foreach($t as $key=>$a)
        {
            list($sourceText, $translatedText) = explode('=>', $a);
            $glossaryPairs[$key]['sourceText'] = $sourceText;
            $glossaryPairs[$key]['translatedText'] = $translatedText;

        }
        $glossaryConfig = array("glossaryData" => array("glossaryPairs" => $glossaryPairs));
        #var_export($glossaryConfig);
        return $glossaryConfig;
    }



    /*
    Метод translate
    Переводит текст на указанный язык.

    https://cloud.yandex.ru/docs/translate/api-ref/Translation/translate
    */

    protected function _translateYandexCloud($text, $params, &$errors)
    {
        #var_export($params);
        $this->_echo('<br /><b>TGrabberCore::translateYandexCloud</b><br>');
        if (empty($text))
        {
            $errors[] = 'Нет данных для перевода';
        }
        if (empty($params['lang']))
        {
            $errors[] = 'Не задан язык перевода';
        }
        if (empty($params['folder_id']))
        {
            $errors[] = 'Не задан идентификатор каталога';
        }
        if (empty($params['key']))
        {
            $errors[] = 'Не задан OAuth-токен Yandex';
        }
        if (!sizeof((array)$errors))
        {
            // длина  текста
            $text = preg_replace('~[\t\n\r]+~',' ',$text);
            $text = preg_replace('~[\s]{2,}~',' ',$text);

            $this->_echo('<b>Длина текста</b>: '. mb_strlen($text) );

            if(mb_strlen($text) > 10000)
            {
                #$text = mb_strimwidth($text, 0, 9990, "...");
                $t_text = array();

                if ($this->feed['params']['splitTextWidth'])
                {
                    $splitTextWidth = $this->feed['params']['splitTextWidth'];
                }else
                {
                    $splitTextWidth = 1000;
                }

                foreach($this-> splitTextWidth($text, $splitTextWidth) as $chunk=>$splitText)
                {
                    if(trim(mb_strlen($splitText)) != 0)
                    {
                        $this->_echo('<br /><i>Часть текста</i>: '. $chunk . 'mb_strlen($splitText): ' .mb_strlen($splitText) );
                        $t_text[] = $this-> makeTranslateYandexCloud($splitText, $params, $errors);
                    }
                }
                #file_put_contents("splitTextWidth__" . md5($t_text) . ".html", var_export($t_text, true));
                return implode('', $t_text);

            }else{
                return $this-> makeTranslateYandexCloud($text, $params, $errors);
            }

        }
        $errors[] = 'Сбой сервиса';
        return false;
    }




    function makeTranslateYandexCloud($text, $params, &$errors)
    {
            list($sourceLanguageCode, $targetLanguageCode) = explode('-',$params['lang']);
            $post_data['texts']   = array(preg_replace('~[\t\n\r]+~',' ',$text));
            $post_data['sourceLanguageCode']   = $sourceLanguageCode;
            $post_data['targetLanguageCode']   = $targetLanguageCode;
            $post_data['format'] = 'HTML';
            $post_data['folder_id'] = $params['folder_id'];
            // собственный глоссарий для перевода
            $glossary = $this-> getGlossaryConfig($params['yandex_glossary_pairs']);
            if($glossary)
            {
                $post_data['glossaryConfig'] = $glossary;
            }
            #var_export($post_data);
            $query               = json_encode($post_data);
            #var_export($query);
            $url                 = 'https://translate.api.cloud.yandex.net/translate/v2/translate';
            $ch                  = curl_init();
            $headers[] = "ContentType: Application/json";
            $headers[] = "Authorization: Bearer " .$this-> getYandexPassportOauthToken($params['key']);
            $headers[] = "X-Client-Request-ID: 0da512b9-27b4-4b9d-9133-a02d6b7a8879";
            #$headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
            #$headers[] = "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8,cs;q=0.7";
            #$headers[] = "Connection: keep-alive";
            #$headers[] = "DNT: 1";
            if ($this->config->get('curlGzipOn'))
                $headers[] = "Accept-Encoding: gzip";
            if ($this->config->get('userAgent'))
                $headers[] = "User-Agent: " . $this->config->get('userAgent');
            #var_export($headers);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if($this->config->get('userAgent'))
            {
                curl_setopt($ch, CURLOPT_USERAGENT, $this->config->get('userAgent'));
            }else
            {
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36");
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            if ($this->config->get('curlProxyOn'))
            {
                // Берём из списка
                if ($this->config->get('curlProxyListOn'))
                {
                    // прокси из списка
                    if ($this->config->get('curlProxyHostPort_List'))
                    {
                        $this->_echo('getContent->curlProxyHostPort_List: <b>' . $this->config->get('curlProxyHostPort_List') . '</b><br />');
                        $proxy_array = explode("\r", trim($this->config->get('curlProxyHostPort_List')));
                        shuffle($proxy_array);  shuffle($proxy_array);  shuffle($proxy_array);
                        $proxy = array_pop($proxy_array);
                        $this->_echo('getContent->proxy: <b>' . $proxy . '</b><br />');
                        curl_setopt($ch, CURLOPT_PROXY, trim($proxy));
                        $this->_echo('<br /><b>TGrabberCore::getContent CURLOPT_PROXY</b>: ' . $proxy . ' <br>');
                    }
                }
                else
                {
                    // прокси
                    if ($this->config->get('curlProxyHostPort'))
                    {
                        curl_setopt($ch, CURLOPT_PROXY, $this->config->get('curlProxyHostPort'));
                        $this->_echo('<br /><b>TGrabberCore::getContent CURLOPT_PROXY</b>: ' . $this->config->get('curlProxyHostPort') . ' <br>');
                    }
                }
                // тип прокси
                if ($this->config->get('curlProxyType'))
                {
                    switch ($this->config->get('curlProxyType'))
                    {
                        case 1:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                            break;
                        case 2:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4A);
                            break;
                        case 3:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
                            break;
                        default:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    }
                }
                // авторизация
                if ($this->config->get('curlProxyUserPwd'))
                {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->config->get('curlProxyUserPwd')); // Стока с именем пользователя и паролем к HTTP прокси-серверу в виде [username]:[password].
                }
            }


            $out    = curl_exec($ch);
            $result = curl_getinfo($ch);
            curl_close($ch);
            #var_export($result);
            #var_export(json_decode($out, true));
            $buff = json_decode($out, true);
            #var_export($buff);
            /*
            {
              "translations": [
                {
                  "text": "string",
                  "detectedLanguageCode": "string"
                }
              ]
            }
            */
            if ($result['http_code'] == 200)
            {
                if (!empty($buff['translations'][0]['text']))
                {
                    return html_entity_decode($buff['translations'][0]['text'], ENT_COMPAT, 'utf-8');
                }
                else
                {
                    $errors[] = 'Перевод отсутсвует!';
                }
            }
            else
            {
                $errors[] = 'Ошибочный ответ сервера TranslateYandexCloud API: ' . $result['http_code'] . "<br>". $buff['message'];
            }
    }





    function splitTextWidth($text, $w = 5300)
    {
        $offset = $w;
        $tk = array();
        $strlen = mb_strlen($text);
        #echo '$strlen: '.$strlen ."\n";
        #echo intdiv ($strlen, $w) ."\n";
        $end  = intdiv ($strlen, $w);
        if($strlen > $w)
        {
            #echo '$strlen: '.$strlen ."\n\n";
            $i = 1;
            do
            {
                $pos = mb_strripos ($text, '>', '-'.$offset );
                #echo '$pos: '.$pos ."\n";
                $marks[$i] = $pos + 1;
                $offset = $offset + $w + 1;
                #echo '$offset: -'.$offset ."\n";
                $i++;
            } while ($i < $end);
        }
         #print_r($marks);
         $reversed = array_reverse($marks);
         #print_r($reversed);
         $key_last = $this-> array_key_last($reversed);
         foreach($reversed as $p => $mark)
         {
             if($p == 0)
             {
                $start = 0;
                $length = $mark;
             }else
             {
                $length = $mark - $start;
             }

             if($p == $key_last)
             {
                $length = NULL;
             }
             $ch[$p] = mb_substr ($text, $start, $length);
             $start = $mark;
         }
        #print_r($ch);
        #file_put_contents("content_ch_" . md5($text) . ".html", var_export($ch, true));
        return $ch;
    }





    function array_key_last($array) {
        if (!is_array($array) || empty($array)) {
            return NULL;
        }

        return array_keys($array)[count((array)$array)-1];
    }



    protected function _translateYandex($text, $params, &$errors) {
      $this->_echo('<br /><b>TGrabberCore::translateYandex v1.5</b><br>');
      if (empty($text)) {
        $errors[] = 'Нет данных для перевода';
      }
      if (empty($params['lang'])) {
        $errors[] = 'Не задан язык перевода';
      }
      if (empty($params['key'])) {
        $errors[] = 'Не задан API-ключ Yandex';
      }
      if (!sizeof((array)$errors)) {
        $post_data['text'] = $text;
        $post_data['lang'] = $params['lang'];
        $post_data['format'] = 'html';
        $post_data['key'] = $params['key'];
        $query = http_build_query($post_data);
        #var_export($post_data);
        #die();
        $url = 'https://translate.yandex.net/api/v1.5/tr/translate';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->config->get('userAgent'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);


            if ($this->config->get('curlProxyOn')) {
                // Берём из списка
                if($this->config->get('curlProxyListOn'))
                {
                    // ставим прокси
                    if ($this->config->get('curlProxyHostPort_List')) {
                        $this->_echo('getContent->curlProxyHostPort_List: <b>'.$this->config->get('curlProxyHostPort_List').'</b><br />');
                        $proxy_array = explode("\r", trim($this->config->get('curlProxyHostPort_List')));
                        shuffle($proxy_array);  $proxy = array_pop($proxy_array);
                        $this->_echo('getContent->proxy: <b>'.$proxy.'</b><br />');
                        curl_setopt($ch, CURLOPT_PROXY, trim($proxy));
                        $this->_echo('<br /><b>TGrabberCore::getContent CURLOPT_PROXY</b>: ' . $proxy . ' <br>');

                    }
                }
                else
                {
                    // ставим прокси
                    if ($this->config->get('curlProxyHostPort')) {
                        curl_setopt($ch, CURLOPT_PROXY, $this->config->get('curlProxyHostPort'));
                        $this->_echo('<br /><b>TGrabberCore::getContent CURLOPT_PROXY</b>: ' . $this->config->get('curlProxyHostPort') . ' <br>');
                    }
                }


                // ставим тип прокси
                #  array('0'=>'CURLPROXY_HTTP','1'=>'CURLPROXY_SOCKS5','2'=>'CURLPROXY_SOCKS4A','3'=>'CURLPROXY_SOCKS5_HOSTNAME'), get_option('wpg_' .'curlProxyType'), 1);

                if ($this->config->get('curlProxyType')) {
                    switch ($this->config->get('curlProxyType')) {
                        case 1:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                            break;
                        case 2:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4A);
                            break;
                        case 3:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
                            break;
                        default:
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    }
                }
                // авторизация
                if ($this->config->get('curlProxyUserPwd')) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->config->get('curlProxyUserPwd'));    // Стока с именем пользователя и паролем к HTTP прокси-серверу в виде [username]:[password].
                }
            }


        $out = curl_exec($ch);
        $result = curl_getinfo($ch);
        curl_close($ch);
        #var_export($result);
        #var_export($out);
        if ($result['http_code'] == 200) {
          if (preg_match('|<Translation code="200" lang="'.$post_data['lang'].'"><text>(.*?)</text></Translation>|is', $out, $buff)) {
            if (!empty($buff[1])) {
              return html_entity_decode($buff[1], ENT_COMPAT, 'utf-8');
            } else {
                $errors[] = 'Перевод отсутсвует!';
            }
          }
        } else {
            $errors[] = 'Ошибочный ответ сервер Яндекс.Перевод: ' . $result['http_code'];
        }
      }
      $errors[] = 'Сбой сервиса';
      return false;
    }

    // Не работает с длинными текстами

    // http://msdn.microsoft.com/en-us/library/ff512387.aspx
    // http://blogs.msdn.com/b/translation/p/phptranslator.aspx
    // http://blogs.msdn.com/b/translation/p/gettingstarted1.aspx
    // http://maarkus.ru/perevodchik-dlya-sajta-bing-translator-api/
    // https://code.google.com/p/micrsoft-translator-php-wrapper/
    // http://social.msdn.microsoft.com/Forums/en-US/b504dab2-75a9-4e5c-a7ea-27add00e32fe/how-to-post-large-data-using-http-interface-for-translate-method-in-microsoft-translate-api-v2?forum=microsofttranslator
    protected function _translateBing($text, $params, &$errors) {
      if (empty($text)) {
        $errors[] = 'Нет данных для перевода';
      }
      if (empty($params['from'])) {
        $errors[] = 'Не задан язык перевода';
      }
      if (empty($params['to'])) {
        $errors[] = 'Не задан язык перевода';
      }
      if (empty($params['key'])) {
        $errors[] = 'Не задан ключ АПИ';
      }
      if (!sizeof((array)$errors)) {
        $url = 'https://api.datamarket.azure.com/Bing/MicrosoftTranslator/v1/Translate';
        $query['Text'] = "'".$text."'";
        $query['From'] = "'".$params['from']."'";
        $query['To'] = "'".$params['to']."'";
        $query['$format'] = 'Raw';
        $url .= '?'.http_build_query($query);
        $query = 'Text='."'".urlencode($text)."'";
        $headers = array(
          'Authorization: Basic '.base64_encode($params['key'].':'.$params['key'])
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($this->config->get('userAgent'))
        {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->config->get('userAgent'));
        }else
        {
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36");
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $out = curl_exec($ch);
        $result = curl_getinfo($ch);
        curl_close($ch);
        if ($result['http_code'] == 200) {
          if (preg_match('|<string[^>]*?>(.*?)<\/string>|is', $out, $buff)) {
            if (!empty($buff[1])) {
              return html_entity_decode($buff[1], ENT_COMPAT, 'utf-8');
            }
          }
        }
      }
      $errors[] = 'Сбой сервиса';
      return false;
    }
}
?>