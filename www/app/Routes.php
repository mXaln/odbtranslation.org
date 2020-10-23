<?php
/**
 * Routes - all standard Routes are defined here.
 */


/** Define static routes. */

// The default Routing
Router::get("", "App\Controllers\MainController@index");
Router::get("about", "App\Controllers\MainController@about");
Router::any("contact", "App\Controllers\MainController@contactUs");
Router::get("maintenance", "App\Controllers\MainController@maintenance");



// TRANSLATIONS
Route::group(["prefix" => "translations", "namespace" => "App\Controllers"], function() {
    Router::any("{lang}/{bookProject}/{sourceBible?}/{bookCode}/usfm", "TranslationsController@downloadUsfm")
        ->where([
            "lang" => "[a-zA-Z0-9-]+",
            "bookProject" => "[a-z0-9]+",
            "bookCode" => "[a-z0-9]+"
        ]);
    Router::any("{lang}/{bookProject}/{sourceBible?}/{bookCode}/json", "TranslationsController@downloadJson")
        ->where([
            "lang" => "[a-zA-Z0-9-]+",
            "bookProject" => "[a-z0-9]+",
            "bookCode" => "[a-z0-9]+"
        ]);
    Router::any("{lang}/{bookProject}/{sourceBible?}/{bookCode}/ts", "TranslationsController@downloadTs")
        ->where([
            "lang" => "[a-zA-Z0-9-]+",
            "bookProject" => "[a-z0-9]+",
            "bookCode" => "[a-z0-9]+"
        ]);
    Router::any("{lang}/{bookProject}/{sourceBible?}/{bookCode}/{server}/export", "TranslationsController@export")
        ->where([
            "lang" => "[a-zA-Z0-9-]+",
            "bookProject" => "[a-z0-9]+",
            "bookCode" => "[a-z0-9]+"
        ]);
    Router::any("{lang?}/{bookProject?}/{sourceBible?}/{bookCode?}", "TranslationsController@index")
        ->where([
            "lang" => "[a-zA-Z0-9-]+",
            "bookProject" => "[a-z0-9]+",
            "sourceBible" => "[a-z0-9]+",
            "bookCode" => "[a-z0-9]+"
        ]);
});


// EVENTS
Route::group(["prefix" => "events", "namespace" => "App\Controllers"], function() {
    Router::any("", "EventsController@index");
    Router::any("translator/{eventID}", "EventsController@translator")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("translator-sun/{eventID}", "EventsController@translatorSun")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("translator-odb-sun/{eventID}", "EventsController@translatorOdbSun")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("translator-odb/{eventID}", "EventsController@translatorOdb")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("checker-l2/{eventID}", "EventsController@checkerL2")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("checker-l3/{eventID}", "EventsController@checkerL3")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("checker-l3/{eventID}/{memberID}/{chapter}", "EventsController@checkerL3Peer")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "chapter" => "[0-9]+"
        ]);
    Router::any("information/{eventID}", "EventsController@information")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("information-l2/{eventID}", "EventsController@informationL2")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("information-l3/{eventID}", "EventsController@informationL3")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("information-sun/{eventID}", "EventsController@informationSun")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("information-odb-sun/{eventID}", "EventsController@informationOdbSun")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("information-odb/{eventID}", "EventsController@informationOdb")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("manage/{eventID}", "EventsController@manage")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("manage-l2/{eventID}", "EventsController@manageL2")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("manage-l3/{eventID}", "EventsController@manageL3")
        ->where(["eventID" => "[0-9]+"]);
    Router::any("checker/{eventID}/{memberID}", "EventsController@checker")
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
    Router::any("checker-odb-sun/{eventID}/{memberID}/{chapter}", "EventsController@checkerOdbSun")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "chapter" => "[0-9]+"
        ]);
    Router::any("checker-odb/{eventID}/{memberID}/{chapter}", "EventsController@checkerOdb")
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
    Router::any("checker/{eventID}/{memberID}/{step}/{chapter}/apply", "EventsController@applyCheckerL2L3")
        ->where([
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "chapter" => "[0-9]+",
            "step" => "[23a-z\-]+"
        ]);
    Router::any("checker-{mode}/{eventID}/{memberID}/{step}/{chapter}/apply", "EventsController@applyCheckerOther")
        ->where([
            "mode" => "sun|odb",
            "eventID" => "[0-9]+",
            "memberID" => "[0-9]+",
            "chapter" => "[0-9]+",
            "step" => "[2a-z\-]+"
        ]);

    Router::any("notifications", "EventsController@allNotifications");
    Router::any("demo/{page?}", "EventsController@demo");
    Router::any("demo-scripture-input/{page?}", "EventsController@demoLangInput");
    Router::any("demo-l2/{page?}", "EventsController@demoL2");
    Router::any("demo-l3/{page?}", "EventsController@demoL3");
    Router::any("demo-sun/{page?}", "EventsController@demoSun");
    Router::any("demo-sun-odb/{page?}", "EventsController@demoSunOdb");
    Router::any("news", "EventsController@news");
    Router::any("faq", "EventsController@faqs");
    Router::any("rpc/apply_event", "EventsController@applyEvent");
    Router::any("rpc/get_notifications", "EventsController@getNotifications");
    Router::any("rpc/autosave_chunk", "EventsController@autosaveChunk");
    Router::any("rpc/autosave_li_verse", "EventsController@autosaveVerseLangInput");
    Router::any("rpc/delete_li_verse", "EventsController@deleteVerseLangInput");
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
    Router::any("rpc/check_internet", "EventsController@checkInternet");
    Router::any("rpc/apply_verb_checker", "EventsController@applyVerbChecker");
    Router::any("rpc/get_tq/{bookCode}/{chapter}/{lang}", "EventsController@getTq");
    Router::any("rpc/get_tw/{bookCode}/{chapter}/{lang}", "EventsController@getTw");
    Router::any("rpc/get_tn/{bookCode}/{chapter}/{lang}/{totalVerses}", "EventsController@getTn");
    Router::any("rpc/get_rubric/{lang}", "EventsController@getRubric");
    Router::any("rpc/get_saildict/", "EventsController@getSailDict");
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
    Router::any("rpc/cloud_login", "MembersController@cloudLogin");
});


