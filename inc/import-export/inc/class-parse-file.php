<?php


namespace SfSync;


class ParseFile {
    static function parse( $filename ) {

        $data = self::parseFile( $filename );

        return $data;

        $result = ParseFile::checkKeys( $data );

        if ( count( $result['unknown_columns'] ) ) {

            ob_start();
            var_export( $result );
            $result = ob_get_clean();
            echo "<h2>Есть ошибки в структуре файла</h2><pre>";
            echo htmlspecialchars( $result );
            echo '</pre>';
            exit;
        }

        $data = self::replaceKeys( $data );

//		$data = self::groupData( $data );

//		echo '<pre>';
//		var_dump( $data );
//		echo '</pre>';
//		die();

        return $data;

    }

    static function getHeaders() {
        return FileStructure::getHeaders();
    }


    static function replaceKeys( $data ) {
        $result = [];
        $header = self::getHeaders();

        foreach ( $data as $row ) {
            $add = [];
            foreach ( $row as $key => $value ) {
                if ( ! $key ) {
                    continue;
                }
                if ( ! isset( $header[ $key ] ) ) {
                    throw new \Exception( 'Unknown column "' . $key . '"' );
                }
                $value = trim( $value );
                if ( $value ) {
                    $add[ $header[ $key ]['category'] ][] = array_merge( $header[ $key ], [ 'value' => $value ] );
                }
            }
            $result[] = $add;
        }

        return $result;
    }

    static function checkKeys( $data ) {
        $result = [];
        $header = self::getHeaders();

        $unknown_columns = [];
        $all_columns     = [];
        $does_not_exist  = [];

        foreach ( $data as $row ) {
            $add = [];
            foreach ( $row as $key => $value ) {
                $all_columns[ $key ] = true;
                if ( ! $key ) {
                    continue;
                }
                if ( ! isset( $header[ $key ] ) ) {
                    $unknown_columns[ $key ] = true;
                    continue;
                }

                $add[ $key ] = array_merge( $header[ $key ], [ 'value' => trim( $value ) ] );
            }
            $result[] = $add;
        }

        foreach ( $header as $key => $params ) {
            if ( ! isset( $all_columns[ $key ] ) ) {
                $does_not_exist[ $key ] = true;
            }
        }

        return [
            //'result' => $result,
            'unknown_columns' => array_keys( $unknown_columns ),
            'does_not_exist'  => array_keys( $does_not_exist ),
            'all_columns'     => array_keys( $all_columns ),
        ];
    }

//	static function groupData( $data ) {
//		$result = [];
//		foreach ( $data as $row ) {
//
//
//
//			$hash = md5( $row['category']['value'] . ( $row['subcategory']['value'] ?? '_' ) . $row['name']['value'] . ( $row['description']['value'] ?? '' ) );
//			if ( ! isset( $result[ $hash ] ) ) {
//				$result[ $hash ] = [];
//			}
//			$result[ $hash ][] = $row;
//		}
//
//		$products = [];
//		$header   = self::getHeaders();
//		foreach ( $result as $prod ) {
//
//			$add = [];
//			foreach ( $header as $head ) {
//				if ( ! isset( $prod[0][ $head['code'] ] ) ) {
//					continue;
//				}
//				// Параметры, относящиеся к продукту берём только один раз из всех вариаций
//				if ( in_array( $head['category'], [ 'product', 'category' ] ) ) {
//					$add[ $head['code'] ] = $prod[0][ $head['code'] ];
//				}
//			}
//			$add['attribs'] = [];
//			$add['variate'] = [];
//			$add['photo']   = false;
//
//			foreach ( $prod as $variate ) {
//
//				if ( ! isset( $variate['fasovka']['value'] ) ) {
//					$variate['fasovka'] = [
//						'code'     => 'fasovka',
//						'category' => 'variate',
//						'type'     => 'join_array',
//						'value'    => '-'
//					];
//					//throw new \Exception('no fasovka');
//				}
//
//				$fasovka = explode( ', ', $variate['fasovka']['value'] );
//				if ( ! isset( $add['attribs']['fasovka'] ) ) {
//					$add['attribs']['fasovka'] = array_merge( $variate['fasovka'], [ 'value' => $fasovka ] );
//				} else {
//					$add['attribs']['fasovka']['value'] = array_merge( $add['attribs']['fasovka']['value'], $fasovka );
//				}
//
//				foreach ( $fasovka as $fas ) {
//					$var = [ 'fasovka' => array_merge( $variate['fasovka'], [ 'value' => $fas ] ) ];
//
//					foreach ( $header as $head ) {
//						if ( ! isset( $variate[ $head['code'] ] ) || $head['code'] == 'fasovka' ) {
//							continue;
//						}
//						if ( ! in_array( $head['category'], [
//								'product',
//								'category'
//							] ) && ( ! isset( $head['type'] ) || $head['type'] !== 'image_name' ) ) {
//
//							if ( ! isset( $var[ $head['code'] ] ) ) {
//								$var[ $head['code'] ] = $variate[ $head['code'] ];
//							} else {
//								var_dump( $header );
//								var_dump( $var );
//								throw new \Exception( 'repeat variate attribute ' . $head['code'] );
//							}
//
//							if ( $head['category'] == 'variate_attribute' ) {
//								if ( ! isset( $add['attribs'][ $head['code'] ] ) ) {
//									$add['attribs'][ $head['code'] ]          = $variate[ $head['code'] ];
//									$add['attribs'][ $head['code'] ]['value'] = [ $add['attribs'][ $head['code'] ]['value'] ];
//								} else {
//									$add['attribs'][ $head['code'] ]['value'][] = $variate[ $head['code'] ]['value'];
//								}
//							}
//
//						}
//					}
//
//					//Выбираем фотографию вариации по её объёму
//					$photo = false;
//					$fas   = trim( $fas );
//					if ( isset( FileStructure::PHOTO_FIELD[ $fas ] ) && isset( $variate[ FileStructure::PHOTO_FIELD[ $fas ] ]['value'] ) ) {
//						$p = $variate[ FileStructure::PHOTO_FIELD[ $fas ] ]['value'];
//						if ( $p && $p != '/' ) {
//							$photo = $p;
//						}
//					} // Если нет отдельного поля с фото объёма, ищем в поле по умолчанию
//					elseif ( isset( FileStructure::PHOTO_FIELD['*'] ) && isset( $variate[ FileStructure::PHOTO_FIELD['*'] ]['value'] ) ) {
//						$p = $variate[ FileStructure::PHOTO_FIELD['*'] ]['value'];
//						if ( $p && $p != '/' ) {
//							$photo = $p;
//						}
//					}
//
//					$var['photo'] = $photo;
//
//					// Для главного фото берём первое попавшееся
//					if ( ! $add['photo'] ) {
//						$add['photo'] = $photo;
//					}
//
//					$add['variate'][] = $var;
//
//				}
//
//			}
//
//			foreach ( $add['attribs'] as &$elem ) {
//				$elem['value'] = array_unique( $elem['value'] );
//			}
//
//			// Если нет ни одного фото в вариациях, ищем любое доступное
//			if ( ! $add['photo'] ) {
//				foreach ( $header as $head ) {
//					if ( ! isset( $head['type'] ) || $head['type'] !== 'image_name' ) {
//						continue;
//					}
//					foreach ( $prod as $variate ) {
//						if ( isset( $variate[ $head['code'] ] ) ) {
//							$image = $variate[ $head['code'] ]['value'];
//							if ( $image && $image != '/' ) {
//								$add['photo'] = $image;
//								break 2;
//							}
//
//						}
//					}
//
//				}
//
//			}
//
//
//			$products[] = $add;
//		}
//
//		return array_values( $products );
//
//	}

