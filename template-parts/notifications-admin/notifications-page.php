<body class="text-center">
<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
    <main role="main" class="inner cover">
        <div class="container">
            <div class="row">
                <div class="col-12 justify-content-center d-flex">
                    <h4><?php echo __('Notifications options') ?></h4>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-12 justify-content-center d-flex">
                    <button class="btn btn-primary"
                            type="button"
                            id="resetNotificationsCron">
                        <?php echo __('Reset cron tasks'); ?>
                    </button>
                </div>
                <div id="ES-notice" class="col-12  mt-5 d-none"></div>
            </div>
        </div>
    </main>
</div>
</body>
