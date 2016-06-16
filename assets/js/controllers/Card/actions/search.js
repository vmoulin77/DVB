function confirm_deletion(id_card) {
    var ok_url = BASE_URL + 'Card/delete/' + id_card;
    
    display_generic_conform_dialog('Confirmation', 'Are you sure ?', 400, 200, ok_url);
}
