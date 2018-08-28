jQuery(document).ready(function ($) {
    var $body = $('body');
    (function () {
        var $fonts_demo = $('#hocwp_fonts_demo');
        $fonts_demo.on('click', '.hocwp-meta-box .add-demo', function (e) {
            e.preventDefault();
            var $element = $(this),
                $list_demos = $element.prev(),
                item = '',
                key = $list_demos.attr('data-key');
            item += '<div class="item" style="border-bottom: 1px dotted #eee">';
            item += '<div class="meta-row">';
            item += '<label class="">Name</label>';
            item += '<input class="widefat regular-text demo-name" value="" name="font_demos[' + key + '][name]" type="text">';
            item += '</div>';
            item += '<div class="meta-row">';
            item += '<label class="">Download</label>';
            item += '<div class="media-container field-group">';
            item += '<span class="media-preview"></span>';
            item += '<input autocomplete="off" class="media-url widefat regular-text demo-url" value="" name="font_demos[' + key + '][url]" type="url" style="margin-right: 10px">';
            item += '<button class="button btn-add-media btn btn-insert-media">Add media</button>';
            item += '<button class="btn button btn-remove hidden">Remove</button>';
            item += '<input class="media-id widefat regular-text demo-id" value="" name="font_demos[' + key + '][id]" type="hidden">';
            item += '</div>';
            item += '</div>';
            item += '</div>';
            $list_demos.append(item);
            $fonts_demo.find('.btn-add-media').hocwpMediaUpload();
            key++;
            $list_demos.attr('data-key', key);
        });
        $body.on('hocwp_media:selected', function (e, items, $element, options) {
            var $list_demos = $element.closest('.item');
            $list_demos.find('.demo-name').val(items.title);
        });
        $body.on('hocwp_media:removed', function (e, $element, options) {
            var $list_demos = $element.closest('.item');
            $list_demos.find('.demo-name').val('');
            $list_demos.fadeOut();
        });
    })();
});