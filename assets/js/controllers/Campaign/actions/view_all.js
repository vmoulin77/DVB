function confirm_deletion(id_campaign) {
    if (confirm('Are you sure ?')) {
        window.location.href = BASE_URL + 'Campaign/delete/' + id_campaign;
    }
}
