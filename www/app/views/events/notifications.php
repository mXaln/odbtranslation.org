<?php
use \Helpers\Constants\EventSteps;
use Core\Language;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo Language::show("l1_events", "Events") ?></h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th><?php echo Language::show("book", "Events") ?></th>
                        <th><?php echo Language::show("target_lang", "Events") ?></th>
                        <th><?php echo Language::show("project", "Events") ?></th>
                        <th><?php echo Language::show("current_step", "Events") ?></th>
                        <th><?php echo Language::show("user", "Events") ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["all_notifications"] as $event): ?>
                        <tr>
                            <td><?php echo $event->bookName.", chapter ".$event->currentChapter ?></td>
                            <td><?php echo $event->tLang ?></td>
                            <td><?php echo Language::show($event->bookProject, "Events") ?></td>
                            <td><?php echo $event->translateDone ? Language::show(EventSteps::FINISHED, "Events") : Language::show($event->step, "Events")?></td>
                            <td><?php echo $event->userName ?></td>
                            <td><a href="/events/checker/<?php echo $event->eventID."/".$event->memberID; ?>/apply"
                                   data="check:<?php echo $event->eventID.":".$event->memberID ?>">
                                    <?php echo Language::show("apply", "Events") ?>
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