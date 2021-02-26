<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class SolutionFactoryThemeSettings{
  private $settings = [];
  private $plugins = [];
  private static $_instance = null;
  public $min_hats;
  public $excluded_cats;

  private function __construct()
  {
  }
  protected function __clone()
  {
  }
  static public function getInstance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public $google_api_key = 'AIzaSyBUSaEqW_k2TcVBaVsCM9Z9LmfPrD8ha_I';

  function init(){

    add_action( 'carbon_fields_register_fields', [$this, 'add_theme_settings'] );

    add_action( 'init', [$this,'register_posttypes'] );
  }

  function register_posttypes(){

    register_post_type( 'sf_questions', [
      'label'  => null,
      'labels' => [
        'name'               => 'Questions', // основное название для типа записи
        'singular_name'      => 'Question', // название для одной записи этого типа
        'add_new'            => 'Add Question', // для добавления новой записи
        'add_new_item'       => 'Adding Question', // заголовка у вновь создаваемой записи в админ-панели.
        'edit_item'          => 'Edit Question', // для редактирования типа записи
        'new_item'           => 'New Question', // текст новой записи
        'view_item'          => 'View Question', // для просмотра записи этого типа.
        'search_items'       => 'Find Question', // для поиска по этим типам записи
        'not_found'          => 'Not found', // если в результате поиска ничего не было найдено
        'not_found_in_trash' => 'Not found in trash', // если не было найдено в корзине
        'parent_item_colon'  => '', // для родителей (у древовидных типов)
        'menu_name'          => 'Questions', // название меню
      ],
      'description'         => '',
      'public'              => false,
      'exclude_from_search' => true, // зависит от public
      'show_ui'             => true, // зависит от public
      'show_in_menu'        => true, // показывать ли в меню адмнки
      'show_in_rest'        => null, // добавить в REST API. C WP 4.7
      'rest_base'           => null, // $post_type. C WP 4.7
      'menu_icon'           => 'dashicons-editor-help',
      //'capability_type'   => 'post',
      //'capabilities'      => 'post', // массив дополнительных прав для этого типа записи
      //'map_meta_cap'      => null, // Ставим true чтобы включить дефолтный обработчик специальных прав
      'hierarchical'        => false,
      'supports'            => [ 'title', 'editor' ], // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
      'has_archive'         => false,
      'rewrite'             => true,
      'query_var'           => true,
    ] );

  }

  function add_theme_settings() {

    $basic_options_container = Container::make( 'theme_options', __( 'Theme Options' ) )
        ->add_tab( __( 'Main Settings' ), array(
          Field::make( 'html', 'crb_information_text' )
            ->set_html( '<p>Configure settings</p>' )
        ) )
        ->add_tab( __( 'Home page' ), array(
                Field::make( 'text', 'op_homepage_slider_button_title', 'Slider right button title' )
                    ->set_default_value( 'Discover meals' ),
            )
        )
        ->add_tab( __( 'Checkout Settings' ), array(
          Field::make( 'association', 'sf_checkout_questions', __( 'Checkout Common Question' ) )
          ->set_types( array(
              array(
                  'type'      => 'post',
                  'post_type' => 'sf_questions',
              )
          ) ),
        ) )
        ->add_tab( __( 'Cart Page Settings' ), array(
          Field::make( 'association', 'sf_exclude_category_page', __( 'Exclude category from cart page' ) )
            ->set_types( array(
                array(
                  'type'     => 'term',
                  'taxonomy' => 'product_cat',
                )
            ) )
        ) )
        ->add_tab( __( 'Header Settings' ), array(
          Field::make( 'image', 'op_theme_header_logo', __( 'Header logo' ) ),
          Field::make( 'separator', 'op_theme_header_sep_1', __( 'PROMO BAR' ) ),
          Field::make( 'separator', 'op_theme_header_sep_2', __( 'displayed for non-logged in users and causes a pop-up window for zip codes' ) ),
          Field::make( 'textarea', 'op_theme_header_txt_promo', __( 'Text' ) )->set_width( 50 ),
	        Field::make( 'text', 'op_theme_header_btn_promo', __( 'Text button' ) )->set_width( 50 ),
	        Field::make( 'association', 'op_theme_header_coupon', __( 'Show Coupon For User' ) )->set_types( array(
		        array(
			        'type'      => 'post',
			        'post_type' => 'shop_coupon',
		        )
	        ) )->set_max(1),
        ) )
        ->add_tab( __( 'Footer Settings' ), array(
          Field::make( 'image', 'op_theme_footer_logo', __( 'Footer logo' ) ),
          Field::make( 'textarea', 'op_theme_footer_slogan', __( 'Text under logo' ) ),
          Field::make( 'complex', 'op_theme_footer_menus', __( 'Footer menus' ) )->set_max( 3 )->add_fields( array(
              Field::make( 'text', 'title', __( 'Title' ) )
          ) ),
          Field::make( 'text', 'op_theme_footer_facebook', __( 'Facebook link' ) )->set_attribute( 'type', 'link' ),
          Field::make( 'text', 'op_theme_footer_twitter', __( 'Twitter link' ) )->set_attribute( 'type', 'link' ),
          Field::make( 'text', 'op_theme_footer_instagram', __( 'Instagram link' ) )->set_attribute( 'type', 'link' ),
          Field::make( 'text', 'op_theme_footer_youtube', __( 'YouTube link' ) )->set_attribute( 'type', 'link' ),
          Field::make( 'textarea', 'op_theme_footer_copyright', __( 'CopyRights Text' ) )

        ) )
	    ->add_tab( __( 'Meal page setting' ), array(
		    Field::make( 'text', 'op_meal_hats_show', __( 'Show panel "Best choice" if chef score' ) )
			    ->set_attribute( 'min', '2' )
		        ->set_attribute('type', 'number')
	    ) )
        ->add_tab( __( 'EasyPost setting' ), array(
          Field::make( 'select', 'op_easypost_env', __( 'Environment' ) )
             ->set_options( array(
               'prod' => 'Production',
               'demo'       => 'Demo',
             ) ),
        ) )
        ->add_tab( __( 'Unsubscribe page' ), array(
                Field::make( 'text', 'op_unsubscribe_reasons_title', 'Page title' ),
                Field::make( 'text', 'op_unsubscribe_reasons_description', 'Page description' ),
                Field::make( 'textarea', 'op_unsubscribe_reasons', 'Reasons' ),
                Field::make( 'text', 'op_unsubscribe_reasons_button', 'Button text' ),
            )
        )
        ->add_tab( __( '404 page' ), array(
                Field::make( 'text', 'op_404_page_title', __( 'Page title' ) )
                    ->set_default_value( 'Oops!' ),
                Field::make( 'textarea', 'op_404_page_description', __( 'Page description' ) )
                    ->set_default_value( 'For some reason the page you’re looking for is missing or moved to another place.' ),
                Field::make( 'text', 'op_404_page_try_title', __( 'Try title' ) )
                    ->set_default_value( 'Try the following instead:' ),
                Field::make( 'text', 'op_404_page_first_link_text_before', __( 'Try first link text before' ) )
                    ->set_default_value( 'Explore' ),
                Field::make( 'text', 'op_404_page_first_link_title', __( 'Try first link title' ) )
                    ->set_default_value( 'Healthy Meal Plans' ),
                Field::make( 'text', 'op_404_page_first_link_url', __( 'Try first link url' ) )
                    ->set_default_value( '/product-category/meals/' ),
                Field::make( 'text', 'op_404_page_second_link_text_before', __( 'Try second link text before' ) )
                    ->set_default_value( 'Check' ),
                Field::make( 'text', 'op_404_page_second_link_title', __( 'Try second link title' ) )
                    ->set_default_value( 'Fresh Groceries' ),
                Field::make( 'text', 'op_404_page_second_link_url', __( 'Try second link url' ) )
                    ->set_default_value( '/groceries/' ),
                Field::make( 'text', 'op_404_page_third_link_title', __( 'Try third link title' ) )
                    ->set_default_value( 'Contact Us' ),
                Field::make( 'text', 'op_404_page_third_link_url', __( 'Try third link url' ) )
                    ->set_default_value( '/contact-us/' ),
            )
        )->add_tab( __( 'Contact us page' ), array(
                Field::make( 'text', 'op_contact_page_description', __( 'Page description' ) )
                    ->set_default_value( 'We’d love to hear from you. Give us a shout.' ),
                Field::make( 'text', 'op_contact_page_form_heading', __( 'Form heading' ) )
                    ->set_default_value( 'Get in touch' ),
                Field::make( 'text', 'op_contact_page_form_description', __( 'Form description' ) )
                    ->set_default_value( 'Please fill out the form below' ),
                Field::make( 'text', 'op_contact_page_sidebar_title', __( 'Right sidebar title' ) )
                    ->set_default_value( 'Connect us with' ),
                Field::make( 'text', 'op_contact_page_sidebar_mail', __( 'Right sidebar mail' ) )
                    ->set_default_value( 'contact@lifechef.com' ),
                Field::make( 'text', 'op_contact_page_sidebar_phone_title', __( 'Right sidebar phone title' ) )
                    ->set_default_value( 'Call Us:' ),
                Field::make( 'text', 'op_contact_page_sidebar_phone', __( 'Right sidebar phone' ) )
                    ->set_default_value( '1 855 932 4048' ),
                Field::make( 'text', 'op_contact_page_work_time', __( 'Right sidebar work time' ) )
                    ->set_default_value( 'Monday – Friday, 8:00 AM to 5:00 PM EST' ),
                Field::make( 'text', 'op_contact_page_address', __( 'Right sidebar address' ) )
                    ->set_default_value( '1040 Pennsylvania Ave. Trenton, NJ 08638' ),
            )
        )
        ->add_tab( __( 'Loaders' ), array(
              Field::make( 'multiselect', 'op_main_loader_pages', __('List of pages, where "main" loader use') )
                ->add_options( $this->get_all_pages_slugs() ),
              Field::make( 'text', 'op_loaders_catalog_text', __( 'Catalog loader text' ) )
                ->set_default_value( 'Loading, please wait...' ),
            )
        )
        ->add_tab( __( 'ZIP settings' ), [
          Field::make( 'multiselect', 'op_zip_ajax_pages', __('List of static pages, with AJAX zip initial set') )
            ->add_options( $this->get_all_pages_slugs() ),
        ] );

    do_action('sf_add_theme_suboption', $basic_options_container );
  }

  public function min_hats_show() {

  	if ( empty( $this->min_hats ) ) {
  		$this->min_hats  = carbon_get_theme_option( 'op_meal_hats_show' );
    }

  	return $this->min_hats;
  }

  public function excluded_cats() {
	  $user_zone = op_help()->zip_codes->get_current_user_zone();

	  if ( empty( $this->excluded_cats ) ) {
		  $this->excluded_cats  = array_column( carbon_get_theme_option( 'sf_exclude_category_page' ), 'id');
	  }

	  // Todo show this in admin page (when make admin page for zips)
	  $catalogs_zones = [
	  	'overnight' => [ 27 ],
	    'national' => [ 27, 15 ],
	    'local' => []
	  ];

	  $this->excluded_cats = array_merge($this->excluded_cats, $catalogs_zones[$user_zone]);

	  return $this->excluded_cats;
  }

  public function get_all_pages_slugs() {
    $pages = get_pages();
    $slugs = [];

    foreach ( $pages as $key => $page ) {
      $slugs[ $page->post_name ] = $page->post_title;
    }

    return $slugs;
  }
}
