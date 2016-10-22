var Script = function () {
    // loader activate and deactivate
    jQuery( ".loader" ).on( "activate", function( e ) {
        $( this ).fadeIn();
    }).on( "deactivate", function( e ) {
        $( this ).fadeOut();
    });        

    //    sidebar dropdown menu
    jQuery('#sidebar .sub-menu > a').click(function () {
        var last = jQuery('.sub-menu.open', $('#sidebar'));
        last.removeClass("open");
        jQuery('.arrow', last).removeClass("open");
        jQuery('.sub', last).slideUp(200);
        var sub = jQuery(this).next();
        if (sub.is(":visible")) {
            jQuery('.arrow', jQuery(this)).removeClass("open");
            jQuery(this).parent().removeClass("open");
            sub.slideUp(200);
        } else {
            jQuery('.arrow', jQuery(this)).addClass("open");
            jQuery(this).parent().addClass("open");
            sub.slideDown(200);
        }
    });

//    sidebar toggle

    $('.icon-reorder').click(function () {
        if ($('#sidebar > ul').is(":visible") === true) {
            $('#main-content').css({
                'margin-left': '0px'
            });
            $('#sidebar').css({
                'margin-left': '-180px'
            });
            $('#sidebar > ul').hide();
            $("#container").addClass("sidebar-closed");
        } else {
            $('#main-content').css({
                'margin-left': '180px'
            });
            $('#sidebar > ul').show();
            $('#sidebar').css({
                'margin-left': '0'
            });
            $("#container").removeClass("sidebar-closed");
        }
    });

// custom scrollbar
    $(".sidebar-scroll").niceScroll({styler:"fb",cursorcolor:"#4A8BC2", cursorwidth: '5', cursorborderradius: '0px', background: '#404040', cursorborder: ''});

    $("html").niceScroll({styler:"fb",cursorcolor:"#4A8BC2", cursorwidth: '8', cursorborderradius: '0px', background: '#404040', cursorborder: '', zindex: '1000'});


// theme switcher

    var scrollHeight = '60px';
    jQuery('#theme-change').click(function () {
        if ($(this).attr("opened") && !$(this).attr("opening") && !$(this).attr("closing")) {
            $(this).removeAttr("opened");
            $(this).attr("closing", "1");

            $("#theme-change").css("overflow", "hidden").animate({
                width: '20px',
                height: '22px',
                'padding-top': '3px'
            }, {
                complete: function () {
                    $(this).removeAttr("closing");
                    $("#theme-change .settings").hide();
                }
            });
        } else if (!$(this).attr("closing") && !$(this).attr("opening")) {
            $(this).attr("opening", "1");
            $("#theme-change").css("overflow", "visible").animate({
                width: '226px',
                height: scrollHeight,
                'padding-top': '3px'
            }, {
                complete: function () {
                    $(this).removeAttr("opening");
                    $(this).attr("opened", 1);
                }
            });
            $("#theme-change .settings").show();
        }
    });

    jQuery('#theme-change .colors span').click(function () {
        var color = $(this).attr("data-style");
        setColor(color);
    });

    jQuery('#theme-change .layout input').change(function () {
        setLayout();
    });

    var setColor = function (color) {
        $('#style_color').attr("href", "css/style-" + color + ".css");
    }

// widget tools

    jQuery('.widget .tools .icon-chevron-down, .widget .tools .icon-chevron-up').click(function () {
        var el = jQuery(this).parents(".widget").children(".widget-body");
        if (jQuery(this).hasClass("icon-chevron-down")) {
            jQuery(this).removeClass("icon-chevron-down").addClass("icon-chevron-up");
            el.slideUp(200);
        } else {
            jQuery(this).removeClass("icon-chevron-up").addClass("icon-chevron-down");
            el.slideDown(200);
        }
    });

    jQuery('.widget .tools .icon-remove').click(function () {
        jQuery(this).parents(".widget").parent().remove();
    });
    
//    tool tips

    $('.element').tooltip();

    $('.tooltips').tooltip();

//    popovers

    $('.popovers').popover();

// scroller

    $('.scroller').slimscroll({
        height: 'auto'
    });
    
// linkable elements
    $('.linkable').on('click', function(e) {
        if ($(this).attr('href')!=undefined) {
            e.preventDefault();
        
            window.location.href = $(this).attr('href');
            return false;
        }
    });
    
}();