// ADMIN
Route::group(["prefix" => "admin", "namespace" => "App\Controllers\Admin"], function() {
    Router::any("", "AdminController@index");
    Router::any("project/{projectID}", "AdminController@project")
        ->where([
            "projectID" => "[0-9]+"
    ]);
    Router::any("members", "AdminController@members");
    Router::any("tools", "AdminController@toolsCommon");
    Router::any("tools/vsun", "AdminController@toolsVsun");
    Router::any("tools/faq", "AdminController@toolsFaq");
    Router::any("tools/news", "AdminController@toolsNews");
    Router::any("tools/source", "AdminController@toolsSource");
    Router::any("rpc/create_gw_project", "AdminController@createGwProject");
    Router::any("rpc/get_super_admins", "AdminController@getSuperAdmins");
    Router::any("rpc/edit_super_admins", "AdminController@editSuperAdmins");
    Router::any("rpc/import", "AdminController@import");
    Router::any("rpc/repos_search/{q}", "AdminController@repos_search");
    Router::any("rpc/get_project", "AdminController@getProject");
    Router::any("rpc/get_event", "AdminController@getEvent");
    Router::any("rpc/get_event_contributors", "AdminController@getEventContributors");
    Router::any("rpc/get_project_contributors", "AdminController@getProjectContributors");
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
    Router::any("rpc/update_languages", "AdminController@updateLanguages");
    Router::any("rpc/update_catalog", "AdminController@updateCatalog");
    Router::any("rpc/clear_all_cache", "AdminController@clearAllCache");
    Router::any("rpc/create_multiple_users", "AdminController@createMultipleUsers");
    Router::any("rpc/delete_sail_word", "AdminController@deleteSailWord");
    Router::any("rpc/create_sail_word", "AdminController@createSailWord");
    Router::any("rpc/delete_faq", "AdminController@deleteFaq");
    Router::any("rpc/create_faq", "AdminController@createFaq");
    Router::any("rpc/create_news", "AdminController@createNews");
    Router::any("rpc/upload_sun_font", "AdminController@uploadSunFont");
    Router::any("rpc/upload_sun_dict", "AdminController@uploadSunDict");
    Router::any("rpc/upload_image", "AdminController@uploadImage");
    Router::any("rpc/upload_source", "AdminController@uploadSource");
    Router::any("rpc/create_custom_src", "AdminController@createCustomSource");
    Router::any("rpc/get_event_progress/{eventID}", "AdminController@getEventProgress")
        ->where([
            "eventID" => "[0-9]+"
        ]);
});

/** End default Routes */
