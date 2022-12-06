<?php
/**
 * @package WPGrabber
 * Plugin Name: WPGrabber 2.1.7175.4a
 * Plugin URI: http://wpgrabber-tune.blogspot.com/
 * Description: for php8 fix [23.06.2022], bugfix[11.11.2021]
 * Version: 2.1.7175.4a
 * Author: tushov.ru, servakov
 * Author URI: http://wpgrabber-tune.blogspot.com/
 * GitHub Plugin URI: https://github.com/wpgrabber-tune
 */
if (defined('WPGRABBER_VERSION')) {
    die('На сайте активирован плагин WPGrabber версии ' . WPGRABBER_VERSION . '. Пожалуйста, деактивируйте его перед активацией данного плагина.');
}
define('WPGRABBER_VERSION', '2.1.7175.4a');

define('WPGRABBER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPGRABBER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPGRABBER_PLUGIN_FILE', __FILE__);

require WPGRABBER_PLUGIN_DIR.'init.php';

function delFirstPic($content)
{
    $content = preg_replace("~<img[^>]+>~is", "", $content, 1);
    return $content;
}

if (get_option('wpg_' .'delFirstPic') == '1') add_filter ('the_content', 'delFirstPic');
if (get_option('wpg_' .'delFirstPic') == '0') remove_filter ('the_content', 'delFirstPic');


#
# https://wp-kama.ru/function/wp_enqueue_script#primery
#
function wpg_instagram_embed() {
    wp_register_script( 'instagram_embed', 'https://www.instagram.com/embed.js');
    // in_footer
    #wp_register_script( 'instagram_embed', 'https://www.instagram.com/embed.js', array(), false, true);

    wp_enqueue_script( 'instagram_embed' );
    // in_footer
    #wp_enqueue_script( 'instagram_embed', 'https://www.instagram.com/embed.js', array(), false, true);

}
if (get_option('wpg_' .'instagram_embed_on') == '1') add_action( 'wp_enqueue_scripts', 'wpg_instagram_embed' );


?>
