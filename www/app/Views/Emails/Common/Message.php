<?php
$membersModel = new \App\Models\MembersModel();
?>
<!DOCTYPE html>
<html lang="<?php echo LANGUAGE_CODE; ?>">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <div style="margin-top: 10px;">
            <div><?php echo $data["message"] ?></div>
        </div>

        <div style="margin-top: 20px;">
            <div><?php echo $membersModel->translate("member_profile_message", $data["lang"]) ?>:</div>
            <div>
                <a href="<?php echo SITEURL."members/profile/".$data["tMemberID"] ?>">
                    <?php echo $data["tName"] . " (" .$data["tUserName"] . ")" ?>
                </a>
            </div>
        </div>

        <div style="color: #249b45; font-weight: bold; margin-top: 20px;">
            <?php echo $membersModel->translate("facilitator_message_tip", $data["lang"]) ?>
        </div>
    </body>
</html>
