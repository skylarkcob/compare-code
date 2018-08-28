/**
 * Last update: 23/01/2016
 */

jQuery(document).ready(function ($) {
    (function () {
        $('.hocwp .media-download .download-link:not(.more-link)').on('click', function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: hocwp.ajax_url,
                cache: true,
                data: {
                    action: 'hocwp_font_demo_download_count',
                    post_id: $(this).attr('data-id')
                }
            });
        });
    })();

    (function () {
        var $font_size = $('#fontSize');
        if ($font_size.length) {
            var $font_demos = $('.hocwp .media-content .font-demos'),
                $list_fonts = $font_demos.find('.list-fonts');
            $font_size.slider({
                min: 30,
                max: 100,
                value: 50,
                slide: function (event, ui) {
                    $list_fonts.find('.font-display').css({fontSize: ui.value + 'px'});
                }
            });
            $('.font-tester .set-text-preview').on('input', function () {
                $list_fonts.find('.font-display').html($(this).val());
            });
            $('.hocwp .media-content .font-demos .toolbar-btn-group .set-text-transform').on('click', function (e) {
                e.preventDefault();
                var $element = $(this),
                    text_transform = 'none';
                if ($element.hasClass('set-capitalize')) {
                    text_transform = 'capitalize';
                } else if ($element.hasClass('set-uppercase')) {
                    text_transform = 'uppercase';
                } else if ($element.hasClass('set-lowercase')) {
                    text_transform = 'lowercase';
                }
                $list_fonts.find('.font-display').css({textTransform: text_transform});
            });
        }
    })();
});