function resetFormErrors(name) {
    $('#'+name+' .error').removeClass('error');
    $('#'+name+' .help-inline').remove();
}

function addFormError(name, error, formId) {
    node = $(((formId)?'#'+formId+' ':'')+'[name='+name+']');
    
    if (node==undefined){
        return false;
    }
    
    if (!node.length){
        node = $(((formId)?'#'+formId+' ':'')+'[name="'+name+'[]"]');
        if (!node.length){
            return false;
        }
    }
    
    if (node.parent().parent().hasClass('control-group')) {
        node.parent().parent().addClass('error');
    } else if (node.parent().parent().parent().hasClass('control-group')) {
        node.parent().parent().parent().addClass('error');
    }
    
    
    if (typeof error === 'string') {
        if (node.parent().hasClass('controls')) {
            node.after($('<span>').addClass("help-inline").text(error));
        } else {
            node.parent().after($('<span>').addClass("help-inline").text(error));
        }
    } else if (typeof error === 'object'){ 
        txt = '';
        cnt = 0;
        for(var i in error){
            txt+=((cnt>0)?' | ':'')+error[i];
            cnt++;
        }
        if (node.parent().hasClass('controls')) {
            node.after($('<span>').addClass("help-inline").text(txt));
        } else {
            node.parent().after($('<span>').addClass("help-inline").text(txt));
        }
    }
    
    return true;
}

function scrollFormError(name, offset) {
    $('html,body').animate({
        scrollTop: $('#'+name+' .error:first').offset().top - offset
    }, 'slow');
}

function scrollFormTop(name, offset) {
    $('html,body').animate({
        scrollTop: $('#'+name).offset().top - offset
    }, 'slow');
}

function msgAlert (parentId, options) {
    if( !options ) var options = {};
    if (options.empty == undefined) {
        options.empty = true;
    }
    if (options.mode == undefined) {
        options.mode = 1;
    } else {
        if ((options.mode>3) || (options.mode<1)) {
            options.mode = 1;
        }
    }
    
    var parent = $('#'+parentId);
    if (parent==undefined) {
        return false;
    }
    
    if (options.empty) {
        if (options.clickExit) {
            parent.find('.alert button').trigger('click');
        }
        parent.empty();
    }
    
    var msg = $('<div>').addClass('alert alert-block alert-'+((options.mode==1)?'success':((options.mode==2)?'info':'error'))+' fade in');
    msg.append($('<button>', {type: 'button'}).attr('data-dismiss', 'alert').addClass('close').text('×'))
    
    // check for title option
    if (options.title) {
        msg.append($('<h4>').addClass('alert-heading').text(options.title));
    }
    
    //add body
    if (options.body) {
        msg.append($('<p>').html(options.body));
    }
    
    parent.append(msg);
    
    
}

function growl(title, text, settings) {
    var obj = {
        // (string | mandatory) the heading of the notification
        title: title,
        // (string | mandatory) the text inside the notification
        text: text,
        // (bool | optional) if you want it to fade out on its own or just sit there
        sticky: false,
        // (int | optional) the time you want it to be alive for before fading out
    };
    
    if (settings.time) {
        obj.time = settings.time;
    } else if (settings.sticky) {
        obj.sticky = settings.sticky;
    }
    
    if (settings.class_name) {
        obj.class_name = settings.class_name;
    }

    if (settings.image) {
        obj.image = settings.image;
    }

    
    $.gritter.add(obj);
}

function number_format (number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function is_float(val) {
    try {
        if(val==undefined) {
            throw 'undefined value';
        }

        if(!val.match(/^[\d]+([.][\d]+)?$/)) {
            throw 'illegal number';
        }

        parseFloat(val);

        return true;

    } catch (e) {
        return false;
    }
}

Date.prototype.ddmmyyyy = function() {
   var yyyy = this.getFullYear().toString();
   var mm = (this.getMonth()+1).toString(); // getMonth() is zero-based
   var dd  = this.getDate().toString();
   return (dd[1]?dd:"0"+dd[0]) + '/' + (mm[1]?mm:"0"+mm[0]) + '/' + yyyy; // padding
  };
  
Date.prototype.hhii = function() {
   var hh = this.getHours().toString();
   var ii = this.getMinutes().toString(); // getMonth() is zero-based
   return (hh[1]?hh:"0"+hh[0]) + ':' + (ii[1]?ii:"0"+ii[0]); // padding
  };