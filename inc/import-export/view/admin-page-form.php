<?php
global $wpdb;

$import_history = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}sf_import` ORDER BY `id` DESC LIMIT 32 " );
function sf_is_in_progress( $import_history ) {
    foreach ( $import_history as $item ) {
        if ( $item->status == 'in-progress' ) {
            return true;
        }
    }

    return false;
}

?>
<div class="wrap">
    <div id="import_form">
        <h1>Data Sync</h1>
        <h2 class="sf-h2"><span>Load CSV file</span> #<?php echo SFImport::$ver; ?></h2>

        <?php
        if ( ! sf_is_in_progress( $import_history ) ) {
            ?>
            <table class="sf-main-table">
                <tr>
                    <td>
                        <form method="post" action="/wp-admin/admin.php?page=sf_sync&mode=import-components"
                              enctype="multipart/form-data"
                              id="form_import">
                            <?
                            echo isset( $error ) ? $error : ''
                            ?>
                            <h3><label>Components</label></h3>
                            <input type="file" name="file" required>
                            <input type="button" id="start_import" class="button button-primary"
                                   value="Load file">
                        </form>
                    </td>
                    <td>
                        <a class="button button-secondary" style="display: block; margin-top: 55px"
                           href="<?php echo "/wp-admin/admin.php?page=sf_sync&mode=regenerate-export-components"; ?>">
                            <b>Regenerate</b> Export Components
                        </a>
                    </td>
                    <td>
                        <a class="" style="display: block; margin-top: 55px"
                           href="<?php echo \SfSync\AdminPage::getExportFileLink( 'export_components' ); ?>"
                           title="<?php echo \SfSync\AdminPage::getExportFileLink( 'export_components' ); ?>">
                            Download Export Components
                        </a>
                    </td>
                </tr>


                <tr>
                    <td>
                        <form method="post" action="/wp-admin/admin.php?page=sf_sync&mode=import-meals"
                              enctype="multipart/form-data"
                              id="meals_import">
                            <?
                            echo isset( $error ) ? $error : ''
                            ?>
                            <h3><label>Meals</label></h3>
                            <input type="file" name="file" required>
                            <input type="button" id="start_meals" class="button button-primary"
                                   value="Load file">

                        </form>
                    </td>
                    <td>
                        <a class="button button-secondary" style="display: block; margin-top: 55px"
                           href="<?php echo "/wp-admin/admin.php?page=sf_sync&mode=regenerate-export-meals"; ?>">
                            <b>Regenerate</b> Export Meals
                        </a>
                    </td>
                    <td>
                        <a class="" style="display: block; margin-top: 55px"
                           href="<?php echo \SfSync\AdminPage::getExportFileLink( 'export_meals' ); ?>">
                            Download Export Meals
                        </a>
                    </td>
                </tr>

                <tr>
                    <td>
                        <form method="post" action="/wp-admin/admin.php?page=sf_sync&mode=import-staples"
                              enctype="multipart/form-data"
                              id="staples_import">
                            <?
                            echo isset( $error ) ? $error : ''
                            ?>
                            <h3><label>Staples</label></h3>
                            <input type="file" name="file" required>
                            <input type="button" id="start_staples" class="button button-primary"
                                   value="Load file">
                        </form>
                    </td>
                    <td>
                        <a class="button button-secondary" style="display: block; margin-top: 55px"
                           href="<?php echo "/wp-admin/admin.php?page=sf_sync&mode=regenerate-export-staples"; ?>">
                            <b>Regenerate</b> Export Staples
                        </a>
                    </td>
                    <td>
                        <a class="" style="display: block; margin-top: 55px"
                           href="<?php echo \SfSync\AdminPage::getExportFileLink( 'export_staples' ); ?>">
                            Download Export Straples
                        </a>
                    </td>
                </tr>
            </table>
            <div class="warning-sync-version"
                 style="border: 2px solid #ea9601;padding: 0 1em 1.2em;margin: 5em 0 0;display: inline-block;">
                <h3>sync_version field</h3>
                Used for product import migrations. If you do not know what to do with this field, do not change it.
            </div>
            <div class="warning-sync-version"
                 style="border: 2px solid #ea9601;padding: 0 1em 1.2em;margin: 5em 0 0;display: inline-block;">
                <h3>About import</h3>
                The import process is added to cron. You may not immediately see the process in the table. Reload the
                page. The task cron queue can take about a minute.
            </div>
            <?php
        } else {
            ?>
            <h3 style="color: #ca4a1f;">Process in progress</h3>
            <?php
        }
        ?>

        <?php
        if ( isset( $_GET['regenerate_nsid'] ) ) {
            ?>
            <h3>Regenerate Component NSID</h3>
            <a href="/wp-admin/admin.php?page=sf_sync&regenerate_nsid=start">Regenerate Component NSID to NEW</a>
            <?php
        }
        ?>

        <h3>History</h3>
        <div class="result-table" style="">
            <table class="result-table__table">
                <tr>
                    <th>id</th>
                    <th>user_id</th>
                    <th>date</th>
                    <th>type</th>
                    <th>execution_time</th>
                    <th>status</th>
                    <th>return</th>
                </tr>
                <?php
                foreach ( $import_history as $result ) {
                    echo "<tr class='status--{$result->status}'>";
                    echo "<td>{$result->id}</td><td>{$result->user_id}</td>";
                    echo "<td>{$result->date}</td><td>{$result->type}</td>";
                    echo "<td>{$result->execution_time} " . ( ( $result->execution_time ) ? "sec." : '' ) . "</td><td><b>{$result->status}</b></td>";
                    echo "<td><div class='result-table__return'>{$result->return}</div></td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
        <style>
            .sf-h2 {
                font-weight: normal;
            }
            .sf-h2 span {
                font-weight: bold;
            }
            .result-table__return p, .result-table__return h4 {
                margin: 0;
            }
            .result-table__table {
                border: 1px solid #999;
                width: 100%;
            }
            .result-table__table td {
                border: 1px solid #999;
            }
            .result-table__return {
                min-height: 45px;
            }
            .sf-main-table td {
                padding-right: 12px;
            }
            .status--in-progress {
                background: #ca4a1f21;
            }
        </style>
    </div>
    <div style="display: none" id="process_text"></div>

    <script>
        jQuery(function (e) {
            jQuery('#start_import').click(function (e) {
                if (jQuery('#form_import')[0].checkValidity()) {
                    e.preventDefault();
                    jQuery('#import_form').hide().next().show().text('Импорт начался, процесс может занять более 5 минут. Не перезагружайте страницу');
                    setTimeout(function () {
                        jQuery('#form_import').submit();
                    }, 100)
                    return true;
                } else {
                    alert('Выберите файл')
                }
            });
            jQuery('#start_meals').click(function (e) {
                if (jQuery('#meals_import')[0].checkValidity()) {
                    e.preventDefault();
                    jQuery('#meals_import').hide().next().show().text('Импорт начался, процесс может занять более 5 минут. Не перезагружайте страницу');
                    setTimeout(function () {
                        jQuery('#meals_import').submit();
                    }, 100)
                    return true;
                } else {
                    alert('Выберите файл')
                }
            });
            jQuery('#start_staples').click(function (e) {
                if (jQuery('#staples_import')[0].checkValidity()) {
                    e.preventDefault();
                    jQuery('#staples_import').hide().next().show().text('Импорт начался, процесс может занять более 5 минут. Не перезагружайте страницу');
                    setTimeout(function () {
                        jQuery('#staples_import').submit();
                    }, 100)
                    return true;
                } else {
                    alert('Выберите файл')
                }
            });

            jQuery('#start_export').click(function (e) {
                jQuery('#import_form').hide().next().show().text('Экспорт начался, скоро начнётся скачивание файла.');
            })
        })
    </script>


    <form method="post" action="/wp-admin/admin.php?page=sf_sync&mode=checkHeader" enctype="multipart/form-data"
          style="display: none;">
        <h2>Проверка заголовков</h2>
        <input type="file" name="file">

        <input type="submit" class="button button-primary" value="Проверить заголовки">

    </form>

</div>