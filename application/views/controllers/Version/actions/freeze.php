<?php
echo validation_errors();

echo form_open('Version/freeze');
?>
    <div class="form-group">
        <label for="database_version">Database version</label>
        <input id="database_version" class="form-control" type="text" name="database_version" value="<?php echo set_value('database_version'); ?>" />
    </div>

    <div class="form-group">
        <label for="app_version_code">Application version code</label>
        <input id="app_version_code" class="form-control" type="text" name="app_version_code" value="<?php echo set_value('app_version_code'); ?>" />
    </div>

    <div class="form-group">
        <label for="app_version_name">Application version name</label>
        <input id="app_version_name" class="form-control" type="text" name="app_version_name" value="<?php echo set_value('app_version_name'); ?>" />
    </div>

    <button class="btn btn-default" type="submit">OK</button>
</form>