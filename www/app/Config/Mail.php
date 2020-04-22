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
    'host'   => 'smtp.gmail.com',
    'port'   => 587,
    'from'   => array(
        'address' => 'noreply@v-mast.com',
        'name'    => 'V-Mast Team',
    ),
    'encryption' => 'tls',
    'username'   => '',
    'password'   => '',
    'sendmail'   => '',

    // Whether or not the Mailer will pretend to send the messages.
    'pretend' => false,
));