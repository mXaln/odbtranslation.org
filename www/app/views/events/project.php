<?php
use \Core\Language;
use Helpers\Constants\EventStates;
use \Helpers\Session;

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
                    <li role="presentation" class="active"><a href="#old_test">Old Testament</a></li>
                    <li role="presentation"><a href="#new_test">New Testament</a></li>
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

                <div class="row event-block">
                    <div class="col-sm-3 event-box">
                        <div class="book-name"><?php echo $event->name ?></div>
                    </div>

                    <div class="col-sm-3">
                        <div class="event-box">
                            <div class="event-header">Draft 1</div>

                            <div>Translators: <?php echo $event->translators . "/" . (integer)$event->translatorsNum ?></div>

                            <div>
                                <?php echo $event->translatorsNum > $event->translators
                                    ? '<button data="'.$event->code.'" data2="'.$event->name.'" data3="d1" class="btn btn-primary applyDraft applyEvent">Apply</button>'
                                    : 'N/A' ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="event-box">
                            <div class="event-header">Level 2 Check</div>

                            <div>Checkers: <?php echo $event->checkers_l2 . "/" . (integer)$event->l2CheckersNum ?></div>

                            <div>
                                <?php echo $event->l2CheckersNum > $event->checkers_l2
                                    ? '<button data="'.$event->code.'" data2="'.$event->name.'" data3="l2" class="btn btn-primary applyL2Checkers applyEvent">Apply</button>'
                                    : 'N/A' ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3 event-box">
                        <div class="event-box">
                            <div class="event-header">Level 3 Check</div>

                            <div>Checkers: <?php echo $event->checkers_l3 . "/" . (integer)$event->l3CheckersNum ?></div>

                            <div>
                                <?php echo $event->l3CheckersNum > $event->checkers_l3
                                    ? '<button data="'.$event->code.'" data2="'.$event->name.'" data3="l3" class="btn btn-primary applyL3Checkers applyEvent">Apply</button>'
                                    : 'N/A' ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php if($event->abbrID == 39): ?>
                </div>
            </div>
            <?php elseif($event->abbrID == 67): ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach ?>

    </div>
</div>

<div class="event-content form-panel">
    <div class="create-event-content panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"></h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div class="col-sm-8">
                <div class="bookName"></div>

                <div class="errors"></div>

                <form action="/events/rpc/apply_event" method="post" id="applyEvent" style="width: 500px;">
                    <div class="form-group">
                        <h3 class="ftr">Apply as Translator</h3>
                        <h3 class="fl2" style="display: none">Apply as L2 Checker</h3>
                        <h3 class="fl3" style="display: none">Apply as L3 Checker</h3>
                    </div>

                    <div class="checker_info">
                        <div class="form-group">
                            <label for="churchName"><?php echo Language::show('churchName', 'Events'); ?></label>
                            <input type="text" class="form-control" id="churchName" name="churchName" placeholder="<?php echo Language::show('church_name', 'Events'); ?>" value="<?php echo Session::get("churchName") ?>">
                        </div>

                        <div class="form-group">
                            <label for="position"><?php echo Language::show('position', 'Events'); ?></label>
                            <input type="text" class="form-control" id="position" name="position" placeholder="<?php echo Language::show('position', 'Events'); ?>" value="<?php echo Session::get("position") ?>">
                        </div>

                        <div class="form-group">
                            <label for="expYears"><?php echo Language::show('expYears', 'Events'); ?></label>
                            <input type="text" class="form-control" id="expYears" name="expYears" placeholder="<?php echo Language::show('exp_years', 'Events'); ?>" value="<?php echo Session::get("expYears") > 0 ? Session::get("expYears") : "" ?>">
                        </div>

                        <div class="form-group">
                            <label for="education"><?php echo Language::show('education', 'Events'); ?></label>
                            <input type="text" class="form-control" id="education" name="education" placeholder="<?php echo Language::show('education', 'Events'); ?>" value="<?php echo Session::get("education") ?>">
                        </div>

                        <div class="form-group">
                            <label for="educationPlace"><?php echo Language::show('educationPlace', 'Events'); ?></label>
                            <input type="text" class="form-control" id="educationPlace" name="educationPlace" placeholder="<?php echo Language::show('education_place', 'Events'); ?>" value="<?php echo Session::get("educationPlace") ?>">
                        </div>
                    </div>

                    <input type="hidden" name="book_code" id="bookCode" value="" />
                    <input type="hidden" name="projectID" id="projectID" value="<?php echo $data["project"][0]->projectID?>" />
                    <input type="hidden" name="userType" value="translator">

                    <br><br>

                    <button type="submit" name="applyEvent" class="btn btn-primary"><?php echo Language::show('Apply Now', 'Events'); ?></button>
                    <img class="applyEventLoader" width="24px" src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif">
                </form>
            </div>

            <div class="col-sm-4">
                <div class="help_info">
                    <div class="help_title">Help</div>
                    <div class="help_name">Translator</div>
                    <div class="help_descr">Description about translator's role</div>

                    <div class="help_name">Checker</div>
                    <div class="help_descr">Description about checker's role</div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php else: ?>
<div>Project does not exist or you do not have rights to see it</div>
<?php endif; ?>