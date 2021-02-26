<?php
$meals = $args['meals'];
$staples = $args['staples'];
$vitamins = $args['vitamins'];
$search_string = $args['search_string'];
global $wp, $sf_results_all_items, $sf_results_filtered_items;


?>
    <main class="site-main search-results-main">
        <div class="search-results-main__head head-search-results head-search-results--2">
            <div class="container">
                <div class="head-search-results__box" style="align-items: center">
                    <p class="head-search-results__title"><?php echo __('Search results for') ?>
                        <b><?php echo __('"' . $search_string . '"') ?></b></p>
                    <?php
                    $use_survey_results = op_help()->sf_user->check_survey_default();
                    wc_get_template(
                        'catalog/recommended-switch.php',
                        [
                            'use_survey' => $use_survey_results,
                            'search_page' => true
                        ]
                    );
                    ?>
                    <!--                <div class="products__filter-and-sort">-->
                    <!--                    --><?php
                    //
                    //                        wc_get_template(
                    //                            'loop/filters.php',
                    //                            [
                    //                                'use_survey' => $use_survey_results,
                    //                            ]
                    //                        );
                    //
                    op_help()->search_controller->get_sort_template();
                    ?>
                    <!--                </div>-->
                </div>
            </div>
        </div><!-- / .head-search-results -->

        <div id="survey-exists"
             class="breadcrumbs-box">
            <div class="container">
                <?php do_action('echo_kama_breadcrumbs') ?>
            </div>
        </div><!-- / .breadcrumbs-box -->

        <?php
        if ($meals) {
            get_template_part('template-parts/search-results/search-results-landing-category', '',
                ['category' => 'Meals', 'category_content' => $meals]);
        } else {
            get_template_part('template-parts/search-results/search-results-landing-category-nothing-found', '',
                ['category' => 'Meals']);
        }

        if ($staples) {
            get_template_part('template-parts/search-results/search-results-landing-category', '',
                ['category' => 'Groceries', 'category_content' => $staples]);
        } else {
            get_template_part('template-parts/search-results/search-results-landing-category-nothing-found', '',
                ['category' => 'Groceries']);
        }

        if ($vitamins) {
            get_template_part('template-parts/search-results/search-results-landing-category', '',
                ['category' => 'Vitamins & Supplements', 'category_content' => $vitamins]);
        } else {
            get_template_part('template-parts/search-results/search-results-landing-category-nothing-found', '',
                ['category' => 'Vitamins & Supplements']);
        }
        ?>

    </main><!-- / .site-main .catalog-main -->
<?php get_template_part('template-parts/meal-plan-bottom-nav');