// Configuration
var BASE_URL = 'http://www.dvb.fr/';
var STR_DB_BOOL_FALSE = 'f';
var STR_DB_BOOL_TRUE = 't';
//--------------------------------------------------

$(document).ready(function() {
    $(window).unload(function () { $(window).unbind('unload'); });
    
    // Management of the menu
    var menu_section_counter = 0;

    $('.menu-section-hidden').click(function() {
        if ( ! $(this).hasClass('menu-section-visible')) {
            $('.menu-section-visible').attr('class', 'menu-section-hidden menu-color-dark');
            $('.menu-section-hidden').next('ul').slideUp(function() {
                if ($(this).prev('span').hasClass('menu-section-hidden')) {
                    $(this).prev('span').attr('class', 'menu-section-hidden menu-color-light');
                }
            });
            $(this).attr('class', 'menu-section-visible menu-color-dark');
            $(this).next('ul').slideDown();
        }
    });
    
    $('#menu').mouseleave(function() {
        var menu_section_value = menu_section_counter;
        menu_section_counter = ++menu_section_counter % 10000;
        
        $('.menu-section-visible').attr('class', 'menu-section-' + menu_section_value + ' menu-color-dark');
        $('.menu-section-' + menu_section_value).next('ul').slideUp(function() {
            if ($(this).prev('span').hasClass('menu-section-' + menu_section_value)) {
                $(this).prev('span').attr('class', 'menu-section-hidden menu-color-light');
            }
        });
    });
    //--------------------------------------------------

    $('.datepicker').datepicker({ 'dateFormat': 'dd/mm/yy' });
});

function display_generic_alert_dialog(title, message, width, height) {
    $('#generic_dialog_content').html(message);
    $('#generic_dialog_content').dialog({
        'title':      title,
        'resizable':  false,
        'width':      width,
        'height':     height,
        'modal':      true,
        'buttons':    {
            'Ok': function() {
                $(this).dialog('close');
                $(this).empty();
            }
        }
    });
}

function display_generic_conform_dialog(title, message, width, height, ok_url) {
    $('#generic_dialog_content').html(message);
    $('#generic_dialog_content').dialog({
        'title':      title,
        'resizable':  false,
        'width':      width,
        'height':     height,
        'modal':      true,
        'buttons':    {
            'Cancel': function() {
                $(this).dialog('close');
                $(this).empty();
            },
            'Ok': function() {
                window.location.href = ok_url;
                $(this).dialog('close');
                $(this).empty();
            }
        }
    });
}

function display_technical_problem_dialog() {
    var message;
    message = '<p class="error-msg">A technical problem occurred.<br />Please reload the page.<br />If the problem persists, contact the administrator.</p>';
    display_generic_alert_dialog('Error message', message, 500, 220);
}

function start_loader_animation() {
    $('#loader').fadeIn();
}

function stop_loader_animation() {
    $('#loader').css('display', 'none');
}
