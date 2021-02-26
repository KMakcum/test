<?php

class SFFilters
{
    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function init()
    {
        add_action('save_post_sf_survey_step', [$this, 'save_data_filters'], 100);

        add_action('wp_ajax_set_chose_filters', [$this, 'set_chose_filters']);
        add_action('wp_ajax_nopriv_set_chose_filters', [$this, 'set_chose_filters']);

        add_action('wp_ajax_clear_selected_filters', [$this, 'clear_selected_filters']);
        add_action('wp_ajax_nopriv_clear_selected_filters', [$this, 'clear_selected_filters']);

//		if ( ! is_admin()  ) {
//			add_action( 'woocommerce_init', function () {
//				if ( ! WC()->session->has_session() ) {
//					WC()->session->set_customer_session_cookie( true );
//				}
//			} );
//		}

    }

    public function save_data_filters($post_id)
    {
        if (wp_is_post_revision($post_id)) {
            return;
        }

        if (get_post($post_id)->post_status != 'publish') {
            return;
        }

        $structure_data = $this->generate_arr_data_from_survey();

        if (!empty($structure_data)) {
            update_option('filters_data_cache', serialize($structure_data), false);
        }
    }

    public function generate_arr_data_from_survey()
    {
//		$start = microtime( true );
        global $wpdb;

        $ids = $wpdb->get_col("SELECT `ID` FROM {$wpdb->posts} WHERE post_type='sf_survey_step' AND post_status='publish'");

        $data_filters = [];

        foreach ($ids as $id) {
            $survey_plan = carbon_get_post_meta($id, 'sf_survey');

            $questions_to_show = array_filter($survey_plan, function ($item) {
                return $item['show_in_catalog_filters'] === true;
            });

            foreach ($questions_to_show as $question) {
                $temp = [];
                $temp['type'] = $question['filter_type'];
                $temp['question_id'] = $id;
                $temp['question_title'] = sanitize_title_with_dashes($question['question_title']);
                $temp['show_title'] = $question['show_in_catalog_filters_title'];
                $temp['show_filter'] = $question['show_in_catalog_filters'];
                $temp['answers'] = $this->get_answers_list($question['components']);

                $data_filters[] = $temp;
            }
        }

        return $data_filters;

//		echo '<pre>';
//		var_dump( $data_filters );
//		echo '</pre>';
//		echo 'Время выполнения скрипта: ' . round( microtime( true ) - $start, 4 ) . ' сек.';
//		die();
    }

    public function get_answers_list($components)
    {
        $arr = [];

        foreach ($components as $component) {
            if ($component['_type'] === 'answer_list') {
                foreach ($component['answer_list'] as $answer) {
                    if ($answer['show_in_filter']) {
                        $temp = [];
                        $temp['icon'] = $answer['icon'];
                        $temp['icon_url'] = wp_parse_url(wp_get_attachment_url($answer['icon']), PHP_URL_PATH);
                        $temp['title'] = $answer['title'];
                        $temp['slug'] = sanitize_title_with_dashes($answer['title']);

                        $arr[] = $temp;
                    }
                }
            }
        }

        return $arr;
    }

    public function set_chose_filters()
    {
        $post_data = sanitize_post($_POST, 'db');
        $filters = $post_data['filters'];
        $question_id = $_POST['questionId'];
        $questions = unserialize(get_option('filters_data_cache'));
        $ordering = empty($post_data['order']) ? 'default' : sanitize_text_field($post_data['order']);
        $is_search_page = $post_data['isSearchPage'] === 'true';


        $question = array_filter($questions, function ($item) use ($question_id) {
            return $item['question_id'] === $question_id;
        });

        $data = [];

        if (!empty($filters)) {
            foreach ($post_data['filters'] as $filter_type => $filter_answers) {
                $data = [
                    $question[array_key_last($question)]['question_id'] => [
                        $question[array_key_last($question)]['question_title'] => $filters[$question[array_key_last($question)]['type']]
                    ]
                ];
            }
        }
        // save to user_meta or session
        $this->set_selected_filters($data);

        if ($is_search_page) {
            $search_string = json_decode(stripslashes($_COOKIE['es_search_string']), true)['s'];
            $sort_cache = op_help()->sort_cache->get_sort_cache(get_option('posts_per_page'), 0, $ordering, false, 0, 'variation', '', '', ['search' => $search_string]);

        } else {
            $sort_cache = op_help()->sort_cache->get_sort_cache(get_option('posts_per_page'), 0, $ordering, false, (bool)op_help()->sf_user->check_survey_default(), 'variation');
        }

        $products = $sort_cache['ids_with_chef_score'];

        $html = $this->load_template_part('template-parts/catalog/pagination', 'products', $products);

        wp_send_json_success([
            'filteredTotal' => $sort_cache['filtered_total'],
            'total' => $sort_cache['total'],
            'html' => $html
        ]);

        wp_die();
    }

    public function load_template_part($template_name, $part_name = null, $args)
    {
        ob_start();
        get_template_part($template_name, $part_name, $args);
        $var = ob_get_contents();
        ob_end_clean();

        return $var;
    }

    public function clear_selected_filters()
    {
        $post_data = sanitize_post($_POST, 'db');
        $this->set_selected_filters([]);
	    $is_search_page = $post_data['isSearchPage'] === 'true';

        if ($is_search_page) {
            $search_string = json_decode(stripslashes($_COOKIE['es_search_string']), true)['s'];
            $sort_cache = op_help()->sort_cache->get_sort_cache(get_option('posts_per_page'), 0, 'default', false, 0, 'variation', '', '', ['search' => $search_string]);

        } else {
            $sort_cache = op_help()->sort_cache->get_sort_cache(get_option('posts_per_page'), 0, 'default', false,
                (bool)op_help()->sf_user->check_survey_default(), 'variation');
        }
        $products = $sort_cache['ids_with_chef_score'];

        $html = $this->load_template_part('template-parts/catalog/pagination', 'products', $products);

        wp_send_json_success([
            'filteredTotal' => $sort_cache['filtered_total'],
            'total' => $sort_cache['total'],
            'html' => $html
        ]);
    }

    public function set_selected_filters($data)
    {
        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), 'selected_filters', serialize($data));
        } else {
            $_SESSION['selected_filters'] = $data;
        }
    }

    public function get_selected_filters()
    {
        if (is_user_logged_in()) {
            $data = unserialize(get_user_meta(get_current_user_id(), 'selected_filters', 1));
        } else {
            $data = $_SESSION['selected_filters'];
        }

        return $data;
    }

    public function get_selected_allergens($type)
    {
        $data = $this->get_selected_filters();

        $value = [];
        foreach ($data as $question_id => $item) {
            $value = $item[$type];
        }

        return $value;
    }
}