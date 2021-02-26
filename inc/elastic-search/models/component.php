<?php

namespace ES\Models;

class Component
{
    public function get_all_components_attributes()
    {
        $pa_part_1_terms = get_terms(['taxonomy' => 'pa_part-1']);
        return $pa_part_1_terms;
    }

    public function get_single_component_attributes($component_slug)
    {
        return op_help()->single_component->get_single_component_fields($component_slug);
    }

}
