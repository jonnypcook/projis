var Script = function () {
    //toggle button
    function listDocuments(category) {
        try {
            var url = $('form#documentViewFrm').attr('action');
            var params = 'ts='+Math.round(new Date().getTime()/1000)+'&category='+category;
            var tblDocs = $('#tblDocumentViewer tbody');
            tblDocs.empty();
            $('#fileViewLoader').fadeIn(function(){
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
                                growl('Failure!', 'Could not retrieve the document list - please contact an administrator if this error persists.', {time: 3000});
                                //scrollFormError('SetupForm', 210);
                            } else{ // no errors
                                if (obj.data.length) {
                                    for (var i in obj.data) {
                                        tblDocs.append(
                                            $('<tr>').css({'text-decoration':(obj.data[i][6]?'normal':'line-through')}).attr('data-document-id', obj.data[i][0]).append(
                                                $('<td>').append($('<a>').addClass('btn-doc-download').attr('href','javascript:').text(obj.data[i][1])),
                                                $('<td>').text(obj.data[i][2]),
                                                $('<td>').text(obj.data[i][3]),
                                                $('<td>').text(obj.data[i][4]),
                                                $('<td>').text(obj.data[i][5]),
                                                $('<td>').html(obj.data[i][6]?'<button class="btn btn-success btn-doc-download"><i class="icon-download-alt"></i></button>':'<div class="btn btn-danger"><i class="icon-warning-sign"></i></div>')
                                            )
                                        );
                                    }
                                } else {
                                    tblDocs.append(
                                        $('<tr>').append(
                                            $('<td>').attr('colspan', 6).text('No documents found')
                                        )
                                    );
                                }
                                
                            }
                        }
                        catch(error){
                            $('#errors').html($('#errors').html()+error+'<br />');
                        }
                    },
                    complete: function(jqXHR, textStatus){
                        $('#fileViewLoader').fadeOut(function(){});
                    }
                });
            });

        } catch (ex) {

        }/**/
        return false;
    }
    
    $('#fileOptions ul.dropdown-menu li a').on('click', function(e) {
        var category = $(this).attr('data-category');
        if (category == undefined) {
            return;
        }
        $('#fileViewName').text($(this).text());
        listDocuments(category);
    });
    
    $(document).on('click', '.btn-doc-download', function(e) {
        e.preventDefault();
        var documentId = $(this).parent().parent().attr('data-document-id');
        
        if (documentId == undefined) {
            return false;
        }
        
        if (!documentId.match(/^[0-9]+$/)) {
            return false;
        }
        
        
        $('#documentDownloadFrm input[name=documentListId]').val(documentId);
        $('#documentDownloadFrm').submit();
        
        
    });

}();