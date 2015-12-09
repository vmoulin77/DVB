    <div class="form-group">
        <label for="name">Name</label>
        <input id="name" class="form-control" type="text" name="name" value="<?php echo set_value('name', $name); ?>" />
    </div>

<?php
if ($action == 'create') {
    echo '<div class="form-group">';
    echo '<label for="id_deck">Deck</label>';
    echo '<select id="id_deck" class="form-control" name="id_deck">';
    echo '<option value=""></option>';
    foreach ($decks as $deck) {
        echo '<option value="' . $deck->get_id() . '" ';
        echo set_select('id_deck', $deck->get_id());
        echo '>' . $deck->get_num() . ' - ' . $deck->get_name() . '</option>';
    }
    echo '</select>';
    echo '</div>';
}
?>

    <button class="btn btn-default" type="submit">Submit</button>

</form>