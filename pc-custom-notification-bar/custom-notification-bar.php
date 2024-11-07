<?php
/**
 * Plugin Name: Custom Notification Bar
 * Description: A WordPress plugin to display a customizable notification bar with delay, page-specific display, cookie integration, responsive images, and hyperlink options.
 * Version: 1.6
 * Author: Paul C.
 */

// Enqueue styles and scripts for the frontend notification bar
function custom_nb_enqueue_scripts() {
    wp_enqueue_style('custom_nb-style', plugin_dir_url(__FILE__) . 'css/paulc_cnb-style.css');
    wp_enqueue_script('custom_nb-script', plugin_dir_url(__FILE__) . 'js/paulc_cnb-script.js', array('jquery'), false, true);

    // Pass delay time and cookie duration to JavaScript
    wp_localize_script('custom_nb-script', 'custom_nb_data', array(
        'delay_time' => get_option('custom_nb_delay_time', 2000), // Default delay of 2000ms
        'cookie_duration' => get_option('custom_nb_cookie_duration', 180) * 60, // Convert minutes to seconds
    ));
}
add_action('wp_enqueue_scripts', 'custom_nb_enqueue_scripts');

// Create the HTML for the notification bar and add it to the footer
function custom_nb_display_notification() {
    $enabled = get_option('custom_nb_enabled');
    $selected_pages = get_option('custom_nb_pages', []);

    // Check if the notification bar is enabled and if we're on a selected page
    if ($enabled !== 'yes' || (!empty($selected_pages) && !in_array(get_the_ID(), $selected_pages))) {
        return;
    }

    // Get the content of the notification bar
    $notification_content = get_option('custom_nb_content');
    $image_url = get_option('custom_nb_image_url');
    $tablet_image_url = get_option('custom_nb_tablet_image_url');
    $mobile_image_url = get_option('custom_nb_mobile_image_url');
    $image_link = get_option('custom_nb_image_link');
    $text_link = get_option('custom_nb_text_link');

    ?>
    <div id="custom_nb-notification-overlay">
        <div id="custom_nb-notification">
            <div id="custom_nb-notification-content">

                <!-- Simplified images using <img> tags and CSS media queries -->
                <?php if ($image_url): ?>
                    <div class="custom-nb-image">
                        <?php if ($image_link): ?>
                            <a href="<?php echo esc_url($image_link); ?>" target="_blank">
                                <img src="<?php echo esc_url($image_url); ?>" alt="Notification Image" class="responsive-img desktop-img">
                                <?php if ($tablet_image_url): ?>
                                    <img src="<?php echo esc_url($tablet_image_url); ?>" alt="Notification Image" class="responsive-img tablet-img">
                                <?php endif; ?>
                                <?php if ($mobile_image_url): ?>
                                    <img src="<?php echo esc_url($mobile_image_url); ?>" alt="Notification Image" class="responsive-img mobile-img">
                                <?php endif; ?>
                            </a>
                        <?php else: ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="Notification Image" class="responsive-img desktop-img">
                            <?php if ($tablet_image_url): ?>
                                <img src="<?php echo esc_url($tablet_image_url); ?>" alt="Notification Image" class="responsive-img tablet-img">
                            <?php endif; ?>
                            <?php if ($mobile_image_url): ?>
                                <img src="<?php echo esc_url($mobile_image_url); ?>" alt="Notification Image" class="responsive-img mobile-img">
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Display the content text with optional hyperlink -->
                <p>
                    <?php
                    if ($text_link) {
                        echo '<a href="' . esc_url($text_link) . '" target="_blank">' . wp_kses_post($notification_content) . '</a>';
                    } else {
                        echo wp_kses_post($notification_content);
                    }
                    ?>
                </p>
            </div>
            <button id="custom_nb-notification-close">Close</button>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'custom_nb_display_notification');

// Create an admin menu page for the plugin
function custom_nb_create_admin_menu() {
    add_menu_page('Notification Settings', 'Notification Settings', 'manage_options', 'custom_nb-notification-settings', 'custom_nb_settings_page', '', 100);
}
add_action('admin_menu', 'custom_nb_create_admin_menu');

