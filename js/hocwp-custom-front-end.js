jQuery(document).ready(function ($) {
    var $body = $('body'),
        siteHeader = $(".site-header");

    (function () {
        $('.primary-menus ul').hocwpMobileMenu();
    })();

    (function () {
        $('#customPreviewTextColour').wpColorPicker({
            defaultColor: '#000'
        });
    })();

    (function () {
        $(".head-more").on("click", "#searchIcon", function (e) {
            e.preventDefault();
            siteHeader.toggleClass("search-active");
        });
    })();

    (function () {
        $('.hocwp-post').on('click', '.btn-download.button, .download-ajax', function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: hocwp.ajax_url,
                cache: true,
                data: {
                    action: 'hocwp_font_downloads',
                    post_id: $(this).attr('data-id')
                }
            });
        });
    })();

    (function () {
        var $font_box = $('.font-box');
        $font_box.each(function () {
            var $element = $(this),
                $demo = $element.find('.demo'),
                $font_name = $demo.find('.font-name');
            if ($font_name.height() >= $demo.height()) {

            }
            $demo.lazyload();
        });

        $('.more-font-demo .preview').lazyload();

        $('html,body').on('scroll', function () {
            $(window).resize()
        });
    })();

    (function () {
        $("#browseFontsButton").on("click", "a.categories", function (e) {
            e.preventDefault();
            var element = $(this),
                widgetsMenu = $("#widgetsMenu");
            element.toggleClass("active");

            widgetsMenu.slideToggle();
        });
    })();
});