require(['jquery'], function($) {
    $(function() {
        if ($.fn.popover) {
            $('[data-toggle="popover"]').popover('dispose');
        }
        if ($.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip('dispose');
        }
    });
});
