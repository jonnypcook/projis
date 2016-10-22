var Script = function () {
    window.prettyPrint && prettyPrint();
    
    $('#btn-modify-survey').on('click', function(e) {
        $('#SurveyForm').submit();
    });
    
    $('#SurveyForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            resetFormErrors($(this).attr('name'));
            $('#msgs').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            
            $('#setupSurveyLoader').fadeIn(function(){
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
                                growl('Error!', 'The system survey could not be updated.', {time: 3000});
                            } else{ // no errors
                                growl('Success!', 'The system survey has been updated successfully.', {time: 3000});
                            }
                        }
                        catch(error){
                            //$('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#setupSurveyLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
}();