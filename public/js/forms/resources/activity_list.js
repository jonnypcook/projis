var Script = function () {

    $('#filter_types').on('change', function () {
        var fType = $.trim($(this).val());

        if (fType == '') {
            return false;
        }
        if (fType === 'cos') {
            $('#cos_block').show();
            $('#wip_block').hide();
            $('#invoice_block').hide();
            url = '/resource-activities/coslist';
            params = {
                'cos_start_date': $('#cos_start_date').val(),
                'cos_end_date': $('#cos_end_date').val()
            };
        }
        else if (fType === 'wip') {
            $('#cos_block').hide();
            $('#wip_block').show();
            $('#invoice_block').hide();
            url = '/resource-activities/wislist';
            params = {
                'wip_end_date': $('#wip_end_date').val()

            };
        }
        else if (fType === 'invoice') {
            console.log('Here');
            $('#cos_block').hide();
            $('#wip_block').hide();
            $('#invoice_block').show();
        }
    });

    $('#cos_start_date, #cos_end_date, #wip_end_date, #invoice_start_date, #invoice_end_date').datepicker({
        format: 'dd-mm-yyyy'
    });

    $('#btn_cos, #btn_wip, #btn_invoice').on('click', function () {
        var bType = $.trim($(this).attr('id'));
        var url = '';
        var params = '';
        //Check clicked button and execute ajax according to that.
        if (bType === 'btn_cos') {

            var start_date = $.trim($('#cos_start_date').val());
            var end_date = $.trim($('#cos_end_date').val());

            if (start_date === '') {
                $('#cos_start_date').val('01-01-1900');
                start_date = '01-01-1900';
            }

            if (end_date === '') {
                alert('Please check dates properly');
                return false;
            }

            url = '/resource-activities/coslist';
            params = {
                'cos_start_date': start_date,
                'cos_end_date': end_date
            };
        }
        else if (bType === 'btn_invoice') {
            var start_date = $.trim($('#invoice_start_date').val());
            var end_date = $.trim($('#invoice_end_date').val());

            if (start_date === '') {
                $('#invoice_start_date').val('01-01-1990');
                start_date = '01-01-1900';
            }

            if (end_date === '') {
                alert('Please check invoice end date');
                return false;
            }


            url = '/resource-activities/invoices';
            params = {
                'start_date' : start_date,
                'end_date' : end_date
            };
        }
        else if (bType === 'btn_wip') {
            var end_date = $.trim($('#wip_end_date').val());
            if (end_date === '') {
                alert('Please check end date');
                return false;
            }
            url = '/resource-activities/wiplist';
            params = {
                'wip_end_date': $('#wip_end_date').val()
            };
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: params, // Just send the Base64 content in POST body
            processData: true, // No need to process
            timeout: 60000, // 1 min timeout
            dataType: 'json', // Pure Base64 char data
            beforeSend: function () {
                $('#activityRows').empty();
                $('#activityRows').html('<tr><td colspan="10" style="text-align:center;">Loading...</td></tr>');
            },
            error: function onError(XMLHttpRequest, textStatus, errorThrown) {
            },
            success: function onUploadComplete(response) {
                $('#activityRows').empty();
                var activities = response.activities;
                console.log(activities);
                if ($.isEmptyObject(activities)) {
                    $('#activityRows').html('<tr><td colspan="10" style="text-align:center;">No record found</td></tr>');
                    return false;
                }
                else {
                    for (var key in activities) {
                        var html = '<tr style="background-color:' + activities[key].color + '">';
                        html += '<td>' + activities[key].date + '</td>';
                        html += '<td><a href="/client-' + activities[key].client_id + '/job-' + activities[key].project_id + '/resources">' + activities[key].project_ref + '</a></td>';
                        html += '<td>' + activities[key].reference + '</td>';
                        html += '<td>' + activities[key].details + '</td>';
                        html += '<td>' + ucfirst(activities[key].reference_type) + '</td>';
                        html += '<td>' + activities[key].resource + '</td>';
                        html += '<td>' + activities[key].cost_code + '</td>';
                        html += '<td style="text-align:right;">' + activities[key].quantity + '</td>';
                        html += '<td style="text-align:right;"> &#163;' + activities[key].rate + '</td>';
                        html += '<td style="text-align:right;"> &#163;' + activities[key].total + '</td>';
                        if (activities[key].reference_type == 'product') {
                            html += '<td><a href="javascript:;" data-value="' + activities[key].id + '" class="btn btn-success btn-edit-product"><i class="icon-pencil"></i></a></td>';
                        }
                        else if (activities[key].reference_type == 'invoice') {
                            html += '<td><a href="javascript:;" data-value="' + activities[key].id + '" class="btn btn-success btn-edit-invoice"><i class="icon-pencil"></i></a></td>';
                        }
                        else {
                            html += '<td><a href="javascript:;" data-value="' + activities[key].id + '" class="btn btn-success btn-edit-activity"><i class="icon-pencil"></i></a></td>';
                        }
                        html += '</tr>';
                        $('#activityRows').append(html);
                    }
                }
            },
            complete: function (jqXHR, textStatus) {

                $('#resouceChangeLoader').fadeOut(function () {

                });
            }
        });


    });

    function ucfirst(str) {
        var f = str.charAt(0).toUpperCase();

        return f + str.substr(1);
    }
}();