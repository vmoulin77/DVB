<?php
echo validation_errors();

echo form_open('Version/compare');
?>

    <input id="version_from" type="text" name="version_from" value="<?php echo set_value('version_from'); ?>" />
    <input id="version_to" type="text" name="version_to" value="<?php echo set_value('version_to'); ?>" />
    <button type="submit">Submit</button>
</form>

<!-- TO DO -->