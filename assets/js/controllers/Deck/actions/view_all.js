function confirm_deletion(id_deck) {
    var ok_url = BASE_URL + 'Deck/delete/' + id_deck;
    
    display_generic_conform_dialog('Confirmation', 'Are you sure ?', 400, 200, ok_url);
}
