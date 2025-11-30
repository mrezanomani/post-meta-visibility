<?php

if (!defined('ABSPATH')) {
    exit;
}

function pmv_sanitize_checkbox($value)
{
    return $value === '1' ? '1' : '0';
}

function pmv_sanitize_comment_meta($value)
{
    if (!is_array($value)) {
        return [];
    }

    $clean = [];
    foreach ($value as $post_type => $settings) {
        $pt_key = sanitize_key($post_type);
        $clean[$pt_key] = [
            'show_author' => (!empty($settings['show_author']) ? '1' : '0'),
            'link_author' => (!empty($settings['link_author']) ? '1' : '0'),
            'show_date'   => (!empty($settings['show_date']) ? '1' : '0'),
            'link_date'   => (!empty($settings['link_date']) ? '1' : '0'),
            'show_time'   => (!empty($settings['show_time']) ? '1' : '0'),
            'link_time'   => (!empty($settings['link_time']) ? '1' : '0'),
        ];
    }

    return $clean;
}

add_action('admin_menu', function () {
    add_options_page(
        'تنظیمات نمایش متا و دیدگاه',
        'نمایش متا نوشته',
        'manage_options',
        'pmv-settings',
        'pmv_settings_page'
    );
});

add_action('admin_init', function () {
    register_setting('pmv_settings', 'pmv_author', [
        'type' => 'string',
        'sanitize_callback' => 'pmv_sanitize_checkbox',
        'default' => '1',
    ]);
    register_setting('pmv_settings', 'pmv_date', [
        'type' => 'string',
        'sanitize_callback' => 'pmv_sanitize_checkbox',
        'default' => '1',
    ]);
    register_setting('pmv_settings', 'pmv_time', [
        'type' => 'string',
        'sanitize_callback' => 'pmv_sanitize_checkbox',
        'default' => '1',
    ]);

    register_setting('pmv_settings', 'pmv_author_link', [
        'type' => 'string',
        'sanitize_callback' => 'pmv_sanitize_checkbox',
        'default' => '1',
    ]);
    register_setting('pmv_settings', 'pmv_date_link', [
        'type' => 'string',
        'sanitize_callback' => 'pmv_sanitize_checkbox',
        'default' => '1',
    ]);
    register_setting('pmv_settings', 'pmv_time_link', [
        'type' => 'string',
        'sanitize_callback' => 'pmv_sanitize_checkbox',
        'default' => '1',
    ]);

    register_setting(
        'pmv_settings',
        'pmv_comment_meta',
        [
            'type' => 'array',
            'sanitize_callback' => 'pmv_sanitize_comment_meta',
            'default' => [],
        ]
    );
});

