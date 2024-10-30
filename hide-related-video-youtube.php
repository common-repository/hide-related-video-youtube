<?php

/*
Plugin Name: Hide Related Video Youtube
Plugin URI: https://wordpress.org/plugins/hide-related-video-youtube/
Description: This is a plugin remove related video other chanel when you use YouTube oEmbed.
Version: 1.0
Author: Trần Hoàng Quốc.
Author URI: http://tranhoangquoc.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

//Delete all oembed cache in database
register_activation_hook(__FILE__, 'thq_hide_related_video_youtube_activation');
function thq_hide_related_video_youtube_activation()
{
    global $wpdb;
    $post_ids = $wpdb->get_col("SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key LIKE '_oembed_%'");
    if ($post_ids) {
        $postmeta_ids = $wpdb->get_col("SELECT meta_id FROM $wpdb->postmeta WHERE meta_key LIKE '_oembed_%'");
        $in = implode(',', array_fill(1, count($postmeta_ids), '%d'));
        do_action('delete_postmeta', $postmeta_ids);
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_id IN($in)", $postmeta_ids));
        do_action('deleted_postmeta', $postmeta_ids);
        foreach ($post_ids as $post_id)
            wp_cache_delete($post_id, 'post_meta');
        return true;
    }
}

//Remove shortcode youtube from Jacpack
function thq_remove_jetpack_shortcode_youtube($shortcodes)
{
    $dir_jetpack_shortcodes = plugin_dir_path( __DIR__ ).'jetpack/modules/shortcodes/';
    $shortcodes_remove = array('youtube.php');
    foreach ($shortcodes_remove as $sc) {
        if ($key = array_search($dir_jetpack_shortcodes . $sc, $shortcodes)) {
            unset($shortcodes[$key]);
        }
    }
    return $shortcodes;
}

add_filter('jetpack_shortcodes_to_include', 'thq_remove_jetpack_shortcode_youtube');

//Add filter for work
add_filter('oembed_result', 'thq_hide_related_videos_youtube', 10, 3);
function thq_hide_related_videos_youtube($data)
{
    $data = preg_replace('/(youtube\.com.*)(\?feature=oembed)(.*)/', '$1?rel=0&showinfo=0$3', $data);
    return $data;
}
