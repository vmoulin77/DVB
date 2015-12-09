function confirm_deletion(id_campaign) {
    if (confirm('Are you sure ?')) {
        window.location.href = base_url + 'Campaign/delete/' + id_campaign;
    }
}
