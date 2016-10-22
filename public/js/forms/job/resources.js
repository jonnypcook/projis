var Script = function () {

    $('#btn-save-resource').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        $('#resourceForm').submit();
        return false;
    });

    $("#resourceForm").validate({
        rules: {
            expected_date: "required",
            resource: "required",
            costCode: "required",
            project: "required",
            project: "required",
            quantity: "required",
            rate: "required",
            startDate: "required",
            endDate: "required"
        },
        messages: {
            expected_date: "Plaease enter date",
            resource: "Please select resource",
            costCode: "Please select cost code",
            project: "Please enter valid project id",
            quantity: "Please enter quantity",
            rate: "Please enter rate",
            startDate: "Please enter start date",
            endDate: "Please enter end date"
        },
        submitHandler: function (form) {
            var params = 'ts=' + Math.round(new Date().getTime() / 1000) + '&' + $('#resourceForm').serialize();
            $('#resourceLoader').fadeIn(function () {
                $.ajax({
                    type: 'POST',
                    url: '/resource-activities/add',
                    data: params, // Just send the Base64 content in POST body
                    processData: true, // No need to process
                    timeout: 60000, // 1 min timeout
                    dataType: 'text', // Pure Base64 char data
                    beforeSend: function onBeforeSend(xhr, settings) {
                    },
                    error: function onError(XMLHttpRequest, textStatus, errorThrown) {
                    },
                    success: function onUploadComplete(response) {
                        console.log(resource);
                        try {
                            var obj = jQuery.parseJSON(response);
                            var k = 0;
                            // an error has been detected
                            if (obj.err == true) {

                            } else { // no errors
                                growl('Success!', 'The resource has been added successfully.', {time: 3000});

                                //window.location.reload();
                            }
                        }
                        catch (error) {
                        }
                    },
                    complete: function (jqXHR, textStatus) {
                        $('#resourceLoader').fadeOut(function () {
                            var d = new Date();
                            $('#expected_date').val(d.getDate() + '/' + d.getMonth() + '/' + d.getFullYear());
                            $('#resource').val('');
                            $('#costCode').val('');
                            $('#reference').val('');
                            $('#quantity').val('');
                            $('#rate').val('');
                            $('#total_cost').html('');
                            $('#start_date, #end_date').val('');
                            $('#resourceForm input[type="radio"]').attr('checked', false);
                            $('#expected_date').focus();

                        });
                    }
                });
            });
        }
    });

    /*
    $('#expected_date').datepicker({
        format: 'dd/mm/yyyy',
    });

    $('#expected_date').on('changeDate', function (ev) {
        $(this).datepicker('hide');
        $(this).blur();
    });

    $('#expectedDateIcon').on('click', function () {
        $('#expected_date').val('');
    });
    */

    $('#expected_date, #start_date, #end_date').on('blur', function(){
        var d = $(this).val();
        var currDate = new Date();
        var new_date = '';
        if ( d.length >= 8 )
        {
            return false;
        }
        else if ( d.length == 2 && !isNaN(d)  )
        {
            currMonth = (currDate.getMonth() + 1 );
            if ( currMonth < 10 )
            {
                currMonth = '0'  + currMonth;
            }

            new_date = d + '/' + currMonth + '/' + currDate.getFullYear();
            $(this).val(new_date);
        }
        else if( d.length == 4 && !isNaN(d) )
        {
            new_date = d.substring(0,2) + '/' + d.substring(2,4) + '/' + currDate.getFullYear();
            $(this).val(new_date);
        }
        else if( d.length == 6 && !isNaN(d) )
        {
            new_date = d.substring(0,2) + '/' + d.substring(2,4) + '/' + parseInt((2000 + parseInt(d.substring(4,6))));
            $(this).val(new_date);
        }
        else if( d.length == 8 && !isNaN(d) )
        {
            new_date = d.substring(0,2) + '/' + d.substring(2,4) + '/' + d.substring(4,8);
            $(this).val(new_date);
        }
        else {
        }
    });


    $('#quantity, #rate').on('keyup', function () {
        var quantity = $('#quantity').val();
        var rate = $('#rate').val();
        var total = 0;
        if (quantity != '' && rate != '') {
            total = parseFloat(quantity * rate);
        }

        $('#total_cost').html(total);
    });

    $('#project').on('blur', function () {
        var project_id = $(this).val();
        $.ajax({
            type: 'POST',
            url: '/resource-activities/getclientid',
            data: {'project_id': project_id},
            beforeSend: function () {
                $('#project_ref').css('color', '#000');
                $('#project_ref').html('Loading...');
            },
            success: function (res) {
                $('#project_ref').html('');
                if (res.error) {
                    $('#project_ref').html('Invalid Project');
                    $('#project_ref').css('color', 'red');
                }
                else {
                    $('#project_ref').html(res.project_ref);
                }
            }
        });
    });

    $('#resource').on('change', function () {
        var id = $(this).val();
        url = '/resource-item/resource-' + id + '/fetchresource';
        $.ajax({
            type: 'POST',
            url: url,
            data: {'resource_id': id},
            beforeSend: function () {
                $('#resource_ref').css('color', '#000');
                $('#resource_ref').html('Processing...');
            },
            success: function (res) {
                $('#resource_ref').html('');
                if (res.error > 0) {
                    $('#resource_ref').css('color', '#000');
                    $('#resource_ref').html('Invalid Resource');
                    return false;
                }
                else {
                    $('#costCode').val(res.costCode);
                    $('#rate').val(res.rate);
                }
            }
        });
    });

    $('#btn-add-activity-modal').on('click', function () {
        $('#modalResource').modal();
    });
}();