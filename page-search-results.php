<?php
$search_string = json_decode(stripslashes($_COOKIE['es_search_string']), true)['s'];
$single_category = $_COOKIE['selector'];
$orderby = $_GET['orderby'];

//var_dump($current_user);

$sort_cache_meals = op_help()->sort_cache->get_sort_cache(
    54,
    0,
    $orderby ? $orderby : 'default',
    false,
    0,
    'variation', '', '', ['search' => $search_string]);

$sort_cache_vitamins = op_help()->sort_cache->get_sort_cache(
    54,
    0,
    $orderby ? $orderby : 'default',
    false,
    0,
    'simple',
    28, '', ['search' => $search_string]);

$sort_cache_staples = op_help()->sort_cache->get_sort_cache(
    54,
    0,
    $orderby ? $orderby : 'default',
    false,
    0,
    'simple',
    27, '', ['search' => $search_string]);

get_header();
switch ($single_category) {
    case '\"#allStaplesLink\"':
        get_template_part('template-parts/search-results/search-results-single-category-page', '', [
            'products' => $sort_cache_staples['ids_with_chef_score'],
            'totals' => $sort_cache_staples['total'],
            'search_string' => $search_string,
            'category' => 'Groceries'
        ]);
        break;
    case '\"#allVitaminsLink\"':
        get_template_part('template-parts/search-results/search-results-single-category-page', '', [
            'products' => $sort_cache_vitamins['ids_with_chef_score'],
            'totals' => $sort_cache_vitamins['total'],
            'search_string' => $search_string,
            'category' => 'Vitamins & Supplements'
        ]);
        break;
    case '\"#allMealsLink\"':
        get_template_part('template-parts/search-results/search-results-single-category-page', '', [
            'products' => $sort_cache_meals['ids_with_chef_score'],
            'totals' => $sort_cache_meals['total'],
            'search_string' => $search_string,
            'category' => 'Meals'
        ]);
        break;
    default:
        get_template_part('template-parts/search-results/search-results-landing-all-in-one', '', [
            'meals' => $sort_cache_meals['ids_with_chef_score'],
            'meals_totals' => $sort_cache_meals['total'],
            'vitamins' => $sort_cache_vitamins['ids_with_chef_score'],
            'vitamins_totals' => $sort_cache_vitamins['total'],
            'staples' => $sort_cache_staples['ids_with_chef_score'],
            'staples_totals' => $sort_cache_staples['total'],
            'search_string' => $search_string
        ]);
}
get_footer();