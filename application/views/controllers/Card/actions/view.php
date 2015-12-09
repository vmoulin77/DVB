<div>
    <label>Card history&nbsp;:</label>
    <table id="card_history" class="table-style-2">
        <thead>
            <tr>
                <th>ENGLISH</th>
                <th>FRENCH</th>
                <th>DATABASE VERSION</th>
            </tr>
        </thead>
        <tbody>
<?php
foreach ($card->get_card_contents_history() as $card_content) {
    echo '<tr>';
    echo '<td><table class="table-word"><tr><td class="';
    if ($card_content->get_is_active_french()) {
        echo 'word-flag-active';
    } else {
        echo 'word-flag-passive';
    }
    echo '"></td><td>' . $card_content->get_word_french() . '</td></tr></table></td>';

    echo '<td><table class="table-word"><tr><td class="';
    if ($card_content->get_is_active_english()) {
        echo 'word-flag-active';
    } else {
        echo 'word-flag-passive';
    }
    echo '"></td><td>' . $card_content->get_word_english() . '</td></tr></table></td>';
    echo '<td>';
    if ($card_content->get_version()->get_database_version() === null) {
        echo 'Not yet';
    } else {
        echo $card_content->get_version()->get_database_version();
    }
    echo '</td>';
    echo '</tr>';
}
if ($card->get_is_deleted()) {
    echo '<tr>';
    echo '<td colspan="2">The card has been deleted</td>';
    echo '<td>';
    if ($card->get_version_when_deleted()->get_database_version() === null) {
        echo 'Not yet';
    } else {
        echo $card->get_version_when_deleted()->get_database_version();
    }
    echo '</td>';
    echo '</tr>';
}
?>
        </tbody>
    </table>

    <label>Decks list&nbsp;:</label>
    <table class="table-style-2">
        <thead>
            <tr>
                <th>ID</th>
                <th>NUM</th>
                <th>NAME</th>
                <th>IN/OUT</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($decks as $deck): ?>
                <tr>
                    <td><?php echo $deck->get_id(); ?></td>
                    <td><?php echo $deck->get_num(); ?></td>
                    <td><?php echo $deck->get_name(); ?></td>
<?php
echo '<td id="presence_in_deck_' . $deck->get_id() . '">';
if ($deck->contains_current_card) {
    echo 'in';
} else {
    echo 'out';
}
echo '</td>';
echo '<td>';
if ( ! $card->get_is_deleted()) {
    echo '<button id="move_for_deck_' . $deck->get_id() . '" ';
    if ($deck->contains_current_card) {
        echo 'onclick="manage_card_move(\'remove\',' . $id . ',' . $deck->get_id() . ');">remove';
    } else {
        echo 'onclick="manage_card_move(\'add\',' . $id . ',' . $deck->get_id() . ');">add';
    }
    echo '</button>';
}
echo '</td>';
?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>