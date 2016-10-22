var Script = function () {
    
    $('.chbx-building').on('click', function(e) {
        e.preventDefault();
        var buildingId = $(this).attr('data-buildingId');
        
        if ($(this).hasClass('icon-chevron-down')) {
            $('#tbl-export-building-'+buildingId+' tbody tr').hide();
            $(this).removeClass('icon-chevron-down');
            $(this).addClass('icon-chevron-up');
        } else {
            $('#tbl-export-building-'+buildingId+' tbody tr').show();
            $(this).removeClass('icon-chevron-up');
            $(this).addClass('icon-chevron-down');
        }
    });
    
    $('.chbx-space').on('click', function(e) {
        e.preventDefault();
        var spaceId = $(this).attr('data-spaceId');
        
        if ($(this).hasClass('icon-chevron-down')) {
            $('#tbl-export-system-'+spaceId).hide();
            $(this).removeClass('icon-chevron-down');
            $(this).addClass('icon-chevron-up');
        } else {
            $('#tbl-export-system-'+spaceId).show();
            $(this).removeClass('icon-chevron-up');
            $(this).addClass('icon-chevron-down');
        }
    });
    
    $('#btn-select-all').on('click', function(e) {
        e.preventDefault();
        $('.tbl-export-building tbody tr').show();
        $('.tbl-export-system').show();
        $('.chbx-building').removeClass('icon-chevron-up').addClass('icon-chevron-down');
        $('.chbx-space').removeClass('icon-chevron-up').addClass('icon-chevron-down');
        
        return false;
    });
    
    $('#btn-deselect-all').on('click', function(e) {
        e.preventDefault();
        $('.tbl-export-building tbody tr').hide();
        $('.tbl-export-system').hide();
        $('.chbx-building').removeClass('icon-chevron-down').addClass('icon-chevron-up');
        $('.chbx-space').removeClass('icon-chevron-down').addClass('icon-chevron-up');
        
        return false;
    });

    
   

}();