var Script = function () {
    //toggle button
    window.prettyPrint && prettyPrint();

    $('#active-toggle-button').toggleButtons({
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "primary",
            disabled: "danger"
        }
    });
    
     $('#eca-toggle-button').toggleButtons({
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "primary",
            disabled: "danger"
        }
    });
    
     $('#mcd-toggle-button').toggleButtons({
        style: {
            // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
            enabled: "primary",
            disabled: "danger"
        }
    });
    
    
    function setTabButtons (tab) {
        if (tab > 1) {
            $('#btn-last').removeAttr('disabled');
        } else if (tab == 1) {
            $('#btn-last').attr('disabled','disabled');
        } 

        if (tab == 3) {
            $('#btn-next').attr('disabled','disabled');
        } else if (tab < 3) {
            $('#btn-next').removeAttr('disabled');
        }
    }
    
    $('#btn-next').on('click', function(e) {
        e.preventDefault();
        var activeTab = $("ul#tabsAddProduct li.active a").attr('data-number');
        if (activeTab == undefined) {
            return false;
        }
        
        activeTab = parseInt(activeTab);
        var nextTab = (activeTab<3)?activeTab+1:activeTab;
        
        if (activeTab != nextTab) {
            setTabButtons (nextTab);
            $('a[href=#widget_tab'+nextTab+']').tab('show');
        }
        
    });
    
    
    
    // last button press
    $('#btn-last').on('click', function(e) {
        e.preventDefault();
        var activeTab = $("ul#tabsAddProduct li.active a").attr('data-number');
        if (activeTab == undefined) {
            return false;
        }
        
        activeTab = parseInt(activeTab);
        var nextTab = (activeTab>1)?activeTab-1:activeTab;
        
        if (activeTab != nextTab) {
            setTabButtons (nextTab);
            $('a[href=#widget_tab'+nextTab+']').tab('show');
        }
        
    });
    
    $('#ProductConfigForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            resetFormErrors($(this).attr('name'));
            $('#productMsgs').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            
            $('#productAddLoader').fadeIn(function(){
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
                                        if (tab>1){
                                            switch (i) {
                                                case 'brand': case 'type': case 'model': case 'description': tab = 1; break;
                                                case 'cpu': case 'ppu': case 'ibppu': case 'ppu_trial': if (tab>=2) tab = 2; break;
                                                case 'active': case 'pwr': case 'eca': case 'mcd': case 'sagepay': if (tab>=3) tab = 3; break;
                                            }
                                        }
                                    }
                                }

                                if (additional != '') {
                                    msgAlert('productMsgs',{
                                        mode: 3,
                                        body: 'Error: '+additional,
                                        empty: true
                                    });
                                }

                                $('ul#tabsAddProduct a[href=#widget_tab'+tab+']').tab('show');

                            } else{ // no errors
                                window.location.reload();
                            }
                        }
                        catch(error){

                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#productAddLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
    });
    
    $('#btn-product-add').on('click', function(e) {
        e.preventDefault();
        $('#ProductConfigForm').submit();
        return false;
    });


    // begin first table
    $('#products_tbl').dataTable({
        "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ records per page",
            "oPaginate": {
                "sPrevious": "Prev",
                "sNext": "Next"
            }
        },
        bStateSave: true,
        bProcessing: false,
        bServerSide: true,
        iDisplayLength:15,
        aLengthMenu: [[5, 10, 15, 20, 25, 50], [5, 10, 15, 20, 25, 50]],
        "aoColumns": [
            null,
            //{ "sClass": "hidden-phone" },
            { "sClass": "hidden-phone" },
            { "sClass": "hidden-phone" },
            null,
            null,
            { 'bSortable': false }
        ],
        sAjaxSource: "/product/list/"
    });

    jQuery('#products_tbl .group-checkable').change(function () {
        var set = jQuery(this).attr("data-set");
        var checked = jQuery(this).is(":checked");
        jQuery(set).each(function () {
            if (checked) {
                $(this).attr("checked", true);
            } else {
                $(this).attr("checked", false);
            }
        });
        jQuery.uniform.update(set);
    });

    $('#product-add-btn').on('click', function(e){
        e.preventDefault();
        $('#modalProductAdd').modal();
        return false;
    });
    
    
    jQuery('#products_tbl_wrapper .dataTables_filter input').addClass("input-medium"); // modify table search input
    jQuery('#products_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
    
    $('#ProductConfigForm input[name=cpu]').on('change', function (e) {
        var cpu = $('#ProductConfigForm input[name=cpu]').val();
        if (!cpu.match(/^[\d]+$/)) {
            return false;
        }
        $('#ProductConfigForm input[name=ppu]').val((cpu/0.55).toFixed(2));
    });
    
    $(document).on('click','.copy-product', function(e) {
        e.preventDefault();
        var productId = $(this).attr('data-productId');
        var model = $(this).attr('data-model');
        
        if ((productId==undefined) || (model==undefined)) {
            return false;
        }
        
        $('#frmCopyProduct input[name=productId]').val(productId);
        $('#frmCopyProduct input[name=newProductModel]').val(model);
        
        $('#modalCopyProduct').modal();
    });
    
    $('#btn-confirm-copy-product').on('click', function(e) {
        e.preventDefault();
        $('#frmCopyProduct').submit();
        
        return false;
    });
    
    $('#frmCopyProduct').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            resetFormErrors($(this).attr('name'));
            $('#productCopyMsgs').empty();
            var url = $(this).attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&'+$(this).serialize();
            
            $('#productCopyLoader').fadeIn(function(){
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
                                            additional+=obj.info[i]+'<br>';
                                        }
                                    }
                                }

                                if (additional != '') {
                                    msgAlert('productCopyMsgs',{
                                        mode: 3,
                                        body: 'Error: '+additional,
                                        empty: true
                                    });
                                }

                            } else{ // no errors
                                document.location = '/product-'+obj.productId+'/setup/';
                            }
                        }
                        catch(error){

                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#productCopyLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
    });

}();