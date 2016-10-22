var Script = function () {
    // toggle button
    window.prettyPrint && prettyPrint();
    $('#transition-percent-toggle-button').toggleButtons({
        transitionspeed: "500%",
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "success",
            disabled: "danger"
        }
    });
    
    //chosen select
    $(".chzn-select").chosen({search_contains: true}); 
    
    // setup table
    /*$('#products_tbl').dataTable({
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

    jQuery('#products_tbl_wrapper .dataTables_filter input').addClass("input-xlarge"); // modify table search input
    jQuery('#products_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
    /**/
    
    // setup spaces table
    $('#spaces_tbl').dataTable({
        sDom: "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
        sPaginationType: "bootstrap",
        //bStateSave: true,
        iDisplayLength:5,
        aLengthMenu: [[5, 10, 15, 20], [5, 10, 15, 20]],
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
            { 'bSortable': true}
        ]
    });

    jQuery('#spaces_tbl_wrapper .dataTables_filter input').addClass("input-medium"); // modify table search input
    jQuery('#spaces_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
    
    $(document).on('click', '.action-space-delete', function(e){
        e.preventDefault();
        $('#delSpaceName').val(($(this).attr('data-name').length?$(this).attr('data-name'):'Unnamed #'+$(this).attr('data-spaceId')));
        $('#frmDeleteSpace input[name=spaceId]').val($(this).attr('data-spaceId'));
        $('#modalDeleteSpace').modal({});
    });
    
    $('#btn-confirm-delete-space').on('click', function(e) {
        e.preventDefault();
        $('#frmDeleteSpace').submit();
        return false;
    });
    
    $('#frmDeleteSpace').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#spaceDeleteLoader').fadeIn(function(){
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
                            var additional='';
                            if (obj.err == true) {
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        if (!addFormError(i, obj.info[i])) {
                                            additional+='<br>Information: '+obj.info[i];
                                        }
                                        
                                    }
                                }
                                
                            } else{ // no errors
                                growl('Success!', 'The space has been deleted successfully and pricing synchronized.', {time: 3000});
                                $('#tab-building li.active').trigger('click');
                                $('#modalDeleteSpace').modal('hide');
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#spaceDeleteLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    $(document).on('click', '.action-space-copy', function(e){
        e.preventDefault();
        $('#frmCopySpace input[name=newSpaceName]').val($(this).attr('data-name'));
        $('#frmCopySpace input[name=spaceId]').val($(this).attr('data-spaceId'));
        $('#modalCopySpace').modal({});
    });
    
    $('#btn-confirm-copy-space').on('click', function(e) {
        e.preventDefault();
        $('#frmCopySpace').submit();
        return false;
    });
    
    $('#frmCopySpace').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#spaceCopyLoader').fadeIn(function(){
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
                            var additional='';
                            if (obj.err == true) {
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        if (!addFormError(i, obj.info[i])) {
                                            additional+='<br>Information: '+obj.info[i];
                                        }
                                        
                                    }
                                }
                                
                            } else{ // no errors
                                growl('Success!', 'The space has been duplicated successfully and pricing synchronized.<br /><a href="'+obj.url+'">Click here to edit space</a>', {time: 6000});
                                $('#tab-building li.active').trigger('click');
                                $('#modalCopySpace').modal('hide');
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#spaceCopyLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    
    $('#btn-create-space-dialog').on('click', function(e){
        e.preventDefault();
        $('input[name=name]').val('');
        $('#myModal3').modal({});
    });
    
    $('#btn-create-space').on('click', function(e){ 
        $('#SpaceCreateForm').submit();
    });
    
    $('#tab-building li:not(.disabled)').on('click', function(e) {
        $('#info-name').text('Building: '+$(this).attr('building-name'));
        $('#info-address').text('Address: '+$(this).attr('building-address'));
        $('#spaces_tbl').dataTable().fnClearTable();
        /*$('#spaces_tbl tbody').empty().append(
            $('<tr>').append(
                $('<td>').attr('colspan', 4).text('Please wait whilst space configuration loads ...')
            )
        );/**/

        findSpaces($(this).attr('building-id'));
    });
    
    $('#SpaceCreateForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
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
    
    
    $(document).on('click', '.action-space-edit', function(e) {
        e.preventDefault();
        var sid = $(this).attr('sid');
        if (sid == undefined) {
            return false;
        }
        
        document.location = $('#SpaceListForm').attr('action')+'space-'+sid+'/';
    });
    
    $('#tab-building li:not(.disabled):first-child').trigger('click');

    $('#btnSaveConfig').on('click', function(e) {
        try {
            var url = $('#formSaveConfig').attr('action');
            var params = $('#formSaveConfig').serialize();
            
            $('#saveConfigLoader').fadeIn(function(){
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
                                growl('Error!', 'The system could no be saved.', {time: 3000});
                            } else{ // no errors
                                $('#myModalSaveConfig').modal('hide');
                                growl('Success!', 'The system has been saved successfully.', {time: 3000});
                                $('#formSaveConfig input[name=name]').val('');
                                reloadConfigs();
                                //{$callback}(obj.aid);
                            }
                        }
                        catch(error){
                            
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#saveConfigLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
    });
    
    
    $('#btnLoadConfig').on('click', function(e) {
        try {
            var url = $('#formLoadConfig').attr('action');
            var params = $('#formLoadConfig').serialize();
            
            $('#loadConfigLoader').fadeIn(function(){
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
                                growl('Error!', 'The system could no be loaded.', {time: 3000});
                            } else{ // no errors
                                window.location.reload();
                            }
                        }
                        catch(error){
                            
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#loadConfigLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
    });
    
    

    $('#refresh-config').on('click', function(e) {
        reloadConfigs();
    });
    
    $('#optReapplyPricing').on('click', function(e){
        e.preventDefault();
        $('#modalReapplyPricing').modal();
        return false;
    });
                            
}();

function reloadConfigs() {
    try {
        var url = $('#formLoadConfig').attr('action').replace(/configload/,'configrefresh');
        var params = 'ts='+Math.round(new Date().getTime()/1000);
        $('#refresh-config').fadeOut();
        
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
                        var saves = $('select[name=saveId]');
                        saves.empty();
                        saves.append($('<option>').text('Please Select'));
                        for(var i in obj.saves){
                            var opt = $('<option>').val(obj.saves[i][0]).text(obj.saves[i][1]);
                            saves.append(opt);
                        }
                    }
                }
                catch(error){
                    $('#errors').html($('#errors').html()+error+'<br />');
                }
            },
            complete: function(jqXHR, textStatus){
                $('#refresh-config').fadeIn();
            }
        });

    } catch (ex) {

    }/**/

}

function findSpaces(building_id) {
    try {
        var url = $('#SpaceListForm').attr('action')+'spacelist/';
        var params = 'ts='+Math.round(new Date().getTime()/1000)+'&bid='+building_id;
        $('#buildingLoader').fadeIn(function(){
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
                            var tbl = $('#spaces_tbl');
                            //tbl.empty();
                            tbl.dataTable().fnClearTable();
                            for(var i in obj.spaces){
                                tbl.dataTable().fnAddData([
                                    '<a sid="'+obj.spaces[i].spaceId+'" href="javascript:" class="action-space-edit">'+((obj.spaces[i].name.length)?obj.spaces[i].name:'unnamed')+'</a>',
                                    (obj.spaces[i].quantity==undefined)?'0':obj.spaces[i].quantity,
                                    (obj.spaces[i].ppu==undefined)?'0':obj.spaces[i].ppu,
                                    '<div style="width:120px"><button sid="'+obj.spaces[i].spaceId+'" class="btn btn-primary action-space-edit"><i class="icon-pencil"></i></button>&nbsp;'+
                                    '<button sid="'+obj.spaces[i].spaceId+'" class="btn btn-success action-space-copy" data-spaceId="'+obj.spaces[i].spaceId+'" data-name="'+obj.spaces[i].name+'"><i class="icon-copy"></i></button>&nbsp;'+
                                    '<button sid="'+obj.spaces[i].spaceId+'" class="btn btn-danger action-space-delete" data-spaceId="'+obj.spaces[i].spaceId+'" data-name="'+obj.spaces[i].name+'"><i class="icon-remove"></i></button>'+
                                    '</div>']);
                            }
                            
                        }
                    }
                    catch(error){
                        $('#errors').html($('#errors').html()+error+'<br />');
                    }
                },
                complete: function(jqXHR, textStatus){
                    $('#buildingLoader').fadeOut(function(){});
                }
            });
        });

    } catch (ex) {

    }/**/
}