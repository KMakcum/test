<?php

class Walker_Menu_Submenu extends Walker_Nav_Menu {

	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		$class_names = join( ' ',
			apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names . '>';

		$atts           = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']   = ! empty( $item->url ) ? $item->url : '';

		////////////////////////////////////////////////////////////////////////
		if ( is_user_logged_in() ) {
			$zip_code = trim( get_user_meta( get_current_user_id(), 'sf_zipcode', true ) );
		} else {
			$zip_code = op_help()->sf_user::op_get_zip_cookie();
		}
		$zip_code   = empty( $zip_code ) ? null : $zip_code;
		$show_modal = carbon_get_nav_menu_item_meta( $item->ID, 'show_modal' );

		if ( ! $zip_code && $show_modal ) {
			$atts['class']         = ' ';
			$atts['data-redirect'] = esc_url( $item->url );
		}
		////////////////////////////////////////////////////////////////////////

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {

			if ( ! empty( $value ) ) {

				if ( strstr( $value, 'meals' ) ) {
					if ( intval( get_user_meta( get_current_user_id(), 'survey_default', true ) ) ) {
						$value = add_query_arg( 'use_survey', 'on', $value );
					}
				}

				if ( 'href' === $attr ) {
					$href  = ( ! $zip_code && $show_modal ) ? '#js-modal-zip-code' : esc_url( $value );
					$value = $href;
				} elseif ( 'class' === $attr ) {
					$value = ( ! $zip_code && $show_modal ) ? esc_attr( $value . ' btn-modal' ) : esc_attr( $value );
				} else {
					$value = esc_attr( $value );
				}

				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$item_output = $args->before;
		$item_output .= '<a' . $attributes . '>';

		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '';
		$item_output .= $args->after;
		$item_output .= '</a>';

		////////////////////////////////////////////////
		$menu_item_slug   = basename( $item->url );
		$is_zip_national  = op_help()->zip_codes->is_zip_zone_national( $zip_code );
		$is_zip_overnight = op_help()->zip_codes->get_current_user_zone() === 'overnight';

		if ( $menu_item_slug === 'meals' ) {
			if ( $is_zip_national ) {
				return false;
			} else {
				$output .= "";
			}
		} elseif ( $menu_item_slug === 'groceries' || strstr( $item->url, 'product_tag' ) ) {
			if ( $is_zip_national || $is_zip_overnight ) {
				return false;
			} else {
				$output .= "";
			}
		}
		////////////////////////////////////////////////

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul class=\"sub-menu\">\n";
	}
}
