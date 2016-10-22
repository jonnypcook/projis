var Script = function () {
    function resetSerialForm() {
        $('#SerialForm input[name=serialStart]').val('');
        $('#SerialForm input[name=range]').val('1');
        $('#serialEnd').text('-');
        $('#SerialForm select[name=products]').val('');
        $('#sel-system').empty().append($('<option>').val('').text('Select Placement (optional)'));
    }
    
    // last button press
    $('#btn-add-serial-modal').on('click', function(e) {
        e.preventDefault();
        resetSerialForm();
        $('#modalSerialAdd').modal();
    });
    
    $('#SerialForm input[name=serialStart]').on('change', function(e){
        e.preventDefault();
        calculateLastSerial();
    });
    
    $('#SerialForm input[name=range]').on('change', function(e){
        e.preventDefault();
        calculateLastSerial();
    });
    
    function calculateLastSerial() {
        try {
            var serialId = parseInt($('#SerialForm input[name=serialStart]').val());
            var range = parseInt($('#SerialForm input[name=range]').val())-1;
            if(isNaN(serialId) || isNaN(range)) {
                throw "not a number";
            } 
            $('#serialEnd').text((serialId+range));
        } catch (e) {
            $('#serialEnd').text('-');
        }
    }
    
    
    $('#SerialForm select[name=products]').on('change', function(e) {
        e.preventDefault();


        if ($(this).val() == "") {
            return false;
        }

        findSystems($(this).val(), $('#SerialForm input[name=projectId]').val());
    });   
    
    function findSystems(productId, projectId) {
        try {
            var url = '/product/findspaceconfig/';
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&productId='+productId+'&projectId='+projectId;
            
            var selSystem = $('#sel-system');
            selSystem.empty();
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
                            if (obj.data.length>1) {
                                selSystem.append($('<option>').val('').text('Select Placement (optional)'));
                            }
                            for (var i in obj.data) {
                                selSystem.append(
                                    $('<option>').val(obj.data[i]['systemId']).text(obj.data[i]['quantity']+' units '+(obj.data[i]['root']?'(not in space)':'in space "'+obj.data[i]['name']+'"'))
                                )
                            }
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
    
    
    $('#btn-confirm-add-serials').on('click', function(e) {
        e.preventDefault();
        $('#SerialForm').submit();
        return false;
    });

    $('#SerialForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            resetFormErrors($(this).attr('name'));
            $('#serialMsgs').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();

            $('#serialAddLoader').fadeIn(function(){
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
                                var msgs = '';
                                
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        if (!addFormError(i, obj.info[i])) {
                                            msgs+=obj.info[i]+'<br />';
                                        }
                                    }
                                }
                                
                                if (msgs.length) {
                                    msgAlert('serialMsgs',{
                                        mode: 3,
                                        body: msgs,
                                        empty: true
                                    });
                                }
                                //scrollFormError('SetupForm', 210);
                            } else{ // no errors
                                growl('Success!', 'The serial range has been added successfully.', {time: 3000});
                                resetSerialForm();
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#serialAddLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;    
    });
}();