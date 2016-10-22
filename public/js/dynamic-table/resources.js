var Script = function () {

        var serialTable = $('#resources_tbl').dataTable({
            bProcessing: true,
            sAjaxSource: "/client-{$client->getClientId()}/job-{$project->getProjectId()}/resourcelist/",
            "aoColumns" : [
                null,
                null,
                null,
                null,
                null,
                null,
                {"sClass": "center"},
                {"sClass": "right"},
                {"sClass": "right"},
                { 'bSortable': false, "sClass": "hidden-phone" }
            ]
        });

        $(document).on('click', '.action-client-edit', function(e) {
            document.location = '/resource-activity/resource-'+$(this).attr('rid') + '/edit/';
        });

        $('#btn-add-activity-modal').on('click', function(){
            $('#modalResource').modal();
        });
}();