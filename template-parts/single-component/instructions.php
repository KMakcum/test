<?php
$cooking_steps = $args['cooking_steps'];
$cooking_steps_header = $args['cooking_steps_header'];
$cooking_steps_instructions = $args['cooking_steps_instructions'];
?>
<section class="product-card__section instruction">
    <div class="instruction__head">
        <h2 class="instruction__title"><?php echo __('Heating instructions') ?></h2>
        <ul class="instruction__meal-overview meal-overview">
            <li class="meal-overview__item">
                <svg class="meal-overview__icon" width="16" height="16" fill="#252728">
                    <use href="#icon-microvawe"></use>
                </svg>
                <?php echo __('Microwave') ?>
            </li>
            <li class="meal-overview__item">
                <svg class="meal-overview__icon" width="16" height="16" fill="#252728">
                    <use href="#icon-clock"></use>
                </svg>
                <?php $i = 1;
                foreach ($cooking_steps as $cooking_step): ?>
                    <span><?php echo $i . __(' tray') . ' – ' . __($cooking_step['op_variations_step_text']); ?></span>
                    <?php $i++;
                endforeach; ?>
            </li>
        </ul><!-- / .meal-overview -->
    </div>
    <div class="instruction__body content">
        <p>
            <?php echo __($cooking_steps_header); ?>
        </p>
        <ol class="instruction__numbered-list numbered-list">
            <?php $i = 1;
            foreach ($cooking_steps_instructions as $cooking_steps_instruction): ?>
                <li class="numbered-list__item">
                    <span class="numbered-list__number"><?php echo $i; ?></span>
                    <div class="numbered-list__txt content">
                        <p>
                            <?php echo $cooking_steps_instruction ?>
                        </p>
                    </div>
                </li>
                <?php $i++;
            endforeach; ?>
        </ol><!-- / .numeric-list -->
    </div>
</section><!-- / .instruction -->
