<?php
defined( 'ABSPATH' ) || exit;

/**
 * Get instance of helper.
 *
 * @return SFThemeHelper
 */
function op_help() {
    return SFThemeHelper::getInstance();
}

/**
 * Main class of all the settings.
 */
class SFThemeHelper {

    /**
     * @var SolutionFactoryThemeSettings
     */
    public $settings;

    /**
     * @var SFAddonVariations
     */
    public $variations;

    /**
     * @var SolutionFactorySurvey
     */
    public $survey;

    /**
     * @var SFAddonSubscription
     */
    public $subscriptions;

    /**
     * @var SolutionFactoryZones
     */
    public $zones;

    /**
     * @var SFMainPage
     */
    public $mainpage;

    /**
     * @var SolutionFactoryShop
     */
    public $shop;

    /**
     * @var PaymentPayeezy
     */
    public $payment;

    /**
     * @var FaqPageClass
     */
    public $faq;

    /**
     * @var ResetPasswordPageClass
     */
    public $forgot_password;

    /**
     * @var ZenDeskHelpCenterIntegration
     */
    public $zendesc_hc_integration;

    /**
     * @var HowItWorksPageClass
     */
    public $how_it_works;

    /**
     * @var MealPlanModal
     */
    public $meal_plan_modal;

    /**
     * @var OfferingsPageClass
     */
    public $offerings;

    /**
     * @var MenuClass
     */
    public $menu;

    /**
     * @var OurStoryClass
     */
    public $our_story;

    /**
     * @var Subcategories
     */
    public $subcategories;

    /**
     * @var SyncComponents
     */
    public $sync_components;

    /**
     * @var SingleComponentPageClass
     */
    public $single_component;

    /**
     * @var OP_User
     */
    public $sf_user;

    /**
     * @var CartPageClass
     */
    public $cart;

    /**
     * @var \ES\Controllers\ElasticSearchDataController
     */
    public $elastic_search;

    /**
     * @var \ES\Controllers\SearchController
     */
    public $search_controller;

    /**
     * @var Notifications
     */
    public $notifications;

    /**
     * @var EmailVerification
     */
    public $email_verification;

    /**
     * @var SFEasyPost
     */
    public $easypost;

    /**
     * @var SFSortCache
     */
    public $sort_cache;

    /**
     * @var SFCustomizeCache
     */
    public $customize_cache;

    /**
     * @var SFReportOrders
     */
    public $report_orders;

    /**
     * @var SFOrderReceipt
     */
    public $order_receipt;

    /**
     * @var SFCoupons
     */
    public $coupons;

    /**
     * @var SFGlobalCache
     */
    public $global_cache;

    /**
     * @var SFZipCodes
     */
    public $zip_codes;

    /**
     * @var SFFilters
     */
    public $sf_filters;

    /**
     * @var SFImport
     */
    public $import;

    /**
     * @var SFCategoryPage
     */
    public $category_page;

    /**
     * @var SFCustomize
     */
    public $customizer;

    /**
     * @var SolutionFactoryStore
     */
    public $rest_pick_and_pack;

    /**
     * @var SolutionFactoryTutorial
     */
    public $tutorial;


    private $plugins = [];
    private static $_instance = null;

