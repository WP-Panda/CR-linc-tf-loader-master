jQuery(document).ready(function ($) {
    
    $('.file-acces').click(function () {
        
        $(this).html('Search File');
        var button = $(this);
        var file = $(this).attr('data-file');
        var div = $(this).prev('.append-div').attr('id');
        var file_reserv = $(this).attr('data-file-reserv');
        var id = $(this).attr('data-id');
        //console.log(file + '  ' + div + '   ' + url);
        $.getJSON('http://ex-load.com/?op=api_json&u=yackupal@gmail.com&p=15426378&f=' + file, function (data) {
            if (data.length < 1) {
                //console.log('пусто запуск резерва');
                ajax_update(id, 'no');
                $.getJSON('http://ex-load.com/?op=api_json&u=yackupal@gmail.com&p=15426378&f=' + file_reserv, function (data) {
                    if (data.length < 1) {
                        console.log('пусто стоп грузим сохраненные');
                        ajax_post_load(div);
                        button.slideUp('slow');
                        $('#' + div).slideDown('slow');
                    } else {
                        // console.log(data);
                        $('#' + div).prepend('<h3>к сожалению файл в настоящее вреня недоступен, рекомендуем просметреть фильмы из этой же категории</h3>');
                        $.each(data, function (i, item) {
                            var urls = hwcrypt_go(item);
                            $('<a target="_blank">' + urls + '</a><br>').attr('href', urls).appendTo('#' + div);
                            $('#' + div).slideDown('slow');
                        });
                        button.slideUp('slow');
                    }
                });
            } else {
                //console.log(data);
                $.each(data, function (i, item) {
                    var urls = hwcrypt_go(item);
                    //console.log (urls);
                    $('<a target="_blank">' + urls + '</a><br>').attr('href', urls).prependTo('#' + div);
                    ajax_update(id, 'yes');
                    button.slideUp('slow');
                    $('#' + div).slideDown('slow');
                });
            }
        });
        
    });
    
    function ajax_update($post_id, $vals) {
        var data = {
            action: 'my_action',
            security: MyAjax.security,
            id: $post_id,
            vals: $vals
        };
        $.post(MyAjax.ajaxurl, data, function (response) {
            // console.log(response);
        });
    }
    
    function ajax_post_load($append_div) {
        var data = {
            action: 'my_action_post',
            security: MyAjax.security,
        };
        var div = $append_div;
        $.post(MyAjax.ajaxurl, data, function (response) {
            // console.log(response);
            $('#' + div).prepend(response);
        });
    }
    
    function hwcrypt_go($str) {
        var xx = '';
        function handleSuccess(data) {
            //console.log(data);
            xx = data;
        }
        jQuery.ajax(MyAjax.ajaxurl, {
                type: 'POST',
                data: {
                    action: 'wcrypt_com_action',
                    security: MyAjax.security,
                    url: $str
                },
                async: false,
                success: handleSuccess
            }
        );
        return xx;
    }
});