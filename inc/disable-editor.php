<?php
/**
 * Disable Editor
 */

/**
 * Templates and Page IDs without editor
 *
 */
function op_disable_editor( $id = false ) { 

	$excluded_templates = array(
		'front-page.php',
		'template-offerings.php',
		'template-our-story.php',
	);

	$excluded_ids = array(
		// get_option( 'page_on_front' )
	);

	if ( empty( $id ) )
		return false;

	$id = intval( $id );
	$template = get_page_template_slug( $id );

	return in_array( $id, $excluded_ids ) || in_array( $template, $excluded_templates );
}

/**
 * Disable Gutenberg by template
 *
 */
function op_disable_gutenberg( $can_edit, $post_type ) {

	if ( ! ( is_admin() && ! empty( $_GET['post'] ) ) )
		return $can_edit;

	if ( op_disable_editor( $_GET['post'] ) )
		$can_edit = false;

	return $can_edit;

}
add_filter( 'gutenberg_can_edit_post_type', 'op_disable_gutenberg', 10, 2 );
add_filter( 'use_block_editor_for_post_type', 'op_disable_gutenberg', 10, 2 );

/**
 * Disable Classic Editor by template
 *
 */
function op_disable_classic_editor() {

	$screen = get_current_screen();
	if ( 'page' !== $screen->id || ! isset( $_GET['post'] ) )
		return;

	if ( op_disable_editor( $_GET['post'] ) ) {
		remove_post_type_support( 'page', 'editor' );
	}

}
add_action( 'admin_head', 'op_disable_classic_editor' );