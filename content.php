<?php

add_action('wp_head','millioneyez_hook_head_top', 0);
add_action('wp_head','millioneyez_hook_head_bottom', 99999);
add_shortcode('millioneyez', 'millioneyez_hook_shortcode');
add_action('wp', 'millioneyez_wp_loaded');

function millioneyez_wp_loaded() {
    $options = get_option('millioneyez_options');

    if (filter_var($options['remove_shortcodes_from_the_excerpt'], FILTER_VALIDATE_BOOLEAN)) {
        add_filter('get_the_excerpt', 'millioneyez_hook_get_the_exceprt', 9);
    }
    $useMEFeaturedPhoto = false;

    $simulateFeaturedPhoto = isset($_GET['simulateFeaturedPhoto']) ? $_GET['simulateFeaturedPhoto'] : false;
    $simulatePhotoboxId = isset($_GET['simulatePhotoboxId']) ? $_GET['simulatePhotoboxId'] : false;

    if ($simulateFeaturedPhoto) {
        $useMEFeaturedPhoto = filter_var($simulateFeaturedPhoto, FILTER_VALIDATE_BOOLEAN);
    } else if ($options['featured']) {
        $useMEFeaturedPhoto = filter_var($options['featured'], FILTER_VALIDATE_BOOLEAN);
    }

    if ($simulatePhotoboxId) {
        add_filter ('the_content', 'millioneyez_simulate_shortcode');
    }

    if ($useMEFeaturedPhoto == true) {
//        if (filter_var($options['hideFeatured'], FILTER_VALIDATE_BOOLEAN) == false || filter_var($options['hideFeatured'], FILTER_VALIDATE_BOOLEAN) && is_singular('post') == false) {
            // add_filter('post_thumbnail_html', 'millioneyez_thumbnail_html_hook', 1, 5);
            add_filter('get_post_metadata', 'millioneyez_thumbnail_metadata_hook', 1, 4);
            add_filter('wp_get_attachment_image_src', 'millioneyez_hook_wp_get_attachment_image_src', 10, 4);
//        }
    }
}

function millioneyez_hook_wp_get_attachment_image_src($image, $attachment_id, $size, $icon) {
//    if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) && in_array( $size, array_keys( $_wp_additional_image_sizes ) ) ) {
    global $_wp_additional_image_sizes;
    if (is_string($attachment_id) && substr($attachment_id,0,3) == 'me-') {
        if (is_array($size)) {
            $width = $size[0];
        } else {
            $width = $_wp_additional_image_sizes[$size]['width'];
        }
        $height = $width / 4 * 3;
        return array('https://api.millioneyez.com/v1.0/missions/'.substr($attachment_id,3).'/featuredphoto', $width, $height, false);
    }
    return $image;
}

//function millioneyez_thumbnail_html_hook($html, $post_id, $post_thumbnail_id, $size, $attr) {
//    $options = get_option('millioneyez_options');
//    $simulateFeaturedPhotoType = $_GET['featuredType'];
//    $simulateFeaturedPhotoFixedSize= $_GET['fixedSize'];
//
//    if ($html == null && is_string($post_thumbnail_id) && substr($post_thumbnail_id,0,3) == 'me-') {
//        $widthStyle = "100%";
//        if ($simulateFeaturedPhotoType == "fixed") {
//            $widthStyle = $simulateFeaturedPhotoFixedSize."px";
//        } else if (is_null($simulateFeaturedPhotoType) && $options['featuredType'] == 'fixed') {
//            $widthStyle = $options['fixedText']."px";
//        }
//
//        return '<img src="https://api.millioneyez.com/v1.0/missions/'.substr($post_thumbnail_id,3).'/featuredphoto" class="wp-post-image" style="width:'.$widthStyle.'"/>';
//    }
//    return $html;
//}

function millioneyez_simulate_shortcode($content) {
    $mergedContent = "[millioneyez missionid=".$_REQUEST['simulatePhotoboxId']."][/millioneyez]".$content;
    return $mergedContent;
}

// Override get_post_metadata to return special millioneyez thumbnail id
function millioneyez_thumbnail_metadata_hook($t, $object_id, $metakey, $single) {
    global $wp_query;
    $options = get_option('millioneyez_options');
    if (filter_var($options['hideFeatured'], FILTER_VALIDATE_BOOLEAN) && is_singular('post') && $object_id == $wp_query->post->ID) {
        return $t;
    }

    if ($metakey == '_thumbnail_id') {

        // Check if thumbnail is already defined
        remove_filter('get_post_metadata', 'millioneyez_thumbnail_metadata_hook', 1);
        $val = get_post_meta($object_id, '_thumbnail_id', true);
        add_filter('get_post_metadata', 'millioneyez_thumbnail_metadata_hook', 1, 4);
        if ($val != null) {
            return $val;
        }

        $simulateFeaturedPhotoboxId = $_REQUEST['simulateFeaturedPhotoboxId'];
        if ($simulateFeaturedPhotoboxId) {
            return 'me-'.$simulateFeaturedPhotoboxId;
        }

        $content = get_the_content();

        if (preg_match('/\[millioneyez\s+missionid="(.*?)"/', $content, $matches)) {
            return 'me-'.$matches[1];
        }
        if (preg_match('/<iframe.*src=.*missionId=([a-zA-Z0-9]+).*><\/iframe>/', $content, $matches)) {
            return 'me-'.$matches[1];
        }
    }
    return $t;
}


