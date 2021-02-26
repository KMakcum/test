<?php
if ( is_user_logged_in() ) {
	do_action( 'make_survey_default' );
	?>
	<?php if ( op_help()->sf_user->check_survey_exist() && (op_help()->shop->is_meals_category() || $search_page) ) { ?>
		<div class="products__toggle products__toggle--mobile toggle toggle--easy" >
			<input id="products-filter-disable-survey"
			       class="js-toggle-switch js-catalog-toggle-switch visually-hidden toggle-switch__checkbox"
			       type="checkbox"
			       name="use_survey" <?php echo $use_survey ? ' checked' : ''; ?>>
			<span class="toggle__txt">Recommended only</span>
		</div>
	<?php } ?>
<?php } ?>