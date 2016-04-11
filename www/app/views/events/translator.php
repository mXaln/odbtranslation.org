<?php
use \Helpers\Url;
use \Helpers\Constants\EventSteps;
use \Core\Language;
use \Helpers\Session;

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
        <li class="chunking-step <?php echo $data["event"][0]->step == EventSteps::CHUNKING ||
                $data["event"][0]->step == EventSteps::PRE_CHUNKING ? "active" : "" ?>">
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

<script>
    var memberID = <?php echo Session::get('memberID') ;?>;
    var eventID = <?php echo $data["event"][0]->eventID; ?>;
    var cotrID = <?php echo $data["event"][0]->cotrID; ?>;
    var aT = '<?php echo Session::get('authToken'); ?>';
    var step = '<?php echo $data["event"][0]->step; ?>';

    var peerStep = '<?php echo EventSteps::PEER_REVIEW; ?>';
    var keywordStep = '<?php echo EventSteps::KEYWORD_CHECK; ?>';
    var contentStep = '<?php echo EventSteps::CONTENT_REVIEW; ?>';
</script>

<div id="chat_container" class="closed">
    <div id="chat_hide" class="glyphicon glyphicon-chevron-left"></div>

    <div class="chat panel panel-info">
        <div class="chat_tabs panel-heading">
            <div class="row">
                <div id="p2p" class="col-sm-4 chat_tab active">Partner</div>
                <div id="evnt" class="col-sm-4 chat_tab">Event</div>
            </div>
        </div>
        <ul id="p2p_messages"></ul>
        <ul id="evnt_messages"></ul>
        <form action="" class="form-inline">
            <div class="form-group">
                <textarea id="m" class="form-control"></textarea>
                <input type="hidden" id="chat_type" value="p2p" />
            </div>
        </form>
    </div>

    <div class="members_online panel panel-info">
        <div class="panel-heading">Members Online</div>
        <ul id="online" class="panel-body"></ul>
    </div>

    <div class="clear"></div>
</div>

<!-- Audio for missed chat messages -->
<audio id="missedMsg">
    <source src="<?php echo \Helpers\Url::templatePath()?>sounds/missed.ogg" type="audio/ogg" />
</audio>

<script src="https://cdn.socket.io/socket.io-1.4.5.js"></script>
<script src="<?php echo \Helpers\Url::templatePath()?>js/chat.js"></script>

<?php endif; ?>