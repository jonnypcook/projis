var Script = function () {
    // begin contacts table
    $('#contacts_tbl').dataTable({
        sDom: "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span4'i><'span8'p>>",
        sPaginationType: "bootstrap",
        iDisplayLength:3,
        aLengthMenu: [[3, 5, 10, 15, 20], [3, 5, 10, 15, 20]],
        bProcessing: false,
        bServerSide: true,
        oLanguage: {
            sLengthMenu: "_MENU_ per page",
            oPaginate: {
                sPrevious: "",
                sNext: ""
            },
            sInfo: "_START_ to _END_ of _TOTAL_",
            sInfoFiltered: ""
        },
        aoColumnDefs: [{
            'bSortable': false,
            'aTargets': [0]
        }],
        sAjaxSource: "/contact/list/?mini=1"
    });

    jQuery('#contacts_tbl_wrapper .dataTables_filter input').addClass("input-small"); // modify table search input
    jQuery('#contacts_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
    
    
    
    
    
}();