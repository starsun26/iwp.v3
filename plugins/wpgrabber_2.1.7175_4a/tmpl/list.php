<style>
    .update-nag
    {
        display:none !important;
    }

    .wp-list-table td.column-published {
        width: 50px;
    }

    .wp-list-table #type {
        width: 100px;
    }

    .wp-list-table #id {
        width: 50px;
    }

    .wp-list-table #last_update {
        width: 120px;
    }

    .wp-list-table #published {
        text-align: center;
        width: 100px;
    }
</style>
<div class="wrap">
    <form method="post">
        <div id="icon-themes" class="icon32"></div>
        <h3>WPGrabber > Список лент</h3><hr>
        <h3>Заказать настройку: <a href="http://wpgrabber-tune.blogspot.com/2017/11/wpgrabber.html" target="_blank"                                         title="Заказать платную настройку ленты" alt="Order parsing links">wpgrabber-tune.blogspot.com</a>,                                         Telegram: <a href="tg://resolve?domain=servakov" target="_blank" title="Заказать настройку лент в Telegram"><b>servakov</b></a></h3>
        <?php $wpgrabberTable->search_box('поиск', 'search_id'); ?>
        <?php echo $wpgrabberTable->display(); ?>
    </form>
