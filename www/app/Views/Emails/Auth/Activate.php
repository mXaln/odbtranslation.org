<!DOCTYPE html>
<html lang="<?= LANGUAGE_CODE; ?>">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2><?= __("activate_account_title"); ?></h2>

        <div>
            <?= __("activation_link_message", site_url('members/activate/' .$memberID."/".$token)); ?><br/>
            <?= __("url_use_problem_hint"); ?>
        </div>
    </body>
</html>
