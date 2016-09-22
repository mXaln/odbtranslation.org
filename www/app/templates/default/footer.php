<?php
/**
 * Sample layout
 */

use Helpers\Assets;
use Helpers\Url;
use Helpers\Hooks;

//initialise hooks
$hooks = Hooks::get();

$code = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : "en";
?>

    </div>

    <div class="footer row">
        <div class="col-sm-10">
            v-mast.com
        </div>
        <div class="col-sm-2 footer_langs">
            <?php if(\Helpers\Session::get("loggedin")): ?>
                <div class="dropup flangs">
                    <div class="dropdown-toggle" id="footer_langs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="/app/templates/default/img/<?php echo $code?>.png">
                        <span class="caret"></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="footer_langs">
                        <li><a href="/lang/en"><img src="/app/templates/default/img/en.png"> English</a></li>
                        <li><a href="/lang/ru"><img src="/app/templates/default/img/ru.png"> Русский</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
//hook for plugging in code into the footer
$hooks->run('footer');
?>

</body>
</html>
