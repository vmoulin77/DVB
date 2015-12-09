<p class="error-msg">
    Processing error&nbsp;:
    <br />
<?php
if (isset($message)) {
    echo $message;
} else {
    echo 'An error occurred.';
}
?>
</p>