var Script = function () {
    // toggle button

    // date picker setup
    $(function(){
        window.prettyPrint && prettyPrint();
        $('input[name=required]').datepicker({
            format: 'dd/mm/yyyy'
        }).on('changeDate', function (e) {
            $('input[name=required]').datepicker('hide').blur();
        });
        
        $('#startDtIcon').on('click', function(e) {
            $('input[name=required]').datepicker('show');
        });
        
    });
    
    $('#btn-create-task').on('click', function(e) {
        e.preventDefault();
        $('#AddTaskForm').submit();
        return false;
    });
    
    
    $('#AddTaskForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            resetFormErrors($(this).attr('name'));
            $('#taskAddMsgs').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#taskAddLoader').fadeIn(function(){
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
                                        addFormError(i, obj.info[i]);
                                    }
                                }
                                
                                msgAlert('taskAddMsgs',{
                                    mode: 3,
                                    body: 'The task could not be added due to errors in the form (displayed in red).',
                                    empty: true
                                });
                                //scrollFormError('SetupForm', 210);
                            } else{ // no errors
                                $('#modalAddTask').modal('hide');
                                growl('Success!', 'The task has been added successfully.', {time: 3000});
                                
                                $('#AddTaskForm input').val('');
                                $('#AddTaskForm textarea').val('');
                                $('#AddTaskForm select').val('');
                                window.location.reload();
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#taskAddLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });

}();