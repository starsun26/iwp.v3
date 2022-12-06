<style>
    .update-nag
    {
        display:none !important;
    }

    .button-panel {
        padding-top: 30px;
        padding-left: 10px;
        border-top: 1px solid #cacaca;
        margin-top: 30px;
    }

    .button-panel p.submit {
        float: left;
        margin-right: 10px;
        padding: 0px;
        margin: 0px;
        margin-right: 10px;
    }

    .button-panel input.button {
        margin-right: 10px;
        float: left;
        display: block;
    }

    div.tab-content {
        display: none;
        padding: 5px;
        padding-top: 20px;
    }

    .tab-content-table tr td, .tab-content-table tr th {
        padding-top: 13px;
        padding-bottom: 13px;
    }

    div.tab-content fieldset {
        padding: 10px;
        border: 1px solid #cacaca;
        margin-top: 15px;
    }

    .myBold, div.tab-content fieldset legend {
        font-weight: bold;
    }

    .tab-content-table tr td, .tab-content-table tr th {
        padding-top: 3px;
        padding-bottom: 3px;
    }

    .wrap fieldset {
        padding: 10px;
        border: 1px solid #cacaca;
        margin-top: 10px;
    }

    .wrap fieldset legend {
        font-weight: bold;
    }
</style>
<script>
    jQuery(document).ready(function ($) {
        $('.nav-tab-wrapper a').click(function () {
            $('.nav-tab-wrapper a').attr('class', 'nav-tab');
            $('.tab-content').hide();
            $('#div_' + $(this).attr('id')).show();
            $(this).attr('class', 'nav-tab nav-tab-active');
            $('#tab-active').val($(this).attr('id').replace('tab', ''));
        });
        $('#tab1').trigger('click');
    });
