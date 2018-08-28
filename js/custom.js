jQuery(document).ready(function ($) {
    var body = $("body");

    (function () {
        var fesImages = $("#fes-zf_fes_featured_images");

        if (fesImages.length && $.fn.sortable) {
            fesImages.sortable({
                items: "div.dz-preview"
            }).disableSelection();
        }
    })();

    (function () {
        var sliderContainer = $(".pxf-post-slider");

        if (sliderContainer.length && $.fn.slick) {
            var sliderFor = sliderContainer.find(".slider-for"),
                sliderNav = sliderContainer.find(".slider-nav");

            if (sliderFor.length) {
                if (sliderNav.length) {
                    sliderFor.slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: false,
                        fade: true,
                        asNavFor: ".pxf-post-slider .slider-nav",
                        init: function () {
                            sliderNav.show();
                        }
                    });


                    sliderNav.slick({
                        asNavFor: ".pxf-post-slider .slider-for",
                        arrows: false,
                        focusOnSelect: true,
                        init: function () {
                            sliderNav.show();
                        },
                        dots: false,
                        slidesToShow: 5,
                        slidesToScroll: 1,
                        autoplay: false,
                        infinite: true,
                        variableWidth: false,
                        centerMode: false,
                        vertical: true,
                        responsive: [
                            {
                                breakpoint: 1300,
                                settings: {
                                    vertical: false
                                }
                            }
                        ]
                    });


                    sliderNav.show();

                    sliderNav.find(".slick-slide:first-child div img").trigger("click");
                } else {
                    sliderFor.slick();
                }
            }
        }
    })();

    (function () {
        body.on("click", ".edd-remove-from-wish-list", function (e) {
            e.preventDefault();
            var element = $(this),
                collection = element.attr("data-collection"),
                post_id = element.attr("data-id"),
                listItem = element.closest("li");

            listItem.css({opacity: 4});

            $.ajax({
                type: "POST",
                dataType: "JSON",
                url: pixelify.ajaxUrl,
                cache: true,
                data: {
                    action: "hocwp_pxf_remove_collection",
                    collection: collection,
                    post_id: post_id
                },
                success: function (response) {
                    listItem.fadeOut().remove();
                }
            });
        });
    })();

    (function () {
        if (!body.hasClass("logged-in")) {
            var collectionModalButton = body.find(".collection .edd-wl-open-modal");

            collectionModalButton.attr("data-target", "");
            collectionModalButton.attr("data-toggle", "");

            collectionModalButton.on("click", function (e) {
                if (!body.hasClass("logged-in")) {
                    e.preventDefault();
                    window.location.href = pixelify.loginUrl;
                }
            });
        }

        var collectionModal = $("#edd-wl-modal");

        collectionModal.on("shown.bs.modal, shown", function () {
            collectionModal.find(".messages").hide();
            collectionModal.find("form").show();
            collectionModal.find(".edd-wl-save").removeClass("disabled").show();
            collectionModal.find(".edd-wl-success").hide();
        });

        $("input[type='radio'][name='list-options']").on("change", function (e) {
            e.preventDefault();
            var element = $(this),
                parent = element.parent(),
                form = element.closest("form");

            form.find("select, input[type='text']").hide();
            parent.find("select, input[type='text']").show();
        });

        collectionModal.on("click", ".edd-wl-save", function (e) {
            e.preventDefault();
            var element = $(this),
                existsList = collectionModal.find("#existing-list"),
                newList = collectionModal.find("#new-list"),
                option = collectionModal.find("input[type='radio'][name='list-options']"),
                listOption = option.val(),
                post_id = element.attr("data-id");

            if (existsList.is(":checked")) {
                listOption = existsList.val();
            } else {
                listOption = newList.val();
            }

            collectionModal.find(".edd-wl-success").hide();

            if ("existing-list" == listOption) {
                var userLists = collectionModal.find("select[name='user-lists']"),
                    userList = userLists.val();

                if (!$.isNumeric(userList)) {
                    userLists.focus();
                } else {
                    element.addClass("disabled");
                    $.ajax({
                        type: "POST",
                        dataType: "JSON",
                        url: pixelify.ajaxUrl,
                        cache: true,
                        data: {
                            action: "hocwp_pxf_add_collection",
                            list: userList,
                            post_id: post_id,
                            list_option: listOption
                        },
                        success: function (response) {
                            element.removeClass("disabled");

                            if (response.success) {
                                collectionModal.find("form").hide();
                                collectionModal.find(".messages").html(response.data.html).show();
                                element.hide();
                                collectionModal.find(".edd-wl-success").show();
                            }
                        }
                    });
                }
            } else if ("new-list" == listOption) {
                var listName = collectionModal.find("#list-name"),
                    postTitle = listName.val(),
                    listStatus = collectionModal.find("#list-status");

                if (!$.trim(postTitle)) {
                    listName.focus();
                } else {
                    element.addClass("disabled");
                    $.ajax({
                        type: "POST",
                        dataType: "JSON",
                        url: pixelify.ajaxUrl,
                        cache: true,
                        data: {
                            action: "hocwp_pxf_add_collection",
                            list: '',
                            post_id: post_id,
                            list_option: listOption,
                            post_title: postTitle,
                            post_status: listStatus.val()
                        },
                        success: function (response) {
                            element.removeClass("disabled");

                            if (response.success) {
                                collectionModal.find("form").hide();
                                collectionModal.find(".messages").html(response.data.html).show();
                                element.hide();
                                collectionModal.find(".edd-wl-success").show();
                            }
                        }
                    });
                }
            }
        });
    })();

    (function () {
        body.on("click", ".follow-button.follow", function (e) {
            e.preventDefault();

            if (!body.hasClass("logged-in")) {
                window.location.href = pixelify.loginUrl;
            } else {
                var element = $(this),
                    status = 0;

                if (element.hasClass("following")) {
                    status = 1;
                }

                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: pixelify.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_pxf_follow_author",
                        status: status,
                        author_id: element.attr("data-author-id")
                    },
                    success: function (response) {
                        if (0 == status) {
                            element.addClass("following");
                            element.html(element.attr("data-following"));
                        } else {
                            element.removeClass("following");
                            element.html(element.attr("data-follow"));
                        }

                        if (element.hasClass("remove")) {
                            element.closest("li").fadeOut().remove();
                        }
                    }
                });
            }
        });
    })();

    (function () {
        $(".like-wishlist .count-box").on("click", function (e) {
            e.preventDefault();
            var element = $(this),
                input = element.prev(),
                status = (input.is(":checked")) ? 1 : 0;

            if (!element.hasClass("disabled")) {
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: pixelify.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_pxf_like_post",
                        status: status,
                        post_id: input.attr("data-id")
                    },
                    success: function (response) {
                        element.addClass("disabled");

                        if (response.success) {
                            element.find("strong").html(response.data.formatted_number);
                        }
                    }
                });
            }
        });
    })();

    (function () {
        body.on("click", "button[name='edd_free_download_submit']", function (e) {
            e.preventDefault();
            var element = $(this),
                email = body.find("input[name='edd_free_download_email']"),
                emailAddress = email.val();

            if (!$.trim(emailAddress)) {
                email.focus();
            } else {
                var span = element.find("span");
                element.attr("data-text", span.text());
                span.html(element.attr("data-waiting"));
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: pixelify.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_pxf_download_file",
                        email: emailAddress,
                        post_id: element.attr("data-id")
                    },
                    success: function (response) {
                        span.html(element.attr("data-text"));

                        if (response.success) {
                            $("#edd-free-downloads-modal").modal("toggle");
                            window.open(response.data.file, "_blank");
                        }

                        if (response.data && response.data.message && trim(response.data.message)) {
                            alert(response.data.message);
                        }
                    }
                });
            }
        });

        $(".edd_free_downloads_form_class > .edd-free-download-single, .media-download > .down-link").on("click", function (e) {
            var element = $(this);

            $.ajax({
                type: "POST",
                dataType: "JSON",
                url: pixelify.ajaxUrl,
                cache: true,
                data: {
                    action: "hocwp_pxf_download_file",
                    post_id: element.attr("data-download-id")
                },
                success: function (response) {

                }
            });
        });
    })();
});