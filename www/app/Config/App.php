<?php
/**
 * Application Configuration
 *
 * @author David Carr - dave@daveismyname.com
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

use Config\Config;


/**
 * The Application configuration.
 */
Config::set('app', array(
    /**
     * Vmast server type
     * Local one will not use features that require internet connection
     */
    'type' => 'local', // Possible values: local, remote.
    /**
     * Maintenance mode
     * And ip addresses that are excluded
     */
    'isMaintenance' => false,
    'ips' => [],
    /**
     * Debug Mode
     */
    'debug' => (ENVIRONMENT == 'development'), // When enabled the actual PHP errors will be shown. development|production

    /**
     * The Website URL.
     */
    'url' => 'https://odbtranslation.dev/',

    /**
    * The Administrator's E-mail Address.
    */
    'email' => 'admin@odbtranslation.dev',

    /**
     * The Website Path.
     */
    'path' => '/',

    /**
     * Website Name.
     */
    'name' => 'ODB Translation',

    /**
     * The default Template.
     */
    'template' => 'Default',

    /**
     * The Backend's Color Scheme.
     */
    'color_scheme' => 'blue',

    /**
     * The default locale that will be used by the translation.
     */
    'locale' => 'en',

    /**
     * The default Timezone for your website.
     * http://www.php.net/manual/en/timezones.php
     */
    'timezone' => 'UTC',

    /**
     * The Encryption Key.
     * This tool can be used to generate key - http://jeffreybarke.net/tools/codeigniter-encryption-key-generator
     */
    'key' => 'W4YmHWeaqUTHBZGVKhIuj6oIWPqOE7GD',

    /**
     *  Prevents the website from CSRF attacks.
     */
    'csrf' => true,

    /**
     * The registered Service Providers.
     */
    'providers' => array(
        'Auth\AuthServiceProvider',
        'Cache\CacheServiceProvider',
        'Routing\RoutingServiceProvider',
        'Cookie\CookieServiceProvider',
        'Language\LanguageServiceProvider',
        'Module\ModuleServiceProvider',
        'Database\DatabaseServiceProvider',
        'Encryption\EncryptionServiceProvider',
        'Filesystem\FilesystemServiceProvider',
        'Hashing\HashServiceProvider',
        'Log\LogServiceProvider',
        'Mail\MailServiceProvider',
        'Pagination\PaginationServiceProvider',
        'Redis\RedisServiceProvider',
        'Auth\Reminders\ReminderServiceProvider',
        'Session\SessionServiceProvider',
        'Validation\ValidationServiceProvider',
        'Html\HtmlServiceProvider',
        'View\ViewServiceProvider',
        'Template\TemplateServiceProvider',
        'Cron\CronServiceProvider',
    ),

    /**
     * The Service Providers Manifest path.
     */
    'manifest' => STORAGE_PATH,

    /**
     * The registered Class Aliases.
     */
    'aliases' => array(
        // The Helpers.
        'Mail'          => 'Helpers\Mailer',
        'Assets'        => 'Helpers\Assets',
        'Csrf'          => 'Helpers\Csrf',
        'Data'          => 'Helpers\Data',
        'Date'          => 'Helpers\Date',
        'Document'      => 'Helpers\Document',
        'Encrypter'     => 'Helpers\Encrypter',
        'Error'         => 'Shared\Legacy\Error',
        'FastCache'     => 'Helpers\FastCache',
        'Form'          => 'Helpers\Form',
        'Ftp'           => 'Helpers\Ftp',
        'GeoCode'       => 'Helpers\GeoCode',
        'Hooks'         => 'Helpers\Hooks',
        'Inflector'     => 'Helpers\Inflector',
        'Number'        => 'Helpers\Number',
        'ReCaptcha'     => 'Helpers\ReCaptcha',
        'ReservedWords' => 'Helpers\ReservedWords',
        'SimpleCurl'    => 'Helpers\SimpleCurl',
        'TableBuilder'  => 'Helpers\TableBuilder',
        'Tags'          => 'Helpers\Tags',

        // The Forensics Console.
        'Console'       => 'Forensics\Console',

        // The Support Classes.
        'Arr'           => 'Support\Arr',
        'Str'           => 'Support\Str',

        // The Support Facades.
        'App'           => 'Support\Facades\App',
        'Auth'          => 'Support\Facades\Auth',
        'Cache'         => 'Support\Facades\Cache',
        'Config'        => 'Support\Facades\Config',
        'Cookie'        => 'Support\Facades\Cookie',
        'Crypt'         => 'Support\Facades\Crypt',
        'DB'            => 'Support\Facades\DB',
        'Event'         => 'Support\Facades\Event',
        'File'          => 'Support\Facades\File',
        'Hash'          => 'Support\Facades\Hash',
        'Input'         => 'Support\Facades\Input',
        'Language'      => 'Support\Facades\Language',
        'Mailer'        => 'Support\Facades\Mailer',
        'Paginator'     => 'Support\Facades\Paginator',
        'Password'      => 'Support\Facades\Password',
        'Redirect'      => 'Support\Facades\Redirect',
        'Redis'         => 'Support\Facades\Redis',
        'Request'       => 'Support\Facades\Request',
        'Response'      => 'Support\Facades\Response',
        'Route'         => 'Support\Facades\Route',
        'Router'        => 'Support\Facades\Router',
        'Session'       => 'Support\Facades\Session',
        'Validator'     => 'Support\Facades\Validator',
        'Log'           => 'Support\Facades\Log',
        'URL'           => 'Support\Facades\URL',
        'Form'          => 'Support\Facades\Form',
        'HTML'          => 'Support\Facades\HTML',
        'Template'      => 'Support\Facades\Template',
        'View'          => 'Support\Facades\View',
        'Cron'          => 'Support\Facades\Cron',
        'Module'        => 'Support\Facades\Module',

        // The Compatibility Support.
        'Errors'        => 'Shared\Legacy\Error',

        //
        'Core\Controller' => 'Shared\Legacy\Controller',
        'Core\Model'      => 'Shared\Legacy\Model',
        'Core\Template'   => 'Support\Facades\Template',
        'Core\View'       => 'Support\Facades\View',
    ),
));
