var Script = function () {
    $(".chzn-select").chosen(); 
    //toggle button

    $('input[name=installed]').datepicker({
        format: 'dd/mm/yyyy'
    }).on('changeDate', function (e) {
        $('input[name=installed]').datepicker('hide').blur();
    });

    $('input[name=completed]').datepicker({
        format: 'dd/mm/yyyy'
    }).on('changeDate', function (e) {
        $('input[name=completed]').datepicker('hide').blur();
    });

    window.prettyPrint && prettyPrint();

    $('#text-toggle-button').toggleButtons({
        width: 160,
        label: {
            enabled: "Test",
            disabled: "Live"
        },
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "danger",
            disabled: "info"
        }
    });
    
    
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
    $('#btn-next').on('click', function(e) {
        e.preventDefault();
        var activeTab = $("ul#tabsProjectSettings li.active a").attr('data-number');
        if (activeTab == undefined) {
            return false;
        }
        
        activeTab = parseInt(activeTab);
        var nextTab = (activeTab<2)?activeTab+1:activeTab;
        
        if (activeTab != nextTab) {
            setTabButtons (nextTab,'', 2);
            $('a[href=#widget_tab'+nextTab+']').tab('show');
        }
        
    });
    
    // last button press
    $('#btn-last').on('click', function(e) {
        e.preventDefault();
        var activeTab = $("ul#tabsProjectSettings li.active a").attr('data-number');
        if (activeTab == undefined) {
            return false;
        }
        
        activeTab = parseInt(activeTab);
        var nextTab = (activeTab>1)?activeTab-1:activeTab;
        
        if (activeTab != nextTab) {
            setTabButtons (nextTab,'', 2);
            $('a[href=#widget_tab'+nextTab+']').tab('show');
        }
        
    });
    
    $('#SetupForm').on('submit', function(e) {
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
                            if (obj.err == true) {
                                var tab = 1;
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        addFormError(i, obj.info[i]);
                                        if (tab<2){
                                            switch (i) {
                                                case 'name': case 'test': case 'sector': case 'type': case 'financeProvider': case 'financeYears': case 'mcd': case 'ibp': tab = (tab<1)?1:tab; break;
                                                case 'fuelTariff': case 'maintenance': case 'co2': case 'rpi': case 'epi': case 'eca': case 'carbon': case 'model': tab = (tab<2)?2:tab; break;
                                                case 'weighting': case 'status': tab = (tab<2)?2:tab; break;
                                            }
                                        }
                                    }
                                }
                                $('ul#tabsProjectSettings a[href=#widget_tab'+tab+']').tab('show');
                                
                                msgAlert('msgs',{
                                    title: 'Error!',
                                    mode: 3,
                                    body: 'The project configuration could not be updated due to errors in the form (displayed in red).',
                                    empty: true
                                });
                                //scrollFormError('SetupForm', 210);
                            } else{ // no errors
                                growl('Success!', 'The project configuration has been updated successfully.', {time: 3000});
                                /*msgAlert('msgs',{
                                    title: 'Success!',
                                    mode: 1,
                                    body: 'The project configuration has been updated successfully.',
                                    empty: true
                                });/**/
                                scrollFormTop('SetupForm',210);
                                $('ul#tabsProjectSettings a[href=#widget_tab1]').tab('show');
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
    
    $('#FormProjectLostActivate').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000);
            $('#projectLostActivateLoader').fadeIn(function(){
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
                        //console.log(response); return;
                        try{
                            var obj=jQuery.parseJSON(response);
                            var k = 0;
                            // an error has been detected
                            if (obj.err == true) {
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        addFormError(i, obj.info[i]);
                                    }
                                }
                            } else{ // no errors
                                window.location.reload();
                            }
                        }
                        catch(error){
                            
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#projectLostActivateLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    $('#btn-confirm-project-lostactivate').on('click', function(e) {
        e.preventDefault();
        $('#FormProjectLostActivate').submit();
        return false;
    });
    
    
     $('#FormProjectSigned').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#projectSignedLoader').fadeIn(function(){
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
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        addFormError(i, obj.info[i]);
                                    }
                                }
                            } else{ // no errors
                                window.location.reload();
                            }
                        }
                        catch(error){
                            
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#projectSignedLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    
    $('#btn-confirm-project-signed').on('click', function(e) {
        e.preventDefault();
        $('#FormProjectSigned').submit();
        return false;
    });
    
    $('#FormProjectCompleted').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            $('#projectCompletedLoader').fadeIn(function(){
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
                                if (obj.info != undefined) {
                                    for(var i in obj.info){
                                        addFormError(i, obj.info[i]);
                                    }
                                }
                            } else{ // no errors
                                window.location.reload();
                            }
                        }
                        catch(error){
                            
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#projectCompletedLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    });
    
    $('#btn-confirm-project-completed').on('click', function(e) {
        e.preventDefault();
        $('#FormProjectCompleted').submit();
        return false;
    });
    

}();