function pmv_settings_page()
{
    $comment_meta = get_option('pmv_comment_meta', []);
    $defaults = [
        'show_author' => '1',
        'link_author' => '1',
        'show_date'   => '1',
        'link_date'   => '1',
        'show_time'   => '1',
        'link_time'   => '1',
    ];
    ?>
    <div class="wrap">
        <h2>تنظیمات نمایش متا و کنترل دیدگاه</h2>

        <form method="post" action="options.php">
            <?php settings_fields('pmv_settings'); ?>

            <h3 style="margin-top:25px;">نمایش اطلاعات نوشته</h3>
            <table class="form-table">
                <tr>
                    <th scope="row">نمایش نویسنده</th>
                    <td>
                        <input type="checkbox" name="pmv_author" value="1" <?php checked(get_option('pmv_author', '1'), '1'); ?>>
                        <span class="description">نمایش یا پنهان کردن نام نویسنده در ابتدای محتوا.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">نمایش تاریخ</th>
                    <td>
                        <input type="checkbox" name="pmv_date" value="1" <?php checked(get_option('pmv_date', '1'), '1'); ?>>
                        <span class="description">نمایش یا پنهان کردن تاریخ انتشار.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">نمایش ساعت</th>
                    <td>
                        <input type="checkbox" name="pmv_time" value="1" <?php checked(get_option('pmv_time', '1'), '1'); ?>>
                        <span class="description">نمایش یا پنهان کردن ساعت انتشار.</span>
                    </td>
                </tr>
            </table>

            <h3 style="margin-top:25px;">لینک‌دهی متای نوشته</h3>
            <p style="color:#555;">می‌توانید لینک روی متای نوشته را فعال/غیرفعال کنید.</p>
            <table class="form-table">
                <tr>
                    <th scope="row">لینک نویسنده</th>
                    <td>
                        <input type="checkbox" name="pmv_author_link" value="1" <?php checked(get_option('pmv_author_link', '1'), '1'); ?>>
                        <span class="description">اگر فعال باشد، نام نویسنده به آرشیو نویسنده لینک می‌شود.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">لینک تاریخ</th>
                    <td>
                        <input type="checkbox" name="pmv_date_link" value="1" <?php checked(get_option('pmv_date_link', '1'), '1'); ?>>
                        <span class="description">اگر فعال باشد، تاریخ به همان نوشته لینک می‌شود.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">لینک ساعت</th>
                    <td>
                        <input type="checkbox" name="pmv_time_link" value="1" <?php checked(get_option('pmv_time_link', '1'), '1'); ?>>
                        <span class="description">اگر فعال باشد، ساعت به همان نوشته لینک می‌شود.</span>
                    </td>
                </tr>
            </table>

            <h3 style="margin-top:25px;">متای دیدگاه (بر اساس پست‌تایپ)</h3>
            <p class="description" style="margin:4px 0 10px 0;">برای هر پست‌تایپ تعیین کنید نام، تاریخ و ساعت دیدگاه نمایش داده شوند و آیا لینک داشته باشند.</p>
            <table class="widefat striped" style="max-width:100%;">
                <thead>
                    <tr>
                        <th>پست‌تایپ</th>
                        <th>نام</th>
                        <th>لینک نام</th>
                        <th>تاریخ</th>
                        <th>لینک تاریخ</th>
                        <th>ساعت</th>
                        <th>لینک ساعت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $post_types = get_post_types(['public' => true], 'objects');
                    $has_commentable_type = false;

                    foreach ($post_types as $post_type => $post_type_obj) {
                        if (!post_type_supports($post_type, 'comments')) {
                            continue;
                        }

                        $has_commentable_type = true;
                        $settings = $comment_meta[$post_type] ?? [];
                        $settings = array_merge($defaults, is_array($settings) ? $settings : []);
                        ?>
                        <tr>
                            <td><?php echo esc_html($post_type_obj->labels->singular_name); ?></td>
                            <td><input type="checkbox" name="pmv_comment_meta[<?php echo esc_attr($post_type); ?>][show_author]" value="1" <?php checked($settings['show_author'], '1'); ?>></td>
                            <td><input type="checkbox" name="pmv_comment_meta[<?php echo esc_attr($post_type); ?>][link_author]" value="1" <?php checked($settings['link_author'], '1'); ?>></td>
                            <td><input type="checkbox" name="pmv_comment_meta[<?php echo esc_attr($post_type); ?>][show_date]" value="1" <?php checked($settings['show_date'], '1'); ?>></td>
                            <td><input type="checkbox" name="pmv_comment_meta[<?php echo esc_attr($post_type); ?>][link_date]" value="1" <?php checked($settings['link_date'], '1'); ?>></td>
                            <td><input type="checkbox" name="pmv_comment_meta[<?php echo esc_attr($post_type); ?>][show_time]" value="1" <?php checked($settings['show_time'], '1'); ?>></td>
                            <td><input type="checkbox" name="pmv_comment_meta[<?php echo esc_attr($post_type); ?>][link_time]" value="1" <?php checked($settings['link_time'], '1'); ?>></td>
                        </tr>
                        <?php
                    }

                    if (!$has_commentable_type) {
                        echo '<tr><td colspan="7"><span class="description">هیچ پست‌تایپ دارای دیدگاه یافت نشد.</span></td></tr>';
                    }
                    ?>
                </tbody>
            </table>
            <p class="description" style="margin-top:8px;">اگر گزینه‌ای را بردارید، همان بخش در متای دیدگاه‌ها نمایش داده نمی‌شود یا لینک نخواهد داشت.</p>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
