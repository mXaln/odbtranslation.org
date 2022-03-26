<?php
use \Helpers\Constants\EventSteps;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("l1_events") ?></h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th><?php echo __("book") ?></th>
                        <th><?php echo __("target_lang") ?></th>
                        <th><?php echo __("project") ?></th>
                        <th><?php echo __("current_step") ?></th>
                        <th><?php echo __("user") ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["all_notifications"] as $notification): ?>
                        <tr>
                            <?php
                            $step = $notification->step;
                            $link = "";

                            $step = isset($notification->translateDone) && $notification->translateDone ? EventSteps::FINISHED :$notification->step;
                            $link = "/events/checker".(isset($notification->manageMode)
                                && in_array($notification->manageMode, ["sun","odb","fnd","bib","theo"]) ? "-".$notification->manageMode : "")
                                ."/".$notification->eventID."/"
                                .$notification->memberID."/"
                                .$notification->step."/"
                                .(isset($notification->manageMode) ? $notification->currentChapter."/" : "")
                                ."apply";
                            ?>
                            <td><?php echo $notification->bookName . (", chapter ".
                                        ($notification->currentChapter > 0 ? $notification->currentChapter : __("intro"))) ?></td>
                            <td><?php echo $notification->tLang ?></td>
                            <td><?php echo $notification->sourceBible == "odb"
                                ? __($notification->sourceBible)
                                : __($notification->bookProject) ?></td>
                            <td><?php echo ($notification->step != "other" ? __($notification->step .
                                        ($notification->sourceBible == "odb"
                                            ? "_odb" : "")) : __("check")) ?></td>
                            <td><?php echo $notification->firstName . " " . mb_substr($notification->lastName, 0, 1)."." ?></td>
                            <td><a href="<?php echo $link ?>"
                                   data="check:<?php echo $notification->eventID.":".$notification->memberID ?>">
                                    <?php echo __("apply") ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>