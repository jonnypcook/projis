var Script = function () {
    //toggle button
    var fullTaskLoaded = false;
    /*$('#header_inbox_bar').on('click', function(e) {
        if (fullMailLoaded) return;
        if (!$('#mail-items').is(':visible')) {
            $('ul#mail-items li:not(.mailCount)').remove();
            $('ul#mail-items').append(
                $('<li>').append(
                    $('<a>').text('Please wait loading ...')

                )
            );
            fullMailLoaded = true;
            loadEmails(2);
        }
    });/**/
    
    function loadTasks() {
        try {
            var url = '/task/preview/';
            var params = 'ts='+Math.round(new Date().getTime()/1000);
            
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
                        // an error has been detected
                        if (obj.err == true) {
                        } else{ // no errors
                            if (obj.count<=0) {
                                $('.task-count').hide().text(obj.iTotalDisplayRecords)
                            } else {
                                $('.task-count').show().text(obj.iTotalDisplayRecords)
                            }

                            var ul = $('ul#task-items');
                            ul.empty().append(
                                $('<li>').append(
                                    $('<p>').text('You have '+obj.iTotalDisplayRecords+' active tasks')

                                )
                            );
                            
                            for (var i in obj.aaData) {
                                var description = obj.aaData[i][2]+' Task #'+obj.aaData[i][0];
                                if (obj.aaData[i][3].length) {
                                    description+=' - '+((obj.aaData[i][3].length>100)?obj.aaData[i][3].substring(0,100)+'...':obj.aaData[i][3]);
                                }
                                
                                var progressCls = 'progress-';
                                if (obj.aaData[i][5]<10) {
                                    progressCls+='danger';
                                } else if (obj.aaData[i][5]<30) {
                                    progressCls+='warning';
                                } else if (obj.aaData[i][5]<50) {
                                    progressCls+='info';
                                } else if (obj.aaData[i][5]<80) {
                                    progressCls+='striped';
                                } else {
                                    progressCls+='success';
                                }/**/
                                
                                ul.append(
                                    $('<li>').append(
                                        $('<a>').attr('href','/task-'+obj.aaData[i][0]+'/').append(
                                            $('<div>').addClass('task-info').append(
                                                $('<div>').addClass('desc').text(description),
                                                $('<div>').addClass('percent').text(obj.aaData[i][5]+'%')
                                            ),
                                            $('<div>').addClass('progress progress-striped '+progressCls+' active no-margin-bot').append(
                                                $('<div>').addClass('bar').css({width: obj.aaData[i][5]+'%'})
                                            )
                                        )
                                    )
                                );
                            }
                            ul.append(
                                $('<li>').append(
                                    $('<a>').attr('href','/task/').text('View all tasks')
                                )
                            );

                        }
                    }
                    catch(error){
                        $('#errors').html($('#errors').html()+error+'<br />');
                    }
                },
                complete: function(jqXHR, textStatus){
                    $('#setupLoader').fadeOut(function(){});
                }
            });

        } catch (ex) {

        }/**/
    }
    
    
    loadTasks();

}();