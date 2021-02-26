<?php

$nutrition_data =
    [
        'fats' => $args['nutrition_data']['fats'],
        'proteins' => $args['nutrition_data']['proteins'],
        'carbohydrates' => $args['nutrition_data']['carbohydrates'],
        'calories' => $args['nutrition_data']['calories'],
    ];

$nutrition_data_total = $nutrition_data['fats'] + $nutrition_data['proteins'] + $nutrition_data['carbohydrates'];
if ($nutrition_data_total > 0) {

    $nutrition_data_percentage = [
        'calories' => round($nutrition_data['calories'] / 2500, 2) * 100,
        'fats' => round($nutrition_data['fats'] / $nutrition_data_total, 2) * 100,
        'proteins' => round($nutrition_data['proteins'] / $nutrition_data_total,
                2) * 100,
        'carbohydrates' => round($nutrition_data['carbohydrates'] / $nutrition_data_total,
                2) * 100,
    ];

} else {
    $nutrition_data_percentage = [
        'calories' => round($nutrition_data['calories'] / 2500, 2),
        'fats' => 0,
        'proteins' => 0,
        'carbohydrates' => 0,
    ];
}
?>

<div class="nutrition__left">
    <h2 class="nutrition__title"><?php echo __('Nutrition facts') ?></h2>
    <ul class="nutrition__list nutrition-list">
        <li class="nutrition-list__item nutrition-item">
            <div class="nutrition-item__progress-bar progress-bar"
                 data-value-grams="<?php echo esc_attr($nutrition_data['calories']); ?>"
                 data-color="#fff">
            </div>
            <p class="nutrition-item__title"><?php echo __('Calories'); ?></p>
        </li>
        <li class="nutrition-list__item nutrition-item">
            <div class="nutrition-item__progress-bar progress-bar"
                 data-value-grams="<?php echo esc_attr($nutrition_data['carbohydrates']); ?> g"
                 data-value-percent="<?php echo esc_attr($nutrition_data_percentage['carbohydrates']); ?>"
                 data-color="#0482CC">
            </div>
            <p class="nutrition-item__title"><?php echo __('Carbs'); ?></p>
        </li>
        <li class="nutrition-list__item nutrition-item">
            <div class="nutrition-item__progress-bar progress-bar"
                 data-value-grams="<?php echo esc_attr($nutrition_data['fats']); ?> g"
                 data-value-percent="<?php echo esc_attr($nutrition_data_percentage['fats']); ?>"
                 data-color="#F2AE04">
            </div>
            <p class="nutrition-item__title"><?php echo __('Fat'); ?></p>
        </li>
        <li class="nutrition-list__item nutrition-item">
            <div class="nutrition-item__progress-bar progress-bar"
                 data-value-grams="<?php echo esc_attr($nutrition_data['proteins']); ?> g"
                 data-value-percent="<?php echo esc_attr($nutrition_data_percentage['proteins']); ?>"
                 data-color="#34A34F">
            </div>
            <p class="nutrition-item__title"><?php echo __('Protein') ?></p>
        </li>
    </ul>
</div>
