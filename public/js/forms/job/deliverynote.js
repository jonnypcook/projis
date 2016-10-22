var Script = function () {
    //toggle button
    window.prettyPrint && prettyPrint();


    $('input[name=sent]').datepicker({
        format: 'dd/mm/yyyy'
    }).on('changeDate', function (e) {
        $(this).datepicker('hide').blur();
    });

    function adjustValues() {
        $('input[name="quantity[]"]').each(function(e){
            var val = parseInt($(this).val());
            if(val>0) {
                var unsent = parseInt($(this).attr('max'))-val;
                $(this).attr('max', unsent);
                $(this).val(0);
                if (unsent<=0) {
                    $(this).attr('readonly', true);
                }
                try {
                    var sent = $(this).parent().parent().find('.item-sent');
                    sent.text(parseInt(sent.text())+val);
                    var unsent = $(this).parent().parent().find('.item-unsent');
                    unsent.text(parseInt(unsent.text())-val);
                } catch (e) {}
            }
        });
    }

    $('#btn-add-deliverynote').on('click', function(e) {
        e.preventDefault();
        var cnt = 0;
        $('input[name="quantity[]"]').each(function(e){
           cnt+=parseInt($(this).val());
        });
        if (cnt==0) {
            $('#errAddDeliveryNote').text('You have not entered any product quantities below for the delivery note');
            return false;
        }
        $('#errAddDeliveryNote').text('');

        $('#modalDeliveryNote').modal();
    });

    $('#btn-confirm-deliverynote').on('click', function(e) {
        e.preventDefault();
        $('#DeliveryNoteForm').submit();
        return false;
    });
    
    $('#DeliveryNoteForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            $('#deliveryNoteMsgs').empty();
            resetFormErrors($(this).attr('name'));
            var url = $(this).attr('action');
            
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize()+'&'+$('#deliverySystemDetails').serialize();
            $('#deliveryNoteLoader').fadeIn(function(){
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
                            if (obj.err == true) {
                                var tab = 1;
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        if (!addFormError(i, obj.info[i])) {
                                            msgAlert('deliveryNoteMsgs',{
                                                title: false,
                                                mode: 3,
                                                body: obj.info[i],
                                                empty: true
                                            });
                                        }
                                    }
                                }
                                
                            } else{ // no errors
                                growl('Success!', 'The delivery note has been created successfully.<br />If the document does not automatically download then click <a href="'+
                                        $('#documentDownloadFrm').attr('action')+'?documentListId='+obj.info.documentListId+'">here</a>.', {sticky:true});
                                $('#documentDownloadFrm input[name=documentListId]').val(obj.info.documentListId);
                                $('#documentDownloadFrm').submit();
                                adjustValues();
                                tbDeliveryNote.fnDraw();
                                $('#modalDeliveryNote').modal('hide');
                            }
                        }
                        catch(error){
                            
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#deliveryNoteLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    $(document).on('click','.action-download', function(e) {
        e.preventDefault();
        var did = $(this).attr('data-dispatchId');
        if (did==undefined) {
            return false;
        }
        $('#formWizard input[name=dispatch]').val(did);
        $('#formWizard').submit();
    });

}();