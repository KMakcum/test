<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class MenuClass
{
    private static $_instance = null;

    static public function getInstance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function init()
    {
        add_action('carbon_fields_register_fields', [$this, 'set_menu_fields']);
    }

    public function set_menu_fields()
    {
        Container::make('nav_menu_item', 'Menu Setting')
            ->add_fields( [
                Field::make( 'checkbox', 'show_modal', __('Show modal ZipCode') )->set_option_value( 'yes' ),

            ] );
    }
    
}