</script>
<div class="wrap">
    <form method="post">
        <div id="icon-options-general" class="icon32"></div>
        <h3>WPGrabber > Настройки</h3><hr>
        <h3>Заказать настройку: <a href="http://wpgrabber-tune.blogspot.com/2017/11/wpgrabber.html" target="_blank"                                         title="Заказать платную настройку ленты" alt="Order parsing links">wpgrabber-tune.blogspot.com</a>,                                         Telegram: <a href="tg://resolve?domain=servakov" target="_blank" title="Заказать настройку лент в Telegram"><b>servakov</b></a></h3>
        <h2 class="nav-tab-wrapper">
            <a href="#tab1" id="tab1" class="nav-tab<?php echo $tab == 1 ? ' nav-tab-active' : ''; ?>">Основные</a>
            <a href="#tab2" id="tab2" class="nav-tab<?php echo $tab == 2 ? ' nav-tab-active' : ''; ?>">Картинки</a>
            <a href="#tab3" id="tab3" class="nav-tab<?php echo $tab == 3 ? ' nav-tab-active' : ''; ?>">Синонимизация</a>
            <a href="#tab4" id="tab4" class="nav-tab<?php echo $tab == 4 ? ' nav-tab-active' : ''; ?>">Переводы</a>
            <a href="#tab5" id="tab5" class="nav-tab<?php echo $tab == 5 ? ' nav-tab-active' : ''; ?>">Автообновление</a>
            <a href="#tab6" id="tab6" class="nav-tab<?php echo $tab == 6 ? ' nav-tab-active' : ''; ?>">Дополнительно</a>
        </h2>

        <?php $tab = ''; ?>
        <div class="tab-content" id="div_tab1"<?php echo $tab == 1 ? ' style="display: block;"' : ''; ?>>
            <fieldset>
                <legend>Настройка сетевых запросов</legend>
                <table class="tab-content-table">
                    <tr>
                        <td width="395">Для запросов использовать метод</td>
                        <td><?php echo WPGHelper::selectList('options[getContentMethod]', array('0' => 'CURL', '1' => 'file_get_contents', '2' => 'fsockopen'), get_option('wpg_' . 'getContentMethod'), 1); ?></td>
                    </tr>
                    <tr>
                        <td width="395">Для скачивания файлов (картинок) использовать метод</td>
                        <td><?php echo WPGHelper::selectList('options[saveFileUrlMethod]', array('0' => 'copy', '1' => 'CURL', '2' => 'file_get_contents + file_put_contents'), get_option('wpg_' . 'saveFileUrlMethod'), 1); ?></td>
                    </tr>
                    <tr>
                        <td>Включить обработку <b>редиректов</b> <br></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[curlRedirectOn]', get_option('wpg_' . 'curlRedirectOn')); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i>CURLOPT_FOLLOWLOCATION</i></td>
                    </tr>

                    <tr>
                        <td>Включить <b>сохранять cookie</b> в файл <i>tmpDir/cookies.txt</i> <br></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[curlCookiesOn]', get_option('wpg_' . 'curlCookiesOn')); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i>CURLOPT_COOKIEFILE, CURLOPT_COOKIEJAR</i></td>
                    </tr>
                    <tr>
                        <td>Включить <b>стирать </b> файл <i>tmpDir/cookies.txt</i> при каждом запуске <br></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[curlCookiesClean]', get_option('wpg_' . 'curlCookiesClean')); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i>CURLOPT_COOKIEFILE, CURLOPT_COOKIEJAR</i></td>
                    </tr>
                    <tr>
                        <td>Содержимое заголовка "<b>User-Agent</b>", посылаемого в HTTP-запросе</td>
                        <td><input type="text" size="85" name="options[userAgent]"
                                   value="<?php echo get_option('wpg_' . 'userAgent'); ?>"/> &nbsp; по умолчанию: <i class="myBold">Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36</i><br>
                                Список веб-браузеров агентов - <strong><a href="https://ru.myip.ms/browse/comp_browseragents/%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA_%D0%B2%D0%B5%D0%B1_%D0%B1%D1%80%D0%B0%D1%83%D0%B7%D0%B5%D1%80%D0%BE%D0%B2_%D0%B0%D0%B3%D0%B5%D0%BD%D1%82%D0%BE%D0%B2.html" target="_blank">здесь</a></strong> и
                                <strong><a href="https://user-agents.net/browsers" target="_blank">здесь</a></strong>
                        </td>
                    </tr>
                    <tr>
                        <td>Включить <b>GZIP</b> сжатие <br></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[curlGzipOn]', get_option('wpg_' . 'curlGzipOn')); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i>CURLOPT_HTTPHEADER</i> - Accept-Encoding: gzip,
                                CURLOPT_ENCODING , "gzip"</td>
                    </tr>
                    <tr>
                        <td>Максимальное время ожидания ответа от сервера</td>
                        <td><input type="text" size="1" name="options[requestTime]"
                                   value="<?php echo get_option('wpg_' . 'requestTime'); ?>"/> <i>(0 - неограничено,
                                пустое значение - по умолчанию</i></td>
                    </tr>
                </table>
            </fieldset>
            <fieldset>
                <legend>Настройка Прокси</legend>
                <table class="tab-content-table">
                    <tr>
                        <td>Включить <b>Proxy</b></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[curlProxyOn]', get_option('wpg_' . 'curlProxyOn'), array(
                                ' onchange="if (this.value==1){
                                    document.getElementById(\'tr-proxy-host-port\').style.display=\'\';
                                    document.getElementById(\'tr-proxy-list-use\').style.display=\'\';
                                    document.getElementById(\'tr-proxy-type\').style.display=\'\';
                                    document.getElementById(\'tr-proxy-auth\').style.display=\'\';
                                    }else{
                                    document.getElementById(\'tr-proxy-host-port\').style.display=\'none\';
                                    document.getElementById(\'tr-proxy-list-use\').style.display=\'none\';
                                    document.getElementById(\'tr-proxy-type\').style.display=\'none\';
                                    document.getElementById(\'tr-proxy-auth\').style.display=\'none\';
                                    }" ',
                                ' onchange="if (this.value==0){
                                    document.getElementById(\'tr-proxy-host-port\').style.display=\'none\';
                                    document.getElementById(\'tr-proxy-list-use\').style.display=\'none\';
                                    document.getElementById(\'tr-proxy-type\').style.display=\'none\';
                                    document.getElementById(\'tr-proxy-auth\').style.display=\'none\';
                                    }else{
                                    document.getElementById(\'tr-proxy-host-port\').style.display=\'\';
                                    document.getElementById(\'tr-proxy-list-use\').style.display=\'\';
                                    document.getElementById(\'tr-proxy-type\').style.display=\'\';
                                    document.getElementById(\'tr-proxy-auth\').style.display=\'\';
                                    }" '
                            ));
                            ?> &nbsp;&nbsp;&nbsp;&nbsp;<i>для работы через прокси сервер
                                </i></td>
                    </tr>
                    <tr id="tr-proxy-list-use"<?php
                    if (!get_option('wpg_' . 'curlProxyOn'))
                        echo ' style="display:none;"';
                    ?>>
                        <td>Включить <b>Proxy list</b></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[curlProxyListOn]', get_option('wpg_' . 'curlProxyListOn'), array(
                                ' onchange="if (this.value==1){
                                    document.getElementById(\'tr-proxy-list-on\').style.display=\'\';
                                    document.getElementById(\'tr-proxy-list-off\').style.display=\'none\';
                                    }else{
                                    document.getElementById(\'tr-proxy-list-on\').style.display=\'none\';
                                    document.getElementById(\'tr-proxy-list-off\').style.display=\'\';
                                    }" ',
                                ' onchange="if (this.value==0){
                                    document.getElementById(\'tr-proxy-list-on\').style.display=\'none\';
                                    document.getElementById(\'tr-proxy-list-off\').style.display=\'\';
                                    }else{
                                    document.getElementById(\'tr-proxy-list-on\').style.display=\'\';
                                    document.getElementById(\'tr-proxy-list-off\').style.display=\'none\';
                                    }" '
                            ));
                            ?> &nbsp;&nbsp;&nbsp;&nbsp;<i>использовать для работы список прокси серверов
                                </i></td>
                    </tr>
                    <tr id="tr-proxy-host-port"<?php
                    if (!get_option('wpg_' . 'curlProxyOn'))
                        echo ' style="display:none;"';
                    ?>>
                        <td>Прокси в формате <b>host:port</b> посылаемого в HTTP-запросе</td>
                        <td id="tr-proxy-list-off"<?php
                        if (get_option('wpg_' . 'curlProxyListOn'))
                            echo ' style="display:none;"';
                        ?>><input type="text" size="21" name="options[curlProxyHostPort]" value="<?php echo get_option('wpg_' . 'curlProxyHostPort'); ?>"/> <i>CURLOPT_PROXY</i><br>Бесплатные (недолговечные) прокси без авторизации - <strong><a href="http://spys.one/proxys/" target="_blank">здесь</a></strong> и <strong><a href="http://free-proxy.cz/ru/proxylist/country/all/https/ping/level1" target="_blank">здесь</a></strong></td>

                        <td id="tr-proxy-list-on"<?php
                        if (!get_option('wpg_' . 'curlProxyListOn'))
                            echo ' style="display:none;"';
                        ?>><textarea rows="7" style="width:40%" name="options[curlProxyHostPort_List]"><?php echo WPGTools::esc(get_option('wpg_' . 'curlProxyHostPort_List')); ?></textarea><i>Список, разделитель \r </i><br>Бесплатные (недолговечные) прокси без авторизации - <strong><a href="http://spys.one/proxys/" target="_blank">здесь</a></strong> и <strong><a href="http://free-proxy.cz/ru/proxylist/country/all/https/ping/level1" target="_blank">здесь</a></strong></td>

                    </tr>
                    <tr id="tr-proxy-type"<?php
                    if (!get_option('wpg_' . 'curlProxyOn'))
                        echo ' style="display:none;"';
                    ?>>
                        <td width="395">Для запросов использовать <b>Тип</b> proxy</td>
                        <td><?php echo WPGHelper::selectList('options[curlProxyType]', array('0' => 'CURLPROXY_HTTP', '1' => 'CURLPROXY_SOCKS5', '2' => 'CURLPROXY_SOCKS4A', '3' => 'CURLPROXY_SOCKS5_HOSTNAME'), get_option('wpg_' . 'curlProxyType'), 1); ?>
                            <i>CURLOPT_PROXYTYPE</i></td>
                    </tr>
                    <tr id="tr-proxy-auth"<?php
                    if (!get_option('wpg_' . 'curlProxyOn'))
                        echo ' style="display:none;"';
                    ?>>
                        <td>Авторизация на прокси сервере
                        </td>
                        <td><input type="text" size="25" name="options[curlProxyUserPwd]"
                                   value="<?php echo get_option('wpg_' . 'curlProxyUserPwd'); ?>"/> <i>CURLOPT_PROXYUSERPWD</i>, - Логин и пароль, записанные в виде <b>UserName:pAssW0rd</b>, используемые при соединении
                            через прокси.</td>
                    </tr>
                </table>
            </fieldset>

            <fieldset>
                <legend>Настройка процесса импорта</legend>
                <table class="tab-content-table">
                    <tr>
                        <td width="395">Время выполнение основного процесса импорта в секундах</td>
                        <td><input type="text" size="5" name="options[phpTimeLimit]"
                                   value="<?php echo get_option('wpg_' . 'phpTimeLimit'); ?>"/> <i>(0 - неограничено,
                                пустое значение - по умолчанию: 30 сек.</i></td>
                    </tr>
                    <tr>
                        <td>Разбивать процесс импорта на части</td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[useTransactionModel]', get_option('wpg_' . 'useTransactionModel')); ?></td>
                    </tr>
                    <tr>
                        <td width="395">Задержка импорта в сек.</td>
                        <td><input type="text" size="5" name="options[stopTime]" value="<?php echo get_option('wpg_' . 'stopTime'); ?>" /> <i>Используйте если у вас не успевают загружаться картинки на сервер или донор блокирует слишком частые соединения, возможно пригодится и при парсинге через бесплатные прокси (Обычно 2-10 сек. вполне достаточно)<i></td>
                    </tr>
                </table>
            </fieldset>
            <fieldset>
                <legend>Отладка</legend>
                <table class="tab-content-table">
                    <tr>
                        <td>Включить запись <b>getContent</b> запросов <br></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[getContentWriteLogsOn]', get_option('wpg_' . 'getContentWriteLogsOn')); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i><!-- (tmpDir. md5($url).".html" --></i></td>
                    </tr>
                    <tr>
                        <td>Включить запись <b>copyUrlFile</b> запросов <br></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[getCopyUrlFileWriteLogsOn]', get_option('wpg_' . 'getCopyUrlFileWriteLogsOn')); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i><!-- (tmpDir. md5($url).".html" --></i></td>
                    </tr>
                    <tr>
                        <td>Включить <b>заголовки ответа</b> в вывод <br></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[curlHeaderOn]', get_option('wpg_' . 'curlHeaderOn')); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i>CURLOPT_HEADER</i></td>
                    </tr>
                    <tr>
                        <td>Включить <b>отправляемые заголовки</b> HTTP-запроса <br></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[curlinfoHeaderOutOn]', get_option('wpg_' . 'curlinfoHeaderOutOn')); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i>CURLINFO_HEADER_OUT</i></td>
                    </tr>
                    <tr>
                        <td>Включить вывод лог <b>imageProcessor</b> обработки <br></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[imageProcessorLogsOn]', get_option('wpg_' . 'imageProcessorLogsOn')); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i>Поиск картинок в тексте</i></td>
                    </tr>
                    <tr>
                        <td>Включить вывод лог <b>imageHtmlCode</b> обработки <br></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[imageHtmlCodeLogsOn]', get_option('wpg_' . 'imageHtmlCodeLogsOn')); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i>Обработка изображений из шаблона</i></td>
                    </tr>
                </table>
            </fieldset>

            <fieldset>
                <legend>Настройка каталогов</legend>
                <table class="tab-content-table">
                    <tr>
                        <td>Каталог временных файлов</td>
                        <td><input type="text" name="options[testPath]"
                                   value="<?php echo get_option('wpg_' . 'testPath'); ?>" size="60"/></td>
                    </tr>
                </table>
            </fieldset>
        </div>

        <!-- Картинки -->
        <?php $tab = ''; ?>
        <div class="tab-content" id="div_tab2"<?php echo $tab == 2 ? ' style="display: block;"' : ''; ?>>
            <table class="tab-content-table">
                <tr>
                    <td>Каталог хранения картинок из постов</td>
                    <td><input type="text" name="options[imgPath]" value="<?php echo get_option('wpg_' . 'imgPath'); ?>" size="60"/></td>
                </tr>
                <tr>
                    <td>Удалить первое изображение из статьи<br></td>
                    <td><?php echo WPGHelper::yesNoRadioList('options[delFirstPic]', get_option('wpg_' .'delFirstPic')); ?>&nbsp;&nbsp;&nbsp;&nbsp;<i>Для тем в которых отображаются миниатюры и дублируются в статье</i></td>
                </tr>
                <tr>
                    <td>Включить <a target="_blank" href="http://wpgrabber-tune.blogspot.com/2020/01/wpgrabber-instagram.html">отображение</a><br>постов <b>instagram</b> внутри записи</td>
                    <td><?php echo WPGHelper::yesNoRadioList('options[instagram_embed_on]', get_option('wpg_' .'instagram_embed_on')); ?>&nbsp;&nbsp;&nbsp;&nbsp;<i>В настройках ленты должны быть <b>разрешены</b> теги <b>&#60;a&#62;</b> и <b>&#60;blockquote&#62;</b></i></td>
                </tr>
            </table>
        </div>

        <!-- Синонимизация -->
        <div class="tab-content" id="div_tab3"<?php echo $tab == 3 ? ' style="display: block;"' : ''; ?>>
            <fieldset>
                <h3>
                    Для работы синонимайзера необходимо приобрести пакет символов на <b><a href="http://textorobot.ru/index.php?option=com_billing&partnername=servakov">textorobot.ru</a></b> и получить <b>API-ключ</b>, который надо указать ниже.
                </h3>
            </fieldset>
            <fieldset>
                <legend>Textorobot</legend>
                <table class="tab-content-table" width="95%">
                    <tr>
                        <td width="290">Включить синонимизацию</td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[textorobotEnabled]', get_option('wpg_' . 'textorobotEnabled')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">API-ключ Textorobot.ru</td>
                        <td>
                            <input type="text" name="options[textorobotApiKey]" value="<?php echo get_option('wpg_' . 'textorobotApiKey'); ?>" size="64"/>
                        </td>
                    </tr>

                    <?php if (isset($textorobotBalance->synonymSymbolBalance)) { ?>
                    <tr>
                        <td valign="top"></td>
                        <td>
                                Ваш баланс: <a href="https://textorobot.ru/баланс.html" target="_blank"><?=$textorobotBalance->synonymSymbolBalance?></a> символов<br>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <tr>
                        <td valign="top"></td>
                        <td>
                                <a href="https://textorobot.ru/index.php?option=com_billing&partnername=servakov" target="_blank">Получить
                                    API-ключ Textorobot</a><br>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </fieldset>
        </div>

        <!-- Переводы -->
        <div class="tab-content" id="div_tab4"<?php echo $tab == 4 ? ' style="display: block;"' : ''; ?>>
            <fieldset>

                <legend>Google Cloud Translation v2</legend>
                <table class="tab-content-table" width="95%">
                    <td valign="top" width="290">API-ключ</td>
                    <td><textarea rows="2" style="width:100%"
                                  name="options[google_translate_api_key]"><?php echo WPGTools::esc(get_option('wpg_' . 'google_translate_api_key')); ?></textarea>
                            <a href="https://console.cloud.google.com/apis/library/translate.googleapis.com" target="_blank">Получить API-ключ Google Cloud Translation</a><br>
                            <a href="/wp-admin/admin.php?page=wpgrabber-settings&translate_cloud_google=update"
                               style="font-weight: bold;"><?php echo get_option("wpg_googleTransLangs") ? 'Обновить базу переводов с сервиса Google Cloud Translation' : '<font color="red">Загрузить базу переводов с сервиса Google Cloud Translation</font>'; ?></a>
                    </td>
                    </tr>
                    <!--
                    <tr>
                        <td valign="top" width="290">Project ID</td>
                        <td><textarea rows="2" style="width:100%"
                                      name="options[google_translate_project_id]"><?php echo WPGTools::esc(get_option('wpg_' . 'google_translate_project_id')); ?></textarea>
                        </td>
                    </tr>
                    -->
                    <tr>
                    <td colspan="2">
                    Подробная <a href="https://teletype.in/@itservice/8IDm4Iyb3" target="_blank">инструкция с картинками</a>
                    </td>
                    </tr>
                </table>
            </fieldset>

            <fieldset>

                <legend>Яндекс.Облако Translate</legend>
                <table class="tab-content-table" width="95%">
                    <td valign="top" width="290">OAuth-токен Яндекс (по умолчанию)</td>
                    <td><textarea rows="2" style="width:100%"
                                  name="options[yandexOauth]"><?php echo WPGTools::esc(get_option('wpg_' . 'yandexOauth')); ?></textarea>
                    </td>
                    </tr>
                    <tr>
                    <td valign="top" width="290">Идентификатор каталога</td>
                    <td><textarea rows="2" style="width:100%"
                                  name="options[yandexFolderId]"><?php echo WPGTools::esc(get_option('wpg_' . 'yandexFolderId')); ?></textarea><br><i>
                                  <a href="/wp-admin/admin.php?page=wpgrabber-settings&translate_cloud_yandex=update"
                               style="font-weight: bold;"><?php echo get_option("wpg_yandexCloudTransLangs") ? 'Обновить базу переводов с сервиса Яндекс.Облако Translate' : '<font color="red">Загрузить базу переводов с сервиса Яндекс.Облако Translate</font>'; ?></a>
                                  </i>

                    </td>
                    </tr>
                    <tr>
                    <td colspan="2">
                    Подробная <a href="https://wpgrabber-tune.blogspot.com/2020/06/wpgrabber-v2170-yandex-translate.html" target="_blank">инструкция с картинками</a>
                    </td>
                    </tr>
                </table>
            </fieldset>

            <!--
            <fieldset>
                <legend>Яндекс.Перевод</legend>
                <table class="tab-content-table" width="95%">
                    <tr>
                        <td width="290">Включить режим MULTIKEY</td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[multiKeyTRNSL]', get_option('wpg_' . 'multiKeyTRNSL')); ?>
                            <br><i>Если режим MULTIKEY, в лентах API-ключ не указываете. Ключи разделяйте через "ENTER"</i></td>
                    </tr>
                    <tr>
                    <td valign="top">API-ключ Яндекс (по умолчанию)</td>
                    <td><textarea rows="2" style="width:100%"
                                  name="options[yandexApiKey]"><?php echo WPGTools::esc(get_option('wpg_' . 'yandexApiKey')); ?></textarea>
                        <i>
                            <a href="https://tech.yandex.ru/keys/get/?service=trnsl" target="_blank">Получить бесплатный API-ключ Яндекс</a><br>
                            <a href="/wp-admin/admin.php?page=wpgrabber-settings&translate_yandex=update"
                               style="font-weight: bold;"><?php echo get_option("wpg_yandexTransLangs") ? 'Обновить базу переводов с сервиса Яндекс.Перевод' : '<font color="red">Загрузить базу переводов с сервиса Яндекс.Перевод</font>'; ?></a>
                        </i></td>
                    </tr>
                    <tr>
                        <td width="290">Загрузить базу переводов из локального файла </td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[yandexTransLangsFile]', get_option('wpg_' . 'yandexTransLangsFile')); ?>
                            <br>
                             <i>Файл с базой: <b><?=WPGRABBER_PLUGIN_DIR. "yandex_langs.xml";?></b></i> </td>
                    </tr>

                </table>
            </fieldset>
                    -->

                    <!-- <tr>
                      <td width="395">API-ключ сервиса Bing Переводчик</td>
                      <td><input size="100" type="text" name="options[bingApiKey]" value="<?php echo WPGTools::esc(get_option('wpg_'.'bingApiKey')); ?>" /></td>
                    </tr> -->

        </div>

        <!-- Автообновление -->
        <div class="tab-content" id="div_tab5"<?php echo $tab == 5 ? ' style="display: block;"' : ''; ?>>
            <fieldset>
                <table class="tab-content-table">
                    <tr>
                        <td width="290">Включить автообновление лент</td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[cronOn]', get_option('wpg_' . 'cronOn')); ?></td>
                    </tr>
                    <tr>
                        <td width="290"><b>Автоматически отключать ленты</b> с ошибками и ленты которые не успевают
                            обновляться?</b></td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[offFeedsModeOn]', get_option('wpg_' . 'offFeedsModeOn')); ?></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>Для ручного запуска и проверки скрипта автообновления лент перейдите по адресу: <a
                                target="_blank"
                                href="<?php echo home_url('/?wpgrun=1'); ?>"><?php echo home_url('/?wpgrun=1'); ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Метод обновления</td>
                        <td><?php echo WPGHelper::selectList('options[methodUpdate]', array(0 => '1. WordPress CRON через сайт (зависит от посещаемости сайта!)', 1 => '2. Настроенное CRON-задание на веб-сервере (хостинге)'), get_option('wpg_' . 'methodUpdate'), 1, 'onchange="if (this.value==1){document.getElementById(\'div-methodUpdate\').style.display=\'\';}else{document.getElementById(\'div-methodUpdate\').style.display=\'none\';}"'); ?>
                            <div style="color: #9D0000; font-style: italic; padding-top: 5px;"
                                 id="div-methodUpdate"<?php echo get_option('wpg_' . 'methodUpdate') ? '' : ' style="display:none;"'; ?>>
                                <b>Внимание!</b> Для работы данного метода обновления Вам потребуется настроить
                                CRON-задание на Вашем сервере (хостинге) <a
                                    href="http://wpgrabber-tune.blogspot.ru/2017/11/wpgrabber-cron.html"
                                    target="_blank">( подробнее )</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Порядок и периоды обновления лент</td>
                        <td><?php echo WPGHelper::selectList('options[methodUpdateSort]', array('0' => 'по порядку через заданный интервал', '1' => 'учитывая индивидуальные периоды каждой ленты'), get_option('wpg_' . 'methodUpdateSort'), 1, 'onchange="if (this.value==1){document.getElementById(\'tr-cronInterval\').style.display=\'none\';}else{document.getElementById(\'tr-cronInterval\').style.display=\'\';}"'); ?></td>
                    </tr>
                    <tr id="tr-cronInterval"<?php echo get_option('wpg_' . 'methodUpdateSort') ? ' style="display:none;"' : ''; ?>>
                        <td>Интервал запуска процессов обновления / периоды обновления (мин.)</td>
                        <td><input type="text" size="5" name="options[cronInterval]"
                                   value="<?php echo get_option('wpg_' . 'cronInterval'); ?>"/> <i>(пустое значение
                                будет заменено на 60 минут</i></td>
                    </tr>
                    <tr>
                        <td>Кол-во лент обновляемых за один процесс автообновления</td>
                        <td><?php echo WPGHelper::selectList('options[countUpdateFeeds]', array(1, 2, 3, 4, 5), get_option('wpg_' . 'countUpdateFeeds')); ?>
                            <i>(оптимальным является не более 1-2 лент, для ненагруженных лент можно выбрать 5</i></td>
                    </tr>
                </table>
            </fieldset>
        </div>

        <!-- Дополнительно -->
        <div class="tab-content" id="div_tab6"<?php echo $tab == 6 ? ' style="display: block;"' : ''; ?>>
            <fieldset>
                <legend>Логирование ошибок плагина</legend>
                <table class="tab-content-table">
                    <tr>
                        <td width="395">Включить логирование ошибок</td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[logErrors]', get_option('wpg_' . 'logErrors')); ?></td>
                    </tr>
                    <?php /*
                    <tr>
                        <td width="395">Автоматически отправлять письма с ошибками на адрес службы технической поддержки: bug@wpgrabber.ru</td>
                        <td><?php echo WPGHelper::yesNoRadioList('options[sendErrors]', get_option('wpg_' .'sendErrors')); ?></td>
                    </tr>
                    */ ?>
                    <tr>
                        <td colspan="2">
                            <a href="?page=wpgrabber-settings&wpgrabberGetErrorLogFile" target="_blank">посмотреть
                                лог-файл ошибок</a>
                        </td>
                    </tr>
                </table>
            </fieldset>
            <!-- <fieldset>
                <legend>Количество лент на странице</legend>
                <table class="tab-content-table">
                    <tr>
                        <td width="395">Количество элементов для отображения на одной странице</td>
                        <td><input type="text" name="options[feeds_per_page]" value="<?php echo get_option('wpg_' . 'feeds_per_page'); ?>" size="60"/><i>пустое значение
                                будет заменено на 5</i></td>

                    </tr>
                </table>
            </fieldset> -->
            <?php if (wpgIsStandard()): ?>
                <fieldset>
                    <legend>Настройки сервиса Synonyma.ru</legend>
                    <table class="tab-content-table">
                        <tr>
                        <tr>
                            <td width="395">Логин</td>
                            <td>
                                <input type="text" name="options[synonymaLogin]"
                                       value="<?php echo WPGTools::esc(get_option('wpg_' . 'synonymaLogin')); ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td>Ключ</td>
                            <td>
                                <input type="text" size="50" name="options[synonymaHash]"
                                       value="<?php echo WPGTools::esc(get_option('wpg_' . 'synonymaHash')); ?>"/>
                            </td>
                        </tr>
                        </tr>
                    </table>
                </fieldset>
            <?php endif; ?>
        </div>
        <?php submit_button('Сохранить изменения', 'primary', 'saveButton'); ?>
    </form>
</div>