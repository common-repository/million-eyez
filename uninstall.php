<?php
    // if uninstall.php is not called by WordPress, die
    if (!defined('WP_UNINSTALL_PLUGIN')) {
        die;
    }

    include 'utilities.php';

    $options = get_option('millioneyez_options');
    millioneyez_set_blogger_state('remove', $options["key"]);
    $deleted = delete_option('millioneyez_options');
?>