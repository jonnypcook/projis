var Script = function () {

    $('.btnResourceEdit').on('click', function (e) {
        e.preventDefault();
        var name = $(this).attr('data-name');
        var rid = $(this).attr('data-rid');

        var cost_code = $(this).attr('data-costcode');
        var unit = $(this).attr('data-unit');
        var cost = $(this).attr('data-cost');

        if (rid == undefined) return;
        if (cost_code == undefined) return;

        // set initial values
        $('form[name="ResourceEditForm"] select[name="costCode"]').val(cost_code).attr('selected', 'selected');
        $('form[name="ResourceEditForm"] input[name="name"]').val(name);
        $('form[name="ResourceEditForm"] input[name="cost"]').val(cost);
        $('form[name="ResourceEditForm"] input[name="unit"]').val(unit);
        $('form[name="ResourceEditForm"] input[name="resource_id"]').val(rid);
        $('#modalResourceEdit').modal();
        return false;
    });

    $('#btn-confirm-changeresource').on ('click', function (e) {
        e.preventDefault();
        $('form[name=ResourceEditForm]').submit();
        return false;
    });

    $('form[name=ResourceEditForm]').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            //resetFormErrors($(this).attr('name'));
            //$('#ppMsgs').empty();
            var url = $(this).attr('action');
            var params = 'ts=' + Math.round(new Date().getTime() / 1000) + '&' + $(this).serialize();
            $('#resourceChangeLoader').fadeIn(function () {
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: params, // Just send the Base64 content in POST body
                    processData: false, // No need to process
                    timeout: 60000, // 1 min timeout
                    dataType: 'text', // Pure Base64 char data
                    beforeSend: function onBeforeSend(xhr, settings) {
                    },
                    error: function onError(XMLHttpRequest, textStatus, errorThrown) {
                    },
                    success: function onUploadComplete(response) {
                        //console.log(response); //return;
                        try {
                            var obj = jQuery.parseJSON(response);
                            var k = 0;
                            // an error has been detected
                            if (obj.err == true) {
                                var additional = '';
                                if (obj.info != undefined) {
                                    for (var i in obj.info) {
                                        if (!addFormError(i, obj.info[i])) {
                                            additional += obj.info[i];
                                            break;
                                        }
                                    }
                                }

                                if (additional != '') {
                                    msgAlert('ppMsgs', {
                                        mode: 3,
                                        body: 'Error: ' + additional,
                                        empty: true
                                    });
                                }

                            } else { // no errors
                                growl('Success!', 'The resource has been updated successfully.', {time: 3000});
                                window.location.reload();
                            }
                        }
                        catch (error) {
                            $('#errors').html($('#errors').html() + error + '<br />');
                        }
                    },
                    complete: function (jqXHR, textStatus) {
                        $('#resouceChangeLoader').fadeOut(function () {
                        });
                    }
                });
            });

        } catch (ex) {

        }
        /**/
        return false;
    });

    $('.btnResourceDelete').on('click', function(){
        if ( !confirm('Are you sure to remove?') )
        {
            return false;
        }
        var rid = $(this).attr('data-rid');
        var params = 'ts=' + Math.round(new Date().getTime() / 1000);
        $.ajax({
            url: '/resource-item/resource-' + rid + '/remove',
            type: 'post',
            dataType: 'text',
            data: params,
            beforeSend: function(){},
            success: function(response){
                var obj = $.parseJSON(response);
                var k = 0;
                // an error has been detected
                if (obj.err == true) {
                    var additional = '';
                    if (obj.info != undefined) {
                        for (var i in obj.info) {
                            if (!addFormError(i, obj.info[i])) {
                                additional += obj.info[i];
                                break;
                            }
                        }
                    }

                    if (additional != '') {
                        msgAlert('ppMsgs', {
                            mode: 3,
                            body: 'Error: ' + additional,
                            empty: true
                        });
                    }
                }
                else { // no errors
                    growl('Success!', 'The resource has been removed successfully.', {time: 3000});
                    window.location.reload();
                }
            },
        });

    });

    // begin first table
    /*$('#products_tbl').dataTable({
     sDom: "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
     sPaginationType: "bootstrap",
     oLanguage: {
     sLengthMenu: "_MENU_ per page",
     oPaginate: {
     sPrevious: "",
     sNext: ""
     }
     },
     aoColumnDefs: [{
     'bSortable': false,
     'aTargets': [0]
     }]
     });

     jQuery('#products_tbl_wrapper .dataTables_filter input').addClass("input-medium"); // modify table search input
     jQuery('#products_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
     /**/
}();
        