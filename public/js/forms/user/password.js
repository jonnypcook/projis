var Script = function () {
    $('#btn-savepassword').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#ChangePasswordForm').submit();
        return false;
    });
    
    $('#ChangePasswordForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#passwordLoader').fadeIn(function(){
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
                                    msgAlert('msgs3',{
                                        mode: 3,
                                        body: 'Error: '+additional,
                                        empty: true
                                    });
                                }
                                
                            } else{ // no errors
                                growl('Success!', 'You have updated your password successfully.', {time: 3000});
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#passwordLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });

}();