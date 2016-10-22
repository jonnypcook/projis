var Script = function () {
    $('#contacts_tbl').dataTable({
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
        iDisplayLength:10,
        aLengthMenu: [[5, 10, 15, 20, 25, 50], [5, 10, 15, 20, 25, 50]],
        aoColumns: [
            { "sClass": "hidden-phone" },
            null,
            null,
            { "sClass": "hidden-phone" },
            null,
            { "sClass": "hidden-phone" }
        ],
        sAjaxSource: "/contact/list/"
    });    

    jQuery('#contacts_tbl_wrapper .dataTables_filter input').addClass("input-medium"); // modify table search input
    jQuery('#contacts_tbl_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
   
    $(document).on('click', '#contacts_tbl tbody tr', function(e) {
        $('#contacts_tbl tbody tr.active').removeClass('active');
        $(this).addClass('active');
        
        var contact = $(this).find('.contact-info');
        contact.trigger('click');
        
    });
    
}();

