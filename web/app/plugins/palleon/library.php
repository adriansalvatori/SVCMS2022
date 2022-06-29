<?php
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------
FRAMES
----------------------------------------------------------- */

// Get frame Tags
function palleon_get_frame_tags(){
    $tags = apply_filters('palleonframeTags',array(
        'grunge' => array(esc_html__('Grunge', 'palleon'), 12),
        'grunge-square' => array(esc_html__('Grunge - Square', 'palleon'), 6),
        'business' => array(esc_html__('Business', 'palleon'), 8),
        'bohemian' => array(esc_html__('Bohemian', 'palleon'), 10),
        'abstract' => array(esc_html__('Abstract', 'palleon'), 6),
        'floral' => array(esc_html__('Floral', 'palleon'), 5),
        'neon' => array(esc_html__('Neon', 'palleon'), 4),
        'winter' => array(esc_html__('Winter', 'palleon'), 3),
        'halloween' => array(esc_html__('Halloween', 'palleon'), 2),
        'cute' => array(esc_html__('Cute', 'palleon'), 7),
        'watercolor' => array(esc_html__('Watercolor', 'palleon'), 3),
        'love' => array(esc_html__('Love', 'palleon'), 2),
        'others' => array(esc_html__('Others', 'palleon'), 1),
    ));
    return $tags;
}

/* ---------------------------------------------------------
ELEMENTS
----------------------------------------------------------- */

// Get Element Tags
function palleon_get_element_tags(){
    $tags = apply_filters('palleonElementTags',array(
        'ink-brush-strokes' => array(esc_html__('Ink Brush Strokes', 'palleon'), 21, 'dark', 'no'),
        'abstract-shapes' => array(esc_html__('Abstract Shapes', 'palleon'), 52, 'dark', 'no'),
        'geometric-shapes' => array(esc_html__('Geometric Shapes', 'palleon'), 21, 'light', 'no'),
        'shape-badges' => array(esc_html__('Shapes & Badges', 'palleon'), 36, 'dark', 'no'),
        'hand-drawn-dividers' => array(esc_html__('Hand Drawn Dividers', 'palleon'), 30, 'light', 'no'),
        'arrows' => array(esc_html__('Arrows', 'palleon'), 31, 'dark', 'no'),
        'speech-bubbles' => array(esc_html__('Speech Bubbles', 'palleon'), 41, 'dark', 'no'),
        'clouds' => array(esc_html__('Clouds', 'palleon'), 41, 'dark', 'no'),
        'social-media' => array(esc_html__('Social Media', 'palleon'), 55, 'light', 'no'),
        'payment' => array(esc_html__('Payment', 'palleon'), 80, 'light', 'no'),
        'avatars' => array(esc_html__('Avatars', 'palleon'), 25, 'light', 'no'),
        'people' => array(esc_html__('People', 'palleon'), 43, 'light', 'no'),
        'dividers' => array(esc_html__('Dividers', 'palleon'), 25, 'light', 'no'),
        'trees' => array(esc_html__('Trees', 'palleon'), 23, 'light', 'yes'),
        'animals' => array(esc_html__('Animals', 'palleon'), 48, 'light', 'yes'),
        'vehicles' => array(esc_html__('Vehicles', 'palleon'), 9, 'light', 'no'),
        'quote' => array(esc_html__('Quote', 'palleon'), 12, 'light', 'no'),
        'weather' => array(esc_html__('Weather', 'palleon'), 71, 'light', 'no'),
        'weapons' => array(esc_html__('Weapons', 'palleon'), 25, 'light', 'no'),
        'gifts' => array(esc_html__('Gifts', 'palleon'), 16, 'dark', 'no'),
    ));
    return $tags;
}

/* ---------------------------------------------------------
TEMPLATES
----------------------------------------------------------- */

// Get Template Tags
function palleon_get_template_tags(){
    $tags = apply_filters('palleonTemplateTags',array(
        'blog-banners' => esc_html__('Blog Banners', 'palleon') . ' (' . palleon_get_tag_count('blog-banners') . ')',
        'banner-ads' => esc_html__('Banner Ads', 'palleon') . ' (' . palleon_get_tag_count('banner-ads') . ')',
        'collage' => esc_html__('Collage', 'palleon') . ' (' . palleon_get_tag_count('collage') . ')',
        'quote' => esc_html__('Quote', 'palleon') . ' (' . palleon_get_tag_count('quote') . ')',
        'medium-rectangle' => esc_html__('Medium Rectangle Ads', 'palleon') . ' (' . palleon_get_tag_count('medium-rectangle') . ')',
        'leaderboard' => esc_html__('Leaderboard Ads', 'palleon') . ' (' . palleon_get_tag_count('leaderboard') . ')',
        'billboard' => esc_html__('Billboard Ads', 'palleon') . ' (' . palleon_get_tag_count('billboard') . ')',
        'facebook-ads' => esc_html__('Facebook Ads', 'palleon') . ' (' . palleon_get_tag_count('facebook-ads') . ')',
        'instagram-post' => esc_html__('Instagram Post', 'palleon') . ' (' . palleon_get_tag_count('instagram-post') . ')',
        'facebook-post' => esc_html__('Facebook Post', 'palleon') . ' (' . palleon_get_tag_count('facebook-post') . ')',
        'twitter-post' => esc_html__('Twitter Post', 'palleon') . ' (' . palleon_get_tag_count('twitter-post') . ')',
        'youtube-thumbnail' => esc_html__('Youtube Thumbnail', 'palleon') . ' (' . palleon_get_tag_count('youtube-thumbnail') . ')',
    ));
    return $tags;
}

