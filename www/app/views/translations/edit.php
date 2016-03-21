<script>
    var memberID = <?php echo \Helpers\Session::get('memberID');?>;
    var tID = <?php echo $data['verses'][0]->tID; ?>;
    var aT = '<?php echo \Helpers\Session::get('authToken');?>';
</script>

<div class="chat">
    <ul id="messages"></ul>
    <form action="" class="form-inline">
        <div class="form-group">
            <textarea id="m" class="form-control"></textarea>
        </div>
    </form>
</div>

<div class="members_online panel panel-info">
    <div class="panel-heading">Members Online</div>
    <ul id="online" class="panel-body"></ul>
</div>

<!-- Audio for missed chat messages -->
<audio id="missedMsg">
    <source src="<?php echo \Helpers\Url::templatePath()?>sounds/missed.ogg" type="audio/ogg" />
</audio>

<script src="https://cdn.socket.io/socket.io-1.4.5.js"></script>
<script src="<?php echo \Helpers\Url::templatePath()?>js/chat.js"></script>