// Register settings for the notification bar
function custom_nb_register_settings() {
    register_setting('custom_nb-settings-group', 'custom_nb_enabled');
    register_setting('custom_nb-settings-group', 'custom_nb_content');
    register_setting('custom_nb-settings-group', 'custom_nb_delay_time');
    register_setting('custom_nb-settings-group', 'custom_nb_pages');
    register_setting('custom_nb-settings-group', 'custom_nb_cookie_duration'); // Cookie duration setting
    register_setting('custom_nb-settings-group', 'custom_nb_image_url'); // Desktop image URL setting
    register_setting('custom_nb-settings-group', 'custom_nb_tablet_image_url'); // Tablet image URL setting
    register_setting('custom_nb-settings-group', 'custom_nb_mobile_image_url'); // Mobile image URL setting
    register_setting('custom_nb-settings-group', 'custom_nb_image_link'); // Image link setting
    register_setting('custom_nb-settings-group', 'custom_nb_text_link'); // Text link setting
}
add_action('admin_init', 'custom_nb_register_settings');

// Create the settings page content
function custom_nb_settings_page() {
    ?>
    <div class="wrap">
        <h1>Notification Bar Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('custom_nb-settings-group'); ?>
            <?php do_settings_sections('custom_nb-settings-group'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Enable Notification Bar</th>
                    <td>
                        <input type="checkbox" name="custom_nb_enabled" value="yes" <?php checked('yes', get_option('custom_nb_enabled'), true); ?> />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Notification Content</th>
                    <td>
                        <textarea name="custom_nb_content" rows="5" cols="50"><?php echo esc_attr(get_option('custom_nb_content')); ?></textarea>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Notification Delay Time (milliseconds)</th>
                    <td>
                        <input type="number" name="custom_nb_delay_time" value="<?php echo esc_attr(get_option('custom_nb_delay_time', 2000)); ?>" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Show Notification on Pages</th>
                    <td>
                        <?php 
                        $pages = get_pages(); 
                        $selected_pages = get_option('custom_nb_pages', []);
                        ?>
                        <select name="custom_nb_pages[]" multiple>
                            <?php foreach ($pages as $page): ?>
                                <option value="<?php echo $page->ID; ?>" <?php echo in_array($page->ID, $selected_pages) ? 'selected' : ''; ?>>
                                    <?php echo $page->post_title; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Cookie Duration (minutes)</th>
                    <td>
                        <input type="number" name="custom_nb_cookie_duration" value="<?php echo esc_attr(get_option('custom_nb_cookie_duration', 180)); ?>" />
                        <p class="description">Specify how long (in minutes) before the notification shows again after being closed.</p>
                    </td>
                </tr>

                <!-- Fields for image URLs for different screen sizes -->
                <tr valign="top">
                    <th scope="row">Desktop Image URL</th>
                    <td>
                        <input type="text" name="custom_nb_image_url" value="<?php echo esc_attr(get_option('custom_nb_image_url')); ?>" />
                        <p class="description">Add the URL of the image for desktop view.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tablet Image URL</th>
                    <td>
                        <input type="text" name="custom_nb_tablet_image_url" value="<?php echo esc_attr(get_option('custom_nb_tablet_image_url')); ?>" />
                        <p class="description">Add the URL of the image for tablet view.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Mobile Image URL</th>
                    <td>
                        <input type="text" name="custom_nb_mobile_image_url" value="<?php echo esc_attr(get_option('custom_nb_mobile_image_url')); ?>" />
                        <p class="description">Add the URL of the image for mobile view.</p>
                    </td>
                </tr>

                <!-- Existing fields for image link and text link -->
                <tr valign="top">
                    <th scope="row">Image Link URL</th>
                    <td>
                        <input type="text" name="custom_nb_image_link" value="<?php echo esc_attr(get_option('custom_nb_image_link')); ?>" />
                        <p class="description">If you want the image to be clickable, enter the URL here.</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Text Link URL</th>
                    <td>
                        <input type="text" name="custom_nb_text_link" value="<?php echo esc_attr(get_option('custom_nb_text_link')); ?>" />
                        <p class="description">Enter the URL to make the notification text clickable.</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
