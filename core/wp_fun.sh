wp_core () {
    wp --allow-root --path=${VESTACP_HTML_PATH} core $1
}

wp_config () {
    wp --allow-root --path=${VESTACP_HTML_PATH} config $1
}

wp_db () {
    wp --allow-root --path=${VESTACP_HTML_PATH} db $1
}

wp_language () {
    wp --allow-root --path=${VESTACP_HTML_PATH} language core install $1  
    wp --allow-root --path=${VESTACP_HTML_PATH} site switch-language $1 
}

wp_language_list () {
    wp --allow-root --path=${VESTACP_HTML_PATH} language core list  
}

wp_site () {
    wp --allow-root --path=${VESTACP_HTML_PATH} site $1
}

wp_option () {
    wp --allow-root --path=${VESTACP_HTML_PATH} option $1
}

wp_rewrite () {
    wp --allow-root --path=${VESTACP_HTML_PATH} rewrite $1
}

wp_plugin () {
    wp --allow-root --path=${VESTACP_HTML_PATH} plugin $1
}

wp_menu () {
    wp --allow-root --path=${VESTACP_HTML_PATH} menu $1
}

wp_taxonomy () {
    wp --allow-root --path=${VESTACP_HTML_PATH} taxonomy $1
}

wp_post () {
    wp --allow-root --path=${VESTACP_HTML_PATH} --post_type=post post $1
}

wp_page () {
    wp --allow-root --path=${VESTACP_HTML_PATH} --post_type=page post $1
}

wp_term () {
    wp --allow-root --path=${VESTACP_HTML_PATH} term $1
}


wp_date () {
    wp --allow-root --path=${VESTACP_HTML_PATH} db query "USE ${wp_db_user};UPDATE wp_options SET option_value=DATE_SUB(now(), INTERVAL 20 DAY)"
}

wp_media () {
    wp --allow-root --path=${VESTACP_HTML_PATH} media $1
}
