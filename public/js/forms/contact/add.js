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
            
            var title = $('select[name=titleId]').val();
            var name = ((title=='')?'':$('select[name=titleId] option[value='+title+']').text()+' ')+$('input[name=forename]').val()+' '+$('input[name=surname]').val();
            var position = $('input[name=position]').val();
            var buyingtype = $('select[name=buyingtypeId]').val();
            var address = $('select[name=addressId]').val();
            var tel1 = $('input[name=telephone1]').val();
            var tel2 = $('input[name=telephone2]').val();
            var email = $('input[name=email]').val();
            
            if (position!='') {
                position = 'Position: '+position;
            }
            
            if (buyingtype!='') {
                position+=((position=='')?'':'<br />')+'Buying Type: '+$('select[name=buyingtypeId] option[value='+buyingtype+']').text();
            }
            
            if (tel1!=='') {
                tel1 = 'Telephone (primary): '+tel1;
            }
            
            if (tel2!='') {
                tel1+=((tel1=='')?'':'<br />')+'Telephone (additional): '+tel2;
            }
            
            if (email!='') {
                tel1+=((tel1=='')?'':'<br />')+'Email address: '+email;
            }
            
            var notes = '';
            var cnt = 0;
            $('#notes input').each(function(){
                if ($(this).val().length>0) {
                    cnt++;
                    notes+=((notes=='')?'':'<br />')+cnt+'. '+$(this).val();
                }
            });
            
            $('#confirm-name').text(($('input[name=surname]').val()=='')?'no surname entered (required)':name);
            $('#confirm-details').html((position=='')?'no details entered':position);
            $('#confirm-contact').html((tel1=='')?'no details entered':tel1);
            $('#confirm-address').text((address=='')?'no address selected':$('select[name=addressId] option[value='+address+']').text());
            $('#confirm-notes').html((notes=='')?'no notes entered':notes);
        }
        else {
            $('#pills').find('.pager .next').show();
            $('#pills').find('.pager .last').hide();
            $('#pills').find('.pager .create').hide();
            $('#pills').find('.pager .finish').hide();
        }
    }});

    
    $('#pills .create').click(function() {
        $('#ContactForm').submit();
    });
    
    $('#ContactForm').on('submit', function(e) {
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
                            var tab = 4;
                            var additional='';
                            if (obj.err == true) {
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        if (!addFormError(i, obj.info[i])) {
                                            additional+='<br>Information: '+obj.info[i];
                                        }
                                        if (tab>1){
                                            switch (i) {
                                                case 'forename': case 'surname': case 'titleId': case 'position': tab = 1; break;
                                                case 'telephone1': case 'telephone2': case 'email': case 'addressId': tab = 2; break;
                                                case 'buyingtypeId': case 'influence': case 'mode': case 'keywinresult': tab = 3; break;
                                                case 'notes': tab = 4; break;
                                            }
                                        }
                                    }
                                }
                                msgAlert('msgs',{
                                    title: 'Error!',
                                    mode: 3,
                                    body: 'The contact could not be added due to errors in the form (displayed in red).'+additional,
                                    empty: true
                                });
                                $('a[href=#pills-tab'+tab+']').tab('show'); return;
                            } else{ // no errors
                                //growl('Success!', 'The contact has been added successfully.', {time: 3000});
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
    
    $('#new-note').on('click', function(e) {
        e.preventDefault();
        var len = $('#notes .control-group').length;
        if (len>=10) {
            return false;
        }
        
        var inp = $('<input>', {type: 'text', name: 'note[]'})
                    .addClass('span6')
                    .attr('placeholder', 'Additional Note #'+(len+1));
        $('#notes').append(
            $('<div>')
            .addClass('control-group')
            .append(
                $('<div>')
                .addClass('controls')
                .append(
                    inp
                )
            )
        );

        inp.focus();

        return false;
    });

}();