function millioneyez_hook_shortcode($attr) {
    $options = get_option('millioneyez_options');
    $hideVia = isset($options['hideVia']) ? $options['hideVia'] : false;

    if (isset($attr['photoid'])) {
        $missionId = $attr['missionid'];
        $photoId = $attr['photoid'];
        return "<div data-mission-id='$missionId' data-photo-id='$photoId' data-hide-via='$hideVia' class='me-container me-photo-container'></div>";
    }
    if (isset($attr['missionid'])) {
        $missionId = $attr['missionid'];
        return "<div data-mission-id='$missionId' data-hide-via='$hideVia' class='me-container'></div>";
    }
    if (isset($attr['photographerid'])) {
        $photographerid = $attr['photographerid'];
        return "<div data-photographer-id='$photographerid' data-hide-via='$hideVia' class='me-container'></div>";
    }
}

// This should be used as an override to the wp_trim_excerpt WordPress implementation
function millioneyez_hook_get_the_exceprt( $text = '' ) {
    if ( '' == $text ) {
        $text = get_the_content('');

        // Seems there is a bug here that doesn't remove all shortcodes
        // $text = strip_shortcodes( $text );
        $shortcode_regex = get_shortcode_regex();
        $text = preg_replace("/$shortcode_regex/s", '', $text);

        $text = apply_filters( 'the_content', $text );
        $text = str_replace(']]>', ']]&gt;', $text);
        $excerpt_length = apply_filters( 'excerpt_length', 55 );
        $excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
        $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
    }
    return $text;
}

function millioneyez_hook_head_top() {
    global $post;
    $options = get_option('millioneyez_options');
    $fb_data = millioneyez_get_fb_data();
    $photo_id = isset($_GET['photoId']) ? $_GET['photoId'] : NULL;
    $mission_id = isset($_GET['missionId']) ? $_GET['missionId'] : NULL;

    echo '<meta name="twitter:card" content="summary_large_image">';

    // facebook grabs meta from top, without overriding
    // Facebook top priority tags
    if ($photo_id && $mission_id) {
        echo '<meta property="og:url" content="'.get_permalink().'?photoId='.$photo_id.'&missionId='.$mission_id.'"/>';
        echo '<meta itemprop="image" content="https://api.millioneyez.com/v1.0/photos/'.$photo_id.'/photourl"/>';
        echo '<meta property="og:image" content="https://api.millioneyez.com/v1.0/photos/'.$photo_id.'/photourl"/>';
        echo '<meta property="og:image:width" content="1600" />';
        echo '<meta property="og:image:height" content="840" />';
    }

    // Twitter lowest priority tags
    echo '<meta name="twitter:title" content="'.$fb_data['fb_title'].'">';
    echo '<meta name="twitter:description" content="'.$fb_data['fb_desc'].'">';
    if (!$photo_id && !$mission_id) {
        //if ($options['featured']) {
            $millioneyez_id = NULL;
            if (preg_match('/\[millioneyez\s+missionid="(.*?)"/', $post->post_content, $matches)) {
                // Return special millioneyez thumbnail id
                $millioneyez_id = $matches[1];
            }
            if (is_null($millioneyez_id) && preg_match('/<iframe.*src=.*missionId=([a-zA-Z0-9]+).*><\/iframe>/', $post->post_content, $matches)) {
                $millioneyez_id = $matches[1];
            }
            if (!is_null($millioneyez_id)) {
                echo '<meta property="twitter:image" content="https://api.millioneyez.com/v1.0/missions/'.$millioneyez_id.'/featuredphotoWatermark"/>';
            } else {
                if ($options['defaultImage']) {
                    echo '<meta property="twitter:image" content="'.$options['defaultImage'].'"/>';
                }
            }
        //}
    }
    wp_enqueue_script("jquery");
}
function millioneyez_hook_head_bottom() {
    global $post;
    $options = get_option('millioneyez_options');
    $fb_data = millioneyez_get_fb_data();
    $photo_id = isset($_GET['photoId']) ? $_GET['photoId'] : NULL;
    $mission_id = isset($_GET['missionId']) ? $_GET['missionId'] : NULL;

    // Twitter and WhatsApp top priority tags
    if ($photo_id && $mission_id) {
       // twitter gathers data from the latest tag
        echo '<meta property="twitter:image" content="https://api.millioneyez.com/v1.0/photos/'.$photo_id.'/photourl"/>';
        echo '<meta property="og:image" content="https://api.millioneyez.com/v1.0/photos/'.$photo_id.'/photourl"/>';
        echo '<meta itemprop="image" content="https://api.millioneyez.com/v1.0/photos/'.$photo_id.'/photourl"/>';
    }

    // Facebook lowest priority tags
    echo '<meta name="og:title" content="'.$fb_data['fb_title'].'">';
    echo '<meta name="og:description" content="'.$fb_data['fb_desc'].'">';
    if (!$photo_id && !$mission_id) {
            $millioneyez_id = NULL;
            if (preg_match('/\[millioneyez\s+missionid="(.*?)"/', $post->post_content, $matches)) {
                // Return special millioneyez thumbnail id
                $millioneyez_id = $matches[1];
            }
            if (is_null($millioneyez_id) && preg_match('/<iframe.*src=.*missionId=([a-zA-Z0-9]+).*><\/iframe>/', $post->post_content, $matches)) {
                $millioneyez_id = $matches[1];
            }
            if (!is_null($millioneyez_id)) {
                echo '<meta itemprop="image" content="https://api.millioneyez.com/v1.0/missions/'.$millioneyez_id.'/featuredphotoWatermark"/>';
                echo '<meta property="og:image" content="https://api.millioneyez.com/v1.0/missions/'.$millioneyez_id.'/featuredphotoWatermark"/>';
                echo '<meta property="og:image:width" content="1600" />';
                echo '<meta property="og:image:height" content="840" />';
            } else {
                if ($options['defaultImage']) {
                    echo '<meta itemprop="image" content="'.$options['defaultImage'].'"/>';
                    echo '<meta property="og:image" content="'.$options['defaultImage'].'"/>';
                    echo '<meta property="og:image:width" content="800" />';
                    echo '<meta property="og:image:height" content="600" />';
                }
            }
    }
    echo "\n<!-- million eyez start -->\n";
    echo '  <script type="text/javascript">(function(){
            loadScript("//viewer.millioneyez.com/loader/loader.js");
            function loadScript(src) { var s = document.createElement("script"); s.type = "text/javascript"; s.async = true; s.src = src; var x = document.getElementsByTagName("script")[0]; x.parentNode.insertBefore(s, x);}
            })();</script>';
    echo '<style type="text/stylesheet">.me-container { width: 100%; position: relative; margin: 1em auto;} .me-container:before { content: ""; padding-top: 75%; display: block; padding-bottom: 79px; }.me-container[data-photographer-id]:before { padding-bottom: 30px; } .me-container[data-photo-id]:before { padding-bottom: 30px; }</style>';
    echo "\n<!-- million eyez end -->\n";
}

