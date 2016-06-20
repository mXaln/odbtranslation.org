<?php
use \Core\Language;
use Helpers\Constants\EventStates;

if(!empty($data["project"])):
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo $data["project"][0]->langName . " [".Language::show($data["project"][0]->bookProject, "Events")."]" ?></h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-6">
                <ul class="nav nav-pills book-parts">
                    <li role="presentation" class="active"><a href="#old_test"><?php echo Language::show("old_test", "Events") ?></a></li>
                    <li role="presentation"><a href="#new_test"><?php echo Language::show("new_test", "Events") ?></a></li>
                </ul>
            </div>
            <div class="add-event-btn col-sm-6"></div>
        </div>

        <?php foreach($data["events"] as $event): ?>
            <?php if($event->abbrID == 1): ?>
            <div class="row" id="old_test">
                <div class="col-sm-12">
            <?php elseif($event->abbrID == 41): ?>
                <div class="row" id="new_test">
                    <div class="col-sm-12">
            <?php endif; ?>
                <?php if($event->abbrID == 1 || $event->abbrID == 41): ?>
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th>Book</th>
                        <th>Translators</th>
                        <th>Level 2 Checkers</th>
                        <th>Level 3 Checkers</th>
                        <th>From</th>
                        <th>To</th>
                        <th>State</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                <?php endif; ?>
                        <tr>
                            <td><?php echo $event->name ?></td>
                            <td><?php echo ($event->translators>0 ? '<a href="#translators" data="'.$event->eventID.'">' : '') . $event->translators . "/" . (integer)$event->translatorsNum . ($event->translators>0 ? '</a>' : '') ?></td>
                            <td><?php echo ($event->checkers_l2>0 ? '<a href="#checkers_l2" data="'.$event->eventID.'">' : '') . $event->checkers_l2 . "/" . (integer)$event->l2CheckersNum . ($event->checkers_l2>0 ? '</a>' : '') ?></td>
                            <td><?php echo ($event->checkers_l3>0 ? '<a href="#checkers_l3" data="'.$event->eventID.'">' : '') . $event->checkers_l3 . "/" . (integer)$event->l3CheckersNum . ($event->checkers_l3>0 ? '</a>' : '') ?></td>
                            <td><?php echo $event->dateFrom ?></td>
                            <td><?php echo $event->dateTo ?></td>
                            <td><?php echo $event->state ?></td>
                            <td>
                                <?php
                                switch($event->state)
                                {
                                    case null:
                                        echo '<button data="'.$event->code.'" data2="'.$event->name.'" data3="'.$event->chaptersNum.'" class="btn btn-primary startEvnt">Start</button>';
                                        break;

                                    case EventStates::STARTED:

                                        break;
                                }
                                ?>
                            </td>
                        </tr>

            <?php if($event->abbrID == 39): ?>
                    </tbody>
                </table>
                </div>
            </div>
            <?php elseif($event->abbrID == 67): ?>
                    </tbody>
                </table>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach ?>
    </div>
</div>

<div class="event-content form-panel">
    <div class="create-event-content panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title">Start Event</h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div class="bookName"></div>

            <div class="errors"></div>

            <div class="row">
                <div class="col-sm-6">
                    <form action="/admin/rpc/create_event" method="post" id="startEvent">
                        <div class="form-group">
                            <label for="translators"><?php echo Language::show('translators', 'Events'); ?></label>
                            <input type="text" class="form-control" id="translators" name="translators" value="<?php if(isset($error)){ echo $_POST['translators']; } ?>">
                        </div>

                        <div class="form-group">
                            <label for="checkers_l2"><?php echo Language::show('checkers_l2', 'Events'); ?></label>
                            <input type="text" class="form-control" id="checkers_l2" name="checkers_l2" value="<?php if(isset($error)){ echo $_POST['checkers_l2']; } ?>">
                        </div>

                        <div class="form-group">
                            <label for="checkers_l3"><?php echo Language::show('checkers_l3', 'Events'); ?></label>
                            <input type="text" class="form-control" id="checkers_l3" name="checkers_l3" value="<?php if(isset($error)){ echo $_POST['checkers_l3']; } ?>">
                        </div>

                        <div class="form-group">
                            <label for="cal_from"><?php echo Language::show('cal_from', 'Events'); ?></label>
                            <input type="text" class="form-control" id="cal_from" name="cal_from" value="<?php if(isset($error)){ echo $_POST['cal_from']; } ?>">
                        </div>

                        <div class="form-group">
                            <label for="cal_to"><?php echo Language::show('cal_to', 'Events'); ?></label>
                            <input type="text" class="form-control" id="cal_to" name="cal_to" value="<?php if(isset($error)){ echo $_POST['cal_to']; } ?>">
                        </div>

                        <input type="hidden" name="book_code" id="bookCode" value="" />
                        <input type="hidden" name="projectID" id="projectID" value="<?php echo $data["project"][0]->projectID?>" />
                        <input type="hidden" name="bookProject" id="bookProject" value="<?php echo $data["project"][0]->bookProject?>" />
                        <input type="hidden" name="sourceLangID" id="sourceLangID" value="<?php echo $data["project"][0]->sourceLangID?>" />

                        <br><br>

                        <button type="submit" name="startEvent" class="btn btn-primary"><?php echo Language::show('Start', 'Events'); ?></button>
                        <img class="startEventLoader" width="24px" src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif">
                    </form>
                </div>

                <div class="col-sm-6">
                    <div class="book_info">
                        <!--<img class="bookInfoLoader" width="24px" src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif">-->
                        <div class="book_info_content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div>Project does not exist or you do not have rights to see it</div>
<?php endif; ?>