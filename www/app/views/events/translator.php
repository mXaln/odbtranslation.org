<?php
use \Helpers\Url;
use \Helpers\Constants\EventSteps;
use \Core\Language;

echo \Core\Error::display($error);

if(!empty($data["event"]) && !isset($data["error"])):
?>

<div id="translator_steps" class="open <?php echo $data["event"][0]->step ?>">
    <div id="tr_steps_hide" class="glyphicon glyphicon-chevron-left <?php echo $data["event"][0]->step ?>"></div>

    <ul class="steps_list">
        <li class="pray-step <?php echo $data["event"][0]->step == EventSteps::PRAY ? "active" : "" ?>">
            <span><?php echo Language::show("prayer_focus", "Events")?></span>
        </li>
        <li class="consume-step <?php echo $data["event"][0]->step == EventSteps::CONSUME ? "active" : "" ?>">
            <span><?php echo Language::show("reading_text", "Events")?></span>
        </li>
        <li class="discuss-step <?php echo $data["event"][0]->step == EventSteps::DISCUSS ? "active" : "" ?>">
            <span><?php echo Language::show("text_discussuion", "Events")?></span>
        </li>
        <li class="chunking-step <?php echo $data["event"][0]->step == EventSteps::CHUNKING ? "active" : "" ?>">
            <span><?php echo Language::show("chunking_text", "Events")?></span>
        </li>

        <?php if($data["event"][0]->gwLang != $data["event"][0]->targetLang):?>
        <li class="blind-draft-step <?php echo $data["event"][0]->step == EventSteps::BLIND_DRAFT ? "active" : "" ?>">
            <span><?php echo Language::show("blind_drafting", "Events")?></span>
        </li>
        <?php endif; ?>

        <li class="self-check-step <?php echo $data["event"][0]->step == EventSteps::SELF_CHECK ? "active" : "" ?>">
            <span><?php echo Language::show("self_check", "Events")?></span>
        </li>
        <li class="peer-review-step <?php echo $data["event"][0]->step == EventSteps::PEER_REVIEW ? "active" : "" ?>">
            <span><?php echo Language::show("peer_review", "Events")?></span>
        </li>
        <li class="keyword-check-step <?php echo $data["event"][0]->step == EventSteps::KEYWORD_CHECK ? "active" : "" ?>">
            <span><?php echo Language::show("keyword_check", "Events")?></span>
        </li>
        <li class="content-review-step <?php echo $data["event"][0]->step == EventSteps::CONTENT_REVIEW ? "active" : "" ?>">
            <span><?php echo Language::show("content_review", "Events")?></span>
        </li>
    </ul>
</div>

<?php endif; ?>