function millioneyez_get_fb_data (){
    $fb_desc_chars = 300;
    $fb_desc = "";
    $fb_title = "";
    if (is_singular()) {
		//It's a Post or a Page or an attachment page - It can also be the homepage if it's set as a page
		global $post;
		$fb_title=esc_attr(strip_tags(stripslashes($post->post_title)));
		//SubHeading
		if (trim($post->post_excerpt)!='') {
			//If there's an excerpt that's what we'll use
			$fb_desc=trim($post->post_excerpt);
		} else {
			//If not we grab it from the content
			$fb_desc=trim($post->post_content);
		}
		$fb_desc=(intval($fb_desc_chars)>0 ? mb_substr(esc_attr(strip_tags(strip_shortcodes(stripslashes($fb_desc)))),0,$fb_desc_chars) : esc_attr(strip_tags(strip_shortcodes(stripslashes($fb_desc)))));
	} else {
		global $wp_query;
		//Other pages - Defaults
		$fb_title=esc_attr(strip_tags(stripslashes(get_bloginfo('name'))));


		if (is_category()) {
			$fb_title=esc_attr(strip_tags(stripslashes(single_cat_title('', false))));
			$cat_desc=trim(esc_attr(strip_tags(stripslashes(category_description()))));
			if (trim($cat_desc)!='') $fb_desc=$cat_desc;
		} else {
			if (is_tag()) {
				$fb_title=esc_attr(strip_tags(stripslashes(single_tag_title('', false))));
				$tag_desc=trim(esc_attr(strip_tags(stripslashes(tag_description()))));
				if (trim($tag_desc)!='') $fb_desc=$tag_desc;
			} else {
                if (is_search()) {
					$fb_title=esc_attr(strip_tags(stripslashes(__('Search for', 'wd-fb-og').' "'.get_search_query().'"')));
				} else {
					if (is_author()) {
						$fb_title=esc_attr(strip_tags(stripslashes(get_the_author_meta('display_name', get_query_var('author')))));
					} else {
						if (is_archive()) {
							if (is_day()) {
								$fb_title=esc_attr(strip_tags(stripslashes(get_query_var('day').' '.single_month_title(' ', false).' '.__('Archives', 'wd-fb-og'))));
							} else {
								if (is_month()) {
									$fb_title=esc_attr(strip_tags(stripslashes(single_month_title(' ', false).' '.__('Archives', 'wd-fb-og'))));
								} else {
									if (is_year()) {
										$fb_title=esc_attr(strip_tags(stripslashes(get_query_var('year').' '.__('Archives', 'wd-fb-og'))));
									}
								}
							}
						}
					}
				}
			}
		}
	}

	return array('fb_title' => $fb_title, 'fb_desc' => $fb_desc);
}
?>
