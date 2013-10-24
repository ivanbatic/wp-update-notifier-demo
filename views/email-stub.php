<!DOCTYPE html>
<html>
    <head>
        <title><?= get_bloginfo('name', 'display') ?> <?= __('Updates') ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <h1><?= get_bloginfo('name') . ' ' . __('Update Notifications') ?></h1>
        <br/>
        <?php if ($updates_exist): ?>
            <h3><?= __('There are new updates for your blog!') ?></h3>
            <?php foreach ($updates as $section_name => $section): ?>
                <h4>
                    <?= ucfirst($section_name) . ' ' . __('Updates') ?>
                    <?php if ($section_name != 'core' && $group_updates[$section_name]): ?>
                        -
                        <a href="<?= $group_updates[$section_name] ?>"><?= __('Install All') ?></a>
                    <?php endif; ?>
                </h4>
                <ul>
                    <?php foreach ($section as $update): ?>
                        <li>
                            <?= $update->diff_data['name'] ?>
                            (<?= $update->diff_data['current_version'] ?> &rarr; <?= $update->diff_data['new_version'] ?>) -
                            <a target="_blank" href="<?= $update->diff_data['install_link'] ?>"><?= __('Install') ?></a> 
                            | 
                            <a target="_blank" href="<?= $update->diff_data['info_url'] ?>"><?= __('Info') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        <?php else: ?>
            <h3><?= __('There are no new updates for your blog.') ?></h3>
        <?php endif; ?>
        <p>
            <?php
            printf(__('Feel free to browse %s or go straight to the %sadministration panel%s.'), '<a target="_blank" href="' . get_bloginfo('url') . '">' . get_bloginfo('name', 'display') . '</a>', '<a target="_blank" href="' . get_admin_url() . '">', '</a>'
            );
            ?>
        </p>
        <p><?= __('Best regards') ?>,<br/>
            <?= get_bloginfo('name') ?>
        </p>
    </body>
</html>
