<?php
$badges = carbon_get_theme_option('op_variations_badges');

$spicy = in_array('spicy', $args['badges']);
if (!empty($spicy)) {
    $spicy_data = array_filter($badges, function ($item) {
        return ($item['slug'] == 'spicy');
    });
    $spicy_data = $spicy_data[array_key_last($spicy_data)];
}
$same_badges = $args['badges'];
if (!empty($same_badges)) : ?>
<div class="product-details__labels product-labels">
    <ul class="product-labels__badges badges">
        <?php foreach ($badges as $badge) {
            ?>
            <?php if (in_array($badge['slug'], $same_badges)) : ?>
                <li class="badges__item" data-tippy-content="<?php echo $badge['title']; ?>">
                    <?php
                    if (get_post_mime_type($badge['icon_contains']) == 'image/svg+xml') :
                        echo file_get_contents(get_attached_file($badge['icon_contains'], 'full'));
                    elseif (get_post_mime_type($badge['icon_contains']) == 'image/png') :
                        echo '<img src="' . wp_get_attachment_image_url($badge['icon_contains'], 'full') . '" alt="icon">';
                    endif; ?>
                </li>
            <?php endif; ?>
        <?php } ?>
        <?php if (isset($spicy_data) && !empty($spicy_data)) { ?>
            <li class="badges__item" data-tippy-content="<?php echo $spicy_data['title'] ?>">
                <?php if (get_post_mime_type($spicy_data['icon_contains']) == 'image/svg+xml') :
                    echo file_get_contents(get_attached_file($spicy_data['icon_contains'], 'full'));
                elseif (get_post_mime_type($spicy_data['icon_contains']) == 'image/png') :
                    echo '<img src="' . wp_get_attachment_image_url($spicy_data['icon_contains'], 'full') . '" alt="icon">';
                endif; ?>
            </li>
        <?php } ?>
    </ul>
    <?php endif; ?>
</div>
