<?php
use \Core\Language;
use Helpers\Constants\EventStates;
use \Helpers\Session;

$profile = Session::get("profile");

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

                <form action="/events/rpc/apply_event" method="post" id="applyEvent" style="width: 700px;">
                    <div class="form-group">
                        <h3 class="ftr">Apply as Translator</h3>
                        <h3 class="fl2" style="display: none">Apply as L2 Checker</h3>
                        <h3 class="fl3" style="display: none">Apply as L3 Checker</h3>
                    </div>

                    <div class="checker_info">
                        <div class="form-group">
                            <label class="church_role"><?php echo Language::show('church_role', 'Members'); ?>: </label>
                            <div class="form-control">
                                <label><input type="checkbox" name="church_role[]" value="Elder"
                                        <?php echo isset($profile["church_role"]) && in_array("Elder", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('elder', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Bishop"
                                        <?php echo isset($profile["church_role"]) && in_array("Bishop", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('bishop', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Pastor"
                                        <?php echo isset($profile["church_role"]) && in_array("Pastor", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('pastor', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Teacher"
                                        <?php echo isset($profile["church_role"]) && in_array("Teacher", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('teacher', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Denominational Leader"
                                        <?php echo isset($profile["church_role"]) && in_array("Denominational Leader", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('denominational_leader', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Seminary Professor"
                                        <?php echo isset($profile["church_role"]) && in_array("Seminary Professor", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('seminary_professor', 'Members'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo Language::show('orig_langs', 'Members'); ?>: </label>
                            <div class="form-control">
                                <label class="hebrew_knwlg"><?php echo Language::show('hebrew_knwlg', 'Members'); ?>: </label> &nbsp;&nbsp;
                                <label><input type="radio" name="hebrew_knwlg" value="0"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 0 ? "checked" : "" ?>> <?php echo Language::show('none', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="1"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 1 ? "checked" : "" ?>> <?php echo Language::show('limited', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="2"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 2 ? "checked" : "" ?>> <?php echo Language::show('moderate', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="3"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 3 ? "checked" : "" ?>> <?php echo Language::show('strong', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="4"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 4 ? "checked" : "" ?>> <?php echo Language::show('expert', 'Members'); ?> &nbsp;</label>
                            </div>
                            <br>
                            <div class="form-control">
                                <label class="greek_knwlg"><?php echo Language::show('greek_knwlg', 'Members'); ?>: </label> &nbsp;&nbsp;
                                <label><input type="radio" name="greek_knwlg" value="0"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 0 ? "checked" : "" ?>> <?php echo Language::show('none', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="1"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 1 ? "checked" : "" ?>> <?php echo Language::show('limited', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="2"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 2 ? "checked" : "" ?>> <?php echo Language::show('moderate', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="3"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 3 ? "checked" : "" ?>> <?php echo Language::show('strong', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="4"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 4 ? "checked" : "" ?>> <?php echo Language::show('expert', 'Members'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="education"><?php echo Language::show('education', 'Members'); ?>: </label>
                            <div class="form-control">
                                <label><input type="checkbox" name="education[]" value="BA"
                                        <?php echo isset($profile["education"]) && in_array("BA", $profile["education"]) ? "checked" : "" ?>> <?php echo Language::show('ba_edu', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="education[]" value="MA"
                                        <?php echo isset($profile["education"]) && in_array("MA", $profile["education"]) ? "checked" : "" ?>> <?php echo Language::show('ma_edu', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="education[]" value="PHD"
                                        <?php echo isset($profile["education"]) && in_array("PHD", $profile["education"]) ? "checked" : "" ?>> <?php echo Language::show('phd_edu', 'Members'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="ed_area"><?php echo Language::show('ed_area', 'Members'); ?>: </label>
                            <div class="form-control">
                                <label><input type="checkbox" name="ed_area[]" value="Theology"
                                        <?php echo isset($profile["ed_area"]) && in_array("Theology", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo Language::show('theology', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="ed_area[]" value="Pastoral Ministry"
                                        <?php echo isset($profile["ed_area"]) && in_array("Pastoral Ministry", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo Language::show('pastoral_ministry', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="ed_area[]" value="Bible Translation"
                                        <?php echo isset($profile["ed_area"]) && in_array("Bible Translation", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo Language::show('bible_translation', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="ed_area[]" value="Exegetics"
                                        <?php echo isset($profile["ed_area"]) && in_array("Exegetics", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo Language::show('exegetics', 'Members'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="ed_place"><?php echo Language::show('ed_place', 'Members'); ?>: </label>
                            <input type="text" class="form-control" name="ed_place"
                                   value="<?php echo isset($profile["ed_place"]) ? $profile["ed_place"] : "" ?>">
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