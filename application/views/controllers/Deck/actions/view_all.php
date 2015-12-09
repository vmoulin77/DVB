<table class="table-style-2">
    <thead>
        <tr>
            <th>ID</th>
            <th>NUM</th>
            <th>NAME</th>
            <th>DATABASE VERSION</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($decks as $deck): ?>
            <tr>
                <td><?php echo $deck->get_id(); ?></td>
                <td><?php echo $deck->get_num(); ?></td>
                <td><?php echo $deck->get_name(); ?></td>
                <td><?php
if ($deck->get_version_when_created()->get_database_version() === null) {
    echo 'Not yet';
} else {
    echo $deck->get_version_when_created()->get_database_version();
}
                ?></td>
                <td>
                    <?php echo '<a href="' . base_url() . 'Deck/edit/' . $deck->get_id() . '">'; ?>
                        <img onmouseover="$(this).attr('src', '<?php echo base_url() . 'assets/img/pencil-on.png'; ?>');"
                            onmouseout="$(this).attr('src', '<?php echo base_url() . 'assets/img/pencil-off.png'; ?>');"
                            src="<?php echo base_url() . 'assets/img/pencil-off.png'; ?>" alt="" />
                    </a>
                </td>
                <td>
                    <?php echo '<a href="javascript:void(0)" onclick="confirm_deletion(' . $deck->get_id() . ');">'; ?>
                        <img onmouseover="$(this).attr('src', '<?php echo base_url() . 'assets/img/trash-on.png'; ?>');"
                            onmouseout="$(this).attr('src', '<?php echo base_url() . 'assets/img/trash-off.png'; ?>');"
                            src="<?php echo base_url() . 'assets/img/trash-off.png'; ?>" alt="" />
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>