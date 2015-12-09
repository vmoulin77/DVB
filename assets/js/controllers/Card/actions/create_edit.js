function insert_small_r() {
    var elt = document.getElementById('word_english_edit');
    
    if (elt.selectionStart != undefined
        && elt.selectionEnd != undefined
        && elt.selectionStart == elt.selectionEnd
    ) {
        elt.value = elt.value.substring(0, elt.selectionStart) + '<small><small>r</small></small>' + elt.value.substring(elt.selectionEnd);
    }
}

function visualize() {
    var word_english, word_french;

    word_english = $('#word_english_edit').val();
    word_english = word_english.replace("\r\n", "\n");
    word_english = word_english.replace("\r", "<br />");
    word_english = word_english.replace("\n", "<br />");
    $('#word_english_rendering').html(word_english);

    word_french = $('#word_french_edit').val();
    word_french = word_french.replace("\r\n", "\n");
    word_french = word_french.replace("\r", "<br />");
    word_french = word_french.replace("\n", "<br />");
    $('#word_french_rendering').html(word_french);
}

function modify_word_status(language) {
    if ($('#is_active_' + language).val() == '0') {
        $('#is_active_' + language).val('1');
        $('#button_word_status_' + language).css('background-color', 'green');
        $('#button_word_status_' + language).html('active');
    } else {
        $('#is_active_' + language).val('0');
        $('#button_word_status_' + language).css('background-color', 'red');
        $('#button_word_status_' + language).html('inactive');
    }
}
