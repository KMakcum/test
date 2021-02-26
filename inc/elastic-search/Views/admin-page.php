<body class="text-center">
<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
    <main role="main" class="inner cover">
        <div class="container">
            <div class="row">
                <div class="col-12 justify-content-center d-flex">
                    <h4><?php echo __('Elastic Search options page') ?></h4>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-6 justify-content-center d-flex">
                    <button class="btn btn-primary"
                            type="button"
                            id="reindexES">
                        <?php echo __('To fully reindex Elastic search click me'); ?>
                    </button>
                </div>
                <div class="col-6 justify-content-center d-flex">
                    <button class="btn btn-primary"
                            type="button"
                            id="upsertES">
                        <?php echo __('To upsert Elastic search click me (currently unavailable)'); ?>
                    </button>
                </div>
                <div id="ES-notice" class="col-12  mt-5 d-none"></div>
            </div>
        </div>
    </main>
</div>
</body>
