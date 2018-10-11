jQuery(document).ready(function ($) {
    var $post_id = $('#post_ID'),
        $demo_url = $('#demo_url');
    if ($post_id.length && $demo_url.length) {
        if (!$.trim($demo_url.val())) {
            var saving = true,
                reload = false;
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: hocwp.ajax_url,
                cache: true,
                data: {
                    action: 'hocwp_font_update_demo',
                    post_id: $post_id.val()
                },
                success: function (response) {
                    saving = false;
                    if (response.success) {
                        reload = true;
                    } else {
                        reload = false;
                    }
                }
            });
            var inter = setInterval(function () {
                if (saving) {
                    window.onbeforeunload = function () {
                        if (saving) {
                            return 'Waiting...';
                        }
                        return false;
                    };
                } else {
                    clearInterval(inter);
                    window.onbeforeunload = null;
                    if (reload) {
                        window.location.reload();
                    }
                }
            }, 1000);
        }
    }
});