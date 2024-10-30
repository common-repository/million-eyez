<?php
// Curator
// Settings
add_action( 'admin_enqueue_scripts', 'millioneyez_admin_settings_enqueue_scripts' );
add_action('admin_init', 'millioneyez_settings_init' );
add_action( 'wp_ajax_millioneyez_set_key', 'millioneyez_set_blogger_key' );
add_action( 'wp_ajax_millioneyez_remove_key', 'millioneyez_remove_blogger_key' );
add_action( 'wp_ajax_millioneyez_set_advanced_options', 'millioneyez_set_advanced_options' );
add_action( 'wp_before_admin_bar_render', 'millioneyez_admin_toolbar_handler' );
add_action( 'wp_enqueue_scripts', 'millioneyez_menu_badges_handler' );
add_action( 'admin_enqueue_scripts', 'millioneyez_menu_badges_handler' );

function millioneyez_set_blogger_key() {
    global $millioneyez_plugin_version;
    $options = get_option('millioneyez_options');
    $options["key"] = $_REQUEST['bloggerKey'];
    update_option('millioneyez_options', $options);
    millioneyez_set_blogger_state('connect', $options["key"]);
    wp_die();
}

function millioneyez_remove_blogger_key() {
    $options = get_option('millioneyez_options');
    $key = $options['key'];
    $options['key'] = '';
    $options['version'] = '';
    update_option('millioneyez_options', $options);
    millioneyez_set_blogger_state('disconnect', $key);
    wp_die();
}

function millioneyez_set_advanced_options() {
    $options = get_option('millioneyez_options');
    $options["featured"] = $_REQUEST['featured'];
    $options["featuredType"] = $_REQUEST['featuredType'];
    if ($options["featuredType"] == 'fixed') {
        $options["fixedText"] = $_REQUEST['fixedText'];
    }
    $options["hideFeatured"] = $_REQUEST['hideFeatured'];
    $options["hideVia"] = $_REQUEST['hideVia'];
    $options['defaultImage'] = $_REQUEST['defaultImage'];
    $options['remove_shortcodes_from_the_excerpt'] = $_REQUEST['remove_shortcodes_from_the_excerpt'];
    $options['hideConnect'] = $_REQUEST['hideConnect'];
    update_option('millioneyez_options', $options);
    wp_die();
}

function millioneyez_settings_init(){
    add_thickbox();
    register_setting( 'millioneyez_plugin_options', 'millioneyez_options', 'millioneyez_settings_validate_options' );
}

add_action('admin_menu', 'millioneyez_settings_add_options_page');

function millioneyez_settings_add_options_page() {
	$options = get_option("millioneyez_options");
	if ($options['key']) {
        $menu_label = "million eyez <span class='update-plugins count-0 me-counter-badge' ><span class='update-count'></span></span>";
		add_menu_page('Millioneyez Settings', $menu_label, 'edit_others_posts', 'millioneyez_curator', 'millioneyez_curator_render_form', 'none');
		add_submenu_page('millioneyez_curator', 'million eyez Curator', 'Curator', 'edit_others_posts', 'millioneyez_curator', 'millioneyez_curator_render_form');
		add_submenu_page('millioneyez_curator', 'million eyez Settings', 'Settings', 'manage_options', 'millioneyez_settings', 'millioneyez_settings_render_form');
	} else {
		add_menu_page('Millioneyez Settings', 'million eyez', 'manage_options', 'millioneyez_settings', 'millioneyez_settings_render_form', 'none');
		add_submenu_page('millioneyez_settings', 'million eyez Settings', 'Settings', 'manage_options', 'millioneyez_settings', 'millioneyez_settings_render_form');
		//add_submenu_page('millioneyez_settings', 'Millioneyez Curator', 'Curator', 'manage_options', 'millioneyez_curator', 'millioneyez_curator_render_form');
	}
}

function millioneyez_curator_render_form() {
    global $millioneyez_plugin_version;
    $options = get_option('millioneyez_options');
    ?>
    <div class="iframeHolderDiv">
        <iframe src="//curator.millioneyez.com/#?token=<?php echo $options['key']; ?>&version=<?php echo $millioneyez_plugin_version ?>" style="width:100%;height:100%;"></iframe>
    </div>
<style>

        #wpcontent { padding-left: 0px!important;}
        .iframeHolderDiv {
            position: fixed;
            right: 0;
            bottom: 0;
        }

        .wp-responsive-open .iframeHolderDiv {
            left: 190px !important;
        }

 </style>
 <script>
    jQuery(document).ready(function() {
        setIframePosition();
    });
    window.onresize = setIframePosition;
    function setIframePosition() {
        var bound = jQuery("#wpbody-content").offset();
        jQuery(".iframeHolderDiv").css("top", bound.top + "px");
        jQuery(".iframeHolderDiv").css("left", bound.left + "px");
    }
 </script>
	<?php
}

