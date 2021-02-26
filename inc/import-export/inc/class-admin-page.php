<?php

namespace SfSync;

class AdminPage {
    const slug = 'sf_sync';

    static function init() {
        add_action( 'sf_import_event', [ self::class, 'cron_import_single' ], 10, 3 );
        add_action( 'sf_export_event', [ self::class, 'cron_export_single' ], 10, 2 );

        add_action( 'admin_menu', array( self::class, 'registerMyPage' ) );
        add_filter( 'option_page_capability_' . self::slug, function () {
            return 'edit_others_posts';
        } );
    }

    static function registerMyPage() {
        add_menu_page(
            'Data Sync (import)',
            'Data Sync (import)',
            'edit_others_posts',
            self::slug,
            array( self::class, 'getPage' ),
            'dashicons-media-spreadsheet',
            58
        );
    }

    static function import( $file_name = false, $user_id = false ) {
        if ( ! $file_name ) {
            $file_name = $_FILES['file']['tmp_name'];
        }
        if ( file_exists( $file_name ) ) {
            ImportData::importFromFile( $file_name, 'components', $user_id );
        } else {
            self::form( [ 'error' => '<div style="color: red">Не выбран файл для импорта</div>' ] );
        }
    }

    static function import_meals( $file_name = false, $user_id = false ) {
        if ( ! $file_name ) {
            $file_name = $_FILES['file']['tmp_name'];
        }
        if ( file_exists( $file_name ) ) {
            ImportData::importFromFile( $file_name, 'meals', $user_id );
        } else {
            self::form( [ 'error' => '<div style="color: red">Не выбран файл для импорта</div>' ] );
        }
    }

    static function import_staples( $file_name = false, $user_id = false ) {
        if ( ! $file_name ) {
            $file_name = $_FILES['file']['tmp_name'];
        }
        if ( file_exists( $file_name ) ) {
            ImportData::importFromFile( $file_name, 'staples', $user_id );
        } else {
            self::form( [ 'error' => '<div style="color: red">Не выбран файл для импорта</div>' ] );
        }
    }

    static function import_vitamins() {
        if ( isset( $_FILES['file']['tmp_name'] ) && file_exists( $_FILES['file']['tmp_name'] ) ) {
            ImportData::importFromFile( $_FILES['file']['tmp_name'], 'vitamins' );
        } else {
            self::form( [ 'error' => '<div style="color: red">Не выбран файл для импорта</div>' ] );
        }
    }

    /**
     * Export Meals.
     *
     * @param string $target
     * @param string $mode
     */
    static function export( $target = 'php://output', $mode = 'export_meals' ) {
        ExportCSV::exportMeals( $target, $mode );
//		exit();
    }

    static function exportComponents( $target = 'php://output', $mode = 'export_components' ) {
        ExportCSV::exportComponents( $target, $mode );
//		exit();
    }

    static function exportStaples( $target = 'php://output', $mode = 'export_straples' ) {
        ExportCSV::exportStraples( $target, $mode );
//		exit();
    }

    static function checkHeader() {
        if ( isset( $_FILES['file']['tmp_name'] ) && file_exists( $_FILES['file']['tmp_name'] ) ) {
            ImportData::checkHeader( $_FILES['file']['tmp_name'] );
        }
    }

    static function form( $data = [] ) {
        $date = date( 'Y-m-d H:i:s' );
        extract( $data );

        require SF_IMPORT_PATH . "/view/admin-page-form.php";
    }

    static public function cron_import_single( $file, $mode, $user_id ) {
        \TB::start( 'import' );
        \TB::m( "*start* cron `{$mode}`." );
        switch ( $mode ) {
            case 'import':
            case 'import-components':
                self::import( $file, $user_id );
                break;
            case 'import-meals':
                self::import_meals( $file, $user_id );
                break;
            case 'import-staples':
                self::import_staples( $file, $user_id );
                break;
        }
    }

    static public function cron_export_single( $mode, $user_id ) {
        \TB::start( 'export' );
        \TB::m( "*start* cron `{$mode}`." );

        $type        = '';
        $export_type = '';

        switch ( $mode ) {
            case 'regenerate-export-components':
                $type        = 'components';
                $export_type = 'export_components';
                break;
            case 'regenerate-export-meals':
                $type        = 'meals';
                $export_type = 'export_meals';
                break;
            case 'regenerate-export-staples':
                $type        = 'staples';
                $export_type = 'export_staples';
                break;
        }

        if ( $type ) {
            ImportData::export_by_type( $type, $export_type, $user_id );
        }
    }

    static function getPage( $mode = '' ) {
        if ( ! $mode ) {
            $mode = $_REQUEST['mode'] ?? false;
        }

        $file_name = '';
        switch ( $mode ) {
            case 'import':
            case 'import-components':
            case 'import-meals':
            case 'import-staples':
                if ( isset( $_FILES['file']['tmp_name'] ) AND $_FILES['file']['tmp_name'] ) {
                    echo 'import in progress..';

                    $path = SF_IMPORT_PATH . "/file";

                    if ( ! file_exists( $path ) ) {
                        mkdir( $path, 0777, true );
                    }

                    $destination = "$path/" . time();
                    move_uploaded_file( $_FILES['file']['tmp_name'], $destination );

//                    switch ( $mode ) {
//                        case 'import':
//                        case 'import-components':
//                            self::import( $destination, get_current_user_id() );
//                            break;
//                        case 'import-meals':
//                            self::import_meals( $destination, get_current_user_id() );
//                            break;
//                        case 'import-staples':
//                            self::import_staples( $destination, get_current_user_id() );
//                            break;
//                    }

                    if ( ! wp_next_scheduled( 'sf_import_event' ) ) {
                        wp_schedule_single_event( time(), 'sf_import_event', [
                            $destination,
                            $mode,
                            get_current_user_id()
                        ] );
                        \TB::m( "`import`: cron event *$mode* has been created." );
                    }
                }
                break;
            case 'regenerate-export-components':
            case 'regenerate-export-meals':
            case 'regenerate-export-staples':
                echo 'export in progress..';

                if ( ! wp_next_scheduled( 'sf_export_event' ) ) {
                    wp_schedule_single_event( time(), 'sf_export_event', [
                        $mode,
                        get_current_user_id()
                    ] );
                    \TB::m( "`export`: cron event *$mode* has been created." );
                }
                break;
//			case 'import-vitamins':
//				self::import_vitamins();
//				break;
            case 'export':
            case 'export_meals':
                $file_name = self::getExportFileName( 'export_meals' );
                self::export( $file_name, 'export_meals' );
                break;
            case 'export_components':
                $file_name = self::getExportFileName( $mode );
                self::exportComponents( $file_name, $mode );
                break;
            case 'export_straples':
            case 'export_staples':
                $file_name = self::getExportFileName( $mode );
                self::exportStaples( $file_name, $mode );
                break;
            case 'checkHeader':
                self::checkHeader();
                break;
            default:
                self::form();
        }

        return $file_name;
    }

    static function getExportFileName( $type ) {
        $folder = wp_upload_dir()['basedir'] . "/sf-export";
        if ( ! is_dir( $folder ) ) {
            mkdir( $folder, 0777, true );
        }

        return $folder . "/$type-v-" . \SFImport::$ver . "-" . date( 'Y-m-d_H-i-s' ) . ".csv";
    }

    static function getExportFileLink( $type ) {
        $file = get_option( "sf_{$type}" );
        $file = substr( $file, strpos( $file, '/wp-content' ) );

        return $file;
    }
}