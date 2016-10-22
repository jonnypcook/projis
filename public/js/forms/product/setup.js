var Script = function () {
    //toggle button
    window.prettyPrint && prettyPrint();

    $('#active-toggle-button').toggleButtons({
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "primary",
            disabled: "danger"
        }
    });
    
     $('#eca-toggle-button').toggleButtons({
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "primary",
            disabled: "danger"
        }
    });
    
     $('#mcd-toggle-button').toggleButtons({
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "primary",
            disabled: "danger"
        }
    });
    
    
    
    $('#ProductConfigForm input[name=cpu]').on('change', function (e) {
        var cpu = $('#ProductConfigForm input[name=cpu]').val();
        if (!cpu.match(/^[\d]+$/)) {
            return false;
        }
        $('#ProductConfigForm input[name=ppu]').val((cpu/0.55).toFixed(2));
    });
    
    
    $('#ProductConfigForm').on('submit', function(e) {
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
                                growl('Success!', 'The product settings have been successfully updated', {});
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
    
    $('#btn-product-modify').on('click', function(e) {
        e.preventDefault();
        $('#ProductConfigForm').submit();
        return false;
    });


}();