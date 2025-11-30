<?php
/*
Plugin Name: مدیریت نمایش متا و دیدگاه
Description: کنترل نمایش نویسنده، تاریخ و زمان نوشته و همچنین کنترل نمایش متای دیدگاه (نام، تاریخ، ساعت و لینک‌ها) بر اساس هر پست‌تایپ.
Version: 1.6
Author: mreza
Text Domain: mreza-post-meta
Author URI: https://uhostco.ir
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'admin-page.php';

function pmv_get_bool($name, $default = true)
{
    $default_val = $default ? '1' : '0';
    $val = get_option($name, $default_val);
    return $val === '1';
}

function pmv_get_comment_meta_settings($comment_or_id)
{
    $comment_obj = is_a($comment_or_id, 'WP_Comment') ? $comment_or_id : get_comment($comment_or_id);
    if (!$comment_obj) {
        return false;
    }

    $defaults = [
        'show_author' => '1',
        'link_author' => '1',
        'show_date'   => '1',
        'link_date'   => '1',
        'show_time'   => '1',
        'link_time'   => '1',
    ];

    $meta = get_option('pmv_comment_meta', []);
    $post_type = get_post_type($comment_obj->comment_post_ID);
    if (!$post_type) {
        return $defaults;
    }

    $settings = $meta[$post_type] ?? [];
    return array_merge($defaults, is_array($settings) ? $settings : []);
}

// نمایش متای نوشته‌ها
function pmv_filter_post_meta($content)
{
    if (!is_singular()) {
        return $content;
    }

    $show_author = pmv_get_bool('pmv_author', true);
    $show_date = pmv_get_bool('pmv_date', true);
    $show_time = pmv_get_bool('pmv_time', true);

    $link_author = pmv_get_bool('pmv_author_link', true);
    $link_date = pmv_get_bool('pmv_date_link', true);
    $link_time = pmv_get_bool('pmv_time_link', true);

    if (!$show_author && !$show_date && !$show_time) {
        return $content;
    }

    $parts = [];

    if ($show_author) {
        $author_name = esc_html(get_the_author());
        $author_html = $link_author
            ? '<a href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . $author_name . '</a>'
            : $author_name;
        $parts[] = sprintf(__('نویسنده: %s', 'mreza-post-meta'), $author_html);
    }

    if ($show_date) {
        $date_str = esc_html(get_the_date());
        $date_html = $link_date
            ? '<a href="' . esc_url(get_permalink()) . '">' . $date_str . '</a>'
            : $date_str;
        $parts[] = sprintf(__('تاریخ: %s', 'mreza-post-meta'), $date_html);
    }

    if ($show_time) {
        $time_str = esc_html(get_the_time());
        $time_html = $link_time
            ? '<a href="' . esc_url(get_permalink()) . '">' . $time_str . '</a>'
            : $time_str;
        $parts[] = sprintf(__('ساعت: %s', 'mreza-post-meta'), $time_html);
    }

    $meta_html = '<div class="pmv-meta" style="margin-bottom:15px;color:#777;font-size:14px;">' . implode(' | ', $parts) . '</div>';

    return $meta_html . $content;
}
add_filter('the_content', 'pmv_filter_post_meta');

function pmv_maybe_plain_author_link($link)
{
    if (!pmv_get_bool('pmv_author_link', true)) {
        return esc_html(get_the_author());
    }
    return $link;
}
add_filter('the_author_posts_link', 'pmv_maybe_plain_author_link');

// کنترل متای دیدگاه بر اساس پست‌تایپ
function pmv_filter_comment_author($author, $comment_id, $comment = null)
{
    $comment = $comment ?: get_comment($comment_id);
    $settings = pmv_get_comment_meta_settings($comment);
    if (!$settings || $settings['show_author'] !== '1') {
        return '';
    }
    return $author;
}
add_filter('get_comment_author', 'pmv_filter_comment_author', 10, 3);

function pmv_filter_comment_author_link($link, $author, $comment_id, $comment)
{
    $settings = pmv_get_comment_meta_settings($comment);
    if (!$settings || $settings['show_author'] !== '1') {
        return '';
    }

    if ($settings['link_author'] !== '1') {
        return esc_html(get_comment_author($comment));
    }

    return $link;
}
add_filter('get_comment_author_link', 'pmv_filter_comment_author_link', 10, 4);

function pmv_filter_comment_date($date, $format, $comment_id)
{
    $comment = get_comment($comment_id);
    $settings = pmv_get_comment_meta_settings($comment);
    if (!$settings || $settings['show_date'] !== '1') {
        return '';
    }

    if ($settings['link_date'] !== '1') {
        return esc_html(strip_tags($date));
    }

    return $date;
}
add_filter('get_comment_date', 'pmv_filter_comment_date', 10, 3);

function pmv_filter_comment_time($time, $format, $gmt, $comment_id, $translate)
{
    $comment = get_comment($comment_id);
    $settings = pmv_get_comment_meta_settings($comment);
    if (!$settings || $settings['show_time'] !== '1') {
        return '';
    }

    if ($settings['link_time'] !== '1') {
        return esc_html(strip_tags($time));
    }

    return $time;
}
add_filter('get_comment_time', 'pmv_filter_comment_time', 10, 5);

function pmv_filter_comment_link($link, $comment, $args, $cpage)
{
    $settings = pmv_get_comment_meta_settings($comment);
    if (!$settings) {
        return $link;
    }

    // اگر لینک تاریخ و ساعت هر دو غیرفعال باشند، آدرس لینک را حذف کن.
    if ($settings['link_date'] !== '1' && $settings['link_time'] !== '1') {
        return '';
    }

    return $link;
}
add_filter('get_comment_link', 'pmv_filter_comment_link', 10, 4);

// حذف اتصال «در/at» بین تاریخ و ساعت تا در صورت خالی بودن یکی از آن‌ها متن زائد باقی نماند.
function pmv_strip_comment_datetime_connector($translated_text, $text, $domain)
{
    if ($text === '%1$s در %2$s' || $text === '%1$s at %2$s') {
        return '%1$s %2$s';
    }
    return $translated_text;
}
add_filter('gettext', 'pmv_strip_comment_datetime_connector', 10, 3);
