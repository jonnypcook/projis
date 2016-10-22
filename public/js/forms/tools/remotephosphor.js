var Script = function () {
    
    $('#RemotePhosphorForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            resetFormErrors($(this).attr('name'));
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#info-rp-error').hide();
            $('#remotePhosphorLoader').fadeIn(function(){
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
                            var clrs = ["warning", "info"];
                            // an error has been detected
                            if (obj.err == true) {
                                $('#info-rp-error').fadeIn();
                                
                                //scrollFormError('SetupForm', 210);
                            } else{ // no errors
                                var config = '';
                                for (var i in obj.info.dConf) {
                                    config+='<span class="text-'+clrs[(i%2)]+'">';
                                    for (var j in obj.info.dConf[i]) {
                                        for (var k=0; k<obj.info.dConf[i][j]; k++) {
                                            config+='['+j+']';
                                        }
                                        
                                    }
                                    config+='</span><br />';
                                }
                                $('#info-rp-deliverable').text(obj.info.dLen+'mm');
                                $('#info-rp-billable').html(obj.info.dBillU+' units  (cost = &#163;'+number_format(obj.info.dCost,2)+')');
                                $('#info-rp-config').html(config);
                            }
                        }
                        catch(error){
                            //$('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#remotePhosphorLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    
    $('#btn-remotephosphor-calculate').on('click', function(e) {
        e.preventDefault();
        $('#RemotePhosphorForm').submit();
        return false;
    });
    
     
}();