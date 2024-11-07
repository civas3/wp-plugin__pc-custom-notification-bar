
jQuery(document).ready(function($) {
    // Check if the notification has already been shown
    if (document.cookie.indexOf('custom_nb_notification_shown=true') === -1) {
        var delayTime = parseInt(custom_nb_data.delay_time, 10) || 2000;
        var cookieDuration = parseInt(custom_nb_data.cookie_duration, 10) || 10800; // Fallback to 3 hours if undefined //  ---  3h x 60 x 60c = 10800s  

        // Show the notification after the delay
        setTimeout(function() {
            $('#custom_nb-notification-overlay').addClass('show');
        }, delayTime);
    }

    // Close the notification and set a cookie to prevent it from showing again
    $('#custom_nb-notification-close').click(function() {
        $('#custom_nb-notification-overlay').removeClass('show');
        document.cookie = "custom_nb_notification_shown=true; path=/; max-age=" + cookieDuration;
    });

    // Allow clicking outside the popup to close it (optional)
    $('#custom_nb-notification-overlay').click(function(e) {
        if (e.target.id === 'custom_nb-notification-overlay') {
            $('#custom_nb-notification-overlay').removeClass('show');
            document.cookie = "custom_nb_notification_shown=true; path=/; max-age=" + cookieDuration;
        }
    });
});
