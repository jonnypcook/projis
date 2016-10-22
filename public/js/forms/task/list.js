var Script = function () {
    // toggle button
    var dataTbl =$('#tbl-tasks').dataTable({
        sDom: "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
        sPaginationType: "bootstrap",
        oLanguage: {
            sLengthMenu: "_MENU_ records per page",
            oPaginate: {
                sPrevious: "Prev",
                sNext: "Next"
            }
        },
        bProcessing: true,
        bServerSide: true,
        iDisplayLength:25,
        aLengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        aoColumns: [
            null,
            null,
            { 'bSortable': false, "sClass": "hidden-phone" },
            { "sClass": "hidden-phone" },
            null,
            null,
            { 'bSortable': false }
        ],
        sAjaxSource: "/task/list/"
    });    

    jQuery('#tbl-tasks_wrapper .dataTables_filter input').addClass("input-medium"); // modify table search input
    jQuery('#tbl-tasks_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
    
    dataTbl.fnSort( [ [5,'asc'] ] );
    
}();