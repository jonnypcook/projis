var Script = function () {
    var activityTbl = $('#activity_tbl').dataTable({
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
        aLengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        aoColumns: [
            null,
            { 'bSortable': true, "sClass": "hidden-phone" },
            null,
            { "sClass": "hidden-phone" },
            null
        ],
        sAjaxSource: "/activity/list/"
    });    

    jQuery('#activity_tbl_wrapper .dataTables_filter input').addClass("input-medium"); // modify table search input
    jQuery('#activity_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
   
    
    activityTbl.fnSort( [ [0,'desc']] );
}();