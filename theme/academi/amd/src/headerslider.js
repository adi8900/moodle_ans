define(['jquery', 'theme_academi/slick'], function($) {
    return {
        init: function() {
            $('.header-slider').slick({
                slidesToShow: 1,
                autoplay: true,
                autoplaySpeed: 4000,
                arrows: true,
                dots: true
            });
        }
    };
});

