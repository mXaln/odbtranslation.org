<?php
use \Helpers\Url;
use \Helpers\Constants\EventSteps;

echo \Core\Error::display($error);

/*$arr = array(
    array(
        "01-01" => 44,
        "01-04" => 44,
        "01-07" => 35,
        "01-11" => 35,
        "01-15" => 35
    ),
    array(
        "02-01" => 44,
        "02-07" => 44,
        "02-09" => 35,
        "02-13" => 35
    ),
    array(
        "03-01" => 56,
        "03-04" => 56,
        "03-07" => 57,
        "03-11" => 57,
        "03-15" => 57
    ),
);

\Helpers\Data::pr($arr);

foreach ($arr as $chap => $chunks) {
    echo (in_array(35, $chunks) ? "chapter: " . ($chap+1)."<br>" : "");
    foreach ($chunks as $chunk => $translator) {
        if($translator == 35)
            echo "\t Chunk: " . $chunk."<br>";
    }
    echo "<br><br>";
}
*/

//echo (integer)preg_replace("/\d+-/", "", "11-03")."<br>";


if(!empty($data["event"])):
?>

<div id="translator_steps" class="open <?php echo $data["event"][0]->step ?>">
    <div id="tr_steps_hide" class="glyphicon glyphicon-chevron-left <?php echo $data["event"][0]->step ?>"></div>

    <ul class="steps_list">
        <li class="<?php echo $data["event"][0]->step == EventSteps::PRAY ? "active" : "" ?>">
            <img src="<?php echo Url::templatePath(); ?>" />
            <span>Prayer and focus</span>
        </li>
        <li class="<?php echo $data["event"][0]->step == EventSteps::CONSUME ? "active" : "" ?>">
            <img src="<?php echo Url::templatePath(); ?>" />
            <span>Reading of text</span>
        </li>
        <li class="<?php echo $data["event"][0]->step == EventSteps::DISCUSS ? "active" : "" ?>">
            <img src="<?php echo Url::templatePath(); ?>" />
            <span>Discussion of text</span>
        </li>
        <li class="<?php echo $data["event"][0]->step == EventSteps::CHUNKING ? "active" : "" ?>">
            <img src="<?php echo Url::templatePath(); ?>" />
            <span>Chunking of text</span>
        </li>
        <li class="<?php echo $data["event"][0]->step == EventSteps::BLIND_DRAFT ? "active" : "" ?>">
            <img src="<?php echo Url::templatePath(); ?>" />
            <span>Blind drafting</span>
        </li>
        <li class="<?php echo $data["event"][0]->step == EventSteps::SELF_CHECK ? "active" : "" ?>">
            <img src="<?php echo Url::templatePath(); ?>" />
            <span>Self check</span>
        </li>
        <li class="<?php echo $data["event"][0]->step == EventSteps::PEER_REVIEW ? "active" : "" ?>">
            <img src="<?php echo Url::templatePath(); ?>" />
            <span>Peer review</span>
        </li>
        <li class="<?php echo $data["event"][0]->step == EventSteps::KEYWORD_CHECK ? "active" : "" ?>">
            <img src="<?php echo Url::templatePath(); ?>" />
            <span>Keyword check</span>
        </li>
        <li class="<?php echo $data["event"][0]->step == EventSteps::CONTENT_REVIEW ? "active" : "" ?>">
            <img src="<?php echo Url::templatePath(); ?>" />
            <span>Content review</span>
        </li>
    </ul>
</div>

<?php endif; ?>




