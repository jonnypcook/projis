var Script = function () {
    // begin first table
    var devicesTable = $('#devices_tbl').dataTable({
        "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ records per page",
            "oPaginate": {
                "sPrevious": "Prev",
                "sNext": "Next"
            }
        },
        bProcessing: true,
        bServerSide: true,
        iDisplayLength:5,
        aLengthMenu: [[5, 10, 15, 20, 25, 50], [5, 10, 15, 20, 25, 50]],
        "aoColumns": [
            null,
            null,
            null,
            { 'bSortable': false },
            { 'bSortable': false }
        ],
        sAjaxSource: "/playground/liteipdevicelist/",
        fnServerParams: function (aoData) {
            var fDrawingId = $("#fDrawingId").val();
            aoData.push({name: "fDrawingId", value: fDrawingId});
        },
        fnDrawCallback: function () {
            // called at the end of table rendering
        }
    });

    $(document).on('click', '.serial-trigger', function() {
        $('#locator-container').trigger('addSerial', $(this).attr('data-device-serial'));
    });

    $('#btn-synchronize-drawing').on('click', function() {
        try {
            var drawingID = $("#fDrawingId").val();
            if (!drawingID.match(/^[\d]+$/)) {
                return;
            }

            var url = "/playground/liteiprefreshdevices/?fDrawingId=" + drawingID;
            var params = 'ts='+Math.round(new Date().getTime()/1000);
            $('#locatorLoader').fadeIn(function(){
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: params, // Just send the Base64 content in POST body
                    processData: false, // No need to process
                    timeout: 60000, // 1 min timeout
                    dataType: 'text', // Pure Base64 char data
                    beforeSend: function onBeforeSend(xhr, settings) {},
                    error: function onError(XMLHttpRequest, textStatus, errorThrown) {},
                    success: function onUploadComplete(response) {
                    },
                    complete: function(jqXHR, textStatus){
                        $('#locatorLoader').fadeOut();
                        $("#fDrawingId").trigger("change");
                    }
                });
            });

        } catch (ex) {

        }/**/
    });

    $("#fDrawingId").on("change", function(e) {
        var opt = $("#fDrawingId option[value=" + $(this).val() + "]");
        devicesTable.fnDraw();
        findDeviceList({
            drawingID: $(this).val(),
            width: opt.attr('data-width'),
            height: opt.attr('data-height')
        });
        return;
    });

    function findDeviceList(drawing) {
        try {
            var url = "/playground/liteipdevicelist/?fDrawingId=" + drawing.drawingID + "&plot=1";
            var params = 'ts='+Math.round(new Date().getTime()/1000);
            $('#drawingLoader').fadeIn(function(){
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: params, // Just send the Base64 content in POST body
                    processData: false, // No need to process
                    timeout: 60000, // 1 min timeout
                    dataType: 'text', // Pure Base64 char data
                    beforeSend: function onBeforeSend(xhr, settings) {},
                    error: function onError(XMLHttpRequest, textStatus, errorThrown) {},
                    success: function onUploadComplete(response) {
                        try{
                            var obj=jQuery.parseJSON(response);
                            var k = 0;
                            // an error has been detected
                            if (obj.err !== true && !!obj.devices) {
                                drawing.devices = obj.devices;
                                $('#locator-container').trigger('drawDevices', drawing);
                            }
                        }
                        catch(error){
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#drawingLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/

    }

    $('#sel-project').on("change", function (e) {
        try {
            var url = "/playground/liteipdrawinglist/";
            var projectID = $(this).val();
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&projectId=' + projectID;
            $('#locatorLoader').fadeIn(function(){
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: params, // Just send the Base64 content in POST body
                    processData: false, // No need to process
                    timeout: 60000, // 1 min timeout
                    dataType: 'text', // Pure Base64 char data
                    beforeSend: function onBeforeSend(xhr, settings) {},
                    error: function onError(XMLHttpRequest, textStatus, errorThrown) {},
                    success: function onUploadComplete(response) {
                        try{
                            var obj=jQuery.parseJSON(response);
                            var k = 0;
                            // an error has been detected
                            if (obj.err !== true) {
                                var fDrawingId = $('#fDrawingId');
                                fDrawingId.empty();
                                fDrawingId.append($('<option>').text('select drawing'));
                                for(var i in obj.drawings) {
                                    fDrawingId.append($('<option>')
                                        .attr('data-width', obj.drawings[i].Width)
                                        .attr('data-height', obj.drawings[i].Height)
                                        .text(obj.drawings[i].Drawing)
                                        .val(obj.drawings[i].DrawingID));
                                }
                            }
                        }
                        catch(error){
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#locatorLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
    });

    //jQuery('#devices_tbl_wrapper .dataTables_filter input').addClass("input-medium"); // modify table search input
    //jQuery('#devices_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown

}();