function millioneyez_admin_settings_enqueue_scripts() {
    wp_enqueue_script( 'postbox' );
}

function millioneyez_settings_render_form() {
    $options = get_option('millioneyez_options');
	?>

    <div class="millioneyez">
        <h1>million eyez settings</h1>
        <div class="noconnect hidden">
            <p>Use million eyez by embedding photos and Photoboxes. Go to <a href="https://www.millioneyez.com">millioneyez.com</a> to choose or create your own Photoboxes.</p>
        </div>
        <div class="step1 hidden">
            <h2>Get started</h2>
            <p class="submit debug-report meConnect">
                <button class="button button-primary me-button" id="btnConnect">Connect to million eyez</button>
            </p>
        </div>
        <div class="step2 hidden">
            <h2>Congrats, you're in!</h2>
            <ol>
                <li>Open the million eyez curator</li>
                <li>Select one of your posts and click the <strong>Add million eyez</strong> button to get started - or watch our tutorial <a href='https://www.millioneyez.com/2016/12/22/how-to-add-a-photo-box-to-your-post/' target='_blank'>right here</a></li>
            </ol>
            <p class="submit debug-report">
                <button id="btnDisconnect" class="button">Disconnect</button>
            </p>
        </div>

        <div class="nf-box">
            <div>
                <p>Need help? <a target="_blank" href="mailto:support@millioneyez.com?subject=contact from millioneyez Wordpress plugin">contact support</a></p>
                <p>Check out <a target="_blank" href="https://www.millioneyez.com/support/">support center</a> for frequently asked questions</p>
           </div>
       </div>
    </div>
    <div class="millioneyez metabox-holder">
        <div class="postbox-container">
            <div class="meta-box-sortables ui-sortable" id="normal-sortables">
              <div class="postbox" id="simpleSettingsBox">
                <button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">Toggle panel: <span class="hide-if-no-js">Simple Settings</span> <span class="hide-if-js">Simple Settings</span></span><span class="toggle-indicator" aria-hidden="true"></span></button>
                  <div title="Click to toggle" class="handlediv"><br></div>
                  <h2 class="hndle"><span>Simple Settings</span></h2>
                  <div class="inside">
                    <label><input type="checkbox" id="featuredToggle" name="millioneyez_options[featured]" value="1" <?php checked( $options['featured'], "true" ); ?> />Use million eyez featured photo when Wordpress featured image is not selected as featured</label>
                    <br/>
                    <label class="label-fake-checkbox-align">
                      <a href="" id="previewBtn">Click to preview front page with million eyez featured photos</a>
                      <a target="_blank" href="https://www.millioneyez.com/ufaqs/what-is-click-to-preview-front-page-in-simple-settings/" class="dashicons dashicons-editor-help"></a>
                    </label>
                    <br/>

                  </div>
              </div>
                <div class="postbox closed" id="advancedSettingsBox">
                  <button type="button" class="handlediv button-link" aria-expanded="false"><span class="screen-reader-text">Toggle panel: <span class="hide-if-no-js">Quick Draft</span> <span class="hide-if-js">Drafts</span></span><span class="toggle-indicator" aria-hidden="true"></span></button>
                    <div title="Click to toggle" class="handlediv"><br></div>
                    <h2 class="hndle"><span>Advanced Settings</span></h2>
                    <div class="inside">
                      <div class="featuredSettings hidden" style="display:none">
                          <input type="radio" name="millioneyez_options[featuredType]" value="responsive" <?php checked( 'responsive' == $options['featuredType'] ); ?> /> Responsive<br>
                          <input type="radio" name="millioneyez_options[featuredType]" value="fixed" <?php checked( 'fixed' == $options['featuredType'] ); ?> /> Width in px:
                          <input type="text" id="fixedText" name="millioneyez_options[fixedText]" placeholder="insert width in px" value="<?php echo $options['fixedText']; ?>" />
                          <p/>
                      </div>
                      <label>
                        <input type="checkbox" id="hideConnectToggle" name="millioneyez_options[hideConnect]" value="1" <?php checked( $options['hideConnect'], "true" ); ?> />Hide connect option
                      </label>
                      <br/><br/>
                      <label>
                        <input type="checkbox" id="hideToggle" name="millioneyez_options[hideFeatured]" value="1" <?php checked( $options['hideFeatured'], "true" ); ?> />On posts/pages: hide featured photo
                        <a target="_blank" href="https://www.millioneyez.com/ufaqs/what-is-in-postspages-hide-featured-photo/" class="dashicons dashicons-editor-help"></a>
                      </label>
                      <br/><br/>
                      <label>
                        <input type="checkbox" id="viaToggle" name="millioneyez_options[hideVia]" value="1" <?php checked( $options['hideVia'], "true" ); ?> />Remove 'via million eyez'
                        <a target="_blank" href="https://www.millioneyez.com/ufaqs/what-is-remove-via-millioneyez-in-advanced-settings/" class="dashicons dashicons-editor-help"></a>
                      </label>
                      <br/><br/>
                      <label>
                        <span>Open-graph image: </span>
                        <input type='text' id='defaultImage' name="millioneyez_options[defaultImage]" placeholder="Enter photo url here" value="<?php echo $options['defaultImage']; ?>" />
                        <a target="_blank" href=" https://www.millioneyez.com/ufaqs/what-is-open-graph-image-in-advanced-settings/" class="dashicons dashicons-editor-help"></a>
                      </label>
                    </div>
                </div>
                <div class='applyDiv'>
                        <input disabled="disabled" type="button" id="btnApply"  class="button-primary" value="<?php _e('Save changes') ?>" />
                </div>
            </div>
        </div>
    </div>
    <div id="dialog-message" title="Million eyez key connection">
      <span id="keyResult"></span>
    </div>
    <div id='disconnect-message' title='Disconnect' class='hidden'>
        <span>Are you sure you wish to disconnect from million eyez?</span>
    </div>
    <div id='unsupported-message' title='Unsupported browser' class='hidden'>
        <span>Seems this browser is not supported by million eyez. Please update to the latest version or check out the FAQ section in millioneyez.com to see a list of supported platforms.</span>
    </div>
	<script>
        jQuery(document).ready(function() {
            var key = "<?php echo $options['key']; ?>";
            var hideConnect = "<?php echo $options['hideConnect']; ?>";
            var featured = "<?php echo $options['featured']; ?>" == "true";
            var siteUrl = "<?php echo get_site_url() ?>"
            postboxes.save_state = function(){
                return;
            };
            postboxes.save_order = function(){
                return;
            };
            postboxes.add_postbox_toggles();

            if (key) {
                jQuery("#spanSuccess").removeClass("hidden");
                jQuery(".step2").removeClass("hidden");
                jQuery(".metabox-holder").removeClass("hidden");
            } else {
                if (hideConnect === 'true') {
                    jQuery(".noconnect").removeClass("hidden");
                } else {
                    jQuery(".step1").removeClass("hidden");
                }
            }

            // if (featured) {
                // jQuery(".featuredSettings").removeClass("hidden");
                // jQuery(".postbox").removeClass("closed");
                // jQuery(".button-link").attr("aria-expanded","true");
            // }

            jQuery("#btnConnect").on('click', function(e) {
                if (millioneyez.browserUnsupported) {
                    jQuery("#unsupported-message").dialog({
                        modal:true,
                        buttons: {
                            Ok: function() {
                                jQuery(this).dialog("close");
                            }
                        }
                    })
                } else {
                    tb_show("Connect", "//curator.millioneyez.com/connect.html?TB_iframe=true&width=750&height=560&modal=true");
                }
            });

            jQuery('#btnDisconnect').on('click', function(e) {
                if (millioneyez.browserUnsupported) {
                    jQuery("#unsupported-message").dialog({
                        modal:true,
                        buttons: {
                            Ok: function() {
                                jQuery(this).dialog("close");
                            }
                        }
                    })
                } else {
                    jQuery("#disconnect-message").dialog({
                        modal: true,
                        buttons: {
                            Ok: function() {
                                jQuery.ajax({
                                    url: ajaxurl,
                                    type: 'post',
                                    data: {
                                        action: 'millioneyez_remove_key'
                                    },
                                    success: function(data) {
                                        location.reload();
                                    }
                                });
                            }
                        }
                    });
                }
            });

            jQuery("#btnApply").on("click", function applyHandler(e) {
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'millioneyez_set_advanced_options',
                        featured: jQuery("#featuredToggle").is(':checked'),
                        featuredType: jQuery("input[name='millioneyez_options[featuredType]']:checked").val(),
                        hideFeatured: jQuery("#hideToggle").is(':checked'),
                        hideVia:jQuery('#viaToggle').is(':checked'),
                        remove_shortcodes_from_the_excerpt: jQuery('#remove_shortcodes_from_the_excerpt:checked').val() || 0,
                        fixedText: jQuery("#fixedText").val(),
                        defaultImage: jQuery("#defaultImage").val(),
                        hideConnect: jQuery("#hideConnectToggle").is(':checked'),
                    },
                    success: function(data) {
                        jQuery("#btnApply").attr("disabled","disabled");
                        location.reload();
                    }
                })
            });
            jQuery("#featuredToggle").on("click", function(e) {
                jQuery("#btnApply").removeAttr("disabled");
                if (jQuery(this).is(':checked')) {
                    jQuery(".featuredSettings").removeClass("hidden");
                } else {
                    jQuery(".featuredSettings").addClass("hidden");
                }
            });
            jQuery("#hideConnectToggle").on("click", function(e) {
                jQuery("#btnApply").removeAttr("disabled");
            });
            jQuery("#hideToggle").on("click", function(e) {
                jQuery("#btnApply").removeAttr("disabled");
            });

            jQuery("#viaToggle").on("click", function(e) {
                jQuery("#btnApply").removeAttr("disabled");
            });

            jQuery("input[name='millioneyez_options[featuredType]']").change(function() {
                jQuery("#btnApply").removeAttr("disabled");
            });

            jQuery("#defaultImage").on("input", function(e) {
                jQuery("#btnApply").removeAttr("disabled");
            });
            jQuery("#remove_shortcodes_from_the_excerpt").change(function() {
                jQuery("#btnApply").removeAttr("disabled");
            });
            jQuery("#previewBtn").on("click", function(e) {
                var simulateFeaturedPhoto = jQuery('input[name="millioneyez_options[featured]"]').is(':checked');
                window.open(siteUrl+"?simulateFeaturedPhoto="+simulateFeaturedPhoto);
            });
        })
	</script>
	<?php
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function millioneyez_settings_validate_options($input) {
	 // strip html from textboxes
    $options = get_option('millioneyez_options');
    if (empty($input['secret']) || $input['secret'] == '********') {
        $input['secret'] = $options['secret'];
    }
	return $input;
}

