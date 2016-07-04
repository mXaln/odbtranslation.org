<?php
use \Core\Language;
?>

<?php if(\Helpers\Session::get("isAdmin")): ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title">
                Events for facilitation
                <a href="<?php echo DIR ?>events/demo/pray" class="demo_link">See demo</a>
            </h1>
        </div>

        <div class="form-inline dt-bootstrap no-footer">
            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-bordered table-hover" role="grid">
                        <thead>
                        <tr>
                            <th>Book</th>
                            <th>Target Language</th>
                            <th>Project</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($data["myFacilitatorEvents"] as $event): ?>
                            <tr>
                                <td><a href="/events/information/<?php echo $event->eventID ?>"><?php echo $event->name ?></a></td>
                                <td><?php echo $event->langName ?></td>
                                <td><?php echo Language::show($event->bookProject, "Events") ?></td>
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
        <h1 class="panel-title">Translator Events</h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th>Book</th>
                        <th>Target Language</th>
                        <th>Project</th>
                        <th>Current step</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["myTranslatorEvents"] as $event): ?>
                        <tr>
                            <td><a href="/events/translator/<?php echo $event->eventID ?>"><?php echo $event->name ?></a></td>
                            <td><?php echo $event->tLang ?></td>
                            <td><?php echo Language::show($event->bookProject, "Events") ?></td>
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
        <h1 class="panel-title">Level 1 Check (keyword, content) Events</h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th>Book</th>
                        <th>Target Language</th>
                        <th>Project</th>
                        <th>Current step</th>
                        <th>User</th>
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
        <h1 class="panel-title">Level 2 Check Events</h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th>Book</th>
                        <th>Target Language</th>
                        <th>Project</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["myCheckerL2Events"] as $event): ?>
                        <tr>
                            <td><a href="/events/checker_l2/<?php echo $event->eventID ?>"><?php echo $event->name ?></a></td>
                            <td><?php echo $event->tLang ?></td>
                            <td><?php echo Language::show($event->bookProject, "Events") ?></td>
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
        <h1 class="panel-title">Level 3 Check Events</h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th>Book</th>
                        <th>Target Language</th>
                        <th>Project</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["myCheckerL3Events"] as $event): ?>
                        <tr>
                            <td><a href="/events/checker_l3/<?php echo $event->eventID ?>"><?php echo $event->name ?></a></td>
                            <td><?php echo $event->tLang ?></td>
                            <td><?php echo Language::show($event->bookProject, "Events") ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>