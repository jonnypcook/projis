var Script = function () {
    $('#target-add-btn').on('click', function () {
        $('#modalProductAdd').modal();
    });


    $('.margin, .pcnt').on('keyup', function () {
        var id = $(this).attr('data-value');

        var margin_value = $('#marginMonth' + id).val();
        var percent = $('#p_' + id).val();

        if (margin_value > 0 && percent > 0) {
            var sales = ( margin_value / percent ) * 100;

            $('#salesMonth' + id).val(Math.round(sales));
        }

        return false;
    });

    /**
     $('.margin').on('keyup', function () {
        var id = $(this).attr('data-value');

        var sales = '#salesMonth' + id;
        var margin = '#marginMonth' + id;


        var sales_value = $.trim($(sales).val());
        var margin_value = $.trim($(margin).val());

        if (sales_value == '') {
            //$(sales).val('');
            //$(margin).val('');
            //$(percent).val('');
            return false;
        }

        //var percent = (margin_value / sales_value) * 100;

        $('#p_' + id).val(percent);
    });
     */
    /*
    $('.pcnt').on('keyup', function () {
        var id = $(this).attr('data-value');

        var margin_value = '#salesMonth' + id;
        var percent = '#p_' + id;


        var sales_value = $.trim($(sales).val());
        var percent_value = $.trim($(percent).val());

        if (sales_value == '') {
            //$(sales).val('');
            //$(margin).val('');
            //$(percent).val('');
            return false;
        }

        var margin = (sales_value * percent_value) / 100;

        $('#marginMonth' + id).val(margin);
    })*/
    /*
     $('.margin').on('keyup', function(){
     console.log('hello');
     });

     $('.pcnt').on('keyup', function(){

     });
     */
}();