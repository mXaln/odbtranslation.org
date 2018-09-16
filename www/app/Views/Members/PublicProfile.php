<?php 
use Shared\Legacy\Error;

echo Error::display($error);
?>

<?php if(!isset($error)): ?>

<h1><?php echo __('profile_message'); ?></h1>

<?php
$profile = $data["profile"];

$numValues = [
    "0" => [
        "orig" => __("none")
    ],
    "1" => [
        "lang" => __('moderate'),
        "geo" => __('less_than', array(2)),
        "years" => "0",
        "degree" => __("weak"),
        "freq" => __("rarely"),
        "orig" => __("limited")
    ],
    "2" => [
        "lang" => __('strong'),
        "geo" => __('years', array("2-4")),
        "years" => "1",
        "degree" => __("moderate"),
        "freq" => __("some"),
        "orig" => __("moderate")
    ],
    "3" => [
        "lang" => __('fluent'),
        "geo" => __('years', array("5-7")),
        "years" => "2",
        "degree" => __("strong"),
        "freq" => __("much"),
        "orig" => __("strong")
    ],
    "4" => [
        "lang" => __('native'),
        "geo" => __('years', array("8-10")),
        "years" => "3+",
        "degree" => __("expert"),
        "freq" => __("frequently"),
        "orig" => __("expert")
    ],
    "5" => [
        "lang" => __('expert'),
    ],
];
?>

<div class="public_profile">
    <div class="avatar_control_public">
        <img class="public_avatar" src="<?php echo template_url("img/avatars/".$profile["avatar"].".png") ?>">
        <div class="public_name">
            <div class="public_uname"><?php echo $profile["username"] ?></div>
            <div class="public_fname"><?php echo $profile["fullname"] ?></div>
        </div>
    </div>

    <h3><?php echo __('common_skills'); ?></h3>

    <div class="languages_public public_item">
        <label><?php echo __('known_languages_public'); ?>:</label>
        <div class="languages_public_list">
            <?php foreach ($profile["languages"] as $lang => $values):?>
                <?php
                $langName = $data["languages"][$lang]["langName"];
                $angName = $data["languages"][$lang]["angName"];
                $language = "[".$lang."] ".$langName . ($angName != "" && $angName != $langName ? " (".$angName.")" : "");
                ?>
                <div class="languages_public_lang"><?php echo $language?></div>
                <div class="languages_public_fluency">
                    <label><?php echo __('language_fluency_public'); ?>:</label>
                    <?php echo $numValues[$values["lang_fluency"]]["lang"]?>
                </div>
                <!--<div class="languages_public_years">
                    <label><?php echo __('lang_geographic_years_public'); ?>:</label>
                    <?php echo $numValues[$values["geo_lang_yrs"]]["geo"]?>
                </div>-->
                <div class="clear"></div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="public_item">
        <label><?php echo __('prefered_roles'); ?>: </label>
        <?php
        $preferedRoles = array_map(function($value) {
            return __($value);
        }, $profile["prefered_roles"]);
        echo join(", ", $preferedRoles) ?>
    </div>

    <div class="public_item">
        <label><?php echo __('bbl_trans_yrs_public'); ?>:</label>
        <?php echo $numValues[$profile["bbl_trans_yrs"]]["years"] ?>
    </div>

    <div class="public_item">
        <label><?php echo __('othr_trans_yrs_public'); ?>:</label>
        <?php echo $numValues[$profile["othr_trans_yrs"]]["years"] ?>
    </div>

    <div class="public_item">
        <label><?php echo __('bbl_knwlg_degr_public'); ?>: </label>
        <?php echo $numValues[$profile["bbl_knwlg_degr"]]["degree"] ?>
    </div>

    <div class="public_item">
        <label><?php echo __('mast_evnts_public'); ?>:</label>
        <?php echo $numValues[$profile["mast_evnts"]]["years"] ?>
    </div>

    <div class="public_item">
        <label><?php echo __('mast_role_public'); ?>: </label>
        <?php
        $mastRoles = array_map(function($value) {
            return __($value);
        }, $profile["mast_role"]);
        echo join(", ", $mastRoles) ?>
    </div>

    <div class="public_item">
        <label><?php echo __('teamwork_public'); ?>:</label>
        <?php echo $numValues[$profile["teamwork"]]["freq"] ?>
    </div>

    <h3><?php echo __('facilitator_skills'); ?></h3>

    <div class="public_item">
        <label><?php echo __('mast_facilitator_public'); ?>:</label>
        <?php echo $profile["mast_facilitator"] ? __("yes") : __("no") ?>
    </div>

    <?php if($profile["mast_facilitator"]): ?>
    <div class="public_item">
        <label><?php echo __('org_public'); ?>: </label>
        <?php echo $profile["org"] ?>
    </div>

    <div class="public_item">
        <label><?php echo __('ref_person'); ?>: </label>
        <?php echo $profile["ref_person"] ?>
    </div>

    <div class="public_item">
        <label><?php echo __('ref_email'); ?>: </label>
        <?php echo $profile["ref_email"] ?>
    </div>
    <?php endif; ?>

    <h3><?php echo __('checker_skills'); ?></h3>

    <div class="public_item">
        <label><?php echo __('church_role_public'); ?>: </label>
        <?php
        $churchRoles = array_map(function($value) {
            return __(strtolower($value));
        }, $profile["church_role"]);
        echo join(", ", $churchRoles) ?>
    </div>

    <div class="public_item">
        <label><?php echo __('orig_langs_public'); ?>: </label>
        <div class="orig_lang">
            <label><?php echo __('hebrew_knwlg'); ?>: </label> &nbsp;&nbsp;
            <?php echo $numValues[$profile["hebrew_knwlg"]]["orig"] ?>
        </div>
        <div class="orig_lang">
            <label><?php echo __('greek_knwlg'); ?>: </label> &nbsp;&nbsp;
            <?php echo $numValues[$profile["greek_knwlg"]]["orig"] ?>
        </div>
    </div>

    <div class="public_item">
        <label><?php echo __('education_public'); ?>: </label>
        <?php
        $education = array_map(function($value) {
            return __(strtolower($value)."_edu");
        }, $profile["education"]);
        echo join(", ", $education) ?>
    </div>

    <div class="public_item">
        <label><?php echo __('ed_area_public'); ?>: </label>
        <?php
        $edArea = array_map(function($value) {
            return __(preg_replace("/\s/", "_", strtolower($value)));
        }, $profile["ed_area"]);
        echo join(", ", $edArea) ?>
    </div>

    <div class="public_item">
        <label><?php echo __('ed_place'); ?>: </label>
        <?php echo $profile["ed_place"] ?>
    </div>

    <h3><?php echo __('translator_activities'); ?></h3>
    <?php if(!empty($data["translation_activities"])): ?>
        <?php foreach ($data["translation_activities"] as $t_acts): ?>
            <div>
                <?php
                echo $t_acts->tLang . " - "
                    . __($t_acts->sourceBible) . " - "
                    . $t_acts->name . " "
                    . "(" . __("chapters") . ": " . $t_acts->chapters . ")"
                ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div>No activities</div>
    <?php endif; ?>


    <h3><?php echo __('facilitator_activities'); ?></h3>
    <?php if(!empty($data["facilitation_activities"])): ?>
        <?php foreach ($data["facilitation_activities"] as $f_acts): ?>
            <div><?php echo $f_acts->langName . " - " . __($f_acts->sourceBible) . " - " . $f_acts->name ?></div>
        <?php endforeach; ?>
    <?php else: ?>
        <div>No activities</div>
    <?php endif; ?>

</div>
<?php endif; ?>
