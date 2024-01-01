const simple_ecommerce = {
    init: function () {
        simple_ecommerce.full_sync();
        simple_ecommerce.ajax_form();
    },

    full_sync: function () {
        const $ = jQuery.noConflict();
        let sync = $("._simple_ecommerce_list_full_synchronization_start");
        if (sync.length > 0) {
            sync.unbind("click").click(function (e) {
                let item = $(this);

                e.preventDefault();


                $.ajax({
                    type: 'POST',
                    url: item.data('url'),
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        simple_ecommerce.showtoast("Sync started", "2")
                        item.append('<div class="form-loading"></div>')
                        item.prop('disabled', true)
                    },
                    success: function (returnData) {
                        try {
                            returnData = JSON.parse(returnData)
                        } catch (e) {
                            item.prop('disabled', false)
                            $('.form-loading').remove()
                            return false
                        }

                        if (returnData.status == '1') {
                            let msg = returnData.msg;
                            if (msg.includes('|')) {
                                msg = msg.split('|');
                                for (let i = 0; i < msg.length; i++) {
                                    $("._simple-messages ul").append('<li>' + msg[i] + '</li>');
                                }
                            } else {
                                $("._simple-messages ul").append('<li>' + msg + '</li>');
                            }


                            $("._simple-messages ul").append('<li><h3>' + returnData.msgnext + '</h3></li>');

                            setTimeout(() => $("._simple_ecommerce_list_full_synchronization_start").trigger("click"), 1000)

                        } else if (returnData.status == '2') {
                            $("._simple-messages ul").append('<li><h2>' + returnData.msgnext + '</h2></li>');

                        } else {
                            //error
                            $("._simple-messages ul").append('<li style="color: red">' + returnData.msg + '</li>');
                        }

                        item.prop('disabled', false)
                        $('.form-loading').remove()

                        simple_ecommerce.showtoast("Sync is completed", "2")
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        item.prop('disabled', false)
                        $('.form-loading').remove()

                    },
                })

                return false

            });

        }
    },


    showtoast: function (msg, type) {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }

        if (type == "1") {
            //errror
            toastr.error(msg);
        } else if (type == "3") {
            //warning
            toastr.warning(msg);
        } else if (type == "4") {
            //info
            toastr.info(msg);
        } else {
            toastr.success(msg);
        }


    },



    ajax_form: function () {
        const $ = jQuery.noConflict();

        $('._simple_btn_post').unbind('click').click(function () {
            const item = $(this)
            const form = $(this).closest('form')
            const form_id = form.attr('id')
            const form_vars = '#' + form_id + ' *'
            const href = item.data("href");



            const formData = new FormData(
                document.getElementById(form.attr('id'))
            );

            $.ajax({
                type: 'POST',
                url: href,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    item.append('<div class="form-loading"></div>')
                    $(form_vars).prop('disabled', true)
                },
                success: function (returnData) {
                    try {
                        returnData = JSON.parse(returnData)
                    } catch (e) {
                        $(form_vars).prop('disabled', false)
                        $('.form-loading').remove()
                        return false
                    }

                    if (returnData.status == '1') {
                        if (returnData.msg != '') {
                            simple_ecommerce.showtoast(returnData.msg, "2")
                        }


                        if (returnData.url) {
                            window.top.location.href = returnData.url
                        }
                    } else {
                        simple_ecommerce.showtoast(returnData.msg, "1")
                    }

                    $(form_vars).prop('disabled', false)
                    $('.form-loading').remove()


                },
                error: function (xhr, textStatus, errorThrown) {
                    $(form_vars).prop('disabled', false)
                    $('.form-loading').remove()
                },
            })

            return false
        })

        $('._simple_btn_get').unbind('click').click(function () {
            const item = $(this)
            const href = $(this).data('href')

            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: href,
                beforeSend: function () {
                    item.append('<div class="form-loading"></div>')
                    item.prop('disabled', true)
                },
                success: function (returnData) {
                    try {
                        returnData = JSON.parse(
                            JSON.stringify(returnData)
                        )
                    } catch (e) {
                        $('.form-loading').remove()
                        return false
                    }

                    if (returnData.status == '1') {
                        if (returnData.msg != '') {
                            simple_ecommerce.showtoast(returnData.msg, "2")
                        }

                        if (returnData.returnactions) {
                            if (returnData.returnactions == '1') {
                                item.closest(
                                    returnData.returnactions_target_element
                                )
                                    .fadeOut()
                                    .remove()
                            } else if (returnData.returnactions == '3') {
                                $('.modal,.modal-backdrop,.tooltip')
                                    .fadeOut()
                                    .remove()
                                $('body').removeClass('modal-open')
                            } else if (returnData.returnactions == '5') {
                                $(returnData.returnactions_target_element)
                                    .fadeOut()
                                    .html(
                                        returnData.returnactions_target_element_value
                                    )
                                    .fadeIn()
                            } else if (returnData.returnactions == '6') {
                                item.closest(
                                    returnData.returnactions_target_element
                                )
                                    .fadeOut()
                                    .remove()
                            } else if (returnData.returnactions == '7') {
                                $('.modal,.modal-backdrop,.tooltip')
                                    .fadeOut()
                                    .remove()
                                $('body').removeClass('modal-open')

                                $(returnData.returnactions_target_element)
                                    .fadeOut()
                                    .html(
                                        returnData.returnactions_target_element_value
                                    )
                                    .fadeIn()
                            } else if (returnData.returnactions == '8') {
                                $(returnData.returnactions_target_element)
                                    .fadeOut()
                                    .append(
                                        returnData.returnactions_target_element_value
                                    )
                                    .fadeIn()
                            }
                        }

                        if (returnData.click_item) {
                            $(returnData.click_item).trigger('click')
                        }

                        if (returnData.func) {
                            eval(returnData.func)
                        }

                        if (returnData.url) {
                            window.top.location.href = returnData.url
                        }
                    } else {
                        simple_ecommerce.showtoast(returnData.msg, "1")
                    }

                    $('.form-loading').remove()
                    item.prop('disabled', false)

                    simple_ecommerce.ajax_form()
                },
                error: function (xhr, textStatus, errorThrown) {
                    $('.form-loading').remove()
                    item.prop('disabled', false)
                },
            })

            return false
        })


    }


};

jQuery(document).ready(function () {
    if (jQuery("._simple_ecommerce").length > 0) {
        simple_ecommerce.init();
    }

});
