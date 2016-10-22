var Script = function () {
    $('.project-status').on('click', function (e) {
        $('#modalProjectStatus').modal();
    });

    $('#btn-edit-status').on('click', function () {
        $('#ProjectStatusForm').submit();
    });

    $('#ProjectStatusForm').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            $('#msgs4').empty();
            var url = $(this).attr('action');
            var params = 'ts=' + Math.round(new Date().getTime() / 1000) + '&' + $(this).serialize();
            $('#projectStatusLoader').fadeIn(function () {
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: params, // Just send the Base64 content in POST body
                    processData: false, // No need to process
                    timeout: 60000, // 1 min timeout
                    dataType: 'text', // Pure Base64 char data
                    success: function onUploadComplete(response) {
                        try {
                            var obj = jQuery.parseJSON(response);
                            if (obj.err == true) {
                                additional = '';
                                if (obj.info != undefined) {
                                    for (var i in obj.info) {
                                        if (!addFormError(i, obj.info[i])) {
                                            additional += obj.info[i] + '<br>';
                                        }
                                    }
                                }

                                if (additional != '') {
                                    msgAlert('msgs4', {
                                        mode: 3,
                                        body: 'Error: ' + additional,
                                        empty: true
                                    });
                                }
                            } else {
                                $('#modalProjectStatus').modal('hide');
                                growl('Success!', 'The project status has been successfully modified.', {time: 3000});
                                location.reload();
                            }
                        }
                        catch(error)
                        {
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function (jqXHR, textStatus) {
                        $('#projectStatusLoader').fadeOut(function () {
                        });
                    }
                });
            });
        }
        catch (error) {
            $('#errors').html($('#errors').html() + error + '<br />');
        }
    });
}();

        