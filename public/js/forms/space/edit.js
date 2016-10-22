var Script = function () {
    //chosen select
    $(".chzn-select").chosen({search_contains: true}); 
    
    
    // setup table
    /*$('#system_tbl').dataTable({
        sDom: "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
        sPaginationType: "bootstrap",
        iDisplayLength:20,
        aLengthMenu: [[5, 10, 15, 20, -1], [5, 10, 15, 20, "All"]],
        oLanguage: {
            sLengthMenu: "_MENU_ per page",
            oPaginate: {
                sPrevious: "",
                sNext: ""
            }
        },
        "aoColumns": [
            null,
            null,
            { "sClass": "hidden-phone" },
            null,
            null,
            { "sClass": "hidden-phone" },
            { "sClass": "hidden-phone" },
            null,
            { 'bSortable': false },
            null
        ]
    });

    jQuery('#system_tbl_wrapper .dataTables_filter input').addClass("input-xlarge"); // modify table search input
    jQuery('#system_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
    /**/
    
    //refresh button
    $('#btn-refresh-space').on('click', function(e){ 
        window.location.reload();
    });
    
    //save button
    $('#btn-update-space').on('click', function(e){ 
        $('#SpaceCreateForm').submit();
    });
    
    $('#btnShowMoreSpaceDetails').on('click', function(e) {
       e.preventDefault();
       
       $('#spaceMoreDetails').show();
       $(this).hide();
       $('#btnHideMoreSpaceDetails').show();
       
       return false;
    });
    
    $('#btnHideMoreSpaceDetails').on('click', function(e) {
       e.preventDefault();
       $('#spaceMoreDetails').hide();
       $(this).hide();
       $('#btnShowMoreSpaceDetails').show();
       return false;
    });
    
    //save request
    $('#SpaceCreateForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            $('#msgs2').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#spaceLoader').fadeIn(function(){
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
                                        if (!addFormError(i, obj.info[i], 'SpaceCreateForm')) {
                                            additional+=obj.info[i]+'<br>';
                                        }
                                    }
                                }
                                
                                if (additional != '') {
                                    msgAlert('msgs2',{
                                        mode: 3,
                                        body: 'Error: '+additional,
                                        empty: true
                                    });
                                }
                                
                                
                            } else{ // no errors
                                growl('Success!', 'The space details have been updated.', {time: 3000});
                                //document.location = obj.url;
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#spaceLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    
    // Add new note to space
    $('#btn-add-note').on('click', function(e){
        e.preventDefault();
        $('textarea[name=note]').val('');
        $('#myModal4').modal({}).on('shown.bs.modal', function(e) {$('textarea[name=note]').focus();});
    });

    //save button
    $('#btn-save-note').on('click', function(e){ 
        $('#AddNoteForm').submit();
    });
    
    
    //save request
    $('#AddNoteForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            $('#msgs3').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#noteLoader').fadeIn(function(){
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
                                            additional+=obj.info[i]+'<br>';
                                        }
                                    }
                                }
                                
                                if (additional != '') {
                                    msgAlert('msgs3',{
                                        mode: 3,
                                        body: 'Error: '+additional,
                                        empty: true
                                    });
                                }
                                
                                
                            } else{ // no errors
                                $('#space-notes').append($('<div>').addClass('note').html('<strong>Note:</strong> '+$('textarea[name=note]').val()+' <a class="delete-note" data-index="'+obj.id+'" href="javascript:"><i class="icon-remove"></i></a>'));
                                $('#space-notes #nonote').remove();
                                $('#myModal4').modal('hide');
                                growl('Success!', 'The note has been successfully added to the space.', {time: 3000});
                                
                                //document.location = obj.url;
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#noteLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    $(document).on('click', '.delete-note', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            var parent = $(this).parent();
            var url = $('#DeleteNoteForm').attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&nid='+$(this).attr('data-index');
            $('#noteDeleteLoader').fadeIn(function(){
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
                                
                            } else{ // no errors
                                parent.remove();
                                if($('#space-notes .note').length<1) {
                                    //console.log('yes!');
                                    $('#space-notes').append($('<div>', {id: 'nonote'}).text('No notes added to space'));
                                }
                                growl('Success!', 'The note has been deleted from the space.', {time: 3000});
                            }
                        }
                        catch(error){ }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#noteDeleteLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    $('#btn-create-space-dialog').on('click', function(e){
        e.preventDefault();
        $('#SpaceCreateNewForm input[name=name]').val('');
        $('#myModal3').modal({});
    });
    
    $('#btn-create-space').on('click', function(e){ 
        $('#SpaceCreateNewForm').submit();
    });
    
    $('#SpaceCreateNewForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            $('#msgs4').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#spaceLoader').fadeIn(function(){
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
                                        if (!addFormError(i, obj.info[i], 'SpaceCreateNewForm')) {
                                            additional+='<br>Information: '+obj.info[i];
                                        }
                                    }
                                }
                                
                                if (additional != '') {
                                    msgAlert('msgs4',{
                                        mode: 3,
                                        body: 'Error: '+additional,
                                        empty: true
                                    });
                                }
                                
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
                        $('#spaceLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
}();