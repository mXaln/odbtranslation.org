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
                    <?php foreach($data["all_notifications"] as $event): ?>
                        <tr>
                            <?php
                            $step = $event->step;
                            $link = "";
                            if(!in_array($event->bookProject, ["tn"]) 
                                || !isset($event->notesChapter))
                            {
                                $step = $event->translateDone ? EventSteps::FINISHED :$event->step;
                                $link = "/events/checker/".$event->eventID."/"
                                    .$event->memberID."/".$event->step."/apply";
                            }
                            else 
                            {
                                $step = "not_available";
                                $link = "/events/checker/".$event->eventID."/"
                                    .$event->memberID."/notes/"
                                    .$event->notesChapter."/apply";
                            }
                            ?>
                            <td><?php echo $event->bookName.", chapter ".
                                ($event->currentChapter > 0 ? $event->currentChapter : __("intro")) ?></td>
                            <td><?php echo $event->tLang ?></td>
                            <td><?php echo __($event->bookProject) ?></td>
                            <td><?php echo __($step)?></td>
                            <td><?php echo $event->firstName . " " . mb_substr($event->lastName, 0, 1)."." ?></td>
                            <td><a href="<?php echo $link ?>"
                                   data="check:<?php echo $event->eventID.":".$event->memberID ?>">
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