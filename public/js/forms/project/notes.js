var Script = function () {
    // Add new note to space
    $('.btn-add-note').on('click', function (e) {
        e.preventDefault();
        $('textarea[name=note]').val('');
        $('select[name=nscope]').val(1);
        $('#myModalAddNote').modal({}).on('shown.bs.modal', function (e) {
            $('textarea[name=note]').focus();
        });
    });

    $('#btn-save-note').on('click', function (e) {
        $('#AddNoteForm').submit();
    });


    //save request
    $('#AddNoteForm').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            $('#msgs3').empty();
            var url = $(this).attr('action');
            var params = 'ts=' + Math.round(new Date().getTime() / 1000) + '&' + $(this).serialize();
            $('#noteLoader').fadeIn(function () {
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: params, // Just send the Base64 content in POST body
                    processData: false, // No need to process
                    timeout: 60000, // 1 min timeout
                    dataType: 'text', // Pure Base64 char data
                    beforeSend: function onBeforeSend(xhr, settings) {
                    },
                    error: function onError(XMLHttpRequest, textStatus, errorThrown) {
                    },
                    success: function onUploadComplete(response) {
                        //console.log(response); //return;
                        try {
                            var obj = jQuery.parseJSON(response);
                            var k = 0;
                            // an error has been detected
                            var tab = 3;
                            var additional = '';
                            if (obj.err == true) {
                                if (obj.info != undefined) {
                                    for (var i in obj.info) {
                                        if (!addFormError(i, obj.info[i])) {
                                            additional += obj.info[i] + '<br>';
                                        }
                                    }
                                }

                                if (additional != '') {
                                    msgAlert('msgs3', {
                                        mode: 3,
                                        body: 'Error: ' + additional,
                                        empty: true
                                    });
                                }
                            } else { // no errors
                                if (obj.cnt == 1) {
                                    location.reload();
                                } else {
                                    $('#project-notes').append($('<div>').addClass('note').html('<strong>' +
                                        ((obj.scope != null) ? obj.scope + ' ' : '') + 'Note:</strong> ' +
                                        $('textarea[name=note]').val() + ' <a class="delete-note" ' +
                                        ((obj.scope != null) ? 'data-scope="' + obj.scope.toLowerCase() + '"' : '')
                                        + ' data-index="' + obj.id + '" href="javascript:"><i class="icon-remove"></i></a> <a class="edit-note" ' +
                                        ((obj.scope != null) ? 'data-scope="' + obj.scope.toLowerCase() + '"' : '')
                                        + ' data-index="' + obj.id + '" href="javascript:" title="'+ $('textarea[name=note]').val() +'"><i class="icon-edit"></i></a>'));
                                    //$('#project-notes').append($('<br>'),$('<span>').html('<strong>Note '+obj.cnt+':</strong> '+$('textarea[name=note]').val()+'.'));
                                    $('#myModalAddNote').modal('hide');
                                    growl('Success!', 'The note has been successfully added.', {time: 3000});
                                }
                            }
                        }
                        catch (error) {
                            $('#errors').html($('#errors').html() + error + '<br />');
                        }
                    },
                    complete: function (jqXHR, textStatus) {
                        $('#noteLoader').fadeOut(function () {
                        });
                    }
                });
            });

        } catch (ex) {

        }
        /**/
        return false;
    });

    $(document).on('click', '.edit-note', function(e){
        e.preventDefault();
        e.stopPropagation();
        $('textarea[name=note]').val($(this).attr('title'));

        $('select[name=nscope]').val( $(this).attr('data-scope-id') );
        $('#note_idx').val($(this).attr('data-index'));
        $('#myModalEditNote').modal({}).on('shown.bs.modal', function (e) {
            $('textarea[name=note]').focus();
        });
    });

    $('#btn-edit-note').on('click', function (e) {
        $('#EditNoteForm').submit();
    });

    //Edit request
    $('#EditNoteForm').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            resetFormErrors($(this).attr('name'));
            $('#msgs3').empty();
            var url = $(this).attr('action');
            var params = 'ts=' + Math.round(new Date().getTime() / 1000) + '&' + $(this).serialize();
            $('#noteEditLoader').fadeIn(function () {
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: params, // Just send the Base64 content in POST body
                    processData: false, // No need to process
                    timeout: 60000, // 1 min timeout
                    dataType: 'text', // Pure Base64 char data
                    beforeSend: function onBeforeSend(xhr, settings) {
                    },
                    error: function onError(XMLHttpRequest, textStatus, errorThrown) {
                    },
                    success: function onUploadComplete(response) {
                        //console.log(response); //return;
                        try {
                            var obj = jQuery.parseJSON(response);
                            var k = 0;
                            // an error has been detected
                            var tab = 3;
                            var additional = '';
                            if (obj.err == true) {
                                if (obj.info != undefined) {
                                    for (var i in obj.info) {
                                        if (!addFormError(i, obj.info[i])) {
                                            additional += obj.info[i] + '<br>';
                                        }
                                    }
                                }

                                if (additional != '') {
                                    msgAlert('msgs3', {
                                        mode: 3,
                                        body: 'Error: ' + additional,
                                        empty: true
                                    });
                                }

                            } else { // no errors
                                $('#myModalAddNote').modal('hide');
                                growl('Success!', 'The note has been successfully modified.', {time: 3000});
                                location.reload();
                                if (obj.cnt == 1) {
                                    location.reload();
                                } else {
                                    /*
                                    $('#project-notes').append($('<div>').addClass('note').html('<strong>' +
                                        ((obj.scope != null) ? obj.scope + ' ' : '') + 'Note:</strong> ' +
                                        $('textarea[name=note]').val() + ' <a class="delete-note" ' +
                                        ((obj.scope != null) ? 'data-scope="' + obj.scope.toLowerCase() + '"' : '')
                                        + ' data-index="' + obj.id + '" href="javascript:"><i class="icon-remove"></i></a> <a class="edit-note" ' +
                                        ((obj.scope != null) ? 'data-scope="' + obj.scope.toLowerCase() + '"' : '')
                                        + ' data-index="' + obj.id + '" href="javascript:"><i class="icon-edit"></i></a>'));
                                    //$('#project-notes').append($('<br>'),$('<span>').html('<strong>Note '+obj.cnt+':</strong> '+$('textarea[name=note]').val()+'.'));
                                    */

                                }
                            }
                        }
                        catch (error) {
                            $('#errors').html($('#errors').html() + error + '<br />');
                        }
                    },
                    complete: function (jqXHR, textStatus) {
                        $('#noteEditLoader').fadeOut(function () {
                        });
                    }
                });
            });
        } catch (ex) {

        }
        /**/
        return false;
    });

    $(document).on('click', '.delete-note', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (confirm('Are you sure to delete this note?'))
        {
            try {
                var parent = $(this).parent();
                var url = $('#AddNoteForm').attr('action').replace(/addnote/, 'deletenote');
                var scope = (typeof $(this).attr('data-scope') !== typeof undefined && $(this).attr('data-scope') !== false) ? $(this).attr('data-scope') : '';
                var params = 'ts=' + Math.round(new Date().getTime() / 1000) + '&nid=' + $(this).attr('data-index') + '&scope=' + scope;
                $('#noteDeleteLoader').fadeIn(function () {
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: params, // Just send the Base64 content in POST body
                        processData: false, // No need to process
                        timeout: 60000, // 1 min timeout
                        dataType: 'text', // Pure Base64 char data
                        beforeSend: function onBeforeSend(xhr, settings) {
                        },
                        error: function onError(XMLHttpRequest, textStatus, errorThrown) {
                        },
                        success: function onUploadComplete(response) {
                            //console.log(response); //return;
                            try {
                                var obj = jQuery.parseJSON(response);
                                var k = 0;
                                // an error has been detected
                                var tab = 3;
                                var additional = '';
                                if (obj.err == true) {

                                } else { // no errors
                                    parent.remove();
                                    if ($('#project-notes .note').length < 1) {
                                        location.reload();
                                    }
                                    growl('Success!', 'The note has been successfully deleted.', {time: 3000});
                                }
                            }
                            catch (error) {
                            }
                        },
                        complete: function (jqXHR, textStatus) {
                            $('#noteDeleteLoader').fadeOut(function () {
                            });
                        }
                    });
                });

            } catch (ex) {

            }
            /**/
        }
        return false;
    });


}();