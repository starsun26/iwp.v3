<script>
    var is_wpgrabber_ajax_run = false;
    function wpgrabberRun(id, test, pid) {
        test = test || false;
        pid = pid || null;
        if (!pid) {
            if (is_wpgrabber_ajax_run) {
                wpgrabberLog('<b style="color: red;">Предыдущий процесс импорта еще не завершен!</b><br />');
                return;
            }
            _wpgrabberBeforeStart();
            wpgrabberLog('<b>Старт процесса импорта...</b><br />');
        }


        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpgrabberAjaxExec',
                'id': id,
                'pid': pid,
                'test': test ? 1 : 0
            },
            //dataType: 'text',
            dataType: 'json',
            timeout: 0,
            cache: false,
            success: function (result) {
                console.log(result);

                //var result=jQuery.parseJSON(response);


                var e = 'json:: Сбой сервера, проверь html_encoding!';
                if (result) {

                    if (result.log != '') {
                        wpgrabberLog(result.log);
                        //console.log("result.log: " + result.log );
                    }
                    if (result.status == 2) {
                        if (result.error != '') {
                            console.log("result.error: " + result.error );
                            e = result.error;
                        }
                    } else {
                        if (result.status != 1) {
                            wpgrabberRun(id, test, result.pid);
                        } else {
                            _wpgrabberAfterEnd();
                        }
                        return;
                    }
                }
                wpgrabberLog('<br /><b style="color: red;">' + e + '</b>');
                _wpgrabberAfterEnd();
            },

            error: function(jqXHR, textStatus, errorThrown) {
                wpgrabberLog('<br /><b style="color: red;">error: wpgrabberAjaxExec Сбой сервера!</b><br>');
                console.log("JQuery: [" + textStatus + "] with ERROR thrown: [" + errorThrown + "]");
                wpgrabberLog("JQuery: <b style=\"color: red;\">" + textStatus + "</b>: <b style=\"color: red;\">" + errorThrown + "</b>" + JSON.stringify(jqXHR) );
                //alert(errorThrown);
                //alert(textStatus);
                _wpgrabberAfterEnd();
            }
        });
    }



    function _wpgrabberBeforeStart() {
        jQuery('#ajax-log').html('');
        jQuery('#ajax-loader').show();
        jQuery('#ajax-log-wprap').show();
        jQuery.scrollTo('#ajax-log-wprap', 600, {offset: {top: -60, left: 0}});
        jQuery('#ajax-button-test, #ajax-button-exec').attr('disabled', true);
        is_wpgrabber_ajax_run = true;
    }

    function _wpgrabberAfterEnd() {
        jQuery('#ajax-button-test, #ajax-button-exec').attr('disabled', false);
        jQuery('#ajax-loader').hide();
        is_wpgrabber_ajax_run = false;
    }

    function wpgrabberLog(log) {
        jQuery('#ajax-log').append(log);
        scrollToWpgrabberLog();
    }

    function scrollToWpgrabberLog() {
        jQuery(window).stop();
        var wh = jQuery(window).outerHeight();
        var bh = jQuery('#ajax-log-wprap').outerHeight();
        var bo = jQuery('#ajax-log-wprap').offset();
        var ws = jQuery(window).scrollTop();
        // Нижний край окна
        var win_bot = ws + wh;
        // Нижний край блока
        var blo_bot = bo.top + bh + Math.round(wh / 4);
        if (blo_bot > win_bot) {
            jQuery(window).scrollTo({top: (blo_bot - wh) + 'px', left: '0px'}, 400);
        }

    }
</script>
<style>
    #ajax-log-wprap {
        border: 1px solid #cacaca;
        padding: 10px;
        background: #e5e5e5;
        margin-right: 20px;
        display: none;
    }

    #ajax-loader {
        margin: 10px;
        display: none;
    }
</style>
<br/><br/>
<div id="ajax-log-wprap">
    <div id="ajax-log"></div>
    <div id="ajax-loader"><img src="<?php echo WPGRABBER_PLUGIN_URL; ?>images/ajax-loader.gif"/></div>
</div>