    static function parseFile( $filename ) {
//		require_once SF_IMPORT_PATH . "/vendor/autoload.php";

        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify( $filename );
        $reader        = \PhpOffice\PhpSpreadsheet\IOFactory::createReader( $inputFileType );
        $spreadsheet   = $reader->load( $filename );
        $sheets        = $spreadsheet->getAllSheets();
        $sheet_names   = $spreadsheet->getSheetNames();

        $unique_header  = [];
        $data           = [];
        $all_duplicates = [];

        foreach ( $sheets as $index => $sheet ) {
            $schdeules = $sheet->toArray();
            $header    = [];
            foreach ( $schdeules as $key => $row ) {
                if ( ! trim( $row[0] ) ) {
                    continue;
                }
                if ( $key == 0 ) {
                    $header = $row;
                    foreach ( $header as &$h ) {
                        $h = trim( $h );
                    }

                    $duplicates = array_keys( array_filter( array_count_values( array_filter( $header, function ( $v ) {
                        return $v;
                    } ) ), function ( $v ) {
                        return $v > 1;
                    } ) );

                    if ( count( $duplicates ) ) {
                        $all_duplicates = array_merge( $all_duplicates, $duplicates );
                    }

                    $unique_header = array_unique( array_merge( $unique_header, $header ) );
                } else {
                    $add                = array_combine( $header, $row );
                    $add['_sheet_name'] = $sheet_names[ $index ];
                    $data[]             = $add;
                }
            }
        }

        if ( count( $all_duplicates ) ) {
            var_dump( $all_duplicates );
            throw new \Exception( 'Find duplicate columns ' . json_encode( $all_duplicates ) );
        }

        return $data;
    }
}