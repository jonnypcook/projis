var Script = function () {

    //toggle button
    window.prettyPrint && prettyPrint();

    $('#text-toggle-button').toggleButtons({
        width: 160,
        label: {
            enabled: "Yes",
            disabled: "No"
        },
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "success",
            disabled: "info"
        }
    });

    $('#notification-toggle-button').toggleButtons({
        width: 160,
        label: {
            enabled: "Yes",
            disabled: "No"
        },
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "success",
            disabled: "danger"
        }
    });

    //chosen select
    $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});

    //tag input
    $('input[name=usersBespoke]').tagsInput({
        width:'auto', 
        defaultText:'add email',
    });
    
    //time picker
    $('input[name=calStartTm]').timepicker({
        minuteStep: 1,
        showSeconds: false,
        showMeridian: false,
        defaultTime: false
    });
    
    $('input[name=calEndTm]').timepicker({
        minuteStep: 1,
        showSeconds: false,
        showMeridian: false,
        defaultTime: false
    });
    
    // date picker setup
    if (top.location != location) {
        top.location.href = document.location.href ;
    }
    
    $('input[name=calStartDt]').datepicker({
        format: 'dd/mm/yyyy'
    }).on('changeDate', function (e) {
        $('input[name=calStartDt]').datepicker('hide').blur();
    });
        
    $('input[name=calEndDt]').datepicker({
        format: 'dd/mm/yyyy'
    }).on('changeDate', function (e) {
        $('input[name=calEndDt]').datepicker('hide').blur();
    });
        
   
   $('#btnAddAdvancedEvent').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#CalendarEventAdvancedAddForm').submit();
        
   });
   
   $('#btnCancelAdvancedEvent').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#modalDeleteEvent').modal();
        
   });
   
   $('#btn-confirm-delete').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#CalendarEventAdvancedDeleteForm').submit();
   });
   
   $('input[name=allday]').on('change', function(e) {
        e.preventDefault();

        $('input[name=calStartTm]').attr('disabled', $(this).is(':checked'));
        $('input[name=calEndTm]').attr('disabled', $(this).is(':checked'));
       
        return false;
   });
   
   
   
   $('#CalendarEventAdvancedAddForm').on('submit', function(e){
        try {
            e.preventDefault();
            
            resetFormErrors($(this).attr('name'));
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();

            $('#advancedEventLoader').fadeIn(function(){
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
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        addFormError(i, obj.info[i]);
                                    }
                                }
                            } else{ // no errors
                                document.location = $('#btnClose').attr('href');
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#advancedEventLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
       
        return false;
    });
    
    
    $('#CalendarEventAdvancedDeleteForm').on('submit', function(e){
        try {
            e.preventDefault();
            
            resetFormErrors($(this).attr('name'));
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();

            $('#systemDeleteLoader').fadeIn(function(){
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
                            if (obj.err == true) {
                                window.location.reload();
                            } else{ // no errors
                                document.location = $('#btnClose').attr('href');
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#systemDeleteLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
       
        return false;
    });
        

}();