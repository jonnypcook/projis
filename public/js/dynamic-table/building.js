var Script = function () {
    // begin first table
    $('#buildings_tbl').dataTable({
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

    jQuery('#buildings_tbl_wrapper .dataTables_filter input').addClass("input-small"); // modify table search input
    jQuery('#buildings_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
    
    
    
}();