<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class OurStoryClass
{
    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function init()
    {
        add_action('carbon_fields_register_fields', [$this, 'set_our_story_fields']);
    }

    public function set_our_story_fields()
    {
        Container::make('post_meta', 'Custom Data')
            ->show_on_template('template-our-story.php')
            ->add_tab( 'Main', [
                Field::make( 'image', 'bg_main_os', __('Background') )->set_width( 50 ),
                Field::make( 'image', 'icon_main_os', __('Icon') )->set_width( 50 ),
                Field::make( 'text', 'title_main_os', __('Title') )->set_width( 80 ),

            ] )
            ->add_tab( 'About Us', [
                Field::make( 'rich_text', 'txt_about_os', __('Text') )->set_width( 100 ),

            ] )
            ->add_tab( 'Why We Do It?', [
                Field::make( 'image', 'bg_wwdi_os', __('Background') )->set_width( 100 ),
                Field::make( 'text', 'title_wwdi_os', __('Title') )->set_width( 100 ),
                Field::make( 'textarea', 'desc_wwdi_os', __('Description') )->set_width( 100 ),

            ] )
            ->add_tab( 'Our Difference', [
                Field::make( 'complex', 'rep_od_os', __('Blocks') )
                    ->set_collapsed( true )
                    ->setup_labels( [ 'singular_name' => '', ] )
                    ->add_fields( [
                        Field::make( "image", "bg_rep_od_os", __('Background') )->set_width( 8 ),
                        Field::make( "image", "img_rep_od_os", __('Icon') )->set_width( 8 ),
                        Field::make( "text", "txt_rep_od_os", __('Text') )->set_width( 70 ),
                        Field::make( "text", "txt_link_rep_od_os", __('Text link') )->set_width( 50 ),
                        Field::make( "text", "link_rep_od_os", __('Link') )->set_width( 50 ),
                    ] )
            ] )
            ->add_tab( 'Our Philosophy', [
                Field::make( 'image', 'bg_oph_os', __('Background') )->set_width( 100 ),
                Field::make( 'text', 'title_oph_os', __('Title') )->set_width( 100 ),
                Field::make( 'textarea', 'desc_oph_os', __('Description') )->set_width( 100 ),

            ] )
            ->add_tab( 'Our People', [
                Field::make( 'text', 'title_op_os', __('Title') ),
                Field::make( 'complex', 'rep_op_os', __('People') )
                    ->set_collapsed( true )
                    ->setup_labels( [ 'singular_name' => '', ] )
                    ->add_fields( [
                        Field::make( "image", "img_rep_op_os", __('Image') )->set_width( 8 ),
                        Field::make( "text", "name_rep_op_os", __('Full Name') )->set_width( 40 ),
                        Field::make( "text", "position_rep_op_os", __('Position') )->set_width( 40 ),
                        Field::make( "textarea", "txt_rep_op_os", __('Description') )->set_width( 100 ),
                    ] )
            ] )
            ->add_tab( 'Our Approach', [
                Field::make( 'text', 'title_oa_os', __('Title') ),
                Field::make( 'textarea', 'desc_oa_os', __('Description') ),
                Field::make( 'complex', 'rep_oa_os', __('Blocks') )
                    ->set_collapsed( true )
                    ->setup_labels( [ 'singular_name' => '', ] )
                    ->add_fields( [
                        Field::make( "image", "img_rep_oa_os", __('Image') )->set_width( 8 ),
                        Field::make( 'complex', 'rep_rep_oa_os', __('Blocks') )
                            ->set_width( 80 )
                            ->set_collapsed( true )
                            ->setup_labels( [ 'singular_name' => '', ] )
                            ->add_fields( [
                                Field::make( "rich_text", "txt_rep_rep_oa_os", __('Text') )->set_width( 100 ),

                            ] )
                    ] )
            ] )
            ->add_tab( 'Convenience', [
                Field::make( 'image', 'bg_c_os', __('Background') )->set_width( 100 ),
                Field::make( 'text', 'title_c_os', __('Title') )->set_width( 100 ),
                Field::make( 'textarea', 'desc_c_os', __('Description') )->set_width( 100 ),

            ] )

            ;
    }

    public function get_our_story_fields( $id )
    {
        return [
            'bg_main'    => carbon_get_post_meta( $id, 'bg_main_os' ),
            'icon_main'  => carbon_get_post_meta( $id, 'icon_main_os' ),
            'title_main' => carbon_get_post_meta( $id, 'title_main_os' ),
            'txt_about'  => carbon_get_post_meta( $id, 'txt_about_os' ),
            'bg_wwdi'    => carbon_get_post_meta( $id, 'bg_wwdi_os' ),
            'title_wwdi' => carbon_get_post_meta( $id, 'title_wwdi_os' ),
            'desc_wwdi'  => carbon_get_post_meta( $id, 'desc_wwdi_os' ),
            'bg_oph'     => carbon_get_post_meta( $id, 'bg_oph_os' ),
            'title_oph'  => carbon_get_post_meta( $id, 'title_oph_os' ),
            'desc_oph'   => carbon_get_post_meta( $id, 'desc_oph_os' ),
            'title_op'   => carbon_get_post_meta( $id, 'title_op_os' ),
            'rep_op'     => carbon_get_post_meta( $id, 'rep_op_os' ),
            'rep_od'     => carbon_get_post_meta( $id, 'rep_od_os' ),
            'title_oa'   => carbon_get_post_meta( $id, 'title_oa_os' ),
            'desc_oa'    => carbon_get_post_meta( $id, 'desc_oa_os' ),
            'rep_oa'     => carbon_get_post_meta( $id, 'rep_oa_os' ),
            'bg_c'       => carbon_get_post_meta( $id, 'bg_c_os' ),
            'title_c'    => carbon_get_post_meta( $id, 'title_c_os' ),
            'desc_c'     => carbon_get_post_meta( $id, 'desc_c_os' ),
        ];
    }
}
