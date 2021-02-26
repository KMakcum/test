<?php
require_once 'zendesc-int.php';

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class FaqPageClass
{
    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function getURLSegments()
    {
        return explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    }

    private function getURLSegment($n)
    {
        $segs = $this->getURLSegments();
        return count($segs) > 0 && count($segs) >= ($n - 1) ? $segs[$n] : '';
    }

    public function init()
    {
        add_action('wp_ajax_question_form_handler', [$this, 'question_form_handler']);
        add_action('wp_ajax_nopriv_question_form_handler', [$this, 'question_form_handler']);
        add_action('wp_ajax_nopriv_get_zendesk_articles', [$this, 'get_zendesk_articles_and_categories']);
        add_action('carbon_fields_post_meta_container_saved', [$this, 'faq_single_categories_container_saved']);
        add_action('wp_enqueue_scripts', function () {
            if (($this->getURLSegment(1) === 'faq') || ($this->getURLSegment(1) === 'contact-us')) {
                wp_enqueue_script('faq-ajax', get_stylesheet_directory_uri() . '/assets/js/faq-ajax.js', ['jquery', 'dropzone-js']
                );
                wp_localize_script('faq-ajax', 'ajaxSettings', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('life-chef-action'),
                    'ajax_form_page_slug' => (($this->getURLSegment(1) === 'faq') ? 'faq' : ($this->getURLSegment(1) === 'contact-us')) ? 'contact-us' : ''
                ]);
                wp_enqueue_script('dropzone-js', 'https://rawgit.com/enyo/dropzone/master/dist/dropzone.js', ['jquery']);
            }
        });
        add_action('carbon_fields_register_fields', [$this, 'add_faq_fields']);
        add_action('carbon_fields_register_fields', [$this, 'add_faq_single_fields']);
        add_action('wp', [$this, 'setup_cron_task']);
        add_action('check_zendesk_articles_event', [$this, 'check_zendesk_hc_articles']);

        if (!post_exists('Faq')) {
            $faq_page = [
                'post_title' => 'Faq',
                'post_name' => 'faq',
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'page'
            ];
            wp_insert_post($faq_page);
        }
    }

    public function setup_cron_task()
    {
        if (!wp_next_scheduled('check_zendesk_articles_event')) {
            wp_schedule_event(time(), 'every_minute', 'check_zendesk_articles_event');
        }
    }

    public function check_zendesk_hc_articles()
    {
        $faq_post = get_page_by_path('faq');
        $current_faq_cats_qa = carbon_get_post_meta($faq_post->ID, 'faq-single-categories');
        $zendesk_articles = $this->get_zendesk_articles_and_categories();
        $flag = ($this->arrayDiffRecursive($current_faq_cats_qa, $zendesk_articles) || $this->arrayDiffRecursive($zendesk_articles, $current_faq_cats_qa));
        if ($flag) {
            $this->faq_single_categories_recreate($zendesk_articles);
        }
    }

    private function arrayDiffRecursive($firstArray, $secondArray, $reverseKey = false)
    {
        $oldKey = 'old';
        $newKey = 'new';
        if ($reverseKey) {
            $oldKey = 'new';
            $newKey = 'old';
        }
        $difference = [];
        foreach ($firstArray as $firstKey => $firstValue) {
            if (is_array($firstValue)) {
                if (!array_key_exists($firstKey, $secondArray) || !is_array($secondArray[$firstKey])) {
                    $difference[$oldKey][$firstKey] = $firstValue;
                    $difference[$newKey][$firstKey] = '';
                } else {
                    $newDiff = $this->arrayDiffRecursive($firstValue, $secondArray[$firstKey], $reverseKey);
                    if (!empty($newDiff)) {
                        $difference[$oldKey][$firstKey] = $newDiff[$oldKey];
                        $difference[$newKey][$firstKey] = $newDiff[$newKey];
                    }
                }
            } else {
                if (!array_key_exists($firstKey, $secondArray) || $secondArray[$firstKey] != $firstValue) {
                    $difference[$oldKey][$firstKey] = $firstValue;
                    $difference[$newKey][$firstKey] = $secondArray[$firstKey];
                }
            }
        }
        return $difference;
    }

    public function get_zendesk_articles_and_categories()
    {
        $output_articles = [];
        $zendesk_api = new ZenDeskIntegration();
        $zendesk_api->init();
        $sections_pages[0] = $zendesk_api->client->helpCenter->categories(360004427794)->sections()->findAll();
        for ($i = 1; $i < $sections_pages[0]->page_count; $i++) {
            $next_page = $sections_pages[$i - 1]->next_page;
            $pages[$i] = json_decode(file_get_contents($next_page));
        }
        $sections_data = [];
        $index = 0;
        foreach ($sections_pages as $sections_page) {
            foreach ($sections_page->sections as $section) {
                $sections_data[$index] =
                    [
                        'section_id' => $section->id,
                        'section_name' => $section->name,
                    ];
                $index++;
            }
        }
        $articles_pages[0] = $zendesk_api->client->helpCenter->sections()->articles()->findAll(['per_page' => '100']);
        for ($i = 1; $i < $articles_pages[0]->page_count; $i++) {
            $articles_pages[$i] = $zendesk_api->client->helpCenter->sections()->articles()->findAll([
                'per_page' => '100',
                'page' => $i + 1
            ]);
        }
        $excludes = [
            '360010596534',
            '360010596394',
            '360011717813',
            '360011742613',
            '360009485094',
            '360011922173'
        ];
        $i = 0;
        $temp_response = [];
        foreach ($articles_pages as $articles_page) {
            foreach ($articles_page->articles as $article) {
                foreach ($sections_data as $sections_datum) {
                    if ($article->section_id == $sections_datum['section_id'] && !in_array($sections_datum['section_id'], $excludes)) {
                        $temp_response[strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $sections_datum['section_name'])))][$i] =
                            [
                                'section_name' => $sections_datum['section_name'],
                                'article' => $article
                            ];
                        $i++;
                    }
                }
            }
        }
        $i = 0;
        foreach ($temp_response as $first_level) {
            $output_articles[$i]['_type'] = '_';
            $output_articles[$i]['faq_category_name'] = $first_level[array_key_first($first_level)]['section_name'];
            $j = 0;
            foreach ($first_level as $second_level) {
                $output_articles[$i]['faq_category_qa'][$j] =
                    [
                        '_type' => '_',
                        'single_category_question' => $second_level['article']->title,
                        'single_category_answer' => $second_level['article']->body,
                    ];
                $j++;
            }
            $i++;
        }
        return $output_articles;
    }

    //Pages generator
    public function faq_single_categories_recreate($zendesk_data)
    {
        $post = get_page_by_path('faq');
        $faq_single_categories = $zendesk_data;
        carbon_set_post_meta($post->ID, 'faq-single-categories', $faq_single_categories);

        foreach ($faq_single_categories as $faq_single_category) {
            $faq_single_page = [
                'post_title' => $faq_single_category['faq_category_name'],
                'post_name' => strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', trim($faq_single_category['faq_category_name']))),
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'page',
                'post_parent' => $post->ID
            ];
            $faq_single_page_title = get_page_by_title($faq_single_category['faq_category_name']);
            if ($faq_single_page_title) {
                $faq_single_page['post_id'] = $faq_single_page_title->ID;
            }
            $post_id_child = wp_insert_post($faq_single_page);
            update_post_meta($post_id_child, '_wp_page_template', 'page-faq-single.php');
            $meta = [];
            foreach ($faq_single_category['faq_category_qa'] as $faq_cat_data) {
                array_push($meta,
                    [
                        'single_category_question' => $faq_cat_data['single_category_question'],
                        'single_category_answer' => $faq_cat_data['single_category_answer'],
                    ]
                );
            }
            carbon_set_post_meta($faq_single_page_title->ID ? $faq_single_page_title->ID : $post_id_child, 'faq_category_qa', $meta);

        }
        $child_pages = get_children(['post_parent' => $post->ID]);
        if ($child_pages) {
            foreach ($child_pages as $child_page) {
                $flag = false;
                foreach ($faq_single_categories as $faq_single_category) {
                    if (strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', trim($faq_single_category['faq_category_name']))) === $child_page->post_name) {
                        $flag = true;
                        break;
                    }
                }
                if (!$flag) {
                    wp_delete_post($child_page->ID, true);
                }
            }
        }
    }


    // Question form ajax endpoint
    public function question_form_handler()
    {
        $request = (object)$_POST;
        $request_files = (object)$_FILES;
        $request->data = json_decode(stripslashes($request->data), true);
        if (!$this->checkNonce($request->nonce, 'life-chef-action')) {
            self::returnError('wrong nonce');
        }
        if (!$this->isSetNotEmpty($request->data['user-name'])
            || !$this->isSetNotEmpty($request->data['user-email'])
            || !$this->isSetNotEmpty($request->data['user-phone'])
            || !$this->isSetNotEmpty($request->data['question-category'])
            || !$this->isSetNotEmpty($request->data['user-message'])) {
            self::returnError('some of required fields are empty');
        }
        $user_zip_code = [];
        if ($request->zip_code) {
            $user_zip_code = [
                'id' => '1500000014962',
                'value' => preg_replace('/\s+/', '', $request->zip_code)
            ];
        }
        $zendesk_api = new ZenDeskIntegration();
        $zendesk_api->init();
        if ($this->isSetNotEmpty($_FILES)) {
            $attachments = [];
            $i = 0;
            foreach ($request_files->files['name'] as $file_name) {
                $attachments[$i]['name'] = $file_name;
                $i++;
            }
            $i = 0;
            foreach ($request_files->files['type'] as $file_type) {
                $attachments[$i]['type'] = $file_type;
                $i++;
            }
            $i = 0;
            foreach ($request_files->files['size'] as $file_size) {
                $attachments[$i]['size'] = $file_size;
                $i++;
            }
            if (mkdir(__DIR__ . '/tempuploads', 755, false) || file_exists(__DIR__ . '/tempuploads')) {
                $i = 0;
                foreach ($request_files->files['tmp_name'] as $temp_file) {
                    $temp_uploads = __DIR__ . '/tempuploads/';
                    $attachment_name = $temp_uploads . $attachments[$i]['name'];
                    move_uploaded_file($temp_file, $attachment_name);
                    $attachments[$i]['file'] = __DIR__ . '/tempuploads/' . $attachments[$i]['name'];
                    $i++;
                };
            };
            $token = '';
            foreach ($attachments as $attachment) {
                if (isset($token)) {
                    $attachment['token'] = $token;
                }
                $zendesk_attachments = $zendesk_api->client->attachments()->upload($attachment);
                $token = $zendesk_attachments->upload->token;
            }
        }
        $ticket_data =
            [
                'requester' =>
                    [
                        'name' => $request->data['user-name'],
                        'email' => $request->data['user-email']
                    ],
                'subject' => 'User ' . $request->data['user-name'] . ' left a question',
                'comment' =>
                    [
                        'body' => $request->data['user-message'],
                    ],
                'type' => 'question',
                'priority' => 'normal',
                'status' => 'new',
                'custom_fields' =>
                    [
                        [
                            'id' => '360051983373',
                            'value' =>
                                strtolower(
                                    str_replace(' ', '_',
                                        str_replace(' & ', '_n_',
                                            str_replace('/', '_n_',
                                                $request->data['question-category']))))
                        ],
                        [
                            'id' => '360052241453',
                            'value' => $request->data['user-phone']
                        ],
                        $user_zip_code
                    ],
            ];
        if ($this->isSetNotEmpty($request->data['user-order-number'])) {
            $ticket_data['custom_fields'][2] =
                [
                    'id' => '360050645494',
                    'value' => $request->data['user-order-number']
                ];
        }
        if (isset($token)) {
            $ticket_data['comment']['uploads'] = [$token];

        }
        $zendesk_api->client->tickets()->create($ticket_data);
        rmdir(__DIR__ . '/tempuploads/');
        self::returnData('OK');
    }

    //Faq single template carbon fields
    public function add_faq_single_fields()
    {
        $faq = get_page_by_title('Faq');
        Container::make('post_meta', __('Faq category'))
            ->where('post_parent_id', '=', $faq->ID)
            ->add_fields(
                [
                    Field::make('complex', 'faq_category_qa', __('Category Question / Answer'))
                        ->set_layout('tabbed-horizontal')
                        ->add_fields(
                            [
                                Field::make('text', 'single_category_question', __('Question')),
                                Field::make('text', 'single_category_answer', __('Answer'))
                            ]
                        )
                ]
            );
    }


    // Faq template carbon fields
    public function add_faq_fields()
    {
        Container::make('post_meta', 'Faq page settings')
            ->show_on_page('faq')
            ->add_fields([
                Field::make('text', 'search_title', __('Search Title')),
                Field::make('text', 'search_subtitle_text', __('Text under title')),
                Field::make('text', 'search_placeholder', __('Search placeholder')),
                Field::make( 'text', 'faq_page_sidebar_phone_title', __( 'Right sidebar phone title' ) )
                    ->set_default_value( 'Call Us:' ),
                Field::make( 'text', 'faq_contact_page_sidebar_phone', __( 'Right sidebar phone' ) )
                    ->set_default_value( '1 855 932 4048' ),
                Field::make( 'text', 'faq_contact_page_work_time', __( 'Right sidebar work time' ) )
                    ->set_default_value( 'Monday – Friday, 8:00 AM to 5:00 PM EST' ),
                Field::make( 'text', 'faq_contact_page_address', __( 'Right sidebar address' ) )
                    ->set_default_value( '1040 Pennsylvania Ave. Trenton, NJ 08638' ),
                Field::make('complex', 'faq_categories', __('Faq categories (gallery)'))
                    ->set_layout('tabbed-horizontal')
                    ->add_fields(
                        [
                            Field::make('image', 'category_image', __('Category Image')),
                            Field::make('text', 'category_name', __('Category Name')),
                            Field::make('text', 'category_info', __('Category info'))
                        ]
                    ),
                Field::make('complex', 'faq-single-categories', __('Faq single categories'))
                    ->set_layout('tabbed-vertical')
                    ->add_fields(
                        [
                            Field::make('text', 'faq_category_name', __('Faq single category name'))->set_required(true),
                            Field::make('complex', 'faq_category_qa', __('Faq single category content'))
                                ->set_layout('tabbed-horizontal')
                                ->add_fields(
                                    [
                                        Field::make('text', 'single_category_question', __('Question')),
                                        Field::make('text', 'single_category_answer', __('Answer'))
                                    ]
                                )
                        ]
                    ),
                Field::make('text', 'contact_section_header', __('Contact section header')),
                Field::make('complex', 'contact_section_content', __('Contact us section items'))
                    ->set_layout('tabbed-horizontal')
                    ->add_fields(
                        [
                            Field::make('text', 'contact_us_item_title', __('Contact us item title')),
                            Field::make('text', 'contact_us_item_text', __('Contact us item text(link)')),
                        ]
                    ),
                Field::make('text', 'question_form_title', __('Question form title')),
                Field::make('text', 'question_button_text', __('Question button text'))
            ]);
    }

    //Faq single template fields getter
    public function get_parent_faq_fields($id)
    {
        return [
            'contact_section_header' => carbon_get_post_meta($id, 'contact_section_header') ?
                carbon_get_post_meta($id, 'contact_section_header') :
                __('Couldn’t find what you were looking for?'),
            'contact_section_content' => carbon_get_post_meta($id, 'contact_section_content') ?
                carbon_get_post_meta($id, 'contact_section_content') :
                [
                    [
                        'type' => '_',
                        'contact_us_item_title' => 'Chat now',
                        'contact_us_item_text' => 'Chat now'
                    ],
                    [
                        'type' => '_',
                        'contact_us_item_title' => 'Email',
                        'contact_us_item_text' => 'contact@lifechef.com'
                    ],
                    [
                        'type' => '_',
                        'contact_us_item_title' => 'Have a question?',
                        'contact_us_item_text' => 'Submit a question'
                    ]
                ]
        ];
    }

    //Faq template fields getter
    public function get_faq_fields($id)
    {
        return [
            'search_title' => carbon_get_post_meta($id, 'search_title') ?
                carbon_get_post_meta($id, 'search_title') :
                __('Frequently Asked Questions'),
            'search_subtitle_text' => carbon_get_post_meta($id, 'search_subtitle_text') ?
                carbon_get_post_meta($id, 'search_subtitle_text') :
                __('Search our FAQs or click one of the sections below.  Search'),
            'search_placeholder' => carbon_get_post_meta($id, 'search_placeholder') ?
                carbon_get_post_meta($id, 'search_placeholder') :
                __('Search by topics, keywords or phrases'),
            'faq_sidebar_data' => [
                'faq_page_sidebar_phone_title'   => carbon_get_post_meta($id, 'faq_page_sidebar_phone_title'),
                'faq_contact_page_sidebar_phone' => carbon_get_post_meta($id, 'faq_contact_page_sidebar_phone'),
                'faq_contact_page_work_time'     => carbon_get_post_meta($id, 'faq_contact_page_work_time'),
                'faq_contact_page_address'       => carbon_get_post_meta($id, 'faq_contact_page_address'),
            ],
            'faq_single_categories' => carbon_get_post_meta($id, 'faq-single-categories') ?
                carbon_get_post_meta($id, 'faq-single-categories') :
                [
                    [

                    ]
                ],
            'faq_categories' => carbon_get_post_meta($id, 'faq_categories') ?
                carbon_get_post_meta($id, 'faq_categories') :
                [
                    [
                        'type' => '_',
                        'category_image' => get_stylesheet_directory_uri() . '/assets/img/base/sm-chef-hat.svg',
                        'category_name' => 'About LifeChef™',
                        'category_info' => 'Pick your meal plan for healthier you',
                    ],
                    [
                        'type' => '_',
                        'category_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stages-2.svg',
                        'category_name' => 'Meals & Pricing',
                        'category_info' => 'Pick your meal plan for healthier you',
                    ],
                    [
                        'type' => '_',
                        'category_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stages-1.svg',
                        'category_name' => 'Dietary and Nutrition',
                        'category_info' => 'Pick your meal plan for healthier you',
                    ],
                    [
                        'type' => '_',
                        'category_image' => get_stylesheet_directory_uri() . '/assets/img/base/packaging.svg',
                        'category_name' => 'Packaging & Recycling',
                        'category_info' => 'Pick your meal plan for healthier you',
                    ],
                    [
                        'type' => '_',
                        'category_image' => get_stylesheet_directory_uri() . '/assets/img/base/subscription.svg',
                        'category_name' => 'Managing my Subscription',
                        'category_info' => 'Pick your meal plan for healthier you',
                    ],
                    [
                        'type' => '_',
                        'category_image' => get_stylesheet_directory_uri() . '/assets/img/base/billing.svg',
                        'category_name' => 'Payment/Promotions',
                        'category_info' => 'Pick your meal plan for healthier you',
                    ],
                    [
                        'type' => '_',
                        'category_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stages-4.svg',
                        'category_name' => 'Delivery & Shipping',
                        'category_info' => 'Pick your meal plan for healthier you',
                    ],
                    [
                        'type' => '_',
                        'category_image' => get_stylesheet_directory_uri() . '/assets/img/base/chat.svg',
                        'category_name' => 'Get In Touch',
                        'category_info' => 'Pick your meal plan for healthier you',
                    ]
                ],
            'contact_section_header' => carbon_get_post_meta($id, 'contact_section_header') ?
                carbon_get_post_meta($id, 'contact_section_header') :
                __('Couldn’t find what you were looking for?'),
            'contact_section_content' => carbon_get_post_meta($id, 'contact_section_content') ?
                carbon_get_post_meta($id, 'contact_section_content') :
                [
                    [
                        'type' => '_',
                        'contact_us_item_title' => 'Chat now',
                        'contact_us_item_text' => 'Chat now'
                    ],
                    [
                        'type' => '_',
                        'contact_us_item_title' => 'Email',
                        'contact_us_item_text' => 'contact@lifechef.com'
                    ],
                    [
                        'type' => '_',
                        'contact_us_item_title' => 'Have a question?',
                        'contact_us_item_text' => 'Submit a question'
                    ]
                ],
            'question_form_title' => carbon_get_post_meta($id, 'question_form_title') ?
                carbon_get_post_meta($id, 'question_form_title') :
                __('Can\'t find what you\'re looking for? Let us help you.'),
            'question_button_text' => carbon_get_post_meta($id, 'question_button_text') ?
                carbon_get_post_meta($id, 'question_button_text') :
                __('Submit a question'),
        ];
    }


    private function checkNonce($nonce, $action)
    {
        return wp_verify_nonce($nonce, $action);
    }

    private static function returnData($data = [])
    {
        header('Content-Type:application/json');
        echo json_encode(['status' => true, 'data' => $data]);
        wp_die();
    }

    private static function returnError($message)
    {
        header('Content-Type:application/json');
        wp_send_json_error($message, 400);
        wp_die();
    }

    private function isSetNotEmpty($var)
    {
        return (isset($var) && !empty($var)) ? $var : null;
    }

}
