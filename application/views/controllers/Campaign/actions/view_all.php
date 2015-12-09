<table class="table-style-2">
    <thead>
        <tr>
            <th>ID</th>
            <th>NAME</th>
            <th>CREATED AT</th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($campaigns as $campaign): ?>
            <tr>
                <td><?php echo $campaign->get_id(); ?></td>
                <td><?php echo $campaign->get_name(); ?></td>
                <td><?php echo $campaign->get_created_at()->format('d/m/Y H:i'); ?></td>
                <td class="text-center">
                    <?php echo '<a href="' . base_url() . 'Campaign/edit/' . $campaign->get_id() . '">'; ?>
                        <img onmouseover="$(this).attr('src', '<?php echo base_url() . 'assets/img/pencil-on.png'; ?>');"
                            onmouseout="$(this).attr('src', '<?php echo base_url() . 'assets/img/pencil-off.png'; ?>');"
                            src="<?php echo base_url() . 'assets/img/pencil-off.png'; ?>" alt="" />
                    </a>
                </td>
                <td class="text-center">
                    <?php echo '<a href="javascript:void(0)" onclick="confirm_deletion(' . $campaign->get_id() . ');">'; ?>
                        <img onmouseover="$(this).attr('src', '<?php echo base_url() . 'assets/img/trash-on.png'; ?>');"
                            onmouseout="$(this).attr('src', '<?php echo base_url() . 'assets/img/trash-off.png'; ?>');"
                            src="<?php echo base_url() . 'assets/img/trash-off.png'; ?>" alt="" />
                    </a>
                </td>
                <td class="text-center">
<?php
if ($campaign->next_id_card === null) {
    echo 'Completed';
} else {
    echo '<button onclick="window.location.href=';
    echo "'" . base_url() . "Card/edit/" . $campaign->next_id_card . "/" . $campaign->get_id();
    echo '\';">GO</button>';
}
?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>