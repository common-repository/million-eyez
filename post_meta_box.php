<?php
add_action( 'admin_head', 'millioneyez_admin_head' );
add_action( 'admin_enqueue_scripts', 'millioneyez_admin_enqueue_scripts' );
add_action( 'media_buttons', 'millioneyez_media_buttons' );
add_action( 'transition_post_status', 'millioneyez_post_status_change', 10, 3 );
add_action( 'save_post', 'millioneyez_save_meta_box_data' );

add_action( 'admin_notices', 'millioneyez_no_key_notice' );
add_action( 'wp_ajax_millioneyez_dismiss_no_key_notice', 'millioneyez_dismiss_no_key_notice' );

function millioneyez_post_status_change($new_status, $old_status, $post) {
    $options = get_option('millioneyez_options');
    $isPublished = NULL;
    if ($options['key']) {
        if ($new_status == 'publish') {
            $isPublished = TRUE;
        } else if ($new_status == 'draft' || $new_status == 'trash') {
            $isPublished = FALSE;
        }

        if (!is_null($isPublished)) {
            $millioneyez_ids = array();
            if (preg_match_all('/\[millioneyez\s+missionid="(.*?)"/', $post->post_content, $matches)) {
                // Return special millioneyez thumbnail id
                $millioneyez_ids = $matches[1];
            }

            global $millioneyez_plugin_version;
            $service_url = 'https://api.millioneyez.com/v1.0/missions/setArticleState?authorization=Bearer+'.$options['key'].'&version='.$millioneyez_plugin_version;
            $post_permalink = wp_get_shortlink($post->ID);
            millioneyez_post_set_article_request($service_url, $isPublished, $millioneyez_ids, $post_permalink);
        }
    }
}

function millioneyez_post_set_article_request($service_url, $isPublished, $millioneyez_ids, $post_permalink) {
    $curl = curl_init($service_url);
    $curl_post_data = array(
        'isPublished' => $isPublished == TRUE ? 'true' : 'false',
        'photoboxList' => $millioneyez_ids,
        'articleUrl' => $post_permalink
    );

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));

    $curl_response = curl_exec($curl);
    curl_close($curl);
}

function millioneyez_dismiss_no_key_notice() {
	$options = get_option('millioneyez_options');
	$options["hide_key_notice"] = 1;
	update_option('millioneyez_options', $options);
    wp_die();
}

function millioneyez_no_key_notice() {
    $unsupportedArray = array('post', 'page', 'million-eyez_page_millioneyez_settings', 'plugins');
    $options = get_option('millioneyez_options');
    if (get_current_screen()->id == 'plugins') {
        if (!$options['key'] && $options['hide_key_notice'] != 1) {
            ?>
            <div class="notice-warning notice me-no-key-notice is-dismissible">
                <p><?php _e( 'Get your million eyez connected to your site from the', 'millioneyez_plugin_textdomain' ); ?>
                <a href="<?php echo admin_url('admin.php?page=millioneyez_settings') ?>">settings page</a>
                </p>
            </div>
            <?php
        }
    }
    if (in_array(get_current_screen()->id, $unsupportedArray)) {
        if ($options['key']) {
            ?>
            <div class="notice-error notice me-unsupported-browser hidden">
                <p>Hey! Seems your current browser version is not supported by million eyez. Please update your browser or
                <a href="https://www.millioneyez.com/support">check here</a> for supported list.
                </p>
            </div>
            <?php
        }
    }
}

function millioneyez_admin_enqueue_scripts(){
    global $millioneyez_plugin_version;
    $options = get_option('millioneyez_options');

    wp_enqueue_style('millioneyez_admin', plugins_url('public/styles/style.css' , __FILE__), array(), $millioneyez_plugin_version);
    wp_enqueue_style('millioneyez_edit_post', plugins_url('frontend/admin_edit_post.css' , __FILE__), array(), $millioneyez_plugin_version);
    wp_register_script('millioneyez_edit_post_script', plugins_url('frontend/admin_edit_post.js' , __FILE__), array('jquery-ui-dialog'), $millioneyez_plugin_version);
    wp_enqueue_style('wp-jquery-ui-dialog');

    if (!$options['key'] && current_user_can('manage_options')) {
        wp_localize_script('millioneyez_edit_post_script', 'millioneyez_edit_post_script', array(
            'settings_page' => get_admin_url().'admin.php?page=millioneyez_settings'
        ));
    };

    wp_enqueue_script('millioneyez_edit_post_script');
}

function millioneyez_admin_head() {
    global $millioneyez_plugin_version;
    // check user permissions
    if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
        return;
    }

    // check if WYSIWYG is enabled
    if ( 'true' == get_user_option( 'rich_editing' ) ) {
        add_filter( 'mce_external_plugins', 'millioneyez_mce_external_plugins' );
        add_filter( 'mce_buttons', 'millioneyez_mce_buttons' );
        add_editor_style( plugins_url( 'frontend/admin_mce_plugin.css?ver='. $millioneyez_plugin_version , __FILE__ ) );
        include_once dirname(__FILE__).'/includes/tmpl-millioneyez-placeholder.html';
        include_once dirname(__FILE__).'/includes/tmpl-millioneyez-photo-placeholder.html';
    }
}

function millioneyez_mce_external_plugins( $plugin_array ) {
    global $millioneyez_plugin_version;
    $plugin_array['millioneyez'] = plugins_url( 'frontend/admin_mce_plugin.js?ver='. $millioneyez_plugin_version , __FILE__ );
    return $plugin_array;
}

function millioneyez_mce_buttons( $buttons ) {
    array_push( $buttons, 'millioneyez' );
    return $buttons;
}

function millioneyez_media_buttons() {
    add_thickbox();
    $options = get_option('millioneyez_options');
    if ($options['key']) {
        echo '<a href="" class="button" onclick="millioneyez.onClickedAdd(this); return false;"><span class="dashicons dashicons-camera meCameraIcon"></span> Add Million Eyez</a>';
    } else {
        if (filter_var($options['hideConnect'], FILTER_VALIDATE_BOOLEAN) == false) {
            echo '<a href="" class="button" onclick="millioneyez.onShowNoKeyDialog(this); return false;"><span class="dashicons dashicons-camera"></span> Add Million Eyez</a>';
        }
    }
}



function millioneyez_save_meta_box_data( $post_id ) {
	if ( ! isset( $_POST['millioneyez_meta_box_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['millioneyez_meta_box_nonce'], 'millioneyez_meta_box' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	/* OK, it's safe for us to save the data now. */

	// Make sure that it is set.
	if ( ! isset( $_POST['millioneyez_new_field'] ) ) {
		return;
	}

	$my_data = sanitize_text_field( $_POST['millioneyez_new_field'] );
	update_post_meta( $post_id, '_my_meta_value_key', $my_data );
}

?>
