<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(0);

if ($_GET['ajax'])
{
    $task   = $_GET['task'];
    //sleep(1);
    $result = $task();
    exit();
}
function testFileGetContents()
{
    $content = file_get_contents('https://www.yandex.ru/');
    if (strlen($content) > 0)
    {
        echo ' - <font color="green">успешно!</font>';
    }
    else
    {
        echo ' - <font color="red">ошибка!</font>';
    }
    return $result;
}

function testCurl()
{
    if (!function_exists('curl_init'))
    {
        echo ' - <font color="red">возможно не поддерживается!</font>';
        return false;
    }
    $ch = curl_init();
    $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
    $headers[] = "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8,cs;q=0.7";
    $headers[] = "Connection: keep-alive";
    $headers[] = "Accept-Encoding: gzip";

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL, 'https://www.yandex.ru/');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36");
    $out = curl_exec($ch);
    curl_close($ch);
    if (strlen($out) > 0)
    {
        echo ' - <font color="green">успешно!</font>';
    }
    else
    {
        echo ' - <font color="red">ошибка!</font>';
    }
    return $result;
}


function testCurlFollowLocation()
{
    if (!function_exists('curl_init'))
    {
        echo ' - <font color="red">возможно не поддерживается!</font>';
        return false;
    }

    $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
    $headers[] = "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8,cs;q=0.7";
    $headers[] = "Connection: keep-alive";
    $headers[] = "Accept-Encoding: gzip";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL, 'http://news.yandex.ru/');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36");
    $out = curl_exec($ch);
    curl_close($ch);
    if (strlen($out) > 0)
    {
        echo ' - <font color="green">успешно!</font>';
    }
    else
    {
        echo ' - <font color="red">ошибка!</font>';
    }
    return $out;
}





function testCurlHead()
{
    if (!function_exists('curl_init'))
    {
        echo ' - <font color="red">возможно не поддерживается!</font>';
        return false;
    }

    $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
    $headers[] = "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8,cs;q=0.7";
    $headers[] = "Connection: keep-alive";
    $headers[] = "Accept-Encoding: gzip";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].'/');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36");
    // Only calling the head
    curl_setopt($ch, CURLOPT_HEADER, true); // header will be at output
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD'); // HTTP request is 'HEAD'
    #curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); // ADD THIS

    #curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    $out = curl_exec($ch);

    curl_close($ch);
    if (strlen($out) > 0)
    {
        echo ' - <font color="green">успешно!</font><br>' . preg_replace('~[\n]~',"<br>",$out);
    }
    else
    {
        echo ' - <font color="red">ошибка!</font>';
    }
    return $out;
}


function testCurlSaveFile()
{
    if (!function_exists('curl_init'))
    {
        echo ' - <font color="red">возможно не поддерживается!</font>';
        return false;
    }

    $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
    $headers[] = "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8,cs;q=0.7";
    $headers[] = "Connection: keep-alive";
    $headers[] = "Accept-Encoding: gzip";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $ch = curl_init('https://yastatic.net/morda-logo/i/apple-touch-icon/ru-180x180.png');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36");
    $file = curl_exec($ch);
    curl_close($ch);
    $fp = @fopen('ru-180x180.png', 'x');
    @fwrite($fp, $file);
    @fclose($fp);
    if (is_file('ru-180x180.png'))
    {
        echo ' - <font color="green">успешно!</font>';
        @unlink('ru-180x180.png');
    }
    else
    {
        echo ' - <font color="red">ошибка! (<a target="_blank" href="http://www.php.su/functions/?cat=curl">подробнее...</a>)</font>';
    }
    return strlen($file);
}







function testCopy()
{
    if (@copy('https://yastatic.net/morda-logo/i/apple-touch-icon/ru-180x180.png', 'ru-180x180.png'))
    {
        echo ' - <font color="green">успешно!</font>';
        @unlink('ru-180x180.png');
    }
    else
    {
        echo ' - <font color="red">ошибка! (<a target="_blank" href="http://www.php.su/copy">подробнее...</a>)</font>';
    }
}

