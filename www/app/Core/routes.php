<?php
/**
 * Routes - all standard routes are defined here.
 *
 * @author David Carr - dave@daveismyname.com
 * @version 2.2
 * @date updated Sept 19, 2015
 */

/** Create alias for Router. */
use Core\Router;
use Helpers\Hooks;

/** Define routes. */
Router::any('', 'Controllers\MainController@index');
Router::any('about', 'Controllers\MainController@about');
Router::any('contact', 'Controllers\MainController@contactUs');
Router::any('lang/(:any)', 'Controllers\MainController@lang');

Router::any('translations', 'Controllers\TranslationsController@index');
Router::any('translations/(:any)', 'Controllers\TranslationsController@index');
Router::any('translations/(:any)/(:any)', 'Controllers\TranslationsController@index');
Router::any('translations/(:any)/(:any)/(:any)', 'Controllers\TranslationsController@index');

Router::any('events', 'Controllers\EventsController@index');
Router::any('events/project/(:num)', 'Controllers\EventsController@project');
Router::any('events/rpc/apply_event', 'Controllers\EventsController@applyEvent');
Router::any('events/rpc/get_notifications', 'Controllers\EventsController@getNotifications');
Router::any('events/rpc/save_comment', 'Controllers\EventsController@saveComment');
Router::any('events/rpc/save_comment_alt', 'Controllers\EventsController@saveCommentAlt');
Router::any('events/rpc/get_partner_translation', 'Controllers\EventsController@getPartnerTranslation');
Router::any('events/translator/(:num)', 'Controllers\EventsController@translator');
Router::any('events/information/(:num)', 'Controllers\EventsController@information');
Router::any('events/checker/(:num)/(:num)', 'Controllers\EventsController@checker');
Router::any('events/checker/(:num)/(:num)/apply', 'Controllers\EventsController@applyChecker');
Router::any('events/checker_l2/(:num)', 'Controllers\EventsController@checkerL2');
Router::any('events/checker_l3/(:num)', 'Controllers\EventsController@checkerL3');
Router::any('events/notifications', 'Controllers\EventsController@allNotifications');

Router::any('members', 'Controllers\MembersController@index');
Router::any('members/profile', 'Controllers\MembersController@profile');
Router::any('members/login', 'Controllers\MembersController@login');
Router::any('members/logout', 'Controllers\MembersController@logout');
Router::any('members/passwordreset', 'Controllers\MembersController@passwordReset');
Router::any('members/resetpassword/(:num)/(:any)', 'Controllers\MembersController@resetPassword');
Router::any('members/activate/(:num)/(:any)', 'Controllers\MembersController@activate');
Router::any('members/activate/resend/(:any)', 'Controllers\MembersController@resendActivation');
Router::any('members/success', 'Controllers\MembersController@success');
Router::any('members/rpc/auth/(:num)/(:num)/(:any)', 'Controllers\MembersController@rpcAuth');

Router::any('admin', 'Controllers\admin\AdminController@index');
Router::any('admin/project/(:num)', 'Controllers\admin\AdminController@project');
Router::any('admin/rpc/create_gw_project', 'Controllers\admin\AdminController@createGwProject');
Router::any('admin/rpc/get_gw_project', 'Controllers\admin\AdminController@getGwProject');
Router::any('admin/rpc/create_project', 'Controllers\admin\AdminController@createProject');
Router::any('admin/rpc/get_members', 'Controllers\admin\AdminController@getMembers');
Router::any('admin/rpc/get_target_languages', 'Controllers\admin\AdminController@getTargetLanguagesByGwLanguage');
Router::any('admin/rpc/create_event', 'Controllers\admin\AdminController@createEvent');
Router::any('admin/rpc/get_source', 'Controllers\admin\AdminController@getSource');

/** Module routes. */
$hooks = Hooks::get();
$hooks->run('routes');

/** If no route found. */
Router::error('Core\Error@index');

/** Turn on old style routing. */
Router::$fallback = false;

/** Execute matched routes. */
Router::dispatch();
