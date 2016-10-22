var Script = function () {
    $(".chzn-select").chosen(); 
    //toggle button

    window.prettyPrint && prettyPrint();

    
    
    $('input[name=OrderDate]').datepicker({
        format: 'dd/mm/yyyy'
    }).on('changeDate', function (e) {
        $('input[name=OrderDate]').datepicker('hide').blur();
    });

    $('#startDtIcon').on('click', function(e) {
        $('input[name=OrderDate]').datepicker('show');
    });

    $('.viability-date').datepicker({
        format: 'dd/mm/yyyy'
    }).on('changeDate', function (e) {
        $(this).datepicker('hide').blur();
    });

    $('.viability-field').on('change', function(e) {
       e.preventDefault();
       var icon = $(this).parent().find('.viability-icon');
       if ($(this).val()=='') {
           icon.removeClass('label-important label-success label-warning label-info').addClass('label-info').html('<i class="icon-arrow-left"></i>');
       } else {
           var opt = $(this).find('option[value='+$(this).val()+']');

           switch (opt.attr('data-flag')) {
               case '0':
                   icon.removeClass('label-important label-success label-warning label-info').addClass('label-success').html('<i class="icon-ok"></i>');
                   break;
               case '1':
                   icon.removeClass('label-important label-success label-warning label-info').addClass('label-warning').html('<i class="icon-warning-sign"></i>');
                   break;
               case '2': ;
                   icon.removeClass('label-important label-success label-warning label-info').addClass('label-important').html('<i class="icon-warning-sign"></i>');
                   break;
           }

       }

       calculateScore();

    });
    
    function calculateScore() {
        var total = 0;
        var unanswered = 0;
        $('.viability-field').each(function() {
            if ($(this).val()=='') {
                unanswered++;
            } else {
                var val = $(this).find('option[value='+$(this).val()+']').attr('data-score');
                if (val!=undefined) {
                    total+=parseFloat(val);
                }
            }
        });
        
        $('.viability-score').text('Score: '+total);
    }
    
    function setTabButtons (tab, suffix, max) {
        if (tab > 1) {
            $('#btn-last'+suffix).removeAttr('disabled');
        } else if (tab == 1) {
            $('#btn-last'+suffix).attr('disabled','disabled');
        } 

        if (tab == max) {
            $('#btn-next'+suffix).attr('disabled','disabled');
        } else if (tab < max) {
            $('#btn-next'+suffix).removeAttr('disabled');
        }
    }
    
    
    // next button press
    $('#btn-next-bs').on('click', function(e) {
        e.preventDefault();
        var activeTab = $("ul#tabsProjectBluePaper li.active a").attr('data-number');
        if (activeTab == undefined) {
            return false;
        }
        
        activeTab = parseInt(activeTab);
        var nextTab = (activeTab<5)?activeTab+1:activeTab;
        
        if (activeTab != nextTab) {
            setTabButtons (nextTab, '-bs', 5);
            $('a[href=#widgetBS_tab'+nextTab+']').tab('show');
        }
        
    });
    
    // last button press
    $('#btn-last-bs').on('click', function(e) {
        e.preventDefault();
        var activeTab = $("ul#tabsProjectBluePaper li.active a").attr('data-number');
        if (activeTab == undefined) {
            return false;
        }
        
        activeTab = parseInt(activeTab);
        var nextTab = (activeTab>1)?activeTab-1:activeTab;
        
        if (activeTab != nextTab) {
            setTabButtons (nextTab, '-bs', 5);
            $('a[href=#widgetBS_tab'+nextTab+']').tab('show');
        }
        
    });
    
    $('#btn-modify-bs').on('click', function(e) {
        $('#BlueSheetForm1').submit();
    });
    
    $('#BlueSheetForm1').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            resetFormErrors($(this).attr('name'));
            $('#msgs').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize()+'&'+$('#BlueSheetForm3').serialize()+'&'+$('#BlueSheetForm4').serialize()+'&'+$('#BlueSheetForm5').serialize();
            
            $('#setupBSLoader').fadeIn(function(){
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
                                growl('Error!', 'The blue sheet configuration could not be updated.', {time: 3000});
                                /*msgAlert('msgs',{
                                    title: 'Error!',
                                    mode: 3,
                                    body: 'The project configuration could not be updated due to errors in the form (displayed in red).',
                                    empty: true
                                });/**/
                                //scrollFormError('SetupForm', 210);
                            } else{ // no errors
                                growl('Success!', 'The blue sheet configuration has been updated successfully.', {time: 3000});
                                /*msgAlert('msgs',{
                                    title: 'Success!',
                                    mode: 1,
                                    body: 'The project configuration has been updated successfully.',
                                    empty: true
                                });/**/
                            }
                        }
                        catch(error){
                            //$('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#setupBSLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    
    $(document).on('click', '#tbl-competitors tbody tr:not(.disabled)', function(e) {
        e.preventDefault();
        var cid = $(this).attr('data-cid');
        if (cid==undefined) {
            return false;
        }
        
        $('#tbl-competitors tbody tr').removeClass('active');
        $(this).addClass('active');
        
        findCompetitor(cid);
    });
    
    $('#add-strength').on('click', function(e) {
        e.preventDefault();
        var len = $('#sec-competitor-strengths input').length;
        var input = $('<input>', {
                type: 'text',
                name: 'strengths[]'
            })
            .attr('placeholder', 'Strength #'+(len+1))
            .addClass('span12');
        $('#sec-competitor-strengths p').remove();
        $('#sec-competitor-strengths').append (
            input,
            $('<div>').addClass('space5')
        );

        input.focus();
        return false;
    });
    
    $('#add-weakness').on('click', function(e) {
        e.preventDefault();
        $('#sec-competitor-weaknesses p').remove();
        var len = $('#sec-competitor-weaknesses input').length;
        var input = $('<input>', {
                type: 'text',
                name: 'weaknesses[]'
            })
            .attr('placeholder', 'Weakness #'+(len+1))
            .addClass('span12');
        $('#sec-competitor-weaknesses').append (
            input,
            $('<div>').addClass('space5')
        );

        input.focus();
        return false;
    });
    
    $('#btn-competitors-delete').on('click', function(e) {
        e.preventDefault();
        if ($('#BlueSheetForm2 input[name=cid]').val()==undefined) {
            return false;
        }

        deleteCompetitor($('#BlueSheetForm2 input[name=cid]').val());
        return false;
    });
    
    $('#btn-competitors-modify').on('click', function(e) {
        e.preventDefault();
        if ($('#BlueSheetForm2 input[name=cid]').val()==undefined) {
            return false;
        }
        saveCompetitor();
        return false;
    });
    
    $('#btn-competitors-add-existing').on('click', function(e) {
        e.preventDefault();
        var cid = $('#competitors').val();
        if ((cid=='') || (cid==undefined)) {
            return false;
        }

        if (!$('#tbl-competitors tbody tr[data-cid='+cid+']').length) {
            saveCompetitor(cid);
        } else {
            $('#tbl-competitors tbody tr[data-cid='+cid+']').trigger('click');
        }
        
        return false;
    });
    
    
    
    function deleteCompetitor(cid) {
        try {
            var url = $('#BlueSheetForm2').attr('action').replace(/[%][m]/, 'competitordelete');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&cid='+cid;
            
            $('#btn-competitors-modify').addClass('hidden');
            $('#btn-competitors-delete').addClass('hidden');
            $('#competitorLoader').fadeIn(function(){
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
                            if (obj.err == true) 
                            {
                                growl('Failure!', 'The project competitor details could not be deleted.', {time: 3000});
                            } else{ // no errors
                                $('#tbl-competitors tbody tr[data-cid='+cid+']').remove();
                                var len = $('#tbl-competitors tbody tr').length;
                                if (len==0) {
                                    $('#competitorInfo').css({visibility: 'hidden'});
                                    $('#btn-competitors-modify').addClass('hidden');
                                    $('#btn-competitors-delete').addClass('hidden');
                                } else {
                                    $('#tbl-competitors tbody tr:first-child').trigger('click');
                                    $('#btn-competitors-modify').removeClass('hidden');
                                    $('#btn-competitors-delete').removeClass('hidden');
                                }                                
                                growl('Success!', 'The project competitor details have been deleted successfully.', {time: 3000});
                                $('#btn-competitors-refresh').trigger('click');
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#competitorLoader').fadeOut(function(){});
                        
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    }
    
    function saveCompetitor(cid) {
        try {
            var url = $('#BlueSheetForm2').attr('action').replace(/[%][m]/, 'competitorsave');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+((cid==undefined)?$('#BlueSheetForm2').serialize():'add=1&cid='+cid);
            
            $('#btn-competitors-modify').addClass('hidden');
            $('#btn-competitors-delete').addClass('hidden');
            $('#competitorLoader').fadeIn(function(){
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
                            if (obj.err == true) 
                            {
                                growl('Failure!', 'The project competitor details could not be updated.', {time: 3000});
                            } else{ // no errors
                                if (cid==undefined) {
                                    growl('Success!', 'The project competitor details have been updated successfully.', {time: 3000});
                                } else {
                                    var tr = $('<tr>').attr('data-cid',cid).append(
                                        $('<td>').text(obj.info.name)
                                    );
                                    $('#tbl-competitors tbody').append(tr);
                                    tr.trigger('click');
                                    growl('Success!', 'The project competitor details have been added successfully.', {time: 3000});
                                    $('#btn-competitors-refresh').trigger('click');
                                }
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#btn-competitors-modify').removeClass('hidden');
                        $('#btn-competitors-delete').removeClass('hidden');
                        $('#competitorLoader').fadeOut(function(){});
                        
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    }
    
    
    function findCompetitor(cid) {
        try {
            var url = $('#BlueSheetForm2').attr('action').replace(/[%][m]/, 'competitorfind');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&cid='+cid;
            $('#competitorInfo').css({visibility: 'hidden'});
            $('#btn-competitors-modify').addClass('hidden');
            $('#btn-competitors-delete').addClass('hidden');
            $('#competitorLoader').fadeIn(function(){
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
                                //scrollFormError('SetupForm', 210);
                            } else{ // no errors
                                $('#BlueSheetForm2 input[name=cid]').val(obj.info.cid);
                                $('#lbl-competitor-name').text(obj.info.name);
                                $('#lbl-competitor-url').attr('href',(obj.info.url==null)?'javascript:':obj.info.url).text((obj.info.url==null)?'None Set':obj.info.url);
                                $('#BlueSheetForm2 textarea[name=strategy]').val(obj.info.strategy);
                                $('#BlueSheetForm2 textarea[name=response]').val(obj.info.response);
                                
                                $('#sec-competitor-strengths').empty();
                                $('#sec-competitor-weaknesses').empty();
                                
                                if (obj.info.gStrengths!=null) {
                                    for (var i in obj.info.gStrengths) {
                                        $('#sec-competitor-strengths').append (
                                            $('<input>', {
                                                type: 'text',
                                            })
                                            .addClass('span12')
                                            .val(obj.info.gStrengths[i])
                                            .attr('readonly', true),
                                            $('<div>').addClass('space5')
                                        );
                                    }
                                } 
                                if (obj.info.strengths!=null) {
                                    for (var i in obj.info.strengths) {
                                        $('#sec-competitor-strengths').append (
                                            $('<input>', {
                                                type: 'text',
                                                name: 'strengths[]'
                                            })
                                            .attr('placeholder', 'Strength')
                                            .addClass('span12')
                                            .val(obj.info.strengths[i]),
                                            $('<div>').addClass('space5')
                                        );
                                    }
                                } else {
                                    $('#sec-competitor-strengths').append($('<p>').text('No strengths added to competitor'));
                                }
                                
                                if (obj.info.gWeaknesses!=null) {
                                    for (var i in obj.info.gWeaknesses) {
                                        $('#sec-competitor-weaknesses').append (
                                            $('<input>', {
                                                type: 'text',
                                            })
                                            .addClass('span12')
                                            .val(obj.info.gWeaknesses[i])
                                            .attr('readonly', true),
                                            $('<div>').addClass('space5')
                                        );
                                    }
                                } 
                                if (obj.info.weaknesses!=null) {
                                    for (var i in obj.info.weaknesses) {
                                        $('#sec-competitor-weaknesses').append (
                                            $('<input>', {
                                                type: 'text',
                                                name: 'weaknesses[]'
                                            })
                                            .attr('placeholder', 'Weakness')
                                            .addClass('span12')
                                            .val(obj.info.weaknesses[i]),
                                            $('<div>').addClass('space5')
                                        );
                                        
                                    }
                                } else {
                                    $('#sec-competitor-weaknesses').append($('<p>').text('No weaknesses added to competitor'));
                                }
                                
                                $('#btn-competitors-modify').removeClass('hidden');
                                $('#btn-competitors-delete').removeClass('hidden');

                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#competitorInfo').css({visibility: 'visible'});
                        $('#competitorLoader').fadeOut(function(){});
                        
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    }
    
    
    
    $('#add-position-strengths').on('click', function(e) {
        e.preventDefault();
        $('#sec-position-strengths p').remove();
        var len = $('#sec-position-strengths input').length;
        var input = $('<input>', {
                type: 'text',
                name: 'PositionStrengths[]'
            })
            .attr('placeholder', 'Position Strength #'+(len+1))
            .addClass('span12');
        $('#sec-position-strengths').append (
            input,
            $('<div>').addClass('space5')
        );

        input.focus();
        return false;
    });
    
    $('#add-position-redflags').on('click', function(e) {
        e.preventDefault();
        $('#sec-position-redflags p').remove();
        var len = $('#sec-position-redflags input').length;
        var input = $('<input>', {
                type: 'text',
                name: 'PositionRedFlags[]'
            })
            .attr('placeholder', 'Position Red Flag #'+(len+1))
            .addClass('span12');
        $('#sec-position-redflags').append (
            input,
            $('<div>').addClass('space5')
        );

        input.focus();
        return false;
    });
    
    $('#add-position-actions').on('click', function(e) {
        e.preventDefault();
        $('#sec-position-actions p').remove();
        var len = $('#sec-position-actions input').length;
        var input = $('<input>', {
                type: 'text',
                name: 'PositionActions[]'
            })
            .attr('placeholder', 'Position Action #'+(len+1))
            .addClass('span12');
        $('#sec-position-actions').append (
            input,
            $('<div>').addClass('space5')
        );

        input.focus();
        return false;
    });
    
    $('#btn-competitors-add-new').on('click', function(e) {
        e.preventDefault();
        $('#CompetitorAddForm input').val('');
        $('ul#tabsAddCompetitor a[href=#widgetC_tab1]').tab('show');
        $('#modalCompetitor').modal();
    });
    
    
    // next button press
    $('#btn-next-competitor').on('click', function(e) {
        e.preventDefault();
        var activeTab = $("ul#tabsAddCompetitor li.active a").attr('data-number');
        if (activeTab == undefined) {
            return false;
        }
        
        activeTab = parseInt(activeTab);
        var nextTab = (activeTab<3)?activeTab+1:activeTab;
        
        if (activeTab != nextTab) {
            setTabButtons (nextTab,'-competitor', 3);
            $('a[href=#widgetC_tab'+nextTab+']').tab('show');
        }
        
    });
    
    // last button press
    $('#btn-last-competitor').on('click', function(e) {
        e.preventDefault();
        var activeTab = $("ul#tabsAddCompetitor li.active a").attr('data-number');
        if (activeTab == undefined) {
            return false;
        }
        
        activeTab = parseInt(activeTab);
        var nextTab = (activeTab>1)?activeTab-1:activeTab;
        
        if (activeTab != nextTab) {
            setTabButtons (nextTab,'-competitor', 3);
            $('a[href=#widgetC_tab'+nextTab+']').tab('show');
        }
        
    });
    
    $('#btn-create-competitor').on('click', function (e) {
        e.preventDefault();
        $('#CompetitorAddForm').submit();
        return false;
    });
    
    $('#btn-competitors-refresh').on('click', function(e) {
        e.preventDefault();
        var pid = $(this).attr('data-pid');
        try {
            var url = '/competitor/list/';
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&pid='+pid;
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
                            // do nothing
                        } else{ // no errors
                            var comp = $('select#competitors');
                            comp.empty().append($('<option>').val('').text('Please Select'));
                            for (var i in obj.info) {
                                comp.append($('<option>').val(obj.info[i].competitorId).text(obj.info[i].name));
                            }
                        }
                    }
                    catch(error){
                        $('#errors').html($('#errors').html()+error+'<br />');
                    }
                },
                complete: function(jqXHR, textStatus){
                    $('#systemCompetitorLoader').fadeOut(function(){});
                }
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    $('#CompetitorAddForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            resetFormErrors($(this).attr('name'));
            $('#competitorMsgs').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#systemCompetitorLoader').fadeIn(function(){
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
                                var tab = 1;
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        addFormError(i, obj.info[i]);
                                        if (tab<3){
                                            switch (i) {
                                                case 'name': case 'url':  tab = (tab<1)?1:tab; break;
                                                case 'strengths':  tab = (tab<2)?2:tab; break;
                                                case 'weaknesses': tab = (tab<3)?3:tab; break;
                                            }
                                        }
                                    }
                                }
                                $('ul#tabsAddCompetitor a[href=#widgetC_tab'+tab+']').tab('show');
                                
                                msgAlert('competitorMsgs',{
                                    title: 'Error!',
                                    mode: 3,
                                    body: 'The competitor could not be added due to errors in the form.',
                                    empty: true
                                });
                            } else{ // no errors
                                growl('Success!', 'The competitor has been added successfully.', {time: 3000});
                                
                                $('#btn-competitors-refresh').trigger('click');
                                $('#modalCompetitor').modal('hide');
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#systemCompetitorLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    $('#btn-add-orderdate').on('click', function(e) {
        e.preventDefault();
        $('#BlueSheetOrderDateForm').submit();
        return false;
    });
    
    $('#BlueSheetOrderDateForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            $('#orderDateMsgs').empty();
            resetFormErrors($(this).attr('name'));
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#setupBSLoader').fadeIn(function(){
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
                                var tab = 1;
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        addFormError(i, obj.info[i]);
                                    }
                                }
                                
                                msgAlert('orderDateMsgs',{
                                    title: 'Error!',
                                    mode: 3,
                                    body: 'The expected order date could not be added to this project - please contact an administrator if this error persists.',
                                    empty: true
                                });
                            } else{ // no errors
                                growl('Success!', 'The expected order date has been added successfully.', {time: 3000});
                            }
                        }
                        catch(error){
                            
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#setupBSLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });

}();