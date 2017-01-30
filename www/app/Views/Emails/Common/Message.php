<?php
$membersModel = new \App\Models\MembersModel();

$body = "\n\n\n".$data["tName"] . " (".$data["tUserName"].") ".$membersModel->translate("member_wrote", $data["lang"]).":";
$body .= "\n\n".$data["message"];
?>
<!DOCTYPE html>
<html lang="<?php echo LANGUAGE_CODE; ?>">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2><?php echo "[V-MAST ".$membersModel->translate("message_content", $data["lang"])."]"; ?></h2>

        <div style="color: #249b45; font-weight: bold;">
            <?php echo $membersModel->translate("facilitator_message_tip", $data["lang"]) ?>
        </div>

        <div style="margin-top: 20px">
            <div><?php echo $membersModel->translate("send_message_to", $data["lang"]) . " " . $data["tName"] . " (".$data["tUserName"]."): " ?></div>
            <div><a href="mailto:<?php echo $data["tEmail"] ?>?subject=<?php echo rawurlencode("RE: ".$data["subject"]) ?>&amp;body=<?php echo rawurlencode($body) ?>"><?php echo $data["tEmail"] ?></a></div>
        </div>

        <div style="margin-top: 20px">
            <div><?php echo $membersModel->translate("member_profile_message", $data["lang"]) ?>:</div>
            <div><a href="<?php echo SITEURL."members/profile/".$data["tMemberID"] ?>"><?php echo $data["tUserName"] ?></a></div>
        </div>

        <div style="margin-top: 20px">
            <div style="font-weight: bold;"><?php echo $membersModel->translate("message_content", $data["lang"]) ?>:</div>
            <div><?php echo $data["message"] ?></div>
        </div>
    </body>
</html>