function millioneyez_admin_toolbar_handler() {
    global $wp_admin_bar;
    $options = get_option('millioneyez_options');
    if ($options['key'] && current_user_can('edit_others_posts')) {
            $args = array(
            'id'    => 'me-admin-bar-notifications',
            'title' => "<div class='me-camera-icon'></div><span class='ab-label update-plugins me-counter-badge count-0'><span class='update-count'>0</span></span>",
            'href'  => get_admin_url().'admin.php?page=millioneyez_curator',
            'meta'  => array( 'class' => 'menupop' )
        );
        $wp_admin_bar->add_node( $args );
    }
}

function millioneyez_menu_badges_handler() {
    global $millioneyez_plugin_version;
    global $wp_version;
    $options = get_option('millioneyez_options');
    if (current_user_can('edit_posts')) {
        wp_register_script('millioneyez-socket', plugins_url('includes/socket.io.js' , __FILE__), $millioneyez_plugin_version);
        wp_register_script('millioneyez-cookie', plugins_url('includes/cookies.min.js' , __FILE__), $millioneyez_plugin_version);
        wp_enqueue_style('millioneyez', plugins_url('public/styles/adminbar.css' , __FILE__), array(), $millioneyez_plugin_version);
        wp_register_script('millioneyez', plugins_url('frontend/admin_menus_handler.js' , __FILE__), array('jquery','millioneyez-socket', 'millioneyez-cookie'), $millioneyez_plugin_version);
        wp_localize_script('millioneyez', 'millioneyez', array(
            'shortlink' => wp_get_shortlink(),
            'token' => $options['key'],
            'wpVersion' => $wp_version
        ));
        wp_enqueue_script('millioneyez');
        if (is_admin()) {
            wp_register_script('bowser', plugins_url('includes/bowser.js' , __FILE__), array(), $millioneyez_plugin_version);
            wp_register_script('millioneyez-browser-detect', plugins_url('frontend/admin_browser_detect.js' , __FILE__), array('bowser', 'millioneyez'), $millioneyez_plugin_version);
            wp_enqueue_script('millioneyez-browser-detect');
        }
    }
}

?>