    /**
     * Class instance.
     *
     * @return SFThemeHelper
     */
    static public function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Check if we have needed plugins active
     *
     * @return boolean
     */
    function check_plugins() {
        $needed_classes = [
            'SolutionFactoryThemeSettings',
            //'SFAddonSubscription',
            'SolutionFactoryShop',
        ];

        foreach ( $needed_classes as $class_name ) {
            if ( ! class_exists( $class_name ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return assets dir.
     *
     * @param string $file
     * @param string $dir
     *
     * @return string
     */
    function assets_url( $file, $dir = 'img' ) {
        return get_template_directory_uri() . '/assets/' . $dir . '/' . $file;
    }

    /**
     * Check needed deps from plugins and addons.
     *
     * @return boolean
     */
    function check_dependencies() {
        $check_result = apply_filters( 'sf_check_plugins', true );

        return $check_result;
    }

    /**
     * Load files, plugins, add hooks and filters and do all magic.
     *
     * @return SFThemeHelper
     */
    function init() {
        // load needed files
        $this->import();

        // apply filter for ability to get plugins outside this helper
        $this->plugins = array_merge( $this->plugins, apply_filters( 'sf_add_needed_plugins', [] ) );

        // do TGM plugin activator
        add_action( 'tgmpa_register', [ $this, 'register_plugins' ] );

        if ( $this->check_plugins() ) {
            $this->add_image_sizes();
            $this->load_classes();
            $this->registerHooks();
            $this->email_verification->init();
            $this->global_cache->init();
            $this->faq->init();
            $this->settings->init();
            $this->subscriptions->init();
            $this->survey->init();
            $this->zones->init();
            $this->mainpage->init();
            $this->category_page->init();
            $this->shop->init();
            $this->payment->init();
            $this->forgot_password->init();
            $this->meal_plan_modal->init();
            $this->offerings->init();
            $this->menu->init();
            $this->our_story->init();
            $this->zendesc_hc_integration->init();
            $this->subcategories->init();
            $this->how_it_works->init();
            $this->sync_components->init();
            $this->single_component->init();
            $this->sf_user->init();
            $this->cart->init();
            $this->notifications->init();
            $this->sort_cache->init();
            $this->customize_cache->init();
            $this->easypost->init();
            $this->report_orders->init();
            $this->coupons->init();
            $this->order_receipt->init();
            $this->search_controller->init();
            $this->sf_filters->init();
            $this->elastic_search->init();
            $this->customizer->init();
            $this->rest_pick_and_pack->init();
            $this->import->init();
            $this->variations->init();
            $this->tutorial->init();

        } else {

            add_filter( 'sf_check_plugins', function () {
                return false;
            } );

            add_filter( 'sf_check_plugins_notices', function ( $notices ) {
                return array_merge( $notices, [ 'You need activate all required plugins.' ] );
            } );
        }

        if ( ! $this->check_dependencies() ) {
            $this->show_install_notice();
        }

        return $this;
    }

    /**
     * Load classes.
     */
    function load_classes() {
        $this->faq                    = FaqPageClass::getInstance();
        $this->settings               = SolutionFactoryThemeSettings::getInstance();
        $this->variations             = SFAddonVariations::getInstance();
        $this->subscriptions          = SFAddonSubscription::getInstance();
        $this->survey                 = SolutionFactorySurvey::getInstance();
        $this->zones                  = SolutionFactoryZones::getInstance();
        $this->zip_codes              = SFZipCodes::getInstance();
        $this->mainpage               = SFMainPage::getInstance();
        $this->category_page          = SFCategoryPage::getInstance();
        $this->shop                   = SolutionFactoryShop::getInstance();
        $this->payment                = PaymentPayeezy::getInstance();
        $this->forgot_password        = ResetPasswordPageClass::getInstance();
        $this->meal_plan_modal        = MealPlanModal::getInstance();
        $this->offerings              = OfferingsPageClass::getInstance();
        $this->menu                   = MenuClass::getInstance();
        $this->our_story              = OurStoryClass::getInstance();
        $this->zendesc_hc_integration = ZenDeskHelpCenterIntegration::getInstance();
        $this->subcategories          = Subcategories::getInstance();
        $this->how_it_works           = HowItWorksPageClass::getInstance();
        $this->sync_components        = SyncComponents::getInstance();
        $this->single_component       = SingleComponentPageClass::getInstance();
        $this->sf_user                = OP_User::getInstance();
        $this->cart                   = CartPageClass::getInstance();
        $this->notifications          = Notifications::getInstance();
        $this->sort_cache             = SFSortCache::getInstance();
        $this->customize_cache        = SFCustomizeCache::getInstance();
        $this->report_orders          = SFReportOrders::getInstance();
        $this->order_receipt          = SFOrderReceipt::getInstance();
        $this->email_verification     = EmailVerification::getInstance();
        $this->easypost               = SFEasyPost::getInstance();
        $this->global_cache           = SFGlobalCache::getInstance();
        $this->search_controller      = \ES\Controllers\SearchController::getInstance();
        $this->elastic_search         = \ES\Controllers\ElasticSearchDataController::getInstance();
        $this->sf_filters             = SFFilters::getInstance();
        $this->coupons                = SFCoupons::getInstance();
        $this->rest_pick_and_pack     = SolutionFactoryStore::getInstance();
        $this->customizer             = SFCustomize::getInstance();
        $this->import                 = SFImport::getInstance();
        $this->tutorial               = SolutionFactoryTutorial::getInstance();
    }

    /**
     * Register plugins.
     */
    function register_plugins() {
        $config        = array(
            'id'           => 'sf_lifechef',
            // Unique ID for hashing notices for multiple instances of TGMPA.
            'default_path' => '',
            // Default absolute path to bundled plugins.
            'menu'         => 'tgmpa-install-plugins',
            // Menu slug.
            'parent_slug'  => 'themes.php',
            // Parent menu slug.
            'capability'   => 'edit_theme_options',
            // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
            'has_notices'  => true,
            // Show admin notices or not.
            'dismissable'  => true,
            // If false, a user cannot dismiss the nag message.
            'dismiss_msg'  => '',
            // If 'dismissable' is false, this message will be output at top of nag.
            'is_automatic' => false,
            // Automatically activate plugins after installation or not.
            'message'      => '',
            // Message to output right before the plugins table.

            /*
            'strings'      => array(
              'page_title'                      => __( 'Install Required Plugins', 'sf_lifechef' ),
              'menu_title'                      => __( 'Install Plugins', 'sf_lifechef' ),
              /* translators: %s: plugin name. * /
              'installing'                      => __( 'Installing Plugin: %s', 'sf_lifechef' ),
              /* translators: %s: plugin name. * /
              'updating'                        => __( 'Updating Plugin: %s', 'sf_lifechef' ),
              'oops'                            => __( 'Something went wrong with the plugin API.', 'sf_lifechef' ),
              'notice_can_install_required'     => _n_noop(
                /* translators: 1: plugin name(s). * /
                'This theme requires the following plugin: %1$s.',
                'This theme requires the following plugins: %1$s.',
                'sf_lifechef'
              ),
              'notice_can_install_recommended'  => _n_noop(
                /* translators: 1: plugin name(s). * /
                'This theme recommends the following plugin: %1$s.',
                'This theme recommends the following plugins: %1$s.',
                'sf_lifechef'
              ),
              'notice_ask_to_update'            => _n_noop(
                /* translators: 1: plugin name(s). * /
                'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
                'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
                'sf_lifechef'
              ),
              'notice_ask_to_update_maybe'      => _n_noop(
                /* translators: 1: plugin name(s). * /
                'There is an update available for: %1$s.',
                'There are updates available for the following plugins: %1$s.',
                'sf_lifechef'
              ),
              'notice_can_activate_required'    => _n_noop(
                /* translators: 1: plugin name(s). * /
                'The following required plugin is currently inactive: %1$s.',
                'The following required plugins are currently inactive: %1$s.',
                'sf_lifechef'
              ),
              'notice_can_activate_recommended' => _n_noop(
                /* translators: 1: plugin name(s). * /
                'The following recommended plugin is currently inactive: %1$s.',
                'The following recommended plugins are currently inactive: %1$s.',
                'sf_lifechef'
              ),
              'install_link'                    => _n_noop(
                'Begin installing plugin',
                'Begin installing plugins',
                'sf_lifechef'
              ),
              'update_link' 					  => _n_noop(
                'Begin updating plugin',
                'Begin updating plugins',
                'sf_lifechef'
              ),
              'activate_link'                   => _n_noop(
                'Begin activating plugin',
                'Begin activating plugins',
                'sf_lifechef'
              ),
              'return'                          => __( 'Return to Required Plugins Installer', 'sf_lifechef' ),
              'plugin_activated'                => __( 'Plugin activated successfully.', 'sf_lifechef' ),
              'activated_successfully'          => __( 'The following plugin was activated successfully:', 'sf_lifechef' ),
              /* translators: 1: plugin name. * /
              'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'sf_lifechef' ),
              /* translators: 1: plugin name. * /
              'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'sf_lifechef' ),
              /* translators: 1: dashboard link. * /
              'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'sf_lifechef' ),
              'dismiss'                         => __( 'Dismiss this notice', 'sf_lifechef' ),
              'notice_cannot_install_activate'  => __( 'There are one or more required or recommended plugins to install, update or activate.', 'sf_lifechef' ),
              'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'sf_lifechef' ),
              'nag_type'                        => '', // Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
            ),
            */
        );
        $this->plugins = apply_filters( 'sf_needed_plugins', $this->plugins );
        tgmpa( $this->plugins, $config );
    }

    /**
     * Show install notice for plugin addon.
     */
    public function show_install_notice() {
        add_action( 'admin_notices', [ $this, 'notice_not_finish_installation' ] );
    }

    /**
     * Notice not finish installation.
     */
    public function notice_not_finish_installation() {
        $check_notices = apply_filters( 'sf_check_plugins_notices', [] );
        ?>
        <div class="notice notice-error settings-error is-dismissible">
            <p>Looks like you don't finish installing and activating needed plugins!</p>
            <?php if ( ! empty( $check_notices ) ) { ?>
                <ul>
                    <?php foreach ( $check_notices as $notice ) { ?>
                        <li>- <?php echo esc_html( $notice ); ?></li>
                    <?php } ?>
                </ul>
            <?php } ?>
            <p>Follow <a target="_blank" href="/wp-admin/themes.php?page=tgmpa-install-plugins">here</a> and finish
                installing, please.</p>
        </div>
        <?php
    }

    /**
     * Register all needed hooks.
     */
    public function registerHooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts_n_styles' ] );
        add_action( 'after_setup_theme', [ $this, 'register_menus' ] );
        add_action( 'init', [ $this, 'init_session' ] );

        if ( class_exists( 'NextendSocialLogin' ) ) {
            remove_action( 'login_form', 'NextendSocialLogin::addLoginFormButtons' );
            remove_action( 'register_form', 'NextendSocialLogin::addLoginFormButtons' );
        }
    }

    /**
     * Init session.
     */
    function init_session() {
        if ( empty( session_id() ) || ! isset( $_SESSION ) ) {
            session_start();
        }
    }

    /**
     * Register menus.
     */
    function register_menus() {
        $menus = [
            'header_menu'          => 'Header menu',
            'header_menu_category' => 'Header menu category',
            'right_menu'           => 'Rights menu',
            'hamburger_terms_menu' => 'Hamburger terms menu',
            'hamburger_nav_menu'   => 'Hamburger Learn menu area',
            'hamburger_sub_menu'   => 'Hamburger sub menu area',
        ];

        // need this hack, because carbon_get_theme_option is called after menu registration
        for ( $max_menu = 0; $max_menu < 3; $max_menu ++ ) {
            $footer_menu = get_option( "_op_theme_footer_menus|title|" . $max_menu . "|0|value", false );
            if ( $footer_menu ) {
                $menus[ "footer_menu_" . $max_menu ] = $footer_menu . " in footer";
            }
        }

        register_nav_menus( $menus );
    }

    /**
     * Load scripts and styles.
     */
    function load_scripts_n_styles() {
        $v = wp_get_theme()->get( 'Version' );

        wp_enqueue_style( 'pwd-css', get_template_directory_uri() . '/assets/css/jquery.passwordRequirements.css' );

        wp_enqueue_style( 'op-style', get_stylesheet_uri(), [], $v );
        wp_enqueue_style( 'op_main_styles', get_template_directory_uri() . '/assets/css/style.css', [], $v );

        // Include "Loaders" script at first position
        wp_enqueue_script( 'loaders', get_template_directory_uri() . '/assets/js/loaders.js', [], false, false );

        wp_enqueue_script( 'op_googleapis', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBUSaEqW_k2TcVBaVsCM9Z9LmfPrD8ha_I&libraries=places' );
        wp_enqueue_script( 'op_modernizr_bundle', get_template_directory_uri() . '/assets/js/modernizr.custom.bundle.js', [], $v );
        wp_enqueue_script( 'debounce-js', 'https://cdn.jsdelivr.net/npm/lodash@4.17.20/lodash.min.js' );
        wp_enqueue_script( 'op_app_bundle', get_template_directory_uri() . '/assets/js/app.bundle.js', [], $v, true );

        wp_localize_script( 'op_app_bundle', 'main', [
            'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
            'ajax_nonce'                 => wp_create_nonce( 'op_check' ),
            'get_template_directory_uri' => get_template_directory_uri(),
            'survey_script'              => get_template_directory_uri() . '/assets/js/survey.bundle.js',
            'available_delivery_days'    => carbon_get_theme_option( 'op_subscription_available_delivery_days' ),
            'order_offset'               => carbon_get_theme_option( 'op_subscription_order_schedule_offset' ) ? (integer) carbon_get_theme_option( 'op_subscription_order_schedule_offset' ) : 77
        ] );

        wp_enqueue_script( 'amplitude_front_js', get_template_directory_uri() . '/assets/js/amplitude.js', array( 'jquery' ), $v, true );

        wp_enqueue_script( 'op_progressbar', 'https://cdnjs.cloudflare.com/ajax/libs/progressbar.js/1.1.0/progressbar.js', [ 'jquery' ], false, true );

        // Change file for infinite scroll
        if ( is_tax( 'product_cat' ) OR is_tax( 'product_tag' ) ) {
            global $pagination_num_pages;

            $cate   = get_queried_object();
            $cateID = $cate->term_id;
            wp_enqueue_script( "scroll-js-custom", get_stylesheet_directory_uri() . '/assets/js/sf_infinite_scroll.js', [ 'jquery' ], '', true );

            $vars = array(
                'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                'lastPage'   => $pagination_num_pages,
                'categoryId' => $cateID,
                'taxonomy'   => $cate->taxonomy,
            );

            wp_localize_script( "scroll-js-custom", "infi_scrol_ajaxurl", $vars );
        }

        wp_enqueue_script( 'op_app_backend_dev', get_template_directory_uri() . '/assets/js/op.backend.js', [ 'jquery' ], false, true );
        wp_localize_script('op_app_backend_dev', 'backendCommonParams', [
            'is_user_logged' => get_current_user_id(),
            'site_url' => get_site_url()
        ]);
        wp_enqueue_script( 'order-manage', get_template_directory_uri() . '/assets/js/order-manage.js', [ 'jquery' ], false, $v );

        // overwrite woocommerce checkout scripts
        wp_deregister_script( 'wc-checkout' );
        wp_enqueue_script( 'wc-checkout', get_template_directory_uri() . '/assets/js/wc/checkout.js', array(
            'jquery',
            'woocommerce',
            'wc-country-select',
            'wc-address-i18n'
        ), null, true );

        // Load checkout forms script
        if ( is_checkout() ) {
            wp_enqueue_script( 'checkout-forms', get_template_directory_uri() . '/assets/js/checkout-forms.js', [ 'jquery' ], false, true );
            wp_localize_script( 'checkout-forms', 'checkout_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
        }

        // Load lib for pwd check
        wp_enqueue_script( 'pwd-lib', get_template_directory_uri() . '/assets/js/jquery.passwordRequirements.min.js', [ 'jquery' ], false, true );
        wp_enqueue_script( 'pwd-init', get_template_directory_uri() . '/assets/js/pwd-check.js', [ 'jquery' ], false, true );
    }

    /**
     * Add image sizes.
     */
    function add_image_sizes() {
        add_image_size( 'op_archive_thumbnail', 768, 560, true );
        add_image_size( 'op_single_thumbnail', 588, 447, true );
    }

    /**
     * Import files.
     */
    public function import() {
        include_once 'tgm/class-tgm-plugin-activation.php';
        include_once 'disable-editor.php';
        include_once 'cache/class-global-cache.php';
        include_once 'variations/variations.php';
        include_once 'Zones/zones.php';
        include_once 'ZipCodes/SFZipCodes.php';
        include_once 'site-settings.php';
        include_once 'main-page.php';
        include_once 'zip-code.php';
        include_once 'theme-functions.php';
        include_once 'woo-functions.php';
        include_once 'user.php';
        include_once 'shop.php';
        include_once 'payment.php';
        include_once 'faq.php';
        include_once 'forgot-password.php';
        include_once 'meal-plan-bottom-nav.php';
        include_once 'offerings.php';
        include_once 'sf-menu.php';
        include_once 'walker-menu.php';
        include_once 'walker-menu-mobile.php';
        include_once 'walker-menu-submenu.php';
        include_once 'our-story.php';
        include_once 'zendesk-hc-integration.php';
        include_once 'subcategories.php';
        include_once 'how-it-works.php';
        include_once 'zendesk-hc-integration.php';
        include_once 'subcategories.php';
        include_once 'sync-components.php';
        include_once 'single-component.php';
        include_once 'category-page.php';
        include_once 'cart.php';
        include_once 'notifications.php';
        include_once 'elastic-search/controllers/class-elastic-search-data-controller.php';
        include_once 'cache/class-cache.php';
        include_once 'cache/class-sort-cache.php';
        include_once 'cache/class-customize-cache.php';
        include_once 'email-verification.php';
        include_once 'easypost.php';
        include_once 'reports/class-report-orders.php';
        include_once 'coupons.php';
        include_once 'pdf/class-order-receipt.php';
        include_once 'Survey/survey.php';
        include_once 'SFFilters.php';
        include_once 'Customize/class-customize.php';
        include_once 'rest-pick-and-pack/class-rest-pick-and-pack.php';
        include_once 'subscriptions/class-addon-subscription.php';
        include_once 'import-export/class-import.php';
        include_once 'elastic-search/controllers/class-search-controller.php';
        include_once 'Tutorial/tutorial.php';
    }
}