function testFileGetContentsSaveFile()
{
    $file = @file_get_contents('https://yastatic.net/morda-logo/i/apple-touch-icon/ru-180x180.png');
    @file_put_contents('ru-180x180.png', $file);
    if (is_file('ru-180x180.png'))
    {
        echo ' - <font color="green">успешно!</font>';
        @unlink('ru-180x180.png');
    }
    else
    {
        echo ' - <font color="red">ошибка! (<a target="_blank" href="http://www.php.su/file_get_contents">подробнее...</a>)</font>';
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>WPGrabber Test 2.1.2</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="utf-8">
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.12.1.min.js"></script>
    <script type="text/javascript">
        function __start() {
            $('#display').html('');
            $('#sbmt').hide();
            $('#loading').show();
            __echo('<b>1.</b> Тестированиe внешних запросов из php-функции <b>file_get_contents()</b>...');
            $.get('?ajax=1&task=testFileGetContents', function (data) {
                __echo(data);

                __echo('<br><b>2.</b> Тестированиe работы библиотеки <b>CURL</b>...');
                $.get('?ajax=1&task=testCurl', function (data) {
                    __echo(data);


                    __echo('<br><b>3.</b> Тестированиe работы библиотеки <b>CURL FOLLOWLOCATION</b>...');
                    $.get('?ajax=1&task=testCurlFollowLocation', function (data) {
                        __echo(data);


                        __echo('<br><b>4.</b> Тестированиe внешних запросов из php-функции <b>copy()</b>...');
                        $.get('?ajax=1&task=testCopy', function (data) {
                            __echo(data);

                            __echo('<br><b>5</b>. Тестированиe сохранения файла из php-функции <b>file_get_contents()</b>...');
                            $.get('?ajax=1&task=testFileGetContentsSaveFile', function (data) {
                                __echo(data);

                                __echo('<br><b>6</b>. Тестированиe сохранения файла с помощью библиотеки <b>CURL</b>...');
                                $.get('?ajax=1&task=testCurlSaveFile', function (data) {
                                    __echo(data);

                                    __echo('<br><b>7.</b> Заголовки <b>CURL Head</b>...');
                                    $.get('?ajax=1&task=testCurlHead', function (data) {
                                        __echo(data);

                                    __echo('<br><br><h5>Тестирование завершено!</h5>');

                                    $('#sbmt').show();
                                    $('#loading').hide();

                                }); // testCurlSaveFile

                            }); // testFileGetContentsSaveFile

                        }); // testCopy

                     });  // testCurl

                });  // testCurlFollowLocation


            }); // testFileGetContents

            });  // testCurlHead

            $('#sbmt').attr('value', 'Перезапустить тестирование...');
            /*__echo('Тестированиe php-функции file_get_contents()...');
             $.ajax({
             type: "GET",
             async: false,
             url: "?ajax=1&task=testFileGetContents",
             }).done(function( data ) {
             __echo( data );
             });
             __echo('<br>Тестированиe библиотеки CURL...');
             $.ajax({
             type: "GET",
             async: false,
             url: "?ajax=1&task=testCurl",
             }).done(function( data ) {
             __echo( data );
             });*/
            /*        __echo('<br>Тестированиe библиотеки CURL...');
             $.get('?ajax=1&task=testCurl', function( data ) {
             __echo( data );
             });  */
            /*        $.get('?ajax=1&task=2', function( data ) {
             __echo( data );
             });  */
        }
        function __echo(text) {
            $('#display').html($('#display').html() + text);
        }
    </script>
    <style>
        * {
            font-family: Verdana;
            font-size: 13px;
        }

        h5 {
            font-size: 19px;
            font-weight: normal;
            padding-bottom: 20px;
            border-bottom: 1px solid #ccc;
        }

        #display {
            margin-top: 20px;
        }
        .t{
            color: blue;
        }
        .set{
            color: #800000;
        }
    </style>
</head>
<body>
<h5>Тестирование веб-сервера/хостинга<br>
    <small>на предмет использования плагина WPGrabber  <a target="_blank" href="http://wpgrabber-tune.blogspot.com/">wpgrabber-tune.blogspot.com</a><br>
        Telegram: <a href="tg://resolve?domain=servakov" title="Заказать настройку лент wpgrabber в Telegram">@servakov</a> <br />
        VK: <a href="https://vk.com/wpgrabbertune" target="_blank" title="Заказать настройку лент wpgrabber в VK"><b>https://vk.com/wpgrabbertune</b></a>

    </small>
</h5>

<p>Значение <b class="set">phpversion()</b> = <b><?php
echo phpversion();
?></b></p>

<p>Значение <b class="set">php_sapi_name()</b> = <b><?php
echo php_sapi_name();
?></b></p>

<p>Значение <b class="set">memory_limit</b> = <b><?php
echo ini_get('memory_limit');
?></b></p>

<p>Значение <b class="set">max_execution_time</b> = <b><?php
echo ini_get('max_execution_time');
?></b></p>

<p>Значение <b class="set">max_input_time</b> = <b><?php
echo ini_get('max_input_time');
?></b></p>

<p>Значение <b class="set">allow_url_fopen</b> = <b><?php
echo ini_get('allow_url_fopen');
?></b></p>


<p>Значение <b class="set">open_basedir</b> = <b><?php
if(ini_get('open_basedir'))
{
    echo ini_get('open_basedir');
}
else
{
    echo 'no value';
}
?></b></p>


<p>function_exists <b class="set">file_put_contents</b> = <b><?php
if(function_exists('file_put_contents'))
{
    echo "available<br />\n";
}
else
{
    echo "not available<br />\n";
}
?></b></p>


<p>function_exists <b class="set">getimagesize</b> = <b><?php
if(function_exists('getimagesize'))
{
    echo "available<br />\n";
}
else
{
    echo "not available<br />\n";
}
?></b></p>



<p>Поддержка <b class="set">curl</b> = <?php
if (extension_loaded('curl'))
{
    echo "<span class='t'>True</span>";
    $c = curl_version();
    echo "<br><b class=\"set\">curl version</b> = <b>".$c['version']."</b>";
    echo "<br><b class=\"set\">ssl_version</b> = <b>".$c['ssl_version']."</b>";
}
else
{
    echo ' - <font color="red">возможно не поддерживается!</font>';
}
?></b></p>

<p>Поддержка <b class="set">mbstring</b> = <b><?php
if (extension_loaded('mbstring'))  echo "<span class='t'>True</span>";
?></b></p>

<p>Поддержка <b class="set">pcre</b> = <b><?php
if (extension_loaded('pcre'))  echo "<span class='t'>True</span>";
?></b></p>

<p>Поддержка <b class="set">iconv</b> = <b><?php
if (extension_loaded('iconv'))  echo "<span class='t'>True</span>";
?></b></p>

<p>Поддержка <b class="set">GD library </b> = <b><?php
if (extension_loaded('gd'))  echo "<span class='t'>True</span>";
?></b></p>


<!-- <p>get_loaded_extensions <b> <?php print_r(get_loaded_extensions()); ?></b></p> -->



<input id="sbmt" type="button" value="Начать тестирование..." onclick="__start();"/>
<img id="loading" src="https://abload.de/img/spinner53ra5.gif" style="display: none;"/>

<div id="display"></div>
<!-- <p>Значение <b class="set">phpinfo()</b> = <b> -->
<?php
/*foreach ($_SERVER as $header => $value )
{ if(strpos($header , 'REMOTE')!== false || strpos($header , 'HTTP')!== false ||
	strpos($header , 'REQUEST')!== false) {echo $header.' = '.$value."<br>\n"; }
}
    #var_export($_SERVER);

echo phpinfo();
*/
#echo testCurlHead();
?></b></p>
</body>
</html>