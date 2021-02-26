<?php
include_once 'inc/loader.php'; // main helper for theme

add_filter( 'sf_add_needed_plugins', function ( $plugins ) {
    return array_merge( $plugins, [
        // This is an example of how to include a plugin bundled with a theme.
        // array(
        // 	'name'               => 'TGM Example Plugin', // The plugin name.
        // 	'slug'               => 'tgm-example-plugin', // The plugin slug (typically the folder name).
        // 	'source'             => get_template_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source.
        // 	'required'           => true, // If false, the plugin is only 'recommended' instead of required.
        // 	'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
        // 	'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
        // 	'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
        // 	'external_url'       => '', // If set, overrides default API URL and points to an external URL.
        // 	'is_callable'        => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
        // ),

        [
            'name'             => 'WooCommerce',
            'slug'             => 'woocommerce',
            'version'          => '4.7.0',
            'required'         => true,
            'force_activation' => true,
        ],
        [
            'name'     => 'WordPress SEO by Yoast',
            'slug'     => 'wordpress-seo',
            'version'  => '15.3',
            'required' => true,
        ],
        [
            'name'     => 'Nextend Social Login',
            'slug'     => 'nextend-facebook-connect',
            'version'  => '3.0.25',
            'required' => true,
        ],
        [
            'name'     => 'SVG Support',
            'slug'     => 'svg-support',
            'version'  => '2.3.18',
            'required' => true,
        ],
        [
            'name'     => 'Really Simple SSL',
            'slug'     => 'really-simple-ssl',
            'version'  => '3.3.5',
            'required' => true,
        ],
        [
            'name'     => 'Carbon Fields',
            'slug'     => 'carbon-fields',
            'source'   => get_template_directory() . '/plugins/carbon-fields.zip',
            'required' => true,
        ],
        [
            'name'     => 'Kama Breadcrumbs',
            'slug'     => 'kama-breadcrumbs',
            'source'   => get_template_directory() . '/plugins/kama-breadcrumbs.zip', // The plugin source.
            'version'  => '4.8.6.2',
            'required' => true,
        ],
        [
            'name'     => 'User Verification',
            'slug'     => 'user-verification',
            'version'  => '1.0.51',
            'required' => true,
        ],
        [
            'name'     => 'Google XML Sitemap Generator',
            'slug'     => 'www-xml-sitemap-generator-org',
            'source'   => get_template_directory() . '/plugins/www-xml-sitemap-generator-org.zip', // The plugin source.
            'version'  => '1.3.5',
            'required' => true,
        ],
        [
            'name'     => 'LifeChef Telegram bot',
            'slug'     => 'sf-telegram-bot',
            'source'   => get_template_directory() . '/plugins/sf-telegram-bot.zip', // The plugin source.
            'version'  => '1.0.6',
            'required' => true,
        ],
    ] );
} );

function init_func() {
    require_once ABSPATH . 'wp-admin/includes/post.php';
    op_help()->init();
}

init_func();