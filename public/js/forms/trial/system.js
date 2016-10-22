var Script = function () {
    // toggle button
    window.prettyPrint && prettyPrint();
    
    //chosen select
    $(".chzn-select").chosen({search_contains: true}); 
    
    // setup table
    var tblTrialItems = $('#products_tbl').dataTable({
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
            null
        ]
    });

    jQuery('#products_tbl_wrapper .dataTables_filter input').addClass("input-xlarge"); // modify table search input
    jQuery('#products_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
    
    tblTrialItems.fnSort( [ [4,'desc']] );                       
}();
