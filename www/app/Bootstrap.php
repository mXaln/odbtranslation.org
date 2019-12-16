<?php
/**
 * Bootstrap - the Application's specific Bootstrap.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

use Config\Config;

Sentry\init(['dsn' => Config::get("sentry.dsn") ]);
