<?php
/**
 * Routes - all standard Routes are defined here.
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
            "lang" => "[a-z0-9-]+",
            "bookProject" => "[a-z0-9]+",
            "bookCode" => "[a-z0-9]+"
        ]);
    Router::any("{lang}/{bookProject}/{bookCode}/usfm", "TranslationsController@getUsfm")
        ->where([
            "lang" => "[a-z0-9-]+",
            "bookProject" => "[a-z0-9]+",
            "bookCode" => "[a-z0-9]+"
        ]);
    Router::any("{lang}/{bookProject}/{bookCode}/md", "TranslationsController@getMd")
        ->where([
            "lang" => "[a-z0-9-]+",
            "bookProject" => "[a-z0-9]+",
            "bookCode" => "[a-z0-9]+"
        ]);
});


// EVENTS
Route::group(["prefix" => "events", "namespace" => "App\Controllers"], function() {
    Router::any("", "EventsController@index");
    Router::any("translator/{eventID}", "EventsController@translator")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("translator-tn/{eventID}", "EventsController@translatorNotes")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("translator-sun/{eventID}", "EventsController@translatorSun")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("checker-l2/{eventID}", "EventsController@checkerL2")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("checker-l3/{eventID}", "EventsController@checkerL3")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("information/{eventID}", "EventsController@information")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("information-tn/{eventID}", "EventsController@informationNotes")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("information-l2/{eventID}", "EventsController@informationL2")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("information-sun/{eventID}", "EventsController@informationSun")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("manage/{eventID}", "EventsController@manage")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("manage-l2/{eventID}", "EventsController@manageL2")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("checker/{eventID}/{memberID}", "EventsController@checker")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+"
            ]);
    Router::any("checker-tn/{eventID}", "EventsController@checkerNotes")
        ->where([
            "eventID" => "[0-9]+"
            ]);
    Router::any("checker-tn/{eventID}/{memberID}", "EventsController@checkerNotesPeer")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+"
            ]);
    Router::any("checker-l2/{eventID}/{memberID}/{chapter}", "EventsController@checkerL2Continue")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "chapter" => "[0-9]+"
        ]);
    Router::any("checker-sun/{eventID}/{memberID}/{chapter}", "EventsController@checkerSun")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "chapter" => "[0-9]+"
        ]);
    Router::any("checker/{eventID}/{memberID}/{step}/apply", "EventsController@applyChecker")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "step" => "[a-z\-]+"
        ]);
    Router::any("checker/{eventID}/{memberID}/notes/{chapter}/apply", "EventsController@applyCheckerNotes")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "chapter" => "[0-9]+"
        ]);
    Router::any("checker/{eventID}/{memberID}/{step}/{chapter}/apply", "EventsController@applyCheckerL2")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "chapter" => "[0-9]+",
            "step" => "[2a-z\-]+"
        ]);
    Router::any("checker-sun/{eventID}/{memberID}/{step}/{chapter}/apply", "EventsController@applyCheckerSun")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "chapter" => "[0-9]+",
            "step" => "[2a-z\-]+"
        ]);
    Router::any("notifications", "EventsController@allNotifications");
    Router::any("demo/{page?}", "EventsController@demo");
    Router::any("demo-l2/{page?}", "EventsController@demoL2");
    Router::any("demo-tn/{page?}", "EventsController@demoTn");
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
    Router::any("rpc/move_step_back_alt", "EventsController@moveStepBackAlt");
    Router::any("rpc/set_tn_checker", "EventsController@setTnChecker");
    Router::any("rpc/check_internet", "EventsController@checkInternet");
    Router::any("rpc/apply_verb_checker", "EventsController@applyVerbChecker");
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
    Router::any("migrate/chapters", "AdminController@migrateChapters");
    Router::any("rpc/create_gw_project", "AdminController@createGwProject");
    Router::any("rpc/import", "AdminController@import");
    Router::any("rpc/repos_search/{q}", "AdminController@repos_search");
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
    Router::any("rpc/update_all_cache", "AdminController@updateAllBooksCache");
});

/** End default Routes */
