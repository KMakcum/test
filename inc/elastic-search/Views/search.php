<div class="main-nav__search-2 search-2">
    <form class="search-2__form search-form" id="elastic-search-form" action="#" method="get">
        <p class="search-form__field-box">
            <label class="visually-hidden" for="s"><?php echo __('Search'); ?></label>
            <?php $ss = json_decode(stripslashes($_COOKIE['es_search_string']), true)['s']; ?>
            <input class="search-form__field" id="elastic-search-input" type="search" name="s"
                   placeholder="Search LifeChef™">

            <?php if ($ss): ?>
                <script>
                    jQuery('#elastic-search-input').val('<?php echo $ss ?>')
                </script>
            <?php endif; ?>
            <button class="search-form__button control-button control-button--no-txt" type="submit">
                <svg class="control-button__icon" width="24" height="24" fill="#252728">
                    <use href="#icon-search"></use>
                </svg>
            </button><!-- / .control-button -->
            <button class="search-form__clear control-button control-button--no-txt" type="button">
                <svg class="control-button__icon" width="16" height="16" fill="#BEC1C4">
                    <use href="#icon-remove"></use>
                </svg>
            </button><!-- / .control-button -->
            <button class="search-form__close control-button control-button--no-txt" type="button">
                Cancel
                <svg class="control-button__icon" width="24" height="24" fill="#252728">
                    <use href="#icon-times"></use>
                </svg>
            </button><!-- / .control-button -->
        </p>
    </form>
    <div class="search-2__body search-body">
        <div class="search-body__start search-start search-state">
            <img class="search-state__img"
                 src="<?php echo get_template_directory_uri(); ?>/assets/img/base/search-start.svg"
                 width="80" height="80" alt="">
            <p class="search-state__txt"><?php echo __('Quickly find meals, groceries and vitamins and add them to
                your order') ?></p>
        </div><!-- / .search-start .search-state -->
    </div><!-- / .search-body -->
</div><!-- / .search -->
<button class="main-nav__search-trigger">
    <svg width="24" height="24" fill="#252728">
        <use href="#icon-search"></use>
    </svg>
</button>
<?php if ( op_help()->tutorial->tutorial_page_name() !== '' ) { ?>
    <button class="main-nav__start-tutorial control-button control-button--no-txt js-start-<?php echo op_help()->tutorial->tutorial_page_name(); ?>-tutorial remove-tutorial-status" data-tippy-content="Start Tutorial">
        <svg class="control-button__icon" width="24" height="24" fill="#252728">
            <use href="#icon-question"></use>
        </svg>
    </button>
<?php } ?>
