<?php
$body = "\n\n\n".$data["tName"] . " (".$data["tUserName"].") ".__("member_wrote").":";
$body .= "\n\n".$data["message"];
?>
<!DOCTYPE html>
<html lang="<?php echo LANGUAGE_CODE; ?>">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2><?php echo "[V-MAST ".__("message_content")."]"; ?></h2>

        <div style="color: #249b45; font-weight: bold;">
            <?php echo __("facilitator_message_tip") ?>
        </div>

        <div style="margin-top: 20px">
            <div><?php echo __("send_message_to") . " " . $data["tName"] . " (".$data["tUserName"]."): " ?></div>
            <div><a href="mailto:<?php echo $data["tEmail"] ?>?subject=<?php echo rawurlencode("RE: ".$data["subject"]) ?>&amp;body=<?php echo rawurlencode($body) ?>"><?php echo $data["tEmail"] ?></a></div>
        </div>

        <div style="margin-top: 20px">
            <div><?php echo __("member_profile_message") ?>:</div>
            <div><a href="<?php echo SITEURL."members/profile/".$data["tMemberID"] ?>"><?php echo $data["tUserName"] ?></a></div>
        </div>

        <div style="margin-top: 20px">
            <div style="font-weight: bold;"><?php echo __("message_content") ?>:</div>
            <div><?php echo $data["message"] ?></div>
        </div>
    </body>
</html>
