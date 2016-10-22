var Script = function () {
    var playing = false;
    function focus() {
        $('#serialId').focus();
    }
    
    function setSerialMessage(msg) {
        $('#serialMsg').text(msg);
    }
    
    $('#serialId').focus();
            
    $('#serialId').on('blur', function(e){
        if (playing) {
            setTimeout(focus(),100);
        }
    });/**/
    
    $("#serialId").on('keypress', function(e) {
        if (!playing) return;
        if (e.keyCode == 13) {	
            var code = $(this).val();
            try {
                if (code.match(/^sp[\d]+$/i)) {
                    try {
                        resetFormErrors($(this).attr('name'));
                        var params = 'ts='+Math.round(new Date().getTime()/1000)+'&spaceId='+code.replace(/^sp([\d]+)$/i, '$1');
                        $('#serialLoader').fadeIn(function(){
                            $.ajax({
                                type: 'POST',
                                url: '/assets/find/',
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
                                            $('#details-space').text('None Selected');
                                            $('#details-building').text('None Selected');
                                            $('#details-project').text('None Selected');
                                            $('#details-client').text('None Selected');
                                            $('#spaceId').val('');
                                            setSerialMessage('Error: '+obj.info.ex);
                                        } else{ // no errors
                                            $('#details-space').html('<a target="_tab" href="/client-'+obj.space.project.client.id+'/project-'+obj.space.project.id+'/space-'+obj.space.id+'/">'+obj.space.name+'</a>');
                                            $('#details-building').text(obj.space.building.name);
                                            $('#details-project').html('<a target="_tab" href="/client-'+obj.space.project.client.id+'/project-'+obj.space.project.id+'/">'+obj.space.project.name+'</a>');
                                            $('#details-client').html('<a target="_tab" href="/client-'+obj.space.project.client.id+'/">'+obj.space.project.client.name+'</a>');
                                            $('#spaceId').val(obj.space.id);
                                            var systems = $('#systemId');
                                            systems.empty();
                                            if (obj.space.systems.length>1) {
                                                systems.append($('<option>').val('').text('Not specified'));
                                            }
                                            for (var i in obj.space.systems) {
                                                //systems.append($('<option>').val(1).text('moo'));
                                                systems.append($('<option>').val(obj.space.systems[i].systemId).text(obj.space.systems[i].model));
                                            }
                                            setSerialMessage('Space set to "'+obj.space.name+'" in project "'+obj.space.project.name+'"');
                                            //growl('Success!', 'The space has been deleted successfully and pricing synchronized.', {time: 3000});
                                        }
                                    }
                                    catch(error){
                                        $('#errors').html($('#errors').html()+error+'<br />');
                                    }
                                },
                                complete: function(jqXHR, textStatus){
                                    $('#serialLoader').fadeOut(function(){});
                                }
                            });
                        });

                    } catch (ex) {

                    }/**/
                    
                } else if (code.match(/^[\d]+$/)) {
                    try {
                        resetFormErrors($(this).attr('name'));
                        var spaceId = $('#spaceId').val();
                        if (!spaceId.match(/^[\d]+$/)) {
                            setSerialMessage('Error: Space has not been scanned in');
                            return false;
                        }
                        var params = 'ts='+Math.round(new Date().getTime()/1000)+'&spaceId='+spaceId+'&serialId='+code+'&systemId='+$('#systemId').val();
                        $('#serialLoader').fadeIn(function(){
                            $.ajax({
                                type: 'POST',
                                url: '/assets/scan/',
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
                                            setSerialMessage('Error: '+obj.info.ex);
                                        } else{ // no errors
                                            setSerialMessage('Added Serial: '+obj.serialId);
                                        }
                                    }
                                    catch(error){
                                        $('#errors').html($('#errors').html()+error+'<br />');
                                    }
                                },
                                complete: function(jqXHR, textStatus){
                                    $('#serialLoader').fadeOut(function(){});
                                }
                            });
                        });

                    } catch (ex) {

                    }/**/
                }
            } finally {
                ($(this).val(''));
            }
            e.preventDefault();	//Here to allow keyinput and prvent IE6 errors/**/
        }
    });
    
    $('#btnPlay').on('click', function(e) {
        e.preventDefault();
        if (playing) return;
        
        $('#serialId').removeAttr('disabled');
        playing = true;
        focus();
        setSerialMessage('Scanning Started');
        
        return false;
    });

    $('#btnStop').on('click', function(e) {
        e.preventDefault();
        if (!playing) return;
        
        playing = false;
        $('#serialId').attr('disabled', true);
        setSerialMessage('Scanning Stopped');
        return false;
    });

}();