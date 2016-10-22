var Script = function () {
 
    
    $('.btnPriceChange').on('click', function(e) {
        e.preventDefault();
        var ppu = $(this).attr('data-ppu');
        var cpu = $(this).attr('data-cpu');
        var pid = $(this).attr('data-pid');
        var model = $(this).attr('data-model');
        
        if (pid==undefined) return;
        if (ppu==undefined) return;
        
        // set initial valuess
        $('form[name=PricePointUpdateForm] input[name=ppu]').val(ppu);
        $('form[name=PricePointUpdateForm] input[name=product]').val(pid);
        $('#cppProductName').text(model);
        
        if (cpu!=undefined) {
            $('form[name=PricePointUpdateForm] input[name=cpu]').val(cpu);
        }
        
        $('#modalChangePP').modal();
        
        return false;
    });
    
    $('#btn-confirm-changepp').on ('click', function(e) {
        e.preventDefault();
        $('form[name=PricePointUpdateForm]').submit();
        return false;
    });
    
    $('form[name=PricePointUpdateForm]').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            $('#ppMsgs').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#changePPLoader').fadeIn(function(){
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
                        console.log(response); //return;
                        try{
                            var obj=jQuery.parseJSON(response);
                            var k = 0;
                            // an error has been detected
                            if (obj.err == true) {
                                var additional='';
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        if (!addFormError(i, obj.info[i])) {
                                            additional+=obj.info[i];
                                            break;
                                        }
                                    }
                                }
                                
                                if (additional != '') {
                                    msgAlert('ppMsgs',{
                                        mode: 3,
                                        body: 'Error: '+additional,
                                        empty: true
                                    });
                                }
                                
                            } else{ // no errors
                                //growl('Success!', 'The building has been added successfully.', {time: 3000});
                                window.location.reload();
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#changePPLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
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
        