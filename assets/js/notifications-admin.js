jQuery(document).ready(function ($) {
    const resetNotifications = $('#resetNotificationsCron')

    resetNotifications.on('click', function (e) {
        const formData = new FormData()
        formData.append('action', 'notifications_reset_cron_handler')
        formData.append('nonce', settingsNotificationsPage.ajax_nonce)
        formData.append('data', JSON.stringify('reset_cron'))
        $.ajax({
            url: settingsNotificationsPage.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                console.log(response)
            },
            error: function (response) {
                console.log(response)
            }
        })
    })
})