(function ($) {
    if (!$) {
        console.error('jQuery and $ are missing');
        return;
    }

    // Play/Pause Order (subscription)
    $(function () {
        let $body = $('body');

        $body.on('click', '.manage-order-button', function (e) {
            let $this = $(this),
                this_type = $this.data('manage-order');

            if (!this_type) return;

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                dataType: 'text',
                data: {
                    action: 'sf_manage_order',
                    type: this_type,
                },
                success: function (data) {
                    window.location.reload();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log('sf_manage_order-@21: '+xhr.status);
                    console.log('sf_manage_order-@22: '+thrownError);
                }
            });
        });
    });
}($ || window.jQuery));
// end of file