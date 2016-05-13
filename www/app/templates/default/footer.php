<?php
/**
 * Sample layout
 */

use Helpers\Assets;
use Helpers\Url;
use Helpers\Hooks;

//initialise hooks
$hooks = Hooks::get();
?>

</div>

<?php
//hook for plugging in code into the footer
$hooks->run('footer');
?>

<div class="footer">

</div>

</body>
</html>
