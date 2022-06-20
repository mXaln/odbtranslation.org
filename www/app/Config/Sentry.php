<?php

use Config\Config;

Config::set('sentry', array(
    'dsn' => $_ENV["SENTRY_DSN"],
    'dsn_js' => $_ENV["SENTRY_DSN_JS"],
    'integrity' => $_ENV["SENTRY_INTEGRITY"]
));
