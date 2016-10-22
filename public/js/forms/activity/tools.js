var ScriptActivityTools = function () {
    function resetActivityForm(full) {
        var dt = new Date();
        $('select[name=activityTypeId]').val(500); // general
        $('input[name=duration]').val(5); // 5 mins
        $("input[name=startDt]").datepicker("setValue", dt.ddmmyyyy()); // note ddmmyyyy and hhii are user defined prototypes
        $('input[name=startTm]').val(dt.hhii());
        
        // reset project if neccessary
        $('select[name=projectId]').val(0);
        
        // reset client if neccessary
        $('select[name=clientId]').val(0);
        
        if (full==true) {
            $('input[name=note]').val(''); // clear note
            if ($('#div-advanced-activity').is(':visible')) {
                $('#btn-advanced-activity').trigger('click');
            }
        }
    }
    
    
    $('#btn-activityQS').on('click', function(e) {
        e.preventDefault();
        
        var note = $('.chat-form input[name=note]').val();
        if (note==undefined) {
            return false;
        }
        
        if (!note.length) {
            return false;
        }
        
        if (!$('#div-advanced-activity').is(':visible')) {
            resetActivityForm(false);
        }

        $('form[name=ActivityAddForm]').submit();
    });
    
    $('form[name=ActivityAddForm]').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#quickSendLoader').fadeIn(function(){
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
                                growl('Failure!', 'The activity could not be added to the system', {});
                                /*if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        //addFormError(i, obj.info[i]);
                                    }
                                }/**/
                                
                            } else{ // no errors
                                growl('Success!', 'The activity has been added to the system log', {});
                                var eventInfo = '<a href="#">'+obj.info.activity.user+'</a> at '+obj.info.activity.start+'<br />Activity: '+obj.info.activity.type;
                                if (obj.info.activity.projectName != undefined) {
                                    eventInfo+=' for project'+(($('input[name=projectId]').attr('type')=='hidden')?'':' <a href="/client-'+obj.info.activity.clientId+'/project-'+obj.info.activity.projectId+'/">'+obj.info.activity.projectName+'</a>');
                                } else if (obj.info.activity.clientName != undefined) {
                                    eventInfo+=' for client'+(($('input[name=clientId]').attr('type')=='hidden')?'':' <a href="/client-'+obj.info.activity.clientId+'">'+obj.info.activity.clientName+'</a>');
                                }
                                var log = $('<div>').addClass('msg-time-chat').css({display:'none'}).append(
                                        $('<a>').addClass('message-img').append (
                                            $('<img>').prop('src', '/resources/user/avatar/'+(obj.info.activity.picture)+'.jpg').addClass('avatar')
                                        ),
                                        $('<div>').addClass('message-body msg-'+((obj.info.activity.me?'out':'in'))).append(
                                            $('<span>').addClass('arrow'),
                                            $('<div>').addClass('text').append(
                                                $('<p>').addClass('attribution').html(eventInfo),
                                                $('<p>').html(obj.info.activity.note)
                                            )
                                        )
                                    )
                                $('#activity-log').prepend(log);
                                
                                log.fadeIn('slow');
                                resetActivityForm(true);
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#quickSendLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    $('#btn-advanced-activity').click(function(e) {
        var hideMe = $('#div-advanced-activity').is(':visible');
        if (hideMe) {
            $('#div-advanced-activity').slideUp();
            $(this).find('i').removeClass('icon-chevron-sign-left').addClass('icon-chevron-sign-right');
        } else {
            $('#div-advanced-activity').slideDown();
            $(this).find('i').removeClass('icon-chevron-sign-right').addClass('icon-chevron-sign-left');
        }
    });
    
    //time picker
    $('input[name=startTm]').timepicker({
        minuteStep: 1,
        showSeconds: false,
        showMeridian: false
    });
    
    // date picker setup
    if (top.location != location) {
        top.location.href = document.location.href ;
    }
    $(function(){
        window.prettyPrint && prettyPrint();
        $('input[name=startDt]').datepicker({
            format: 'dd/mm/yyyy'
        }).on('changeDate', function (e) {
            $('input[name=startDt]').datepicker('hide').blur();
        });
        
        $('#startDtIcon').on('click', function(e) {
            $('input[name=startDt]').datepicker('show');
        });
        
    });

}();