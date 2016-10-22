var Script = function () {
    
    $('.chbx-building').on('change', function(e) {
        var checked = $(this).is(':checked');
        $('#tbl-export-building-'+$(this).attr('data-buildingId')+' .chbx-system').attr('checked', checked);
        $('#tbl-export-building-'+$(this).attr('data-buildingId')+' .chbx-space').attr('checked', checked);
    });
    
    $('.chbx-space').on('change', function(e) {
        var checked = $(this).is(':checked');
        $('#tbl-export-system-'+$(this).attr('data-spaceId')+' .chbx-system').attr('checked', checked);
    });
    
    $('.tbl-export-system tbody tr td:not(:first-child)').on('click', function(e){
       e.preventDefault();
       var chkbx = $(this).parent().find('input[type=checkbox]');
       chkbx.attr('checked', !chkbx.is(':checked'));
       chkbx.trigger('change');
       return false;
    });
    
    $('#btn-select-all').on('click', function(e) {
        e.preventDefault();
        $('.chbx-system, .chbx-building, .chbx-space').attr('checked', true);
        
        return false;
    });
    
    $('#btn-deselect-all').on('click', function(e) {
        e.preventDefault();
        $('.chbx-system, .chbx-building, .chbx-space').attr('checked', false);
        
        return false;
    });

    
    $('.chbx-system').on('change', function(e) {
        var checked = $(this).is(':checked');
        var spaceId = $(this).attr('data-spaceId');
        var buildingId = $(this).attr('data-buildingId');

        var tbl = $('#tbl-export-system-'+spaceId);
        
        var cnt = tbl.find('.chbx-system').length;
        var chk = tbl.find('.chbx-system:checked').length;
        
        var updated = false;
        
        if (cnt==chk) {
            if (!$('#chbx-space-'+spaceId).is(':checked')) {
                $('#chbx-space-'+spaceId).attr('checked', true);
                updated = true;
            }
        } else {
            if ($('#chbx-space-'+spaceId).is(':checked')) {
                $('#chbx-space-'+spaceId).attr('checked', false);
                updated = true;
            }
        } 
        
        if (updated) {
            tbl = $('#tbl-export-building-'+buildingId);
            cnt = tbl.find('.chbx-system').length;
            chk = tbl.find('.chbx-system:checked').length;
            if (cnt==chk) {
                if (!$('#chbx-building-'+buildingId).is(':checked')) {
                    $('#chbx-building-'+buildingId).attr('checked', true);
                }
            } else {
                if ($('#chbx-building-'+buildingId).is(':checked')) {
                    $('#chbx-building-'+buildingId).attr('checked', false);
                }
            }
        }
        
        
    });
    
    $('#btn-confirm-exportproject').on('click', function(e) {
        e.preventDefault();
        $('#ExportProjectForm').submit();
        
        return false;
    });
    
    $('#ExportProjectForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        try {
            resetFormErrors($(this).attr('name'));
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize()+'&'+$('#exportSystemDetails').serialize();
            $('#exportProjectLoader').fadeIn(function(){
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
                                    }
                                }
                                $('a[href=#pills-tab'+tab+']').tab('show'); return;
                            } else{ // no errors
                                growl('Success!', 'The new project has been created successfully.<br /><a href="'+obj.url+'">Click to view the new project <i class="icon-double-angle-right"></i></a>', {sticky: true});
                                $('#modalExportProject').modal('hide');
                                $('#ExportProjectForm input').val('');
                            }
                        }
                        catch(error){
                            
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#exportProjectLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });

}();