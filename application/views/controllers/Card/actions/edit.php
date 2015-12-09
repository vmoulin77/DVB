<?php
echo validation_errors();

echo form_open('Card/edit/' . $id . '/' . $id_campaign);

echo $create_edit;
?>
