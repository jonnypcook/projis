var Script = function () {

    $('#pills').bootstrapWizard({'tabClass': 'nav nav-pills', 'debug': false, onShow: function(tab, navigation, index) {
        //console.log('onShow');
    }, onNext: function(tab, navigation, index) {
        //console.log(navigation.html());
    }, onPrevious: function(tab, navigation, index) {
        //console.log('onPrevious');
    }, onLast: function(tab, navigation, index) {
        //console.log('onLast');
    }, onTabClick: function(tab, navigation, index) {
        //console.log('boosh');
        //return false;
    }, onTabShow: function(tab, navigation, index) {
        //console.log('onTabShow');
        var $total = navigation.find('li').length;
        var $current = index+1;
        var $percent = ($current/$total) * 100;
        $('#pills').find('.bar').css({width:$percent+'%'});
        if($current >= $total) {
            $('#pills').find('.pager .next').hide();
            $('#pills').find('.pager .create').show();
            $('#pills').find('.pager .create').removeClass('disabled');
            
            var name = $('input[name=name]').val();
            var notes = $('textarea[name=notes]').val();
            var address = $('select[name=addressId]').val();
            
            $('#confirm-name').text((name=='')?'no name entered (required)':name);
            $('#confirm-notes').text((notes=='')?'no notes entered':notes);
            $('#confirm-address').text((address=='')?'no address selected':$('select[name=addressId] option[value='+address+']').text());
        }
        else {
            $('#pills').find('.pager .next').show();
            $('#pills').find('.pager .last').hide();
            $('#pills').find('.pager .create').hide();
            $('#pills').find('.pager .finish').hide();
        }
    }});

    
    $('#pills .create').click(function() {
        $('#BuildingCreateForm').submit();
    });
    
    $('#BuildingCreateForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            $('#msgs').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#setupLoader').fadeIn(function(){
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
                                            additional+='<br>Information: '+obj.info[i];
                                        }
                                        if (tab>1){
                                            switch (i) {
                                                case 'name': case 'notes': tab = 1; break;
                                                case 'addressId': tab = 2; break;
                                            }
                                        }
                                    }
                                }
                                msgAlert('msgs',{
                                    title: 'Error!',
                                    mode: 3,
                                    body: 'The building configuration could not be added due to errors in the form (displayed in red).'+additional,
                                    empty: true
                                });
                                $('a[href=#pills-tab'+tab+']').tab('show'); return;
                            } else{ // no errors
                                //growl('Success!', 'The building has been added successfully.', {time: 3000});
                                document.location = obj.url;
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
            });

        } catch (ex) {

        }/**/
        return false;
    });
    

}();