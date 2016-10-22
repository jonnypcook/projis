var Script = function () {
    $('.job-status').on('click', function (e) {
        $('#modalJobStatus').modal();
    });

    $('#btn-edit-status').on('click', function () {
        $('#JobStatusForm').submit();
    });

    $('#JobStatusForm').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            $('#msgs3').empty();
            var url = $(this).attr('action');
            var params = 'ts=' + Math.round(new Date().getTime() / 1000) + '&' + $(this).serialize();
            $('#jobStatusLoader').fadeIn(function () {
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: params, // Just send the Base64 content in POST body
                    processData: false, // No need to process
                    timeout: 60000, // 1 min timeout
                    dataType: 'text', // Pure Base64 char data
                    success: function onUploadComplete(response) {
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
                                msgAlert('msgs3', {
                                    mode: 3,
                                    body: 'Error: ' + additional,
                                    empty: true
                                });
                            }

                        } else {
                            $('#modalJobStatus').modal('hide');
                            growl('Success!', 'The Job status has been successfully modified.', {time: 3000});
                            location.reload();
                        }
                    },
                    complete: function (jqXHR, textStatus) {
                        $('#jobStatusLoader').fadeOut(function () {
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

        