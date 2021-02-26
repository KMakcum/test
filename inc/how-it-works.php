<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class HowItWorksPageClass
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
        add_action('carbon_fields_register_fields', [$this, 'set_how_it_works_fields']);
        if (($this->getURLSegment(1) === 'how-it-works')) {
            if (!post_exists('How it works')) {
                $how_it_works_page = [
                    'post_title' => 'How it works',
                    'post_name' => 'how-it-works',
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type' => 'page'
                ];
                wp_insert_post($how_it_works_page);
            }
        }
    }

    public function set_how_it_works_fields()
    {
        Container::make('post_meta', 'How it works page settings')
            ->show_on_page('how-it-works')
            ->add_fields([
                Field::make('text', 'how_it_works_title', __('How it works title')),
                Field::make('text', 'how_it_works_subheader', __('Text under title')),
                Field::make('complex', 'how_it_works_content', __('Page content list'))
                    ->set_layout('tabbed-horizontal')
                    ->add_fields(
                        [
                            Field::make('image', 'how_it_works_right_image', __('Left column image')),
                            Field::make('image', 'how_it_works_left_image', __('Right column icon')),
                            Field::make('text', 'how_it_works_left_title', __('Right column title')),
                            Field::make('text', 'how_it_works_left_text', __('Right column text'))
                        ]
                    ),
                Field::make('image', 'how_it_works_rdy_to_start_bg', __('Ready to start bg image')),
                Field::make('text', 'how_it_works_rdy_to_start_title', __('Ready to start title')),
                Field::make('text', 'how_it_works_rdy_to_start_text', __('Ready to start text')),
                Field::make('text', 'how_it_works_rdy_to_start_button_text', __('Ready to start button text')),
                Field::make('text', 'how_it_works_rdy_to_start_button_url', __('Ready to start button redirect link')),
            ]);
    }

    public function get_how_it_works_fields($id)
    {
        return [
            'how_it_works_title' => carbon_get_post_meta($id, 'how_it_works_title') ?
                carbon_get_post_meta($id, 'how_it_works_title') :
                __('How it works'),
            'how_it_works_subheader' => carbon_get_post_meta($id, 'how_it_works_subheader') ?
                carbon_get_post_meta($id, 'how_it_works_subheader') :
                __('It’s never been easier to eat fresh, tasteful and healthy food.'),
            'how_it_works_content' => carbon_get_post_meta($id, 'how_it_works_content') ?
                carbon_get_post_meta($id, 'how_it_works_content') :
                [
                    [
                        'type' => '_',
                        'how_it_works_right_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stage-1.jpg',
                        'how_it_works_left_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stages-1.svg',
                        'how_it_works_left_title' => __('Nutritional Survey', ''),
                        'how_it_works_left_text' => __('Take a few minutes to complete our nutritional survey to build your personalized menu.', ''),
                    ],
                    [
                        'type' => '_',
                        'how_it_works_right_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stage-2.jpg',
                        'how_it_works_left_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stages-2.svg',
                        'how_it_works_left_title' => __('Your Personal Menu', ''),
                        'how_it_works_left_text' => __("Select from thousand of pre-made meals, groceries and vitamins from Your LifeChef™ Personalized Menu.", ''),
                    ],
                    [
                        'type' => '_',
                        'how_it_works_right_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stage-3.jpg',
                        'how_it_works_left_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stages-3.svg',
                        'how_it_works_left_title' => __('Build Your Plan', ''),
                        'how_it_works_left_text' => __("Add 6, 10 or 14 meals to your weekly plan. You can skip, change or pause at any time.", ''),
                    ],
                    [
                        'type' => '_',
                        'how_it_works_right_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stage-4.jpg',
                        'how_it_works_left_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stages-4.svg',
                        'how_it_works_left_title' => __('Cooked & Delivered', ''),
                        'how_it_works_left_text' => __("Our Chef's cook your gourmet meals and we deliver them weekly to your doorstep.", ''),
                    ],
                    [
                        'type' => '_',
                        'how_it_works_right_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stage-5.jpg',
                        'how_it_works_left_image' => get_stylesheet_directory_uri() . '/assets/img/base/work-stages-5.svg',
                        'how_it_works_left_title' => __('Heat & Enjoy', ''),
                        'how_it_works_left_text' => __("Keep refrigerated for up to 7 days. Microwave and enjoy within minutes. ", ''),
                    ],
                ],
            'how_it_works_rdy_to_start_bg' => carbon_get_post_meta($id, 'how_it_works_rdy_to_start_bg') ?
                carbon_get_post_meta($id, 'how_it_works_rdy_to_start_bg') :
                get_stylesheet_directory_uri() . '/assets/img/bg/ready-to-start-2.jpg',
            'how_it_works_rdy_to_start_title' => carbon_get_post_meta($id, 'how_it_works_rdy_to_start_title') ?
                carbon_get_post_meta($id, 'how_it_works_rdy_to_start_title') :
                __('Ready To Start Your Journey to a Healthier You?'),
            'how_it_works_rdy_to_start_text' => carbon_get_post_meta($id, 'how_it_works_rdy_to_start_text') ?
                carbon_get_post_meta($id, 'how_it_works_rdy_to_start_text') :
                __('Personalized Nutritional Meals, Groceries and high quality vitamins and supplements delivered to your door.'),
            'how_it_works_rdy_to_start_button_text' => carbon_get_post_meta($id, 'how_it_works_rdy_to_start_button_text') ?
                carbon_get_post_meta($id, 'how_it_works_rdy_to_start_button_text') :
                __('Get Started'),
            'how_it_works_rdy_to_start_button_url' => carbon_get_post_meta($id, 'how_it_works_rdy_to_start_button_url') ?
                carbon_get_post_meta($id, 'how_it_works_rdy_to_start_button_url') :
                '',
        ];
    }
}