// Get Template Count
function palleon_get_template_count(){
    $get_templates = palleon_get_templates(false);
    return count($get_templates);
}

// Get Tag Count
function palleon_get_tag_count($tag){
    $get_tags = palleon_get_templates($tag);
    return count($get_tags);
}

// Get Templates
function palleon_get_templates($tag){
    $random =  PalleonSettings::get_option('template_order', 'random');
    $templates = palleon_templates();
    if ($random == 'random') {
        shuffle($templates);
    } else if ($random == 'new') {
        $templates = array_reverse($templates);
    }
    if ($tag) {
        $filteredArray = array();
        foreach($templates as $template) {
            if (in_array($tag, $template[4])) {
                $filteredArray[] = $template;
            }
        }
        return $filteredArray;
    } else {
        return $templates;
    }
}

// Templates Array
function palleon_templates(){
    $img_url = PALLEON_SOURCE_URL . 'templates/img/';
    $json_url = PALLEON_SOURCE_URL . 'templates/json/';

    $templates = apply_filters('palleonTemplates',array(
        array("t-1", esc_html__( 'Sale Banner - Instagram Post - Discount Offer - Square - 1080x1080px', 'palleon' ), $img_url . "1.jpg", $json_url . "1.json", array('banner-ads', 'instagram-post')),
        array("t-2", esc_html__( "Valentine's Day - Instagram Post - Square - 1080x1080px", 'palleon' ), $img_url . "2.jpg", $json_url . "2.json", array('instagram-post')),
        array("t-3", esc_html__( "Quote - 800x600px", 'palleon' ), $img_url . "3.jpg", $json_url . "3.json", array('quote')),
        array("t-4", esc_html__( "Quote - 900x600px", 'palleon' ), $img_url . "4.jpg", $json_url . "4.json", array('quote')),
        array("t-5", esc_html__( "App Banner - 2000x1300px", 'palleon' ), $img_url . "5.jpg", $json_url . "5.json", array('banner-ads')),
        array("t-6", esc_html__( "Fitness Banner - Medium Rectangle - 300x250px", 'palleon' ), $img_url . "6.jpg", $json_url . "6.json", array('banner-ads','medium-rectangle')),
        array("t-7", esc_html__( "Fitness Banner - Medium Rectangle - 300x250px", 'palleon' ), $img_url . "7.jpg", $json_url . "7.json", array('banner-ads','medium-rectangle')),
        array("t-8", esc_html__( "Digital Agency Banner - Leaderboard - 728x90px", 'palleon' ), $img_url . "8.jpg", $json_url . "8.json", array('banner-ads','leaderboard')),
        array("t-9", esc_html__( "Pet Shop Banner - Billboard - 970x250px", 'palleon' ), $img_url . "9.jpg", $json_url . "9.json", array('banner-ads','billboard')),
        array("t-10", esc_html__( "Summer Sale - Facebook Ad - 1200x628px", 'palleon' ), $img_url . "10.jpg", $json_url . "10.json", array('banner-ads','facebook-ads')),
        array("t-11", esc_html__( "Sale Banner - Facebook Ad - 1200x628px", 'palleon' ), $img_url . "11.jpg", $json_url . "11.json", array('banner-ads','facebook-ads')),
        array("t-12", esc_html__( "Christmas Sale - Instagram Post - Square - 1080x1080px", 'palleon' ), $img_url . "12.jpg", $json_url . "12.json", array('instagram-post')),
        array("t-13", esc_html__( "Business Facebook Post - 940x788px", 'palleon' ), $img_url . "13.jpg", $json_url . "13.json", array('facebook-post')),
        array("t-14", esc_html__( "Trending Music Video - Youtube Thumbnail - 1280x720px", 'palleon' ), $img_url . "14.jpg", $json_url . "14.json", array('youtube-thumbnail')),
        array("t-15", esc_html__( "Youtube Video Thumbnail - 1280x720px", 'palleon' ), $img_url . "15.jpg", $json_url . "15.json", array('youtube-thumbnail')),
        array("t-16", esc_html__( "Collage - 3 Photos - 2000x2000px", 'palleon' ), $img_url . "16.jpg", $json_url . "16.json", array('collage')),
        array("t-17", esc_html__( "Kids Style Collage - 2 Photos - 2000x1300px", 'palleon' ), $img_url . "17.jpg", $json_url . "17.json", array('collage')),
        array("t-18", esc_html__( "Kids Style Collage - 2 Photos - 2000x2000px", 'palleon' ), $img_url . "18.jpg", $json_url . "18.json", array('collage')),
        array("t-19", esc_html__( "Stylish Collage - 3 Photos - 2000x2000px", 'palleon' ), $img_url . "19.jpg", $json_url . "19.json", array('collage')),
        array("t-20", esc_html__( "Modern Collage - 2 Photos - 2000x1300px", 'palleon' ), $img_url . "20.jpg", $json_url . "20.json", array('collage')),
        array("t-21", esc_html__( "Collage - 5 Photos - 2000x1000px", 'palleon' ), $img_url . "21.jpg", $json_url . "21.json", array('collage')),
        array("t-22", esc_html__( "Modern Collage - 3 Photos - 2000x2000px", 'palleon' ), $img_url . "22.jpg", $json_url . "22.json", array('collage')),
        array("t-23", esc_html__( "Black Friday Banner - Leaderboard - 728x90px", 'palleon' ), $img_url . "23.jpg", $json_url . "23.json", array('banner-ads','leaderboard')),
        array("t-24", esc_html__( "Christmas Banner - Leaderboard - 728x90px", 'palleon' ), $img_url . "24.jpg", $json_url . "24.json", array('banner-ads','leaderboard')),
        array("t-25", esc_html__( "Blog Banner - 2240x1260px", 'palleon' ), $img_url . "25.jpg", $json_url . "25.json", array('blog-banners')),
        array("t-26", esc_html__( "Blog Banner - 2240x1260px", 'palleon' ), $img_url . "26.jpg", $json_url . "26.json", array('blog-banners')),
        array("t-27", esc_html__( "Blog Banner - 2240x1260px", 'palleon' ), $img_url . "27.jpg", $json_url . "27.json", array('blog-banners')),
        array("t-28", esc_html__( "Cafe Banner - Billboard - 970x250px", 'palleon' ), $img_url . "28.jpg", $json_url . "28.json", array('banner-ads','billboard')),
        array("t-29", esc_html__( "Happy Birthday - Facebook Post - 940x788px", 'palleon' ), $img_url . "29.jpg", $json_url . "29.json", array('facebook-post')),
        array("t-30", esc_html__( "Blog Banner - 2240x1260px", 'palleon' ), $img_url . "30.jpg", $json_url . "30.json", array('blog-banners')),
        array("t-31", esc_html__( "Quote - Twitter Post - 1600x900px", 'palleon' ), $img_url . "31.jpg", $json_url . "31.json", array('quote','twitter-post')),
        array("t-32", esc_html__( "Quote - Instagram Post - 1080x1080px", 'palleon' ), $img_url . "32.jpg", $json_url . "32.json", array('quote','instagram-post')),
        array("t-33", esc_html__( "Happy Children's Day - Facebook Post - 940x788px", 'palleon' ), $img_url . "33.jpg", $json_url . "33.json", array('facebook-post')),
        array("t-34", esc_html__( "Business Blog Banner - 2240x1260px", 'palleon' ), $img_url . "34.jpg", $json_url . "34.json", array('blog-banners')),
        array("t-35", esc_html__( "Blog Banner - 2240x1260px", 'palleon' ), $img_url . "35.jpg", $json_url . "35.json", array('blog-banners')),
        array("t-36", esc_html__( "Beauty - Leaderboard - 728x90px", 'palleon' ), $img_url . "36.jpg", $json_url . "36.json", array('leaderboard')),
        array("t-37", esc_html__( "Quote - Twitter Post - 1600x900px", 'palleon' ), $img_url . "37.jpg", $json_url . "37.json", array('quote','twitter-post')),
        array("t-38", esc_html__( "Blog Banner - 2240x1260px", 'palleon' ), $img_url . "38.jpg", $json_url . "38.json", array('blog-banners')),
        array("t-39", esc_html__( "Banner - Twitter Post - 1600x900px", 'palleon' ), $img_url . "39.jpg", $json_url . "39.json", array('banner-ads','twitter-post')),
        array("t-40", esc_html__( "Fashion Banner - Facebook Ad - 1200x628px", 'palleon' ), $img_url . "40.jpg", $json_url . "40.json", array('banner-ads','facebook-ads')),
        array("t-41", esc_html__( "Real Estate - Facebook Post - 940x788px", 'palleon' ), $img_url . "41.jpg", $json_url . "41.json", array('banner-ads','facebook-post')),
        array("t-42", esc_html__( "Business Banner - Instagram Post - 1080x1080px", 'palleon' ), $img_url . "42.jpg", $json_url . "42.json", array('banner-ads','instagram-post')),
    ));
    return $templates;
}