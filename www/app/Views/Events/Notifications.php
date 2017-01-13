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
                            <td><?php echo $event->bookName.", chapter ".$event->currentChapter ?></td>
                            <td><?php echo $event->tLang ?></td>
                            <td><?php echo __($event->bookProject) ?></td>
                            <td><?php echo $event->translateDone ? __(EventSteps::FINISHED) : __($event->step)?></td>
                            <td><a href="/members/profile/<?php echo $event->memberID ?>"><?php echo $event->userName ?></a></td>
                            <td><a href="/events/checker/<?php echo $event->eventID."/".$event->memberID."/".$event->step; ?>/apply"
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