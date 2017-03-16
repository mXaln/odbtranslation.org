<?php
/**
 * Routes - all standard Routes are defined here.
 *
 * @author David Carr - dave@daveismyname.com
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */


/** Define static routes. */

// The default Routing
Router::any("", "App\Controllers\MainController@index");
Router::any("about", "App\Controllers\MainController@about");
Router::any("contact", "App\Controllers\MainController@contactUs");


// TRANSLATIONS
Route::group(["prefix" => "translations", "namespace" => "App\Controllers"], function() {
    Router::any("{lang?}/{bookProject?}/{bookCode?}", "TranslationsController@index")
        ->where([
            "lang" => "[a-z0-9]+",
            "bookProject" => "[a-z0-9]+",
            "bookCode" => "[a-z0-9]+"
        ]);
    Router::any("{lang}/{bookProject}/{bookCode}/usfm", "TranslationsController@getUsfm")
        ->where([
            "lang" => "[a-z0-9]+",
            "bookProject" => "[a-z0-9]+",
            "bookCode" => "[a-z0-9]+"
        ]);
});


// EVENTS
Route::group(["prefix" => "events", "namespace" => "App\Controllers"], function() {
    Router::any("", "EventsController@index");
    Router::any("project/{projectID}", "EventsController@project")
        ->where(["projectID" => "[0-9]+"]);
    Router::any("translator/{eventID}", "EventsController@translator")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("information/{eventID}", "EventsController@information")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("manage/{eventID}", "EventsController@manage")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("checker/{eventID}/{memberID}", "EventsController@checker")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+"
            ]);
    Router::any("checker/{eventID}/{memberID}/{step}/apply", "EventsController@applyChecker")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "step" => "[a-z\-]+"
        ]);
    Router::any("checker_l2/{eventID}", "EventsController@checkerL2")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("checker_l3/{eventID}", "EventsController@checkerL3")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("notifications", "EventsController@allNotifications");
    Router::any("demo/{page?}", "EventsController@demo");
    Router::any("rpc/apply_event", "EventsController@applyEvent");
    Router::any("rpc/get_notifications", "EventsController@getNotifications");
    Router::any("rpc/autosave_chunk", "EventsController@autosaveChunk");
    Router::any("rpc/save_comment", "EventsController@saveComment");
    Router::any("rpc/save_keyword", "EventsController@saveKeyword");
    Router::any("rpc/get_keywords", "EventsController@getKeywords");
    Router::any("rpc/check_event", "EventsController@checkEvent");
    Router::any("rpc/assign_chapter", "EventsController@assignChapter");
    Router::any("rpc/assign_pair", "EventsController@assignPair");
    Router::any("rpc/get_event_members", "EventsController@getEventMembers");
    Router::any("rpc/delete_event_member", "EventsController@deleteEventMember");
    Router::any("rpc/get_info_update/{eventID}", "EventsController@getInfoUpdate")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("rpc/move_step_back", "EventsController@moveStepBack");
    Router::any("rpc/check_internet", "EventsController@checkInternet");
});


// MEMBERS
Route::group(["prefix" => "members", "namespace" => "App\Controllers"], function() {
    Router::any("", "MembersController@index");
    Router::any("profile", "MembersController@profile");
    Router::any("profile/{memberID}", "MembersController@publicProfile")
        ->where(["memberID" => "[0-9]+"]);
    Router::any("search", "MembersController@search");
    Router::any("signup", "MembersController@signup");
    Router::any("signup_desktop", "MembersController@signupDesktop");
    Router::any("login", "MembersController@login");
    Router::any("login_desktop", "MembersController@loginDesktop");
    Router::any("logout", "MembersController@logout");
    Router::any("error/verification", "MembersController@verificationError");
    Router::any("passwordreset", "MembersController@passwordReset");
    Router::any("resetpassword/{memberID}/{token}", "MembersController@resetPassword")
        ->where([
            "memberID" => "[0-9]+",
            "token" => "[a-z0-9]+"
        ]);
    Router::any("activate/{memberID}/{token}", "MembersController@activate")
        ->where([
            "memberID" => "[0-9]+",
            "token" => "[a-z0-9]+"
        ]);
    Router::any("activate/resend/{email}", "MembersController@resendActivation");
    Router::any("success", "MembersController@success");
    Router::any("rpc/auth/{memberID}/{eventID}/{authToken}", "MembersController@rpcAuth")
        ->where([
            "memberID" => "[0-9]+",
            "eventID" => "[a-z0-9]+",
            "authToken" => "[a-z0-9]+"
        ]);
    Router::any("rpc/search_members", "MembersController@searchMembers");
    Router::any("rpc/send_message", "MembersController@sendMessageToAdmin");
});


// ADMIN
Route::group(["prefix" => "admin", "namespace" => "App\Controllers\Admin"], function() {
    Router::any("", "AdminController@index");
    Router::any("project/{projectID}", "AdminController@project")
        ->where([
            "projectID" => "[0-9]+"
    ]);
    Router::any("members", "AdminController@members");
    Router::any("rpc/create_gw_project", "AdminController@createGwProject");
    Router::any("rpc/get_event", "AdminController@getEvent");
    Router::any("rpc/get_event_contributors", "AdminController@getEventContributors");
    Router::any("rpc/create_project", "AdminController@createProject");
    Router::any("rpc/get_members", "AdminController@getMembers");
    Router::any("rpc/search_members", "AdminController@searchMembers");
    Router::any("rpc/get_target_languages", "AdminController@getTargetLanguagesByGwLanguage");
    Router::any("rpc/create_event", "AdminController@createEvent");
    Router::any("rpc/get_source", "AdminController@getSource");
    Router::any("rpc/verify_member", "AdminController@verifyMember");
    Router::any("rpc/block_member", "AdminController@blockMember");
    Router::any("rpc/clear_cache", "AdminController@clearCache");
});

/** End default Routes */
