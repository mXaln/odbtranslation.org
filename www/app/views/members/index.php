<?php
use \Core\Language;
?>

<?php if(\Helpers\Session::get("isAdmin")): ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title">
                <?php echo Language::show("facilitator_events", "Events") ?>
                <a href="<?php echo DIR ?>events/demo/pray" class="demo_link"><?php echo Language::show("see_demo", "Events") ?></a>
            </h1>
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
                            <th><?php echo Language::show("source", "Events") ?></th>
                            <th><?php echo Language::show("state", "Events") ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($data["myFacilitatorEvents"] as $event): ?>
                            <tr>
                                <td><a href="/events/information/<?php echo $event->eventID ?>"><?php echo $event->name ?></a></td>
                                <td><?php echo $event->langName ?></td>
                                <td><?php echo Language::show($event->bookProject, "Events") ?></td>
                                <td><?php echo Language::show($event->sourceBible, "Events"). " (".$event->sLang.")" ?></td>
                                <td><?php echo Language::show("state_".$event->state, "Events") ?></td>
                                <td><a href="/events/manage/<?php echo $event->eventID ?>"><?php echo Language::show("manage", "Events") ?></a></td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo Language::show("translator_events", "Events") ?></h1>
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
                        <th><?php echo Language::show("source", "Events") ?></th>
                        <th><?php echo Language::show("current_step", "Events") ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["myTranslatorEvents"] as $event): ?>
                        <tr>
                            <td><a href="/events/translator/<?php echo $event->eventID ?>"><?php echo $event->name ?></a></td>
                            <td><?php echo $event->tLang ?></td>
                            <td><?php echo Language::show($event->bookProject, "Events") ?></td>
                            <td><?php echo Language::show($event->sourceBible, "Events"). " (".$event->sLang.")" ?></td>
                            <td><?php echo $event->translateDone ? Language::show(\Helpers\Constants\EventSteps::FINISHED, "Events") : Language::show($event->step, "Events")?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
                        <th><?php echo Language::show("translator", "Events") ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["myCheckerL1Events"] as $event): ?>
                        <tr>
                            <td><a href="/events/checker/<?php echo $event->eventID."/".$event->memberID ?>"><?php echo $event->bookName.", chapter ".$event->currentChapter ?></a></td>
                            <td><?php echo $event->tLang ?></td>
                            <td><?php echo Language::show($event->bookProject, "Events") ?></td>
                            <td><?php echo $event->translateDone ? Language::show(\Helpers\Constants\EventSteps::FINISHED, "Events") : Language::show($event->step, "Events")?></td>
                            <td><?php echo $event->userName ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo Language::show("l2_3_events", "Events", array(2)) ?></h1>
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
                        <th><?php echo Language::show("source", "Events") ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["myCheckerL2Events"] as $event): ?>
                        <tr>
                            <td><a href="/events/checker_l2/<?php echo $event->eventID ?>"><?php echo $event->name ?></a></td>
                            <td><?php echo $event->tLang ?></td>
                            <td><?php echo Language::show($event->bookProject, "Events") ?></td>
                            <td><?php echo Language::show($event->sourceBible, "Events"). " (".$event->sLang.")" ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo Language::show("l2_3_events", "Events", array(3)) ?></h1>
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
                        <th><?php echo Language::show("source", "Events") ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["myCheckerL3Events"] as $event): ?>
                        <tr>
                            <td><a href="/events/checker_l3/<?php echo $event->eventID ?>"><?php echo $event->name ?></a></td>
                            <td><?php echo $event->tLang ?></td>
                            <td><?php echo Language::show($event->bookProject, "Events") ?></td>
                            <td><?php echo Language::show($event->sourceBible, "Events"). " (".$event->sLang.")" ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>