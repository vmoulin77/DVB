function manage_card_move(type, id_card, id_deck) {
    start_loader_animation();

    $.get(
        base_url + 'Card_move/ajax_move/' + type + '/' + id_card + '/' + id_deck,
        {},
        function(data) {
            var new_presence_in_deck, new_move_for_deck;

            stop_loader_animation();

            if (data.ajax_response) {
                if (data.ajax_response.status == 'OK') {
                    if (type == 'add') {
                        new_presence_in_deck  = 'in';
                        new_move_for_deck     = 'remove';
                    } else {
                        new_presence_in_deck  = 'out';
                        new_move_for_deck     = 'add';
                    }

                    $('#presence_in_deck_' + id_deck).html(new_presence_in_deck);
                    $('#move_for_deck_' + id_deck).html(new_move_for_deck);
                    $('#move_for_deck_' + id_deck).attr('onclick', "manage_card_move('" + new_move_for_deck + "'," + id_card + "," + id_deck + ");");
                }

                display_generic_alert_dialog(data.ajax_response.title, data.ajax_response.message, 500, 200);
            } else {
                display_technical_problem_dialog();
            }
        },
        'json'
    );
}
