<?php
$membersModel = new \App\Models\MembersModel();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2><?php echo $membersModel->translate("new_account_title", "en"); ?></h2>

        <div style="font-size: 18px">
            <div style="margin-top: 20px">
                <div><?php echo $membersModel->translate("new_account_message", "en", ["name" => $name, "username" => $userName]) ?></div>
                <div>
                    <strong><?php echo $membersModel->translate("proj_lang_public", "en") ?>: </strong>
                    <?php echo $projectLanguage ?>
                </div>
                <div>
                    <strong><?php echo $membersModel->translate("Projects", "en")  ?>: </strong>
                    <?php echo $projects ?>
                </div>
            </div>

            <div style="margin-top: 20px">
                <div><?php echo __("member_profile_message") ?>:</div>
                <div><a href="<?php echo SITEURL."members/profile/".$id ?>"><?php echo $name." (".$userName.")" ?></a></div>
            </div>

            <div style="margin-top: 20px">
                <div><a href="<?php echo SITEURL."admin/members" ?>"><?php echo $membersModel->translate("members_area", "en") ?></a></div>
            </div>
        </div>
    </body>
</html>
