<?php echo form_open('Card/search', array('class' => 'form-search')); ?>
    <div class="form-search-group">
        <div class="form-search-col form-search-col-left">
            <label for="searched_str">Search on&nbsp;:</label>
        </div>
        <div class="form-search-col form-search-col-right">
            <input id="searched_str" type="text" name="searched_str" value="<?php echo set_value('searched_str'); ?>" size="50" class="form-control" />
        </div>
    </div>

    <div class="form-search-group">
        <div class="form-search-col form-search-col-left">
            <label>Case sensitive&nbsp;:</label>
        </div>
        <div class="form-search-col form-search-col-right">
            <input id="case_sensitive" type="checkbox" name="case_sensitive" value="case_sensitive" <?php echo set_checkbox('case_sensitive', 'case_sensitive'); ?> />
        </div>
    </div>

    <div class="form-search-group">
        <div class="form-search-col form-search-col-left">
            <label>Language&nbsp;:</label>
        </div>
        <div class="form-search-col form-search-col-right">
            <div class="radio">
                <label for="language_french" class="radio">
                    <input id="language_french" type="radio" name="language" value="only_fr" <?php echo set_radio('language', 'only_fr'); ?> />
                    French
                </label>
            </div>
            <div class="radio">
                <label for="language_english" class="radio">
                    <input id="language_english" type="radio" name="language" value="only_en" <?php echo set_radio('language', 'only_en'); ?> />
                    English
                </label>
            </div>
            <div class="radio">
                <label for="language_both" class="radio">
                    <input id="language_both" type="radio" name="language" value="both" <?php echo set_radio('language', 'both', true); ?> />
                    Both
                </label>
            </div>
        </div>
    </div>

    <div class="form-search-group">
        <div class="form-search-col form-search-col-left">
            <label>State&nbsp;:</label>
        </div>
        <div class="form-search-col form-search-col-right">
            <div class="radio">
                <label for="state_deleted" class="radio">
                    <input id="state_deleted" type="radio" name="state" value="deleted" <?php echo set_radio('state', 'deleted'); ?> />
                    Deleted
                </label>
            </div>
            <div class="radio">
                <label for="state_not_deleted" class="radio">
                    <input id="state_not_deleted" type="radio" name="state" value="not_deleted" <?php echo set_radio('state', 'not_deleted'); ?> />
                    Not deleted
                </label>
            </div>
            <div class="radio">
                <label for="state_both" class="radio">
                    <input id="state_both" type="radio" name="state" value="both" <?php echo set_radio('state', 'both', true); ?> />
                    Both
                </label>
            </div>
        </div>
    </div>

    <div class="form-search-buttons">
        <div class="form-search-col form-search-col-left"></div>
        <div class="form-search-col form-search-col-right">
            <input class="btn btn-primary" type="submit" value="Submit" />
        </div>
    </div>
</form>

<?php if ($method === 'post') : ?>
<table class="table-style-2">
    <colgroup span="1"></colgroup>
    <colgroup span="1"></colgroup>
    <colgroup width="42%" span="1"></colgroup>
    <colgroup width="42%" span="1"></colgroup>
    <colgroup span="1"></colgroup>
    <colgroup span="1"></colgroup>
    <colgroup span="1"></colgroup>
    <thead>
        <tr>
            <th>ID</th>
            <th>NUM</th>
            <th>FRENCH</th>
            <th>ENGLISH</th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
<?php
    foreach ($searched_cards as $card) {
        echo '<tr>';
        echo '<td';
        if ($card->get_is_deleted()) {
            echo ' class="deleted-card"';
        }
        echo '>' . $card->get_id() . '</td>';

        echo '<td>' . $card->get_num() . '</td>';
                    
        echo '<td><table class="table-word"><tr><td class="';
        if ($card->get_card_content()->get_is_active_french()) {
            echo 'word-flag-active';
        } else {
            echo 'word-flag-passive';
        }
        echo '"></td><td>' . $card->get_card_content()->get_word_french() . '</td></tr></table></td>';

        echo '<td><table class="table-word"><tr><td class="';
        if ($card->get_card_content()->get_is_active_english()) {
            echo 'word-flag-active';
        } else {
            echo 'word-flag-passive';
        }
        echo '"></td><td>' . $card->get_card_content()->get_word_english() . '</td></tr></table></td>';
        
        echo '<td class="text-center"><a href="' . base_url() . 'Card/view/' . $card->get_id() . '">';
        echo '<img onmouseover="$(this).attr(\'src\', \'' . base_url() . 'assets/img/eye-on.png\');" ';
        echo 'onmouseout="$(this).attr(\'src\', \'' . base_url() . 'assets/img/eye-off.png\');" ';
        echo 'src="' . base_url() . 'assets/img/eye-off.png" alt="" />';
        echo '</a></td>';

        echo '<td class="text-center">';
        if ( ! $card->get_is_deleted()) {
            echo '<a href="' . base_url() . 'Card/edit/' . $card->get_id() . '">';
            echo '<img onmouseover="$(this).attr(\'src\', \'' . base_url() . 'assets/img/pencil-on.png\');" ';
            echo 'onmouseout="$(this).attr(\'src\', \'' . base_url() . 'assets/img/pencil-off.png\');" ';
            echo 'src="' . base_url() . 'assets/img/pencil-off.png" alt="" /></a>';
        }
        echo '</td>';

        echo '<td class="text-center">';
        if ( ! $card->get_is_deleted()) {
            echo '<a href="javascript:void(0)" onclick="confirm_deletion(' . $card->get_id() . ');">';
            echo '<img onmouseover="$(this).attr(\'src\', \'' . base_url() . 'assets/img/trash-on.png\');" ';
            echo 'onmouseout="$(this).attr(\'src\', \'' . base_url() . 'assets/img/trash-off.png\');" ';
            echo 'src="' . base_url() . 'assets/img/trash-off.png" alt="" /></a>';
        }
        echo '</td>';
        echo '</tr>';
    }
?>
    </tbody>
</table>
<?php endif; ?>