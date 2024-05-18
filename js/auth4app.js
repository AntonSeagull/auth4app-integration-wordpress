jQuery(document).ready(function ($) {
    // Устанавливаем маску для ввода номера телефона
    $('#auth4app-phone').mask(auth4app_settings.phone_mask);

    $('#auth4app-form').on('submit', function (e) {
        e.preventDefault();

        var phone = $('#auth4app-phone').val().replace(/\D/g, ''); // Удаляем все нечисловые символы

        $.ajax({
            url: auth4app_ajax.url,
            type: 'POST',
            data: {
                action: 'auth4app_get_code',
                phone: phone,
            },
            success: function (response) {
                if (response.success) {
                    var data = response.data;
                    var links = data.links;
                    $('#auth4app-links').html(
                        '<p>' + auth4app_settings.phone_label + ': <strong id="auth4app-code">' + data.code + '</strong></p>' +
                        '<button id="copy-code">' + auth4app_settings.copy_code_button + '</button>' +
                        '<p>' + auth4app_settings.copy_instructions + '</p>' +
                        '<div id="auth4app-messengers"></div>'
                    );
                    $.each(links, function (index, link) {
                        $('#auth4app-messengers').append(
                            '<a href="' + link.link + '" style="color:' + link.color + '">' +
                            '<img src="' + link.image + '" alt="' + link.title + '">' +
                            '</a>'
                        );
                    });
                    $('#copy-code').on('click', function () {
                        var code = $('#auth4app-code').text();
                        var tempInput = $('<input>');
                        $('body').append(tempInput);
                        tempInput.val(code).select();
                        document.execCommand('copy');
                        tempInput.remove();
                        var button = $(this);
                        button.text(auth4app_settings.code_copied);
                        setTimeout(function () {
                            button.text(auth4app_settings.copy_code_button);
                        }, 3000);
                    });
                    checkAuthStatus(data.code_id);
                } else {
                    $('#auth4app-links').html('<p>' + response.data + '</p>');
                }
            }
        });
    });

    function checkAuthStatus(code_id) {
        setInterval(function () {
            $.ajax({
                url: auth4app_ajax.url,
                type: 'POST',
                data: {
                    action: 'auth4app_check_status',
                    code_id: code_id,
                },
                success: function (response) {
                    if (response.success) {
                        $('#auth4app-links').html('<p>' + response.data.message + '</p>');
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        }
                    }
                }
            });
        }, auth4app_settings.check_interval * 1000);
    }
});
