<?php


namespace SfSync;


class FileStructure {

    const HEADER =
        array(
            'NetSuite Internal ID'         => [ 'code' => '_op_variations_component_sku', 'category' => 'term' ],
            'portion size oz'              => [ 'code' => 'portion_size', 'category' => 'data_sync' ],
            'Item Name/Number'             => [ 'code' => 'title', 'category' => 'termmain' ],
            'Description'                  => [ 'code' => 'description', 'category' => 'termmain' ],
            'warehouse location'           => [ 'code' => 'warehouse_location', 'category' => 'data_sync' ],
            'Base Price'                   => [ 'code' => 'base_price', 'category' => 'data_sync' ],
            'Item Weight'                  => [ 'code' => 'weight', 'category' => 'data_sync' ],
            'Item Unit Weight'             => [ 'code' => 'weight_unit', 'category' => 'data_sync' ],
            'Servin Size Weight ()'        => [ 'code' => 'servings_size_weight', 'category' => 'data_sync' ],
            'Servings Per Container'       => [ 'code' => 'servings_per_container', 'category' => 'data_sync' ],
            'Calories'                     => [ 'code' => '_op_variations_component_calories', 'category' => 'term' ],
            'Total Fat (g)'                => [ 'code' => '_op_variations_component_fats', 'category' => 'term' ],
            'Total Carbohydrate (g)'       => [
                'code'     => '_op_variations_component_carbohydrates',
                'category' => 'term'
            ],
            'Protein (g)'                  => [ 'code' => '_op_variations_component_proteins', 'category' => 'term' ],
            'Allergens'                    => [ 'code' => 'allergens', 'category' => 'temp' ],
            'Facility Allergens'           => [ 'code' => 'facility_allergens', 'category' => 'data_sync' ],
            'Preparation Instructions'     => [
                'code'     => '_op_variations_component_instructions',
                'category' => 'term'
            ],
            'Kit Type'                     => [ 'code' => 'kit_type', 'category' => 'data_sync' ],
            'Warehouse Location type'      => [ 'code' => '_op_variations_component_store_type', 'category' => 'term' ],
            'Keep refregirated'            => [ 'code' => 'keep_refrigerated', 'category' => 'data_sync' ],
            'Suggested reorder frequency'  => [ 'code' => 'reorder_frequency', 'category' => 'data_sync' ],
            'Picture/Image 1'              => [ 'code' => 'image1', 'category' => 'temp' ],
            'Picture/Image 2'              => [ 'code' => 'image2', 'category' => 'temp' ],
            'Picture/Image 3'              => [ 'code' => 'image3', 'category' => 'temp' ],
            'MSRP Multiplier'              => [ 'code' => 'msrp_multiplier', 'category' => 'data_sync' ],
            'Display Multiplier'           => [ 'code' => 'display_multiplier', 'category' => 'data_sync' ],
            'NSDescription'                => [ 'code' => 'temp', 'category' => 'temp' ],
            'Marketing Description'        => [ 'code' => 'temp', 'category' => 'temp' ],
            'marketing description - long' => [ 'code' => 'temp', 'category' => 'temp' ],
            '_sheet_name'                  => [ 'code' => 'debug', 'category' => 'debug' ],
        );

    static function translit( $s ) {
        $s = (string) $s; // преобразуем в строковое значение
        $s = str_replace( array( "\n", "\r" ), " ", $s ); // убираем перевод каретки
        $s = preg_replace( "/\s+/", ' ', $s ); // удаляем повторяющие пробелы
        $s = trim( $s ); // убираем пробелы в начале и конце строки
        $s = function_exists( 'mb_strtolower' ) ? mb_strtolower( $s ) : strtolower( $s ); // переводим строку в нижний регистр (иногда надо задать локаль)
        $s = strtr( $s, array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'j',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shch',
            'ы' => 'y',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            'ъ' => '',
            'ь' => ''
        ) );
        $s = preg_replace( "/[^0-9a-z-_ ]/i", "", $s ); // очищаем строку от недопустимых символов
        $s = str_replace( " ", "-", $s ); // заменяем пробелы знаком минус

        return $s; // возвращаем результат
    }

    static function getHeaders() {
        static $header = null;

        if ( $header ) {
            return $header;
        }

        $header = self::HEADER;

        foreach ( $header as $key => &$value ) {
//			if ( ! isset( $value['code'] ) || ! $value['code'] ) {
//				$value['code'] = self::translit( $key );
//			}
            if ( ! isset( $value['category'] ) || ! $value['category'] ) {
                throw new \Exception( 'no category ' . $key );
            }
            $value['name'] = $key;
        }

        return $header;

    }

}