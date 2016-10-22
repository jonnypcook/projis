var Script = function () {
    // begin first table
    $('#contacts_tbl').dataTable({
        sDom: "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
        sPaginationType: "bootstrap",
        iDisplayLength:5,
        aLengthMenu: [[5, 10, 15, 20], [5, 10, 15, 20]],
        oLanguage: {
            sLengthMenu: "_MENU_ per page",
            oPaginate: {
                sPrevious: "",
                sNext: ""
            }
        },
        aoColumnDefs: [{
            'bSortable': false,
            'aTargets': [0]
        }]
    });

    jQuery('#contacts_tbl_wrapper .dataTables_filter input').addClass("input-xlarge"); // modify table search input
    jQuery('#contacts_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
    
    
    $(document).on('click', '#contacts_tbl tbody tr', function(e) {
        $('#contacts_tbl tbody tr.active').removeClass('active');
        $(this).addClass('active');
        
        try {
            var cid = $(this).attr('cid');
            loadContact(cid);

        } catch (ex) {

        }/**/
    });
    
    $('#ContactForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            var cid = $('#contactId').val();
            if (cid==undefined) {
                return;
            }
            if (cid=='') {
                return;
            }
            resetFormErrors($(this).attr('name'));
            $('#msgs').empty();
            var url = $('#ContactForm').attr('action').replace(/[%][c]/, cid);
            var params = 'save=1&ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
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
                                    body: 'The contact could not be updated due to errors in the form (displayed in red).'+additional,
                                    empty: true
                                });
                                $('a[href=#widget_tab'+tab+']').tab('show'); 
                                return;
                            } else{ // no errors
                                growl('Success!', 'The contact has been updated successfully.', {time: 3000});
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
    
    $('#btn-update-contact').on('click', function(e) {
        $('#ContactForm').submit();
    });
    
    $('#btn-refresh-contact').on('click', function(e) {
        var cid = $('#contactId').val();
        if (cid==undefined) {
            return;
        }
        if (cid=='') {
            return;
        }
        loadContact(cid);
    });
    
    $('#btn-new-config').on('click', function(e) {
        e.preventDefault();
        newAddress();
    });
    
    $('#btn-edit-address').on('click', function(e) {
        e.preventDefault();
        var addressId = $('#ContactForm select[name=addressId]').val();
        editAddress(addressId);
    });
    
    $('select[name=addressId]').on('change', function(e) {
        if($(this).val()=='') {
            $('#btn-edit-address').hide();
        } else {
            $('#btn-edit-address').show();
        }
    });
    
    
    
}();

function loadContact(cid) {
    //console.log('loading ...');
    resetFormErrors('ContactForm');
    $('#msgs').empty();
    try {
        var url = $('#ContactForm').attr('action').replace(/[%][c]/, cid);
        var params = 'ts='+Math.round(new Date().getTime()/1000);
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
                        if (obj.err == true) {

                        } else{ // no errors
                            obj.info.live = true;
                            setContact(obj.info);
                            //growl('Success!', 'The contact has been added successfully.', {time: 3000});
                            //document.location = obj.url;
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
}

function setContact(config) {
    $('#contactId').val(config.contactId?config.contactId:'');
    $('#contact-name').text((config.forename?config.forename:'')+' '+(config.surname?config.surname:''));
    $('select[name=titleId]').val((config.titleId?config.titleId:''));
    $('select[name=addressId]').val((config.addressId?config.addressId:''));
    $('select[name=buyingtypeId]').val((config.buyingtypeId?config.buyingtypeId:''));
    $('select[name=influenceId]').val((config.influence?config.influence:''));
    $('select[name=modeId]').val((config.mode?config.mode:''));

    $('input[name=forename]').val((config.forename?config.forename:''));
    $('input[name=surname]').val((config.surname?config.surname:''));
    $('input[name=position]').val((config.position?config.position:''));
    $('input[name=telephone1]').val((config.telephone1?config.telephone1:''));
    $('input[name=telephone2]').val((config.telephone2?config.telephone2:''));
    $('input[name=email]').val((config.email?config.email:''));
    $('textarea[name=keywinresult]').val((config.keywinresult?config.keywinresult:''));
    
    
    
    var cnt = 0;
    var notes = $('#notes');
    notes.empty();
    if (config.notes) {
        for(var i in config.notes){
            cnt++;
            notes.append(
                $('<div>')
                .addClass('control-group')
                .append(
                    $('<div>')
                    .addClass('controls')
                    .append(
                        $('<input>', {type: 'text', name: 'note[]'})
                        .addClass('span6')
                        .attr('placeholder', 'Additional Note #'+(cnt))
                        .val(config.notes[i])
                    )
                )
            );
        }
    }
    
    for (i=cnt; i<3; i++) {
        cnt++;
        notes.append(
                $('<div>')
                .addClass('control-group')
                .append(
                    $('<div>')
                    .addClass('controls')
                    .append(
                        $('<input>', {type: 'text', name: 'note[]'})
                        .addClass('span6')
                        .attr('placeholder', 'Additional Note #'+(cnt))
                    )
                )
            );
    }
    
    $('a[href=#widget_tab1]').tab('show'); 
    
    if (config.live) {
        $('#msgLoader').fadeOut();
        $('#btn-update-contact')
                .removeClass('disabled')
                .removeAttr('disabled');
        $('#btn-refresh-contact')
                .removeClass('disabled')
                .removeAttr('disabled');
    } else {
        $('#contact-name').text('None Selected');
        $('#msgLoader').fadeIn();
        $('#btn-update-contact')
                .addClass('disabled')
                .attr('disabled', true);
        $('#btn-refresh-contact')
                .addClass('disabled')
                .attr('disabled', true);
    }
}