<?php
/**
 * Mailer Configuration
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

use Config\Config;

Config::set('mail', array(
    'driver' => 'smtp',
    'host'   => $_ENV["MAIL_HOST"],
    'port'   => 587,
    'from'   => array(
        'address' => $_ENV["MAIL_FROM"],
        'name'    => 'ODB Translation Team',
    ),
    'encryption' => 'tls',
    'username'   => $_ENV["MAIL_USER"],
    'password'   => $_ENV["MAIL_PASS"],
    'sendmail'   => $_ENV["MAIL_APP"],

    // Whether or not the Mailer will pretend to send the messages.
    'pretend' => false,
));