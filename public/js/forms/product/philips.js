var Script = function () {
    //toggle button
    window.prettyPrint && prettyPrint();

     $('#eca-toggle-button').toggleButtons({
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "primary",
            disabled: "danger"
        }
    });
    


    // begin first table
    var productTable = $('#products_tbl').dataTable({
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
        iDisplayLength:15,
        aLengthMenu: [[5, 10, 15, 20, 25, 50], [5, 10, 15, 20, 25, 50]],
        "aoColumns": [
            null,
            null,
            null,
            { "sClass": "hidden-phone" },
            { "sClass": "hidden-phone" },
            null,
            null,
            { "bSortable": false }
        ],
        sAjaxSource: "/product/listphilips/",
        fnServerParams: function (aoData) {
            var fCategory = $("#fCategory").val();
            aoData.push({name: "fCategory", value: fCategory});
            var fBrand = $("#fBrand").val();
            aoData.push({name: "fBrand", value: fBrand});
        }
        
    });
    
    $("#fCategory, #fBrand").on("change", function(e) {
        productTable.fnDraw();
        return;
     });
    
    jQuery('#products_tbl_wrapper .dataTables_filter input').addClass("input-medium"); // modify table search input
    jQuery('#products_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown

    $(document).on('click', '.more-info', function(e){
        e.preventDefault();
        $('#product-model').text($(this).attr('data-model'));
        $('#product-desc').text($(this).attr('data-desc'));
        $('#product-category').text($(this).attr('data-category'));
        $('#product-brand').text($(this).attr('data-brand'));
        $('#product-nc').text($(this).attr('data-nc'));
        $('#product-eoc').text($(this).attr('data-eoc'));
        $('#product-tmin').text($(this).attr('data-tmin'));
        $('#product-tmax').text($(this).attr('data-tmax'));
        $('#product-ntrade').text($(this).attr('data-ntrade'));
        $('#product-cpu').text($(this).attr('data-cpu'));
        $('#product-ppu').text($(this).attr('data-ppu'));
        if ($(this).attr('data-pid')==undefined) {
            $('#product-8p3').text($(this).attr('data-8p3'));
        } else {
            $('#product-8p3').html($(this).attr('data-8p3')+'<a href="/product-'+$(this).attr('data-pid')+'/" class="pull-right" target="_tab"><i class="icon-cog"></i></a>');
        }
        
        
        $('#modalProductInfo').modal();
        return false;
    });
    
    
    $(document).on('click', '.add-product', function(e){
        e.preventDefault();
        resetFormErrors($('#ProductPhilipsForm').attr('name'));
        $('#productMsgs').empty();
        $('#ProductPhilipsForm input[name=ppid]').val($(this).attr('data-ppid'));
        $('#ProductPhilipsForm textarea[name=description]').val($(this).attr('data-desc'));
        $('#ProductPhilipsForm input[name=pwr]').val('');
        $('#add-product-name').text($(this).attr('data-model'));
        
        $('#modalProductAdd').modal();
        return false;
    });
    
    $(document).on('click', '.view-product', function(e){
        e.preventDefault();
        var pid = $(this).attr('data-pid');
        if (pid==undefined) {
            return false;
        }
        
        document.location = '/product-'+pid+'/';
        
        return false;
    });
    
    $('#ProductPhilipsForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            resetFormErrors($(this).attr('name'));
            $('#productMsgs').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            
            $('#productAddLoader').fadeIn(function(){
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
                        //console.log(response); //return;
                        try{
                            var obj=jQuery.parseJSON(response);
                            var k = 0;
                            // an error has been detected
                            var tab = 3;
                            var additional='';
                            if (obj.err == true) {
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        if (!addFormError(i, obj.info[i])) {
                                            additional+=obj.info[i]+'<br>';
                                        }
                                    }
                                }

                                if (additional != '') {
                                    msgAlert('productMsgs',{
                                        mode: 3,
                                        body: 'Error: '+additional,
                                        empty: true
                                    });
                                }

                            } else{ // no errors
                                growl('Success!', 'The Philips product has been ported to the 8point3 catalogue successfully.<br /><a href="/product-'+obj.info.productId+'/">Click here to view product</a>', {time: 6000});
                                $('#modalProductAdd').modal('hide');
                                productTable.fnDraw();
                            }
                        }
                        catch(error){

                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#productAddLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
    });
    
    $('#btn-product-add').on('click', function(e) {
        e.preventDefault();
        $('#ProductPhilipsForm').submit();
        return false;
    });
    
}();