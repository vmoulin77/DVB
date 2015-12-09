<input id="is_active_english" type="hidden" name="is_active_english" value="<?php echo set_value('is_active_english', $is_active_english); ?>" />
<input id="is_active_french" type="hidden" name="is_active_french" value="<?php echo set_value('is_active_french', $is_active_french); ?>" />
    <table class="table-style-1 table-card">
        <colgroup width="50%" span="1"></colgroup>
        <colgroup width="50%" span="1"></colgroup>
        <thead>
            <tr>
                <th colspan="2" align="center">
                    <label>Number&nbsp;:</label>
                    <input type="text" name="num" value="<?php echo set_value('num', $num); ?>" size="10" />
                </th>
            </tr>
            <tr>
                <th align="center">ENGLISH</th>
                <th align="center">FRENCH</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <textarea id="word_english_edit" class="word-edit" name="word_english"><?php echo set_value('word_english', $word_english); ?></textarea>
                </td>
                <td>
                    <textarea id="word_french_edit" class="word-edit" name="word_french"><?php echo set_value('word_french', $word_french); ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <button id="button_insert_small_r" onclick="insert_small_r();return false;">insert small r</button>
                </td>
                <td></td>
            </tr>
            <tr>
                <td id="word_english_rendering"></td>
                <td id="word_french_rendering"></td>
            </tr>
            <tr>
                <td>
<?php
echo '<button id="button_word_status_english" onclick="modify_word_status(\'english\');return false;" class="';
if (set_value('is_active_english', $is_active_english)) {
    echo 'word-status-active">active';
} else {
    echo 'word-status-inactive">inactive';
}
echo '</button>';
?>
                </td>
                <td>
<?php
echo '<button id="button_word_status_french" onclick="modify_word_status(\'french\');return false;" class="';
if (set_value('is_active_french', $is_active_french)) {
    echo 'word-status-active">active';
} else {
    echo 'word-status-inactive">inactive';
}
echo '</button>';
?>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="buttons">
        <button onclick="visualize();return false;">VISUALIZE</button>
        <button type="submit">SAVE</button>
    </div>
</form>