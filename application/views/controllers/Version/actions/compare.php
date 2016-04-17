<?php
echo validation_errors();
echo '<h2>Comparison of versions&nbsp;:</h2>';
echo form_open('Version/compare');
?>
    <label>From</label> <input id="version_before" type="text" name="version_before" value="<?php echo set_value('version_before'); ?>" />
    <label>to</label> <input id="version_after" type="text" name="version_after" value="<?php echo set_value('version_after'); ?>" />
    <button type="submit">Go</button>
</form>

<?php if (isset($comparison)): ?>
    <?php foreach ($comparison as $card_contents): ?>
    <table class="table-style-2 table-comparison">
        <colgroup width="50%" span="1"></colgroup>
        <colgroup width="50%" span="1"></colgroup>
        <thead>
            <tr>
                <th>English</th>
                <th>French</th>
            </tr>
        </thead>
        <tbody>
    <?php
        foreach ($card_contents as $card_content) {
            echo '<tr>';
            echo '<td>' . $card_content->get_word_english() . '</td>';
            echo '<td>' . $card_content->get_word_french() . '</td>';
            echo '</tr>';
        }
    ?>
        </tbody>
    </table>
    <?php endforeach; ?>
<?php endif; ?>