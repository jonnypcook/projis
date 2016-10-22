var Script = function () {
    //toggle button
    var fullMailLoaded = false;
    $('#header_inbox_bar').on('click', function(e) {
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
    });
    
    function loadEmails(mode) {
        try {
            var url = '/dashboard/mail/';
            var params = 'ts='+Math.round(new Date().getTime()/1000);
            
            if (mode==2) {
                params+='&preview=1';
            }
            
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
                                $('.mail-count').text(obj.count)
                            } else {
                                $('.mail-count').show().text(obj.count)
                            }

                            var ul = $('ul#mail-items');
                            ul.empty().append(
                                $('<li class="mailCount">').append(
                                    $('<p>').text('You have '+obj.count+' new messages')

                                )
                            );
                            for (var i in obj.msg) {
                                for (var j in obj.msg[i]) {
                                    ul.append(
                                        $('<li>').append(
                                            $('<a>').append(
                                                $('<span>').addClass('subject').append(
                                                    $('<span>').addClass('from').text(obj.msg[i][j].from),
                                                    $('<span>').addClass('time').text(obj.msg[i][j].date)
                                                ),
                                                $('<span>').addClass('message').text(obj.msg[i][j].subject)
                                            )
                                        )
                                    );
                                }
                            }
                            ul.append(
                                $('<li>').append(
                                    $('<a>').attr('href','#').text('See all messages')

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
    
    
    loadEmails(1);

}();