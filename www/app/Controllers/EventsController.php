<?php
/**
 * Created by mXaln
 */

namespace App\Controllers;

use App\Models\NewsModel;
use App\Models\ApiModel;
use App\Models\SailDictionaryModel;
use Helpers\Arrays;
use Helpers\Constants\OdbSections;
use Helpers\Tools;
use Support\Facades\View;
use Config\Config;
use Helpers\Url;
use Helpers\Gump;
use Helpers\Session;
use App\Core\Controller;
use Support\Facades\Cache;
use App\Models\EventsModel;
use App\Models\MembersModel;
use App\Modules\Alma\Models\Word;
use App\Models\TranslationsModel;
use Helpers\Constants\EventSteps;
use Helpers\Constants\EventCheckSteps;
use Helpers\Constants\EventStates;
use Helpers\Constants\EventMembers;
use \stdClass;

class EventsController extends Controller
{
    private $_model;
    private $_translationModel;
    private $_saildictModel;
    private $_apiModel;
    private $_newsModel;
    private $_membersModel;
    private $_notifications;
    private $_news;
    private $_newNewsCount;

    public function __construct()
    {
        parent::__construct();

        if(Config::get("app.isMaintenance")
            && !in_array($_SERVER['REMOTE_ADDR'], Config::get("app.ips")))
        {
            Url::redirect("maintenance");
        }

        if(preg_match("/^\\/events\\/rpc\\/get_saildict/", $_SERVER["REQUEST_URI"]))
        {
            $this->_saildictModel = new SailDictionaryModel();
            return;
        }

        if (!Session::get('loggedin')
            && !preg_match("/^\\/events\\/demo|\\/events\\/faq/", $_SERVER["REQUEST_URI"]))
        {
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $response["errorType"] = "logout";
                $response["error"] = __("not_loggedin_error");
                echo json_encode($response);
                exit;
            }
            else
            {
                Url::redirect('members/login');
            }
        }

        if(Session::get("isDemo") || preg_match("/^\\/events\\/demo/", $_SERVER["REQUEST_URI"]))
        {
            if(!preg_match("/^\\/events\\/demo/", $_SERVER["REQUEST_URI"]))
                Url::redirect('events/demo');
        }
        elseif(preg_match("/^\\/events\\/faq/", $_SERVER["REQUEST_URI"]))
        {
            // continue
        }
        elseif(!Session::get("verified"))
        {
            Url::redirect("members/error/verification");
        }
        elseif(!Session::get("profile")["complete"])
        {
            Url::redirect("members/profile");
        }
        else
        {
            $this->_model = new EventsModel();
            $this->_translationModel = new TranslationsModel();
            $this->_saildictModel = new SailDictionaryModel();
            $this->_apiModel = new ApiModel();
            $this->_newsModel = new NewsModel();
            $this->_membersModel = new MembersModel();

            $this->_notifications = $this->_model->getNotifications();
            $this->_notifications = Arrays::append($this->_notifications, $this->_model->getNotificationsL2());
            $this->_notifications = Arrays::append($this->_notifications, $this->_model->getNotificationsL3());
            $this->_notifications = Arrays::append($this->_notifications, $this->_model->getNotificationsSun());

            $this->_news = $this->_newsModel->getNews();
            $this->_newNewsCount = 0;
            foreach ($this->_news as $news) {
                if(!isset($_COOKIE["newsid".$news->id]))
                    $this->_newNewsCount++;
            }
        }
    }

    /**
     * Show member's dashboard view
     * @return mixed
     */
    public function index()
    {
        $data["menu"] = 1;

        if (Session::get('loggedin') !== true)
        {
            Url::redirect("members/login");
        }

        if(Session::get("isDemo"))
        {
            Url::redirect('events/demo');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }
        
        if(Session::get("isAdmin"))
        {
            $myFacilitatorEvents = $this->_model->getMemberEventsForAdmin(Session::get("memberID"));
            $data["myFacilitatorEventsInProgress"] = [];
            $data["myFacilitatorEventsFinished"] = [];

            foreach ($myFacilitatorEvents as $myFacilitatorEvent) {
                // Level 1
                if(EventStates::enum($myFacilitatorEvent->state) <= EventStates::enum(EventStates::TRANSLATED))
                {
                    $adms = (array)json_decode($myFacilitatorEvent->admins, true);
                    if(!in_array(Session::get("memberID"), $adms)) continue;
                } // Level 2
                elseif(EventStates::enum($myFacilitatorEvent->state) <= EventStates::enum(EventStates::L2_CHECKED))
                {
                    $adms = (array)json_decode($myFacilitatorEvent->admins_l2, true);
                    if(!in_array(Session::get("memberID"), $adms)) continue;
                } // Level 3
                elseif(EventStates::enum($myFacilitatorEvent->state) <= EventStates::enum(EventStates::COMPLETE))
                {
                    $adms = (array)json_decode($myFacilitatorEvent->admins_l3, true);
                    if(!in_array(Session::get("memberID"), $adms)) continue;
                }

                if($myFacilitatorEvent->state == EventStates::TRANSLATED
                    || $myFacilitatorEvent->state == EventStates::L2_CHECKED
                    || $myFacilitatorEvent->state == EventStates::COMPLETE)
                    $data["myFacilitatorEventsFinished"][] = $myFacilitatorEvent;
                else
                    $data["myFacilitatorEventsInProgress"][] = $myFacilitatorEvent;
            }
        }

        $data["myTranslatorEvents"] = $this->_model->getMemberEvents(
            Session::get("memberID"),
            EventMembers::TRANSLATOR,
            null,
            false,
            false);
        $data["myCheckerL1Events"] = $this->_model->getMemberEventsForChecker(Session::get("memberID"));
        $sunCheckers = $this->_model->getMemberEventsForCheckerSun(Session::get("memberID"));

        $data["myCheckerL1Events"] = Arrays::append(
            $data["myCheckerL1Events"],
            $sunCheckers);

        $data["myCheckerL2Events"] = $this->_model->getMemberEventsForCheckerL2(Session::get("memberID"));
        $data["myCheckerL3Events"] = $this->_model->getMemberEventsForCheckerL3(Session::get("memberID"));

        // Extract facilitators from events
        $admins = [];
        foreach ($data["myTranslatorEvents"] as $key => $event) {
            $admins = Arrays::append($admins, (array)json_decode($event->admins, true));
        }
        foreach ($data["myCheckerL1Events"] as $event) {
            $admins = Arrays::append($admins, (array)json_decode($event->admins, true));
        }
        foreach ($data["myCheckerL2Events"] as $event) {
            $admins = Arrays::append($admins, (array)json_decode($event->admins_l2, true));
        }
        foreach ($data["myCheckerL3Events"] as $event) {
            $admins = Arrays::append($admins, (array)json_decode($event->admins_l3, true));
        }
        
        $admins = array_unique($admins);
        $admins = (array)$this->_membersModel->getMembers(array_filter(array_values($admins)), true);

        $adminData = [];
        foreach ($admins as $member) {
            $adminData[$member->memberID]["userName"] = $member->userName;
            $adminData[$member->memberID]["avatar"] = $member->avatar;
            $adminData[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
            $adminData[$member->memberID]["email"] = $member->email;
        }

        $data["admins"] = $adminData;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;

        return View::make('Events/Index')
            ->shares("title", __("welcome_title"))
            ->shares("data", $data);
    }

    /**
     * @param $eventID
     * @return mixed
     */
    public function translator($eventID)
    {
        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["event"] = $this->_model->getMemberEvents(
            Session::get("memberID"),
            EventMembers::TRANSLATOR,
            $eventID
        );

        if(!empty($data["event"]))
        {
            if(!in_array($data["event"][0]->bookProject, ["ulb"]))
            {
                Url::redirect("events/translator-".$data["event"][0]->bookProject."/".$eventID);
            }

            $title = $data["event"][0]->name
                . " " . ($data["event"][0]->currentChapter > 0 ? $data["event"][0]->currentChapter : "")
                . " - " . $data["event"][0]->tLang
                . " - " . __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::TRANSLATING ||
                $data["event"][0]->state == EventStates::TRANSLATED)
            {
                if($data["event"][0]->step == EventSteps::NONE)
                    Url::redirect("events/information/".$eventID);

                $data["turn"] = $this->getTurnCredentials();

                $menuPage = $data["event"][0]->langInput ? "TranslatorLangInput" : "Translator";

                switch ($data["event"][0]->step) {
                    case EventSteps::PRAY:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {

                                $postdata = [
                                    "step" => ($data["event"][0]->langInput ? EventSteps::MULTI_DRAFT : EventSteps::CONSUME),
                                    "currentChapter" => $sourceText["currentChapter"],
                                    "currentChunk" => $sourceText["currentChunk"]
                                ];
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                            }
                        }

                        // Check if translator just started translating of this book
                        $data["event"][0]->justStarted = $data["event"][0]->verbCheck == "";

                        return View::make('Events/L1/'.$menuPage)
                            ->nest('page', 'Events/L1/Pray')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);

                    // Language Input Step 1
                    case EventSteps::MULTI_DRAFT:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $arr["firstvs"] = $tv->firstvs;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);
                            if (isset($_POST["confirm_step"]))
                            {
                                if(isset($translation) && isset($_POST["verses"]) && !empty($_POST["verses"]))
                                {
                                    $postVerses = $_POST["verses"];

                                    // Check for empty chunks
                                    $empty = array_filter($postVerses, function($elm) {
                                        return empty($elm);
                                    });

                                    if(empty($empty))
                                    {
                                        $chunks = array_map(
                                            function($verse) { return [$verse]; },
                                            array_keys($postVerses)
                                        );

                                        $this->_model->updateChapter(
                                            ["chunks" => json_encode($chunks)],
                                            [
                                                "eventID" => $data["event"][0]->eventID,
                                                "chapter" => $data["event"][0]->currentChapter
                                            ]
                                        );

                                        $postdata = [
                                            "step" => EventSteps::SELF_CHECK
                                        ];

                                        $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    }
                                    else
                                    {
                                        $error[] = __("empty_draft_verses_error");
                                    }
                                }
                                else
                                {
                                    $error[] = __("no_translation_data");
                                }
                            }
                        }

                        return View::make('Events/L1/TranslatorLangInput')
                            ->nest('page', 'Events/L1/LangInput')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CONSUME:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $postdata = [
                                    "step" => EventSteps::VERBALIZE,
                                    "hideChkNotif" => false
                                ];


                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/L1/Translator')
                            ->nest('page', 'Events/L1/Consume')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::VERBALIZE:
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        $data["event"][0]->checkerName = null;
                        $verbCheck = (array)json_decode($data["event"][0]->verbCheck, true);
                        if(array_key_exists($data["event"][0]->currentChapter, $verbCheck))
                        {
                            if(!is_numeric($verbCheck[$data["event"][0]->currentChapter]))
                            {
                                $data["event"][0]->checkerName = $verbCheck[$data["event"][0]->currentChapter];
                            }
                            else
                            {
                                $data["event"][0]->checkerName = $data["event"][0]->checkerFName . " " . mb_substr($data["event"][0]->checkerLName, 0, 1).".";
                            }
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                if($data["event"][0]->checkDone)
                                {

                                    $postdata = [
                                        "step" => EventSteps::CHUNKING,
                                        "checkerID" => 0,
                                        "checkDone" => false,
                                        "hideChkNotif" => true
                                    ];
                                    $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                }
                                else
                                {
                                    $error[] = __("verb_checker_not_ready_error");
                                }
                            }
                        }

                        return View::make('Events/L1/Translator')
                            ->nest('page', 'Events/L1/Verbalize')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CHUNKING:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $_POST = Gump::xss_clean($_POST);

								$chunks = isset($_POST["chunks_array"]) ? $_POST["chunks_array"] : "";
                                $chunks = (array)json_decode($chunks);
                                if($this->_apiModel->testChunks($chunks, $sourceText["totalVerses"]))
                                {
                                    if($this->_model->updateChapter(["chunks" => json_encode($chunks)], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]))
                                    {
                                        $this->_model->updateTranslator(["step" => EventSteps::READ_CHUNK], ["trID" => $data["event"][0]->trID]);
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                        exit;
                                    }
                                    else
                                    {
                                        $error[] = __("error_ocured");
                                    }
                                }
                                else
                                {
                                    $error[] = __("wrong_chunks_error");
                                }
                            }
                        }

                        return View::make('Events/L1/Translator')
                            ->nest('page', 'Events/L1/Chunking')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::READ_CHUNK:
                        $sourceText = $this->getSourceText($data, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {

                                $this->_model->updateTranslator(["step" => EventSteps::BLIND_DRAFT], ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/L1/Translator')
                            ->nest('page', 'Events/L1/ReadChunk')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::BLIND_DRAFT:
                        $sourceText = $this->getSourceText($data, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel
                                    ->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter, $data["event"][0]->currentChunk);

                                if(!empty($translationData))
                                {
                                    $verses = json_decode($translationData[0]->translatedVerses, true);
                                    $data["blind"] = $verses[EventMembers::TRANSLATOR]["blind"];
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);
							$draft = isset($_POST["draft"]) ? $_POST["draft"] : "";
							$confirm_step = isset($_POST["confirm_step"]) ? $_POST["confirm_step"] : "";

                            if (isset($_POST["confirm_step"]))
                            {
                                if(trim($draft) != "")
                                {
                                    $postdata["step"] = EventSteps::SELF_CHECK;

                                    // If chapter is finished go to SELF_EDIT, otherwise go to the next chunk
                                    if(array_key_exists($data["event"][0]->currentChunk + 1, $sourceText["chunks"]))
                                    {
                                        // Current chunk is finished, go to next chunk
                                        $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                        $postdata["step"] = EventSteps::READ_CHUNK;
                                    }


                                    $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                }
                                else
                                {
                                    $error[] = __("empty_draft_verses_error");
                                }
                            }
                        }

                        return View::make('Events/L1/Translator')
                            ->nest('page', 'Events/L1/BlindDraft')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::SELF_CHECK:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $arr["firstvs"] = $tv->firstvs;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;

                                if($data["event"][0]->sourceBible == "rsb")
                                {
                                    $words = Word::with('translations')
                                        ->orderBy('title')
                                        ->get();

                                    $data["words"] = json_encode($words->toArray());
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"]))
                            {
                                if($data["event"][0]->langInput)
                                {
                                    if(isset($translationData))
                                    {
                                        foreach ($translationData as $tv)
                                        {
                                            $this->_translationModel->updateTranslation([
                                                "translateDone" => true
                                            ], ["tID" => $tv->tID]);
                                        }
                                    }

                                    $chapters = [];
                                    for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                                    {
                                        $data["chapters"][$i] = [];
                                    }

                                    $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                    foreach ($chaptersDB as $chapter) {
                                        $tmp["trID"] = $chapter["trID"];
                                        $tmp["memberID"] = $chapter["memberID"];
                                        $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                        $tmp["done"] = $chapter["done"];

                                        $chapters[$chapter["chapter"]] = $tmp;
                                    }

                                    $chapters[$data["event"][0]->currentChapter]["done"] = true;

                                    // Check if whole book is finished
                                    if($this->checkBookFinished($chapters, $data["event"][0]->chaptersNum))
                                        $this->_model->updateEvent([
                                            "state" => EventStates::TRANSLATED,
                                            "dateTo" => date("Y-m-d H:i:s", time())],
                                            ["eventID" => $data["event"][0]->eventID]);

                                    $this->_model->updateChapter(["done" => true], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]);

                                    // Check if the member has another chapter to translate
                                    // then redirect to preparation page
                                    $nextChapter = 0;
                                    $nextChapterDB = $this->_model->getNextChapter($data["event"][0]->eventID, Session::get("memberID"));
                                    if(!empty($nextChapterDB))
                                        $nextChapter = $nextChapterDB[0]->chapter;

                                    if($nextChapter > 0)
                                    {
                                        $postdata = [
                                            "step" => EventSteps::PRAY,
                                            "currentChapter" => $nextChapter
                                        ];
                                    }
                                    else
                                    {
                                        $postdata = [
                                            "step" => EventSteps::NONE,
                                            "currentChapter" => 0
                                        ];
                                    }
                                }
                                else
                                {
                                    $postdata = [
                                        "step" => EventSteps::PEER_REVIEW,
                                        "hideChkNotif" => false,
                                    ];
                                }

                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                            }
                        }

                        $page = $data["event"][0]->langInput ? "SelfCheckLangInput" : "SelfCheck";

                        return View::make('Events/L1/'.$menuPage)
                            ->nest('page', 'Events/L1/'.$page)
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::PEER_REVIEW:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"]))
                            {
                                if($data["event"][0]->checkDone)
                                {
                                    $postdata = [
                                        "step" => EventSteps::KEYWORD_CHECK,
                                        "checkerID" => 0,
                                        "checkDone" => false,
                                        "hideChkNotif" => false
                                    ];
                                    $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                }
                                else {
                                    $error[] = __("checker_not_ready_error");
                                }
                            }
                        }

                        return View::make('Events/L1/Translator')
                            ->nest('page', 'Events/L1/PeerReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::KEYWORD_CHECK:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"]))
                            {
                                if($data["event"][0]->checkDone)
                                {

                                    $postdata = array(
                                        "step" => EventSteps::CONTENT_REVIEW,
                                        "checkerID" => 0,
                                        "checkDone" => false,
                                        "hideChkNotif" => false
                                    );
                                    $this->_model->updateTranslator($postdata, array("trID" => $data["event"][0]->trID));
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    exit;
                                }
                                else
                                {
                                    $error[] = __("checker_not_ready_error");
                                }
                            }
                        }

                        return View::make('Events/L1/Translator')
                            ->nest('page', 'Events/L1/KeywordCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CONTENT_REVIEW:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv) {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"]))
                            {
                                if($data["event"][0]->checkDone)
                                {
                                    $postdata = array(
                                        "step" => EventSteps::FINAL_REVIEW,
                                        "checkerID" => 0,
                                        "checkDone" => false,
                                        "hideChkNotif" => true
                                    );
                                    $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                }
                                else
                                {
                                    $error[] = __("checker_not_ready_error");
                                }
                            }
                        }

                        return View::make('Events/L1/Translator')
                            ->nest('page', 'Events/L1/ContentReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::FINAL_REVIEW:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv) {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"]))
                            {
								$chunks = isset($_POST["chunks"]) ? (array)$_POST["chunks"] : array();
                                $chunks = array_map("trim", $chunks);
                                $chunks = array_filter($chunks, function($v) {
                                    return !empty($v);
                                });

                                if(sizeof($chunks) < sizeof($data["chunks"]))
                                    $error[] = __("empty_verses_error");

                                if(!isset($error))
                                {
                                    $versesCombined = [];
                                    foreach ($chunks as $key => $chunk)
                                    {
                                        $verses = preg_split("/\|([0-9]+)\|/", $chunk, -1, PREG_SPLIT_NO_EMPTY);

                                        if(sizeof($data["chunks"][$key]) !=
                                            sizeof($verses))
                                        {
                                            $error[] = __("not_equal_verse_markers");
                                            break;
                                        }

                                        $versesCombined[$key] = array_combine($data["chunks"][$key], $verses);
                                    }

                                    if(!isset($error))
                                    {
                                        foreach ($versesCombined as $key => $chunk)
                                        {
                                            $translation[$key][EventMembers::TRANSLATOR]["verses"] = $chunk;

                                            $tID = $translation[$key]["tID"];
                                            unset($translation[$key]["tID"]);
                                            
                                            $encoded = json_encode($translation[$key]);
                                            $json_error = json_last_error();
                                            
                                            if($json_error == JSON_ERROR_NONE)
                                            {
                                                $trData = array(
                                                    "translatedVerses"  => $encoded,
                                                    "translateDone" => true
                                                );
                                                $this->_translationModel->updateTranslation(
                                                    $trData, 
                                                    array(
                                                        "trID" => $data["event"][0]->trID, 
                                                        "tID" => $tID));
                                            }
                                            else 
                                            {
                                                $error[] = __("error_ocured", array($tID));
                                            }
                                        }

                                        $chapters = [];
                                        for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                                        {
                                            $data["chapters"][$i] = [];
                                        }

                                        $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                        foreach ($chaptersDB as $chapter) {
                                            $tmp["trID"] = $chapter["trID"];
                                            $tmp["memberID"] = $chapter["memberID"];
                                            $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                            $tmp["done"] = $chapter["done"];

                                            $chapters[$chapter["chapter"]] = $tmp;
                                        }

                                        $chapters[$data["event"][0]->currentChapter]["done"] = true;

                                        // Check if whole book is finished
                                        if($this->checkBookFinished($chapters, $data["event"][0]->chaptersNum))
                                            $this->_model->updateEvent([
                                                "state" => EventStates::TRANSLATED,
                                                "dateTo" => date("Y-m-d H:i:s", time())],
                                                ["eventID" => $data["event"][0]->eventID]);

                                        $this->_model->updateChapter(["done" => true], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]);

                                        // Check if the member has another chapter to translate
                                        // then redirect to preparation page
                                        $nextChapter = 0;
                                        $nextChapterDB = $this->_model->getNextChapter($data["event"][0]->eventID, Session::get("memberID"));
                                        if(!empty($nextChapterDB))
                                            $nextChapter = $nextChapterDB[0]->chapter;

                                        $postdata = [
                                            "step" => EventSteps::FINISHED,
                                            "currentChapter" => 0,
                                            "currentChunk" => 0,
                                            "translateDone" => true
                                        ];

                                        if($nextChapter > 0)
                                        {
                                            $postdata["step"] = EventSteps::PRAY;
                                            $postdata["currentChapter"] = $nextChapter;
                                            $postdata["translateDone"] = false;
                                        }


                                        $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    }
                                }
                            }
                        }

                        return View::make('Events/L1/Translator')
                            ->nest('page', 'Events/L1/FinalReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::FINISHED:
                        $data["success"] = __("you_event_finished_success");

                        return View::make('Events/L1/Translator')
                            ->nest('page', 'Events/L1/Finished')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;
                }
            }
            else
            {
                $data["error"] = true;
                $error[] = __("wrong_event_state_error");

                return View::make('Events/L1/Translator')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/L1/Translator')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }

    public function translatorSun($eventID)
    {
        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, $eventID);

        $title = "";

        if(!empty($data["event"]))
        {
            if(!in_array($data["event"][0]->bookProject, ["sun"]))
            {
                if(in_array($data["event"][0]->bookProject, ["ulb"]))
                    Url::redirect("events/translator/".$eventID);
                else
                    Url::redirect("events/translator-".$data["event"][0]->bookProject."/".$eventID);
            }

            $title = $data["event"][0]->name
                . " " . ($data["event"][0]->currentChapter > 0 ? $data["event"][0]->currentChapter : "")
                . " - " . $data["event"][0]->tLang
                . " - " . __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::TRANSLATING || $data["event"][0]->state == EventStates::TRANSLATED)
            {
                if($data["event"][0]->step == EventSteps::NONE)
                    Url::redirect("events/information-sun/".$eventID);

                $data["turn"] = $this->getTurnCredentials();

                switch ($data["event"][0]->step) {
                    case EventSteps::PRAY:
                        $sourceText = $this->getSourceText($data);

                        if ($sourceText !== false) {
                            if (!array_key_exists("error", $sourceText)) {
                                $data = $sourceText;
                            } else {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        } else {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            if (isset($_POST["confirm_step"])) {
                                
                                $postdata = [
                                    "step" => EventSteps::CONSUME,
                                    "currentChapter" => $sourceText["currentChapter"],
                                    "currentChunk" => $sourceText["currentChunk"]
                                ];
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                            }
                        }

                        // Check if translator just started translating of this book
                        $data["event"][0]->justStarted = $data["event"][0]->kwCheck == "";

                        return View::make('Events/SUN/Translator')
                            ->nest('page', 'Events/SUN/Pray')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CONSUME:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $postdata = [
                                    "step" => EventSteps::CHUNKING
                                ];


                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/SUN/Translator')
                            ->nest('page', 'Events/SUN/Consume')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CHUNKING:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $_POST = Gump::xss_clean($_POST);

                                $chunks = isset($_POST["chunks_array"]) ? $_POST["chunks_array"] : "";
                                $chunks = (array)json_decode($chunks);
                                if($this->_apiModel->testChunks($chunks, $sourceText["totalVerses"]))
                                {
                                    if($this->_model->updateChapter(["chunks" => json_encode($chunks)], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]))
                                    {
                                        $this->_model->updateTranslator(["step" => EventSteps::REARRANGE], ["trID" => $data["event"][0]->trID]);
                                        Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                                        exit;
                                    }
                                    else
                                    {
                                        $error[] = __("error_ocured");
                                    }
                                }
                                else
                                {
                                    $error[] = __("wrong_chunks_error");
                                }
                            }
                        }

                        return View::make('Events/SUN/Translator')
                            ->nest('page', 'Events/SUN/Chunking')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::REARRANGE:
                        $sourceText = $this->getSourceText($data, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel
                                    ->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter, $data["event"][0]->currentChunk);

                                if(!empty($translationData))
                                {
                                    $verses = json_decode($translationData[0]->translatedVerses, true);
                                    $data["words"] = $verses[EventMembers::TRANSLATOR]["words"];
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);
                            $words = isset($_POST["draft"]) ? $_POST["draft"] : "";

                            if (isset($_POST["confirm_step"]))
                            {
                                if(trim($words) != "")
                                {
                                    $postdata["step"] = EventSteps::SYMBOL_DRAFT;
                                    $postdata["currentChunk"] = 0;

                                    // If chapter is finished go to SYMBOL_DRAFT, otherwise go to the next chunk
                                    if(array_key_exists($data["event"][0]->currentChunk + 1, $sourceText["chunks"]))
                                    {
                                        // Current chunk is finished, go to next chunk
                                        $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                        $postdata["step"] = EventSteps::REARRANGE;
                                    }

                                    $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                                }
                                else
                                {
                                    $error[] = __("empty_words_error");
                                }
                            }
                        }

                        return View::make('Events/SUN/Translator')
                            ->nest('page', 'Events/SUN/WordsDraft')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::SYMBOL_DRAFT:
                        $sourceText = $this->getSourceText($data, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel
                                    ->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter, $data["event"][0]->currentChunk);

                                if(!empty($translationData))
                                {
                                    $verses = json_decode($translationData[0]->translatedVerses, true);
                                    $data["words"] = $verses[EventMembers::TRANSLATOR]["words"];
                                    $data["symbols"] = $verses[EventMembers::TRANSLATOR]["symbols"];
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);
                            $symbols = isset($_POST["symbols"]) ? $_POST["symbols"] : "";

                            if (isset($_POST["confirm_step"]))
                            {
                                if(trim($symbols) != "")
                                {
                                    $postdata["step"] = EventSteps::SELF_CHECK;

                                    // If chapter is finished go to SELF_EDIT, otherwise go to the next chunk
                                    if(array_key_exists($data["event"][0]->currentChunk + 1, $sourceText["chunks"]))
                                    {
                                        // Current chunk is finished, go to next chunk
                                        $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                        $postdata["step"] = EventSteps::SYMBOL_DRAFT;
                                    }

                                    $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                                }
                                else
                                {
                                    $error[] = __("empty_words_error");
                                }
                            }
                        }

                        return View::make('Events/SUN/Translator')
                            ->nest('page', 'Events/SUN/SymbolsDraft')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::SELF_CHECK:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"]))
                            {
                                $chapters = [];
                                for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                                {
                                    $data["chapters"][$i] = [];
                                }

                                $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                foreach ($chaptersDB as $chapter) {
                                    $tmp["trID"] = $chapter["trID"];
                                    $tmp["memberID"] = $chapter["memberID"];
                                    $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                    $tmp["done"] = $chapter["done"];

                                    $chapters[$chapter["chapter"]] = $tmp;
                                }

                                $chapters[$data["event"][0]->currentChapter]["done"] = true;
                                $this->_model->updateChapter(["done" => true], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]);

                                // Check if the member has another chapter to translate
                                // then redirect to preparation page
                                $nextChapter = 0;
                                $nextChapterDB = $this->_model->getNextChapter($data["event"][0]->eventID, Session::get("memberID"));
                                if(!empty($nextChapterDB))
                                    $nextChapter = $nextChapterDB[0]->chapter;

                                $kwCheck = (array)json_decode($data["event"][0]->kwCheck, true);
                                if(!array_key_exists($data["event"][0]->currentChapter, $kwCheck))
                                {
                                    $kwCheck[$data["event"][0]->currentChapter] = [
                                        "memberID" => 0,
                                        "done" => 0
                                    ];
                                }

                                $postdata = [
                                    "step" => EventSteps::NONE,
                                    "currentChapter" => 0,
                                    "currentChunk" => 0,
                                    "kwCheck" => json_encode($kwCheck)
                                ];

                                if($nextChapter > 0)
                                {
                                    $postdata["step"] = EventSteps::PRAY;
                                    $postdata["currentChapter"] = $nextChapter;
                                    $postdata["translateDone"] = false;
                                }

                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator-sun/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/SUN/Translator')
                            ->nest('page', 'Events/SUN/SelfCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                }
            }
            else
            {
                $data["error"] = true;
                $error[] = __("wrong_event_state_error");

                return View::make('Events/SUN/Translator')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/SUN/Translator')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }


    public function translatorOdbSun($eventID)
    {
        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, $eventID);

        $title = "";

        if(!empty($data["event"]))
        {
            if($data["event"][0]->bookProject != "sun" && $data["event"][0]->sourceBible != "odb")
            {
                Url::redirect("events/");
            }

            $title = $data["event"][0]->name
                . " " . ($data["event"][0]->currentChapter > 0 ? $data["event"][0]->currentChapter : "")
                . " - " . $data["event"][0]->tLang
                . " - " . $data["event"][0]->sourceBible
                . " - " . __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::TRANSLATING || $data["event"][0]->state == EventStates::TRANSLATED)
            {
                if($data["event"][0]->step == EventSteps::NONE)
                    Url::redirect("events/information-odb-sun/".$eventID);

                $data["turn"] = $this->getTurnCredentials();

                switch ($data["event"][0]->step) {
                    case EventSteps::PRAY:
                        $sourceText = $this->getOtherSourceText($data);

                        if ($sourceText !== false) {
                            if (!array_key_exists("error", $sourceText)) {
                                $data = $sourceText;
                            } else {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        } else {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            if (isset($_POST["confirm_step"])) {
                                

                                $keys = array_keys($sourceText["text"]);
                                $chunks = array_map(function ($elm) {
                                    return [$elm];
                                }, $keys);
                                $this->_model->updateChapter(
                                    ["chunks" => json_encode($chunks)],
                                    [
                                        "eventID" => $data["event"][0]->eventID,
                                        "chapter" => $data['currentChapter']
                                    ]
                                );

                                $postdata = [
                                    "step" => EventSteps::CONSUME,
                                    "currentChapter" => $sourceText["currentChapter"],
                                    "currentChunk" => $sourceText["currentChunk"]
                                ];
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);

                                Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                            }
                        }

                        $data["event"][0]->justStarted = $data["event"][0]->kwCheck == "";

                        return View::make('Events/ODBSUN/Translator')
                            ->nest('page', 'Events/ODBSUN/Pray')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CONSUME:
                        $sourceText = $this->getOtherSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $postdata = [
                                    "step" => EventSteps::REARRANGE
                                ];


                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/ODBSUN/Translator')
                            ->nest('page', 'Events/ODBSUN/Consume')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::REARRANGE:
                        $sourceText = $this->getOtherSourceText($data, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel
                                    ->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter, $data["event"][0]->currentChunk);

                                if(!empty($translationData))
                                {
                                    $verses = json_decode($translationData[0]->translatedVerses, true);
                                    $data["words"] = $verses[EventMembers::TRANSLATOR]["words"];
                                }

                                // Skip if section is empty or it is a DATE section
                                $section = key($data["text"]);
                                if(trim($data["text"][$section]) == "" || $section == OdbSections::DATE)
                                {
                                    $translationVerses = [
                                        EventMembers::TRANSLATOR => [
                                            "words" => "",
                                            "symbols" => "",
                                            "bt" => "",
                                            "verses" => [$section => trim($data["text"][$section])]
                                        ],
                                        EventMembers::L2_CHECKER => [
                                            "verses" => array()
                                        ],
                                        EventMembers::L3_CHECKER => [
                                            "verses" => array()
                                        ],
                                    ];

                                    $encoded = json_encode($translationVerses);
                                    $json_error = json_last_error();

                                    if($json_error == JSON_ERROR_NONE)
                                    {
                                        $trData = [
                                            "projectID"         => $data["event"][0]->projectID,
                                            "eventID"           => $data["event"][0]->eventID,
                                            "trID"              => $data["event"][0]->trID,
                                            "targetLang"        => $data["event"][0]->targetLang,
                                            "bookProject"       => $data["event"][0]->bookProject,
                                            "abbrID"            => $data["event"][0]->abbrID,
                                            "bookCode"          => $data["event"][0]->bookCode,
                                            "chapter"           => $data["event"][0]->currentChapter,
                                            "chunk"             => $data["event"][0]->currentChunk,
                                            "firstvs"           => $sourceText["chunk"][0],
                                            "translatedVerses"  => $encoded,
                                            "dateCreate"        => date('Y-m-d H:i:s')
                                        ];

                                        if(empty($translationData))
                                            $this->_translationModel->createTranslation($trData);

                                        $postdata["step"] = EventSteps::SYMBOL_DRAFT;
                                        $postdata["currentChunk"] = 0;

                                        // If chapter is finished go to SYMBOL_DRAFT, otherwise go to the next chunk
                                        if(array_key_exists($data["event"][0]->currentChunk + 1, $sourceText["chunks"]))
                                        {
                                            // Current chunk is finished, go to next chunk
                                            $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                            $postdata["step"] = EventSteps::REARRANGE;
                                        }

                                        $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                        Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                                    }
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);
                            $words = isset($_POST["draft"]) ? $_POST["draft"] : "";

                            if (isset($_POST["confirm_step"]))
                            {
                                if(trim($words) != "")
                                {
                                    $postdata["step"] = EventSteps::SYMBOL_DRAFT;
                                    $postdata["currentChunk"] = 0;

                                    // If chapter is finished go to SYMBOL_DRAFT, otherwise go to the next chunk
                                    if(array_key_exists($data["event"][0]->currentChunk + 1, $sourceText["chunks"]))
                                    {
                                        // Current chunk is finished, go to next chunk
                                        $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                        $postdata["step"] = EventSteps::REARRANGE;
                                    }

                                    $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                                }
                                else
                                {
                                    $error[] = __("empty_words_error");
                                }
                            }
                        }

                        return View::make('Events/ODBSUN/Translator')
                            ->nest('page', 'Events/ODBSUN/WordsDraft')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::SYMBOL_DRAFT:
                        $sourceText = $this->getOtherSourceText($data, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel
                                    ->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter, $data["event"][0]->currentChunk);

                                if(!empty($translationData))
                                {
                                    $verses = json_decode($translationData[0]->translatedVerses, true);
                                    $data["words"] = $verses[EventMembers::TRANSLATOR]["words"];
                                    $data["symbols"] = $verses[EventMembers::TRANSLATOR]["symbols"];
                                }

                                // Skip if section is empty or it is a DATE section
                                $section = key($data["text"]);
                                if(trim($data["text"][$section]) == "" || $section == OdbSections::DATE)
                                {
                                    $postdata["step"] = EventSteps::SELF_CHECK;

                                    // If chapter is finished go to SELF_EDIT, otherwise go to the next chunk
                                    if(array_key_exists($data["event"][0]->currentChunk + 1, $sourceText["chunks"]))
                                    {
                                        // Current chunk is finished, go to next chunk
                                        $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                        $postdata["step"] = EventSteps::SYMBOL_DRAFT;
                                    }

                                    $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);
                            $symbols = isset($_POST["symbols"]) ? $_POST["symbols"] : "";

                            if (isset($_POST["confirm_step"]))
                            {
                                if(trim($symbols) != "")
                                {
                                    $postdata["step"] = EventSteps::SELF_CHECK;

                                    // If chapter is finished go to SELF_EDIT, otherwise go to the next chunk
                                    if(array_key_exists($data["event"][0]->currentChunk + 1, $sourceText["chunks"]))
                                    {
                                        // Current chunk is finished, go to next chunk
                                        $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                        $postdata["step"] = EventSteps::SYMBOL_DRAFT;
                                    }

                                    $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                                }
                                else
                                {
                                    $error[] = __("empty_words_error");
                                }
                            }
                        }

                        return View::make('Events/ODBSUN/Translator')
                            ->nest('page', 'Events/ODBSUN/SymbolsDraft')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::SELF_CHECK:
                        $sourceText = $this->getOtherSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::NONE, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"]))
                            {
                                $chapters = [];
                                for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                                {
                                    $data["chapters"][$i] = [];
                                }

                                $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                foreach ($chaptersDB as $chapter) {
                                    $tmp["trID"] = $chapter["trID"];
                                    $tmp["memberID"] = $chapter["memberID"];
                                    $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                    $tmp["done"] = $chapter["done"];

                                    $chapters[$chapter["chapter"]] = $tmp;
                                }

                                $chapters[$data["event"][0]->currentChapter]["done"] = true;
                                $this->_model->updateChapter(["done" => true], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]);

                                // Check if the member has another chapter to translate
                                // then redirect to preparation page
                                $nextChapter = 0;
                                $nextChapterDB = $this->_model->getNextChapter($data["event"][0]->eventID, Session::get("memberID"));
                                if(!empty($nextChapterDB))
                                    $nextChapter = $nextChapterDB[0]->chapter;

                                $kwCheck = (array)json_decode($data["event"][0]->kwCheck, true);
                                if(!array_key_exists($data["event"][0]->currentChapter, $kwCheck))
                                {
                                    $kwCheck[$data["event"][0]->currentChapter] = [
                                        "memberID" => 0,
                                        "done" => 0
                                    ];
                                }

                                $postdata = [
                                    "step" => EventSteps::NONE,
                                    "currentChapter" => 0,
                                    "currentChunk" => 0,
                                    "kwCheck" => json_encode($kwCheck)
                                ];

                                if($nextChapter > 0)
                                {
                                    $postdata["step"] = EventSteps::PRAY;
                                    $postdata["currentChapter"] = $nextChapter;
                                    $postdata["translateDone"] = false;
                                }

                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator-odb-sun/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/ODBSUN/Translator')
                            ->nest('page', 'Events/ODBSUN/SelfCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                }
            }
            else
            {
                $data["error"] = true;
                $error[] = __("wrong_event_state_error");

                return View::make('Events/ODBSUN/Translator')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/ODBSUN/Translator')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }

    public function checker($eventID, $memberID)
    {
        $response = array("success" => false, "errors" => "");

        $title = "";

        if(!isset($error))
        {
            $data["event"] = $this->_model->getMemberEventsForChecker(Session::get("memberID"), $eventID, $memberID);

            if(!empty($data["event"]))
            {
                if($data["event"][0]->step != EventSteps::FINISHED && !$data["event"][0]->translateDone)
                {
                    if(in_array($data["event"][0]->step, [EventSteps::PEER_REVIEW, EventSteps::KEYWORD_CHECK, EventSteps::CONTENT_REVIEW]))
                    {
                        $data["turn"] = $this->getTurnCredentials();

                        $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"])) {
                                $postdata = array("checkDone" => true);

                                if($data["event"][0]->step == EventSteps::PEER_REVIEW)
                                {
                                    $peerCheck = (array)json_decode($data["event"][0]->peerCheck, true);
                                    if(!array_key_exists($data["event"][0]->currentChapter, $peerCheck))
                                    {
                                        $peerCheck[$data["event"][0]->currentChapter] = Session::get("memberID");
                                        $postdata["peerCheck"] = json_encode($peerCheck);
                                    }
                                }
                                elseif($data["event"][0]->step == EventSteps::KEYWORD_CHECK)
                                {
                                    $kwCheck = (array)json_decode($data["event"][0]->kwCheck, true);
                                    if(!array_key_exists($data["event"][0]->currentChapter, $kwCheck))
                                    {
                                        $kwCheck[$data["event"][0]->currentChapter] = Session::get("memberID");
                                        $postdata["kwCheck"] = json_encode($kwCheck);
                                    }
                                }
                                else
                                {
                                    $crCheck = (array)json_decode($data["event"][0]->crCheck, true);
                                    if(!array_key_exists($data["event"][0]->currentChapter, $crCheck))
                                    {
                                        $crCheck[$data["event"][0]->currentChapter] = Session::get("memberID");
                                        $postdata["crCheck"] = json_encode($crCheck);
                                    }
                                }

                                $this->_model->updateTranslator($postdata, array("trID" => $data["event"][0]->trID));

                                $response["success"] = true;
                                echo json_encode($response);

                                //Url::redirect('members');
                                exit;
                            }
                        }

                        if($data["event"][0]->checkDone)
                        {
                            $data["success"] = __("checker_translator_finished_error");
                        }
                        else
                        {
                            $sourceText = $this->getSourceText($data);

                            if ($sourceText && !array_key_exists("error", $sourceText)) {
                                $data = $sourceText;
                            } else {
                                $error[] = $sourceText["error"];
                            }
                        }
                    }
                    else
                    {
                        $error[] = __("checker_translator_not_ready_error");
                    }
                }
                else
                {
                    $data["success"] = __("translator_event_finished_success");
                    $data["error"] = "";
                }

                $title = $data["event"][0]->bookName ." - ". $data["event"][0]->tLang ." - ". __($data["event"][0]->bookProject);
            }
            else
            {
                $error[] = __("checker_event_error");
                $title = "Error";
            }
        }

        $data["menu"] = 1;
        $data["isCheckerPage"] = true;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;

        $page = null;
        if(!isset($error))
        {
            if($data["event"][0]->step != EventSteps::VERBALIZE)
            {
                $sourceText = $this->getSourceText($data);
                if($sourceText !== false)
                {
                    if (!array_key_exists("error", $sourceText)) {
                        $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                        $translation = array();

                        foreach ($translationData as $tv) {
                            $arr = json_decode($tv->translatedVerses, true);
                            $arr["tID"] = $tv->tID;
                            $translation[] = $arr;
                        }

                        $data = $sourceText;
                        $data["translation"] = $translation;
                    } else {
                        $error[] = $sourceText["error"];
                    }
                }
            }

            switch ($data["event"][0]->step)
            {
                case EventSteps::PEER_REVIEW:
                    $page = "Events/L1/CheckerPeerReview";
                    break;

                case EventSteps::KEYWORD_CHECK:
                    $page = "Events/L1/CheckerKeywordCheck";
                    break;

                case EventSteps::CONTENT_REVIEW:
                    $page = "Events/L1/CheckerContentReview";
                    break;

                default:
                    $page = null;
                    break;
            }
        }

        $view = View::make('Events/L1/Translator')
            ->shares("title", $title)
            ->shares("data", $data)
            ->shares("error", @$error);

        if($page != null) $view->nest('page', $page);

        return $view;
    }

    /**
     * View for Theo check and V-b-v check in SUN event
     * @param $eventID
     * @param $memberID
     * @param $chapter
     * @return View
     */
    public function checkerSun($eventID, $memberID, $chapter)
    {
        $isAjax = false;
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $isAjax = true;
            $response["success"] = false;
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["event"] = $this->_model->getMemberEventsForSun(
            Session::get("memberID"), $eventID, $memberID, $chapter);

        if(!empty($data["event"]))
        {
            if(!in_array($data["event"][0]->bookProject, ["sun"]))
            {
                Url::redirect("events/");
            }

            $title = $data["event"][0]->name
                . " " . ($data["event"][0]->currentChapter > 0 ? $data["event"][0]->currentChapter : "")
                . " - " . $data["event"][0]->tLang
                . " - " . __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::TRANSLATING || $data["event"][0]->state == EventStates::TRANSLATED)
            {
                $data["turn"] = $this->getTurnCredentials();

                $chapters = $this->_model->getChapters($eventID, null, $chapter);
                $data["event"][0]->chunks = [];
                if(!empty($chapters))
                {
                    $data["event"][0]->chunks = $chapters[0]["chunks"];
                }

                switch ($data["event"][0]->step)
                {
                    case EventSteps::THEO_CHECK:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                if(!isset($error))
                                {
                                    $keywords = $this->_translationModel->getKeywords([
                                        "eventID" => $data["event"][0]->eventID,
                                        "chapter" => $data["event"][0]->currentChapter
                                    ]);

                                    if(!empty($keywords))
                                    {
                                        $kwCheck = (array)json_decode($data["event"][0]->kwCheck, true);
                                        if(array_key_exists($data["event"][0]->currentChapter, $kwCheck))
                                        {
                                            $kwCheck[$data["event"][0]->currentChapter]["done"] = 1;
                                        }

                                        $crCheck = (array)json_decode($data["event"][0]->crCheck, true);
                                        $crCheck[$data["event"][0]->currentChapter] = [
                                            "memberID" => 0,
                                            "done" => 0
                                        ];

                                        $postdata = [
                                            "kwCheck" => json_encode($kwCheck),
                                            "crCheck" => json_encode($crCheck)
                                        ];

                                        $this->_model->updateTranslator($postdata, [
                                            "trID" => $data["event"][0]->trID
                                        ]);
                                        Url::redirect('events/');
                                    }
                                    else
                                    {
                                        $error[] = __("keywords_empty_error");
                                    }
                                }
                            }
                        }

                        return View::make('Events/SUN/Checker')
                            ->nest('page', 'Events/SUN/TheoCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CONTENT_REVIEW:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $crCheck = (array)json_decode($data["event"][0]->crCheck, true);
                                if(array_key_exists($data["event"][0]->currentChapter, $crCheck))
                                {
                                    $crCheck[$data["event"][0]->currentChapter]["done"] = 1;
                                }

                                $postdata = [
                                    "crCheck" => json_encode($crCheck),
                                ];

                                $this->_model->updateTranslator($postdata, [
                                    "trID" => $data["event"][0]->trID
                                ]);
                                Url::redirect('events/checker-sun/' . $data["event"][0]->eventID .
                                    "/".$data["event"][0]->memberID."/".$data["event"][0]->currentChapter);
                            }
                        }

                        return View::make('Events/SUN/Checker')
                            ->nest('page', 'Events/SUN/ContentReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::FINAL_REVIEW:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"]))
                            {
                                $chunks = isset($_POST["chunks"]) ? (array)$_POST["chunks"] : array();
                                $chunks = array_map("trim", $chunks);
                                $chunks = array_filter($chunks, function($v) {
                                    return !empty($v);
                                });

                                if(sizeof($chunks) < sizeof($data["chunks"]))
                                    $error[] = __("empty_verses_error");
                                
                                if(!isset($error))
                                {
                                    $versesCombined = [];
                                    foreach ($chunks as $key => $chunk)
                                    {
                                        $verses = preg_split("/\|([0-9]+)\|/", $chunk, -1, PREG_SPLIT_NO_EMPTY);

                                        if(sizeof($data["chunks"][$key]) != sizeof($verses))
                                        {
                                            $error[] = __("not_equal_verse_markers");
                                            break;
                                        }

                                        $versesCombined[$key] = array_combine($data["chunks"][$key], $verses);
                                    }

                                    if(!isset($error))
                                    {
                                        foreach ($versesCombined as $key => $chunk)
                                        {
                                            $translation[$key][EventMembers::TRANSLATOR]["verses"] = $chunk;

                                            $tID = $translation[$key]["tID"];
                                            unset($translation[$key]["tID"]);

                                            $encoded = json_encode($translation[$key]);
                                            $json_error = json_last_error();

                                            if($json_error == JSON_ERROR_NONE)
                                            {
                                                $trData = array(
                                                    "translatedVerses"  => $encoded,
                                                    "translateDone" => true
                                                );
                                                $this->_translationModel->updateTranslation(
                                                    $trData,
                                                    array(
                                                        "trID" => $data["event"][0]->trID,
                                                        "tID" => $tID));
                                            }
                                            else
                                            {
                                                $error[] = __("error_ocured", array($tID));
                                            }
                                        }

                                        if(!isset($error))
                                        {
                                            $chapters = [];
                                            for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                                            {
                                                $data["chapters"][$i] = [];
                                            }

                                            $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                            foreach ($chaptersDB as $chapter) {
                                                $tmp["trID"] = $chapter["trID"];
                                                $tmp["memberID"] = $chapter["memberID"];
                                                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                                $tmp["checked"] = $chapter["checked"];

                                                $chapters[$chapter["chapter"]] = $tmp;
                                            }

                                            $chapters[$data["event"][0]->currentChapter]["done"] = true;

                                            // Check if whole book is finished
                                            if($this->checkBookFinished($chapters, $data["event"][0]->chaptersNum, true))
                                                $this->_model->updateEvent([
                                                    "state" => EventStates::TRANSLATED,
                                                    "dateTo" => date("Y-m-d H:i:s", time())],
                                                    ["eventID" => $data["event"][0]->eventID]);

                                            $this->_model->updateChapter(["checked" => true], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]);

                                            $crCheck = (array)json_decode($data["event"][0]->crCheck, true);
                                            if(array_key_exists($data["event"][0]->currentChapter, $crCheck))
                                            {
                                                $crCheck[$data["event"][0]->currentChapter]["done"] = 2;
                                            }

                                            $postdata = [
                                                "crCheck" => json_encode($crCheck),
                                            ];

                                            $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                            Url::redirect('events/');
                                        }
                                    }
                                }
                            }
                        }

                        return View::make('Events/SUN/Checker')
                            ->nest('page', 'Events/SUN/FinalReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;
                }
            }
            else
            {
                $data["error"] = true;
                $error[] = __("wrong_event_state_error");

                return View::make('Events/SUN/Checker')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/SUN/Checker')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }


    /**
     * View for Theo check and V-b-v check in ODB SUN event
     * @param $eventID
     * @param $memberID
     * @param $chapter
     * @return View
     */
    public function checkerOdbSun($eventID, $memberID, $chapter)
    {
        $isAjax = false;
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $isAjax = true;
            $response["success"] = false;
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["event"] = $this->_model->getMemberEventsForSun(
            Session::get("memberID"), $eventID, $memberID, $chapter);

        if(!empty($data["event"]))
        {
            if(!in_array($data["event"][0]->bookProject, ["sun"]))
            {
                Url::redirect("events/");
            }

            $title = $data["event"][0]->name
                . " " . ($data["event"][0]->currentChapter > 0 ? $data["event"][0]->currentChapter : "")
                . " - " . $data["event"][0]->tLang
                . " - " . __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::TRANSLATING || $data["event"][0]->state == EventStates::TRANSLATED)
            {
                $data["turn"] = $this->getTurnCredentials();

                $chapters = $this->_model->getChapters($eventID, null, $chapter);
                $data["event"][0]->chunks = [];
                if(!empty($chapters))
                {
                    $data["event"][0]->chunks = $chapters[0]["chunks"];
                }

                switch ($data["event"][0]->step)
                {
                    case EventSteps::THEO_CHECK:
                        $sourceText = $this->getOtherSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $kwCheck = (array)json_decode($data["event"][0]->kwCheck, true);
                                if(array_key_exists($data["event"][0]->currentChapter, $kwCheck))
                                {
                                    $kwCheck[$data["event"][0]->currentChapter]["done"] = 1;
                                }

                                $crCheck = (array)json_decode($data["event"][0]->crCheck, true);
                                $crCheck[$data["event"][0]->currentChapter] = [
                                    "memberID" => 0,
                                    "done" => 0
                                ];

                                $postdata = [
                                    "kwCheck" => json_encode($kwCheck),
                                    "crCheck" => json_encode($crCheck)
                                ];

                                $this->_model->updateTranslator($postdata, [
                                    "trID" => $data["event"][0]->trID
                                ]);
                                Url::redirect('events/');
                            }
                        }

                        return View::make('Events/ODBSUN/Checker')
                            ->nest('page', 'Events/ODBSUN/TheoCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CONTENT_REVIEW:
                        $sourceText = $this->getOtherSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                foreach ($translation as $key => $chunk)
                                {
                                    $translation[$key][EventMembers::TRANSLATOR]["verses"] = [
                                        ($key+1) => $chunk[EventMembers::TRANSLATOR]["symbols"]
                                    ];

                                    $tID = $translation[$key]["tID"];
                                    unset($translation[$key]["tID"]);

                                    $encoded = json_encode($translation[$key]);
                                    $json_error = json_last_error();

                                    if($json_error == JSON_ERROR_NONE)
                                    {
                                        $trData = array(
                                            "translatedVerses"  => $encoded,
                                            "translateDone" => true
                                        );
                                        $this->_translationModel->updateTranslation(
                                            $trData,
                                            array(
                                                "trID" => $data["event"][0]->trID,
                                                "tID" => $tID));
                                    }
                                    else
                                    {
                                        $error[] = __("error_ocured", array($tID));
                                    }
                                }

                                if(!isset($error))
                                {
                                    $chapters = [];
                                    for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                                    {
                                        $data["chapters"][$i] = [];
                                    }

                                    $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                    foreach ($chaptersDB as $chapter) {
                                        $tmp["trID"] = $chapter["trID"];
                                        $tmp["memberID"] = $chapter["memberID"];
                                        $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                        $tmp["checked"] = $chapter["checked"];

                                        $chapters[$chapter["chapter"]] = $tmp;
                                    }

                                    $chapters[$data["event"][0]->currentChapter]["done"] = true;

                                    // Check if whole book is finished
                                    if($this->checkBookFinished($chapters, $data["event"][0]->chaptersNum, true))
                                        $this->_model->updateEvent([
                                            "state" => EventStates::TRANSLATED,
                                            "dateTo" => date("Y-m-d H:i:s", time())],
                                            ["eventID" => $data["event"][0]->eventID]);

                                    $this->_model->updateChapter(["checked" => true], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]);

                                    $crCheck = (array)json_decode($data["event"][0]->crCheck, true);
                                    if(array_key_exists($data["event"][0]->currentChapter, $crCheck))
                                    {
                                        $crCheck[$data["event"][0]->currentChapter]["done"] = 1;
                                    }

                                    $postdata = [
                                        "crCheck" => json_encode($crCheck),
                                    ];

                                    $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/');
                                }
                            }
                        }

                        return View::make('Events/ODBSUN/Checker')
                            ->nest('page', 'Events/ODBSUN/ContentReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;
                }
            }
            else
            {
                $data["error"] = true;
                $error[] = __("wrong_event_state_error");

                return View::make('Events/ODBSUN/Checker')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/ODBSUN/Checker')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }

    /**
     * View for Level 2 check page
     * @url /events/check-l2
     * @param $eventID
     * @return View
     */
    public function checkerL2($eventID)
    {
        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["isCheckerPage"] = true;
        $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::L2_CHECKER, $eventID);
        
        $title = "";

        if(!empty($data["event"]))
        {
            $title = $data["event"][0]->name
                . " " . ($data["event"][0]->currentChapter > 0 ? $data["event"][0]->currentChapter : "")
                . " - " . $data["event"][0]->tLang
                . " - " . __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::L2_CHECK || $data["event"][0]->state == EventStates::L2_CHECKED)
            {
                if($data["event"][0]->step == EventCheckSteps::NONE)
                    Url::redirect("events/information-l2/".$eventID);

                $data["turn"] = $this->getTurnCredentials();

                switch ($data["event"][0]->step) {
                    case EventCheckSteps::PRAY:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateL2Checker([
                                "step" => EventCheckSteps::NONE
                            ], [
                                "l2chID" => $data["event"][0]->l2chID
                            ]);
                            Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {

                                $postdata = [
                                    "step" => EventCheckSteps::CONSUME,
                                    "currentChapter" => $sourceText["currentChapter"]
                                ];
                                $this->_model->updateL2Checker($postdata, [
                                    "l2chID" => $data["event"][0]->l2chID
                                ]);
                                Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/Pray')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);

                    case EventCheckSteps::CONSUME:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateL2Checker([
                                "step" => EventCheckSteps::NONE
                            ], [
                                "l2chID" => $data["event"][0]->l2chID
                            ]);
                            Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {

                                $this->_model->updateL2Checker([
                                    "step" => EventCheckSteps::FST_CHECK
                                ], [
                                    "l2chID" => $data["event"][0]->l2chID
                                ]);
                                Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/Consume')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventCheckSteps::FST_CHECK:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateL2Checker([
                                "step" => EventCheckSteps::NONE
                            ], [
                                "l2chID" => $data["event"][0]->l2chID
                            ]);
                            Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                // Update L2 if it's empty
                                foreach ($translation as $tr)
                                {
                                    if(empty($tr[EventMembers::L2_CHECKER]["verses"]))
                                    {
                                        $tr[EventMembers::L2_CHECKER]["verses"] = $tr[EventMembers::TRANSLATOR]["verses"];
                                        $tID = $tr["tID"];
                                        unset($tr["tID"]);
                                        $this->_translationModel->updateTranslation(
                                            ["translatedVerses" => json_encode($tr)],
                                            ["tID" => $tID]
                                        );
                                    }
                                }

                                $chapters = [];
                                for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                                {
                                    $data["chapters"][$i] = [];
                                }

                                $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                foreach ($chaptersDB as $chapter) {
                                    $tmp["trID"] = $chapter["trID"];
                                    $tmp["memberID"] = $chapter["memberID"];
                                    $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                    $tmp["done"] = $chapter["done"];
                                    $tmp["l2memberID"] = $chapter["l2memberID"];
                                    $tmp["l2chID"] = $chapter["l2chID"];
                                    $tmp["l2checked"] = $chapter["l2checked"];

                                    $chapters[$chapter["chapter"]] = $tmp;
                                }

                                $this->_model->updateChapter([
                                    "l2checked" => true
                                ], [
                                    "eventID" => $data["event"][0]->eventID,
                                    "chapter" => $data["event"][0]->currentChapter
                                ]);

                                $chapters[$data["event"][0]->currentChapter]["l2checked"] = true;

                                // Check if the member has another chapter to check
                                // then redirect to preparation page
                                $nextChapter = 0;
                                $nextChapterDB = $this->_model->getNextChapter(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->memberID,
                                    "l2");
                                if(!empty($nextChapterDB))
                                    $nextChapter = $nextChapterDB[0]->chapter;

                                $sndCheck = (array)json_decode($data["event"][0]->sndCheck, true);
                                if(!array_key_exists($data["event"][0]->currentChapter, $sndCheck))
                                {
                                    $sndCheck[$data["event"][0]->currentChapter] = [
                                        "memberID" => 0,
                                        "done" => 0
                                    ];
                                }

                                $postdata = [
                                    "step" => EventSteps::NONE,
                                    "currentChapter" => 0,
                                    "sndCheck" => json_encode($sndCheck)
                                ];

                                if($nextChapter > 0)
                                {
                                    $postdata["step"] = EventSteps::PRAY;
                                    $postdata["currentChapter"] = $nextChapter;
                                }

                                $this->_model->updateL2Checker($postdata, [
                                    "l2chID" => $data["event"][0]->l2chID
                                ]);
                                Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/FstCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;
                }
            }
            else
            {
                $data["error"] = true;
                $error[] = __("wrong_event_state_error");

                return View::make('Events/L2/Checker')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/L2/Checker')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }

    /**
     * View for 2nd check and Peer check in Level2 event
     * @param $eventID
     * @param $memberID
     * @return View
     */
    public function checkerL2Continue($eventID, $memberID, $chapter)
    {
        $isAjax = false;
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $isAjax = true;
            $response["success"] = false;
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["isCheckerPage"] = true;
        $data["event"] = $this->_model->getMemberEventsForCheckerL2(
            Session::get("memberID"), $eventID, $memberID, $chapter);

        if(!empty($data["event"]))
        {
            $title = $data["event"][0]->name
                . " " . ($data["event"][0]->currentChapter > 0 ? $data["event"][0]->currentChapter : "")
                . " - " . $data["event"][0]->tLang
                . " - " . __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::L2_CHECK || $data["event"][0]->state == EventStates::L2_CHECKED)
            {
                $data["turn"] = $this->getTurnCredentials();

                $chapters = $this->_model->getChapters($eventID, null, $chapter);
                $data["event"][0]->chunks = [];
                if(!empty($chapters))
                {
                    $data["event"][0]->chunks = $chapters[0]["chunks"];
                }

                switch ($data["event"][0]->step)
                {
                    case EventCheckSteps::SND_CHECK:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $sndCheck = (array)json_decode($data["event"][0]->sndCheck, true);
                                if(array_key_exists($data["event"][0]->currentChapter, $sndCheck))
                                {
                                    $sndCheck[$data["event"][0]->currentChapter]["done"] = 1;
                                }

                                $postdata = [
                                    "sndCheck" => json_encode($sndCheck)
                                ];

                                $this->_model->updateL2Checker($postdata, [
                                    "l2chID" => $data["event"][0]->l2chID
                                ]);
                                Url::redirect('events/checker-l2/' . $data["event"][0]->eventID .
                                    "/".$data["event"][0]->memberID."/".$data["event"][0]->currentChapter);
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/SndCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventCheckSteps::KEYWORD_CHECK_L2:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $sndCheck = (array)json_decode($data["event"][0]->sndCheck, true);
                                if(array_key_exists($data["event"][0]->currentChapter, $sndCheck))
                                {
                                    $sndCheck[$data["event"][0]->currentChapter]["done"] = 2;
                                }
                                $peer1Check = (array)json_decode($data["event"][0]->peer1Check, true);
                                $peer2Check = (array)json_decode($data["event"][0]->peer2Check, true);
                                if(!array_key_exists($data["event"][0]->currentChapter, $peer1Check))
                                {
                                    $peer1Check[$data["event"][0]->currentChapter] = [
                                        "memberID" => 0,
                                        "done" => 0
                                    ];
                                    $peer2Check[$data["event"][0]->currentChapter] = [
                                        "memberID" => 0,
                                        "done" => 0
                                    ];
                                }

                                $postdata = [
                                    "sndCheck" => json_encode($sndCheck),
                                    "peer1Check" => json_encode($peer1Check),
                                    "peer2Check" => json_encode($peer2Check),
                                ];


                                $this->_model->updateL2Checker($postdata, [
                                    "l2chID" => $data["event"][0]->l2chID
                                ]);
                                Url::redirect('events/');
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/KeywordCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventCheckSteps::PEER_REVIEW_L2:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;

                                if($data["event"][0]->peer == 2)
                                {
                                    unset($data["isCheckerPage"]);
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $skip_kw = isset($_POST["skip_kw"]) && $_POST["skip_kw"] == 1 ? true : false;
                                $peer1Check = (array)json_decode($data["event"][0]->peer1Check, true);
                                $peer2Check = (array)json_decode($data["event"][0]->peer2Check, true);

                                if(!isset($error))
                                {
                                    if($data["event"][0]->peer == 1)
                                    {
                                        if(array_key_exists($data["event"][0]->currentChapter, $peer2Check)
                                            && $peer2Check[$data["event"][0]->currentChapter]["done"] == 1)
                                        {
                                            $peer1Check[$data["event"][0]->currentChapter]["done"] = 1;
                                            $postdata = [
                                                "peer1Check" => json_encode($peer1Check)
                                            ];

                                            // Check if the whole book was checked and set its state to L2_CHECKED
                                            $chapters = [];
                                            $events = $this->_model->getMembersForL2Event($data["event"][0]->eventID);

                                            foreach ($events as $event)
                                            {
                                                $peer2 = (array)json_decode($event["peer2Check"], true);
                                                if(!empty($peer2))
                                                {
                                                    $chapters += $peer2;
                                                }
                                            }

                                            if(sizeof($chapters) == $data["event"][0]->chaptersNum)
                                            {
                                                $allDone = true;
                                                foreach ($chapters as $chapter)
                                                {
                                                    if($chapter["done"] == 0)
                                                    {
                                                        $allDone = false;
                                                        break;
                                                    }
                                                }

                                                if($allDone)
                                                {
                                                    $this->_model->updateEvent([
                                                        "state" => EventStates::L2_CHECKED
                                                    ], [
                                                        "eventID" => $data["event"][0]->eventID
                                                    ]);
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $error[] = __("checker_not_ready_error");
                                        }
                                    }
                                    else
                                    {
                                        $keywords = $this->_translationModel->getKeywords([
                                            "eventID" => $data["event"][0]->eventID,
                                            "chapter" => $data["event"][0]->currentChapter
                                        ]);

                                        if(empty($keywords) || $skip_kw)
                                        {
                                            $peer2Check[$data["event"][0]->currentChapter]["done"] = 1;
                                            $postdata = [
                                                "peer2Check" => json_encode($peer2Check)
                                            ];
                                        }
                                        else
                                        {
                                            $response["kw_exist"] = true;
                                            $error[] = __("keywords_still_exist_error");
                                        }
                                    }

                                    if(!isset($error))
                                    {

                                        $this->_model->updateL2Checker($postdata, [
                                            "l2chID" => $data["event"][0]->l2chID
                                        ]);

                                        if(!$isAjax)
                                        {
                                            Url::redirect('events/');
                                        }
                                        else
                                        {
                                            $response["success"] = true;
                                            echo json_encode($response);
                                            exit;
                                        }
                                    }
                                    else
                                    {
                                        if($isAjax)
                                        {
                                            $response["errors"] = $error;
                                            echo json_encode($response);
                                            exit;
                                        }
                                    }
                                }
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/PeerCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;
                }
            }
            else
            {
                $data["error"] = true;
                $error[] = __("wrong_event_state_error");

                return View::make('Events/L2/Checker')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/L2/Checker')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }

    public function checkerL3($eventID)
    {
        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["isChecker"] = false;
        $data["isCheckerPage"] = true;
        $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::L3_CHECKER, $eventID);

        if(!empty($data["event"]))
        {
            if(!in_array($data["event"][0]->bookProject, ["ulb"]))
            {
                Url::redirect("events/checker-".$data["event"][0]->bookProject."-l3/".$eventID);
            }

            $title = $data["event"][0]->name
                . " " . ($data["event"][0]->currentChapter > -1 ? ($data["event"][0]->currentChapter == 0
                    ? __("front") : $data["event"][0]->currentChapter) : "")
                . " - " . $data["event"][0]->tLang
                . " - " . __($data["event"][0]->bookProject);

            if(($data["event"][0]->state == EventStates::L3_CHECK
                || $data["event"][0]->state == EventStates::COMPLETE))
            {
                if($data["event"][0]->step == EventSteps::NONE)
                    Url::redirect("events/information-l3/".$eventID);

                $data["turn"] = $this->getTurnCredentials();

                switch ($data["event"][0]->step) {
                    case EventCheckSteps::PRAY:

                        $data["currentChapter"] = $data["event"][0]->currentChapter;
                        if($data["event"][0]->currentChapter == 0)
                        {
                            $nextChapter = $this->_model->getNextChapter(
                                $data["event"][0]->eventID,
                                $data["event"][0]->memberID,
                                "l3");
                            $data["currentChapter"] = $nextChapter[0]->chapter;
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $peerCheck = (array)json_decode($data["event"][0]->peerCheck, true);
                                $peerCheck[$data["currentChapter"]] = [
                                    "memberID" => 0,
                                    "done" => 0
                                ];

                                $postdata = [
                                    "step" => EventCheckSteps::PEER_REVIEW_L3,
                                    "currentChapter" => $data["currentChapter"],
                                    "peerCheck" => json_encode($peerCheck)
                                ];
                                $this->_model->updateL3Checker($postdata, ["l3chID" => $data["event"][0]->l3chID]);

                                Url::redirect('events/checker-l3/' . $data["event"][0]->eventID);
                            }
                        }

                        // Check if translator just started translating of this book
                        $data["event"][0]->justStarted = $data["event"][0]->peerCheck == "";

                        return View::make('Events/L3/Checker')
                            ->nest('page', 'Events/L3/Pray')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);

                    case EventCheckSteps::PEER_REVIEW_L3:
                        $sourceText = $this->getSourceText($data);
                        $peerCheck = (array)json_decode($data["event"][0]->peerCheck, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $data["event"][0]->chkMemberID = null;
                                $data["event"][0]->checkerFName = null;
                                $data["event"][0]->checkerLName = null;

                                if(array_key_exists($data["event"][0]->currentChapter, $peerCheck)
                                    && $peerCheck[$data["event"][0]->currentChapter]["memberID"] > 0)
                                {
                                    $member = $this->_membersModel->getMember(
                                        ["memberID","firstName","lastName"],
                                        ["memberID", $peerCheck[$data["event"][0]->currentChapter]["memberID"]]
                                    );

                                    if(!empty($member))
                                    {
                                        $data["event"][0]->chkMemberID = $member[0]->memberID;
                                        $data["event"][0]->checkerFName = $member[0]->firstName;
                                        $data["event"][0]->checkerLName = $member[0]->lastName;
                                    }
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateL3Checker([
                                "step" => EventCheckSteps::NONE
                            ], [
                                "l3chID" => $data["event"][0]->l3chID
                            ]);
                            Url::redirect('events/checker-l3/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                if(array_key_exists($data["event"][0]->currentChapter, $peerCheck)
                                    && $peerCheck[$data["event"][0]->currentChapter]["done"] == 1)
                                {
                                    $postdata = [
                                        "step" => EventCheckSteps::PEER_EDIT_L3
                                    ];
                                    $this->_model->updateL3Checker($postdata, ["l3chID" => $data["event"][0]->l3chID]);
                                    Url::redirect('events/checker-l3/' . $data["event"][0]->eventID);
                                }
                                else
                                {
                                    $error[] = __("checker_not_ready_error");
                                }
                            }
                        }

                        return View::make('Events/L3/Checker')
                            ->nest('page', 'Events/L3/PeerReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);

                        break;

                    case EventCheckSteps::PEER_EDIT_L3:
                        $sourceText = $this->getSourceText($data);
                        $peerCheck = (array)json_decode($data["event"][0]->peerCheck, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $data["event"][0]->chkMemberID = null;
                                $data["event"][0]->checkerFName = null;
                                $data["event"][0]->checkerLName = null;

                                if(array_key_exists($data["event"][0]->currentChapter, $peerCheck)
                                    && $peerCheck[$data["event"][0]->currentChapter]["memberID"] > 0)
                                {
                                    $member = $this->_membersModel->getMember(["memberID","firstName","lastName"]
                                        , ["memberID", $peerCheck[$data["event"][0]->currentChapter]["memberID"]]);

                                    if(!empty($member))
                                    {
                                        $data["event"][0]->chkMemberID = $member[0]->memberID;
                                        $data["event"][0]->checkerFName = $member[0]->firstName;
                                        $data["event"][0]->checkerLName = $member[0]->lastName;
                                    }
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateL3Checker([
                                "step" => EventCheckSteps::NONE
                            ], [
                                "l3chID" => $data["event"][0]->l3chID
                            ]);
                            Url::redirect('events/checker-l3/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                if(array_key_exists($data["event"][0]->currentChapter, $peerCheck)
                                    && $peerCheck[$data["event"][0]->currentChapter]["done"] == 2)
                                {
                                    // Update L3 if it's empty
                                    foreach ($translation as $tr)
                                    {
                                        if(empty($tr[EventMembers::L3_CHECKER]["verses"]))
                                        {
                                            $tr[EventMembers::L3_CHECKER]["verses"] = $tr[EventMembers::L2_CHECKER]["verses"];
                                            $tID = $tr["tID"];
                                            unset($tr["tID"]);
                                            $this->_translationModel->updateTranslation(
                                                ["translatedVerses" => json_encode($tr)],
                                                ["tID" => $tID]
                                            );
                                        }
                                    }

                                    $chapters = [];
                                    for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                                    {
                                        $data["chapters"][$i] = [];
                                    }

                                    $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                    foreach ($chaptersDB as $chapter) {
                                        $tmp["trID"] = $chapter["trID"];
                                        $tmp["memberID"] = $chapter["memberID"];
                                        $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                        $tmp["l3checked"] = $chapter["l3checked"];

                                        $chapters[$chapter["chapter"]] = $tmp;
                                    }

                                    $chapters[$data["event"][0]->currentChapter]["l3checked"] = true;
                                    $this->_model->updateChapter(["l3checked" => true], [
                                        "eventID" => $data["event"][0]->eventID,
                                        "chapter" => $data["event"][0]->currentChapter]);

                                    // Check if whole scripture is finished
                                    if($this->checkBookFinished($chapters, $data["event"][0]->chaptersNum, false, 3))
                                        $this->_model->updateEvent([
                                            "state" => EventStates::COMPLETE,
                                            "dateTo" => date("Y-m-d H:i:s", time())],
                                            ["eventID" => $data["event"][0]->eventID]);

                                    // Check if the member has another chapter to check
                                    // then redirect to preparation page
                                    $nextChapter = 0;
                                    $nextChapterDB = $this->_model->getNextChapter($data["event"][0]->eventID, Session::get("memberID"), "l3");

                                    if(!empty($nextChapterDB))
                                        $nextChapter = $nextChapterDB[0]->chapter;

                                    $postdata = [
                                        "step" => EventSteps::NONE,
                                        "currentChapter" => 0
                                    ];

                                    if($nextChapter > 0)
                                    {
                                        $postdata["step"] = EventSteps::PRAY;
                                        $postdata["currentChapter"] = $nextChapter;
                                    }

                                    $this->_model->updateL3Checker($postdata, ["l3chID" => $data["event"][0]->l3chID]);
                                    Url::redirect('events/checker-l3/' . $data["event"][0]->eventID);
                                }
                                else
                                {
                                    $error[] = __("checker_not_ready_error");
                                }
                            }
                        }

                        return View::make('Events/L3/Checker')
                            ->nest('page', 'Events/L3/PeerEdit')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);

                        break;

                    case EventSteps::FINISHED:
                        $data["success"] = __("you_event_finished_success");

                        return View::make('Events/L3/Checker')
                            ->nest('page', 'Events/L3/Finished')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;
                }
            }
            else
            {
                $data["error"] = true;
                $error[] = __("wrong_event_state_error");

                return View::make('Events/L3Notes/Checker')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/Notes/Translator')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }


    public function checkerL3Peer($eventID, $memberID, $chapter)
    {
        $isAjax = false;
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $isAjax = true;
            $response["success"] = false;
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["event"] = $this->_model->getMemberEventsForCheckerL3(
            Session::get("memberID"), $eventID, $memberID, $chapter);
        $data["isChecker"] = true;

        if(!empty($data["event"]))
        {
            if(Session::get("memberID") == $data["event"][0]->memberID)
            {
                Url::redirect('events/');
            }

            $title = $data["event"][0]->name
                . " " . ($data["event"][0]->currentChapter > 0 ? $data["event"][0]->currentChapter : "")
                . " - " . $data["event"][0]->tLang
                . " - " . __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::L3_CHECK || $data["event"][0]->state == EventStates::COMPLETE)
            {
                $data["turn"] = $this->getTurnCredentials();

                $chapters = $this->_model->getChapters($eventID, null, $chapter);
                $data["event"][0]->chunks = [];
                if(!empty($chapters))
                {
                    $data["event"][0]->chunks = $chapters[0]["chunks"];
                }

                switch ($data["event"][0]->step)
                {
                    case EventCheckSteps::PEER_REVIEW_L3:
                    case EventCheckSteps::PEER_EDIT_L3:
                        $sourceText = $this->getSourceText($data);
                        $peerCheck = (array)json_decode($data["event"][0]->peerCheck, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $error[] = $sourceText["error"];
                            $data["error"] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                if(array_key_exists($data["event"][0]->currentChapter, $peerCheck))
                                {
                                    if($data["event"][0]->step == $data["event"][0]->peerStep)
                                    {
                                        if($peerCheck[$data["event"][0]->currentChapter]["done"] == 0)
                                            $peerCheck[$data["event"][0]->currentChapter]["done"] = 1;
                                        else
                                            $peerCheck[$data["event"][0]->currentChapter]["done"] = 2;

                                        $this->_model->updateL3Checker([
                                                "peerCheck" => json_encode($peerCheck)
                                            ]
                                            , ["l3chID" => $data["event"][0]->l3chID]);

                                        $response["success"] = true;
                                    }
                                    else
                                    {
                                        $error[] = __("peer_checker_not_ready_error");
                                        $response["errors"] = $error;
                                    }
                                    echo json_encode($response);
                                    exit;
                                }
                            }
                        }

                        return View::make('Events/L3/Checker')
                            ->nest('page', 'Events/L3/PeerReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;
                }
            }
            else
            {
                $data["error"] = true;
                $error[] = __("wrong_event_state_error");

                return View::make('Events/L3Notes/Checker')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/L3Notes/Checker')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }


    public function information($eventID)
    {
        $data["menu"] = 1;
        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;
        $canViewInfo = false;
        $isAjax = false;

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $isAjax = true;
            $response = ["success" => false];
        }

        if(!empty($data["event"]))
        {
            if(!in_array($data["event"][0]->bookProject, ["ulb"]))
            {
                Url::redirect("events/");
            }

            $admins = (array)json_decode($data["event"][0]->admins, true);

            if($data["event"][0]->translator === null && $data["event"][0]->checker === null
                && $data["event"][0]->checker_l2 === null && $data["event"][0]->checker_l3 === null)
            {
                // If member is not a participant of the event, check if he is a facilitator
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = $canViewInfo = in_array(Session::get("memberID"), $admins);

                    if(!$data["isAdmin"])
                    {
                        if(Session::get("isSuperAdmin")) // Or superadmin
                        {
                            $data["isAdmin"] = $canViewInfo = true;
                        }
                        else
                        {
                            if(!$isAjax)
                                $error[] = __("empty_or_not_permitted_event_error");
                            else
                            {
                                $response["errorType"] = "empty_no_permission";
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
                }
                else
                {
                    if(!$isAjax)
                        $error[] = __("empty_or_not_permitted_event_error");
                    else
                    {
                        $response["errorType"] = "empty_no_permission";
                        echo json_encode($response);
                        exit;
                    }
                }
            }
            else
            {
                $canViewInfo = true;
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = in_array(Session::get("memberID"), $admins)
                        || Session::get("isSuperAdmin");
                }
            }

            if($data["event"][0]->state == "started" && $canViewInfo)
            {
                if(!$isAjax)
                    $error[] = __("not_started_event_error", array($data["event"][0]->eventID));
                else
                {
                    $response["errorType"] = "not_started";
                    echo json_encode($response);
                    exit;
                }
            }
        }
        else
        {
            if(!$isAjax)
                $error[] = __("empty_or_not_permitted_event_error");
            else
            {
                $response["errorType"] = "empty_no_permission";
                echo json_encode($response);
                exit;
            }
        }

        if(!isset($error))
        {
            $data = array_merge($data, $this->_model->calculateUlbLevel1EventProgress($data["event"]));
            $members = $data["members"];

            $empty = array_fill(0, sizeof($admins), "");
            $adminsArr = array_combine($admins, $empty);

            $members += $adminsArr;
            $membersArray = (array)$this->_membersModel->getMembers(array_filter(array_keys($members)));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = [];
                $members[$member->memberID]["userName"] = $member->userName;
                $members[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                $members[$member->memberID]["avatar"] = $member->avatar;
            }

            foreach ($members as $key => $member) {
                if(!is_numeric($key) && $key != "na")
                {
                    $name = $members[$key];
                    $members[$key] = [];
                    $members[$key]["userName"] = $key;
                    $members[$key]["name"] = $name;
                    $members[$key]["avatar"] = "n1";
                }
            }

            $members["na"] = __("not_available");
            $members = array_filter($members);

            $data["admins"] = $admins;
            $data["members"] = $members;
        }

        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;

        if(!$isAjax)
        {
            return View::make('Events/L1/Information')
                ->shares("title", __("event_info"))
                ->shares("data", $data)
                ->shares("error", @$error);
        }
        else
        {
            $this->layout = "dummy";
            $response["success"] = true;
            $response["progress"] = $data["overall_progress"];
            $response["admins"] = $data["admins"];
            $response["members"] = $data["members"];
            $response["html"] = View::make("Events/L1/GetInfo")
                ->shares("data", $data)
                ->renderContents();

            echo json_encode($response);
        }
    }

    public function informationL2($eventID)
    {
        $data["menu"] = 1;
        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;
        $canViewInfo = false;
        $isAjax = false;

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $isAjax = true;
            $response = ["success" => false];
        }

        if(!empty($data["event"]))
        {
            if(!in_array($data["event"][0]->bookProject, ["ulb"]) || $data["event"][0]->admins_l2 == "")
            {
                Url::redirect("events/");
            }

            $admins = (array)json_decode($data["event"][0]->admins_l2, true);

            if($data["event"][0]->translator === null && $data["event"][0]->checker === null
                && $data["event"][0]->checker_l2 === null && $data["event"][0]->checker_l3 === null)
            {
                // If member is not a participant of the event, check if he is a facilitator
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = $canViewInfo = in_array(Session::get("memberID"), $admins);

                    if(!$data["isAdmin"])
                    {
                        if(Session::get("isSuperAdmin")) // Or superadmin
                        {
                            $data["isAdmin"] = $canViewInfo = true;
                        }
                        else
                        {
                            if(!$isAjax)
                                $error[] = __("empty_or_not_permitted_event_error");
                            else
                            {
                                $response["errorType"] = "empty_no_permission";
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
                }
                else
                {
                    if(!$isAjax)
                        $error[] = __("empty_or_not_permitted_event_error");
                    else
                    {
                        $response["errorType"] = "empty_no_permission";
                        echo json_encode($response);
                        exit;
                    }
                }
            }
            else
            {
                $canViewInfo = true;
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = in_array(Session::get("memberID"), $admins)
                        || Session::get("isSuperAdmin");
                }
            }

            if($data["event"][0]->state == "started" && $canViewInfo)
            {
                if(!$isAjax)
                    $error[] = __("not_started_event_error", array($data["event"][0]->eventID));
                else
                {
                    $response["errorType"] = "not_started";
                    echo json_encode($response);
                    exit;
                }
            }
        }
        else
        {
            if(!$isAjax)
                $error[] = __("empty_or_not_permitted_event_error");
            else
            {
                $response["errorType"] = "empty_no_permission";
                echo json_encode($response);
                exit;
            }
        }

        if(!isset($error))
        {
            $data = array_merge($data, $this->_model->calculateUlbLevel2EventProgress($data["event"]));
            $members = $data["members"];

            $empty = array_fill(0, sizeof($admins), "");
            $adminsArr = array_combine($admins, $empty);

            $members += $adminsArr;
            $membersArray = (array)$this->_membersModel->getMembers(array_filter(array_keys($members)));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = [];
                $members[$member->memberID]["userName"] = $member->userName;
                $members[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                $members[$member->memberID]["avatar"] = $member->avatar;
            }

            foreach ($members as $key => $member) {
                if(!is_numeric($key) && $key != "na")
                {
                    $name = $members[$key];
                    $members[$key] = [];
                    $members[$key]["userName"] = $key;
                    $members[$key]["name"] = $name;
                    $members[$key]["avatar"] = "n1";
                }
            }

            $members["na"] = __("not_available");
            $members = array_filter($members);

            $data["admins"] = $admins;
            $data["members"] = $members;
        }

        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;

        if(!$isAjax)
        {
            return View::make('Events/L2/Information')
                ->shares("title", __("event_info"))
                ->shares("data", $data)
                ->shares("error", @$error);
        }
        else
        {
            $this->layout = "dummy";
            $response["success"] = true;
            $response["progress"] = $data["overall_progress"];
            $response["admins"] = $data["admins"];
            $response["members"] = $data["members"];
            $response["html"] = View::make("Events/L2/GetInfo")
                ->shares("data", $data)
                ->renderContents();

            echo json_encode($response);
        }
    }


    public function informationL3($eventID)
    {
        $data["menu"] = 1;
        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;
        $canViewInfo = false;
        $isAjax = false;

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $isAjax = true;
            $response = ["success" => false];
        }

        if(!empty($data["event"]))
        {
            if($data["event"][0]->admins_l3 == "")
            {
                Url::redirect("events/");
            }

            $admins = (array)json_decode($data["event"][0]->admins_l3, true);

            if($data["event"][0]->translator === null && $data["event"][0]->checker === null
                && $data["event"][0]->checker_l2 === null && $data["event"][0]->checker_l3 === null)
            {
                // If member is not a participant of the event, check if he is a facilitator
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = $canViewInfo = in_array(Session::get("memberID"), $admins);

                    if(!$data["isAdmin"])
                    {
                        if(Session::get("isSuperAdmin")) // Or superadmin
                        {
                            $data["isAdmin"] = $canViewInfo = true;
                        }
                        else
                        {
                            if(!$isAjax)
                                $error[] = __("empty_or_not_permitted_event_error");
                            else
                            {
                                $response["errorType"] = "empty_no_permission";
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
                }
                else
                {
                    if(!$isAjax)
                        $error[] = __("empty_or_not_permitted_event_error");
                    else
                    {
                        $response["errorType"] = "empty_no_permission";
                        echo json_encode($response);
                        exit;
                    }
                }
            }
            else
            {
                $canViewInfo = true;
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = in_array(Session::get("memberID"), $admins)
                        || Session::get("isSuperAdmin");
                }
            }

            if($data["event"][0]->state == EventStates::L3_RECRUIT && $canViewInfo)
            {
                if(!$isAjax)
                    $error[] = __("not_started_event_error", array($data["event"][0]->eventID));
                else
                {
                    $response["errorType"] = "not_started";
                    echo json_encode($response);
                    exit;
                }
            }
        }
        else
        {
            if(!$isAjax)
                $error[] = __("empty_or_not_permitted_event_error");
            else
            {
                $response["errorType"] = "empty_no_permission";
                echo json_encode($response);
                exit;
            }
        }

        if(!isset($error))
        {
            $data = array_merge($data, $this->_model->calculateAnyLevel3EventProgress($data["event"]));
            $members = $data["members"];

            $empty = array_fill(0, sizeof($admins), "");
            $adminsArr = array_combine($admins, $empty);

            $members += $adminsArr;
            $membersArray = (array)$this->_membersModel->getMembers(array_filter(array_keys($members)));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = [];
                $members[$member->memberID]["userName"] = $member->userName;
                $members[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                $members[$member->memberID]["avatar"] = $member->avatar;
            }

            foreach ($members as $key => $member) {
                if(!is_numeric($key) && $key != "na")
                {
                    $name = $members[$key];
                    $members[$key] = [];
                    $members[$key]["userName"] = $key;
                    $members[$key]["name"] = $name;
                    $members[$key]["avatar"] = "n1";
                }
            }

            $members["na"] = __("not_available");
            $members = array_filter($members);

            $data["admins"] = $admins;
            $data["members"] = $members;
        }

        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;

        if(!$isAjax)
        {
            return View::make('Events/L3Notes/Information')
                ->shares("title", __("event_info"))
                ->shares("data", $data)
                ->shares("error", @$error);
        }
        else
        {
            $this->layout = "dummy";
            $response["success"] = true;
            $response["progress"] = $data["overall_progress"];
            $response["admins"] = $data["admins"];
            $response["members"] = $data["members"];
            $response["html"] = View::make("Events/L3Notes/GetInfo")
                ->shares("data", $data)
                ->renderContents();

            echo json_encode($response);
        }
    }

    public function informationSun($eventID)
    {
        $data["menu"] = 1;
        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;
        $canViewInfo = false;
        $isAjax = false;

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $isAjax = true;
            $response = ["success" => false];
        }

        if(!empty($data["event"]))
        {
            if(!in_array($data["event"][0]->bookProject, ["sun"]))
            {
                Url::redirect("events/");
            }

            $admins = (array)json_decode($data["event"][0]->admins, true);

            if($data["event"][0]->translator === null && $data["event"][0]->checker === null
                && $data["event"][0]->checker_l2 === null && $data["event"][0]->checker_l3 === null)
            {
                // If member is not a participant of the event, check if he is a facilitator
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = $canViewInfo = in_array(Session::get("memberID"), $admins);

                    if(!$data["isAdmin"])
                    {
                        if(Session::get("isSuperAdmin")) // Or superadmin
                        {
                            $data["isAdmin"] = $canViewInfo = true;
                        }
                        else
                        {
                            if(!$isAjax)
                                $error[] = __("empty_or_not_permitted_event_error");
                            else
                            {
                                $response["errorType"] = "empty_no_permission";
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
                }
                else
                {
                    if(!$isAjax)
                        $error[] = __("empty_or_not_permitted_event_error");
                    else
                    {
                        $response["errorType"] = "empty_no_permission";
                        echo json_encode($response);
                        exit;
                    }
                }
            }
            else
            {
                $canViewInfo = true;
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = in_array(Session::get("memberID"), $admins)
                        || Session::get("isSuperAdmin");
                }
            }

            if($data["event"][0]->state == "started" && $canViewInfo)
            {
                if(!$isAjax)
                    $error[] = __("not_started_event_error", array($data["event"][0]->eventID));
                else
                {
                    $response["errorType"] = "not_started";
                    echo json_encode($response);
                    exit;
                }
            }
        }
        else
        {
            if(!$isAjax)
                $error[] = __("empty_or_not_permitted_event_error");
            else
            {
                $response["errorType"] = "empty_no_permission";
                echo json_encode($response);
                exit;
            }
        }

        if(!isset($error))
        {
            $data = array_merge($data, $this->_model->calculateSunLevel1EventProgress($data["event"]));
            $members = $data["members"];

            $empty = array_fill(0, sizeof($admins), "");
            $adminsArr = array_combine($admins, $empty);

            $members += $adminsArr;
            $membersArray = (array)$this->_membersModel->getMembers(array_filter(array_keys($members)));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = [];
                $members[$member->memberID]["userName"] = $member->userName;
                $members[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                $members[$member->memberID]["avatar"] = $member->avatar;
            }

            foreach ($members as $key => $member) {
                if(!is_numeric($key) && $key != "na")
                {
                    $name = $members[$key];
                    $members[$key] = [];
                    $members[$key]["userName"] = $key;
                    $members[$key]["name"] = $name;
                    $members[$key]["avatar"] = "n1";
                }
            }

            $members["na"] = __("not_available");
            $members = array_filter($members);

            $data["admins"] = $admins;
            $data["members"] = $members;
        }

        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;

        if(!$isAjax)
        {
            return View::make('Events/SUN/Information')
                ->shares("title", __("event_info"))
                ->shares("data", $data)
                ->shares("error", @$error);
        }
        else
        {
            $this->layout = "dummy";
            $response["success"] = true;
            $response["progress"] = $data["overall_progress"];
            $response["admins"] = $data["admins"];
            $response["members"] = $data["members"];
            $response["html"] = View::make("Events/SUN/GetInfo")
                ->shares("data", $data)
                ->renderContents();

            echo json_encode($response);
        }
    }

    public function informationOdbSun($eventID)
    {
        $data["menu"] = 1;
        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;
        $canViewInfo = false;
        $isAjax = false;

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $isAjax = true;
            $response = ["success" => false];
        }

        if(!empty($data["event"]))
        {
            if(!in_array($data["event"][0]->bookProject, ["sun"]))
            {
                Url::redirect("events/");
            }

            $admins = (array)json_decode($data["event"][0]->admins, true);

            if($data["event"][0]->translator === null && $data["event"][0]->checker === null
                && $data["event"][0]->checker_l2 === null && $data["event"][0]->checker_l3 === null)
            {
                // If member is not a participant of the event, check if he is a facilitator
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = $canViewInfo = in_array(Session::get("memberID"), $admins);

                    if(!$data["isAdmin"])
                    {
                        if(Session::get("isSuperAdmin")) // Or superadmin
                        {
                            $data["isAdmin"] = $canViewInfo = true;
                        }
                        else
                        {
                            if(!$isAjax)
                                $error[] = __("empty_or_not_permitted_event_error");
                            else
                            {
                                $response["errorType"] = "empty_no_permission";
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
                }
                else
                {
                    if(!$isAjax)
                        $error[] = __("empty_or_not_permitted_event_error");
                    else
                    {
                        $response["errorType"] = "empty_no_permission";
                        echo json_encode($response);
                        exit;
                    }
                }
            }
            else
            {
                $canViewInfo = true;
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = in_array(Session::get("memberID"), $admins)
                        || Session::get("isSuperAdmin");
                }
            }

            if($data["event"][0]->state == "started" && $canViewInfo)
            {
                if(!$isAjax)
                    $error[] = __("not_started_event_error", array($data["event"][0]->eventID));
                else
                {
                    $response["errorType"] = "not_started";
                    echo json_encode($response);
                    exit;
                }
            }
        }
        else
        {
            if(!$isAjax)
                $error[] = __("empty_or_not_permitted_event_error");
            else
            {
                $response["errorType"] = "empty_no_permission";
                echo json_encode($response);
                exit;
            }
        }

        if(!isset($error))
        {
            $data = array_merge($data, $this->_model->calculateOdbSunLevel1EventProgress($data["event"]));
            $members = $data["members"];

            $empty = array_fill(0, sizeof($admins), "");
            $adminsArr = array_combine($admins, $empty);

            $members += $adminsArr;
            $membersArray = (array)$this->_membersModel->getMembers(array_filter(array_keys($members)));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = [];
                $members[$member->memberID]["userName"] = $member->userName;
                $members[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                $members[$member->memberID]["avatar"] = $member->avatar;
            }

            foreach ($members as $key => $member) {
                if(!is_numeric($key) && $key != "na")
                {
                    $name = $members[$key];
                    $members[$key] = [];
                    $members[$key]["userName"] = $key;
                    $members[$key]["name"] = $name;
                    $members[$key]["avatar"] = "n1";
                }
            }

            $members["na"] = __("not_available");
            $members = array_filter($members);

            $data["admins"] = $admins;
            $data["members"] = $members;

            $data["odb"] = $this->_apiModel->getOtherSource(
                "odb",
                $data["event"][0]->bookCode,
                $data["event"][0]->sourceLangID
            );
        }

        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;

        if(!$isAjax)
        {
            return View::make('Events/ODBSUN/Information')
                ->shares("title", __("event_info"))
                ->shares("data", $data)
                ->shares("error", @$error);
        }
        else
        {
            $this->layout = "dummy";
            $response["success"] = true;
            $response["progress"] = $data["overall_progress"];
            $response["admins"] = $data["admins"];
            $response["members"] = $data["members"];
            $response["html"] = View::make("Events/ODBSUN/GetInfo")
                ->shares("data", $data)
                ->renderContents();

            echo json_encode($response);
        }
    }

    public function manage($eventID)
    {
        if (!Session::get('isAdmin') && !Session::get("isSuperAdmin"))
        {
            Url::redirect("events");
        }

        $data["menu"] = 1;
        $data["event"] = $this->_model->getMemberEventsForAdmin(
            Session::get("memberID"),
            $eventID,
            Session::get("isSuperAdmin")
        );

        if(!empty($data["event"]))
        {
            $superadmins = (array)json_decode($data["event"][0]->superadmins, true);
            $adms = (array)json_decode($data["event"][0]->admins, true);

            if(Session::get("isAdmin") || Session::get("isSuperAdmin"))
            {
                if(!in_array(Session::get("memberID"), $superadmins)
                    && !in_array(Session::get("memberID"), $adms))
                {
                    Url::redirect("events");
                }
            }
            else
            {
                Url::redirect("events");
            }

            $data["chapters"] = [];
            if($data["event"][0]->bookProject == "tn")
                $data["chapters"][0] = [];
            
                for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
            {
                $data["chapters"][$i] = [];
            }

            $chapters = $this->_model->getChapters(
                $data["event"][0]->eventID,
                null,
                null,
                $data["event"][0]->bookProject
            );

            foreach ($chapters as $key => $chapter) {
                $tmp["trID"] = $chapter["trID"];
                $tmp["memberID"] = $chapter["memberID"];
                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["done"] = $chapter["done"];
                $tmp["kwCheck"] = (array)json_decode($chapter["kwCheck"], true);
                $tmp["crCheck"] = (array)json_decode($chapter["crCheck"], true);
                $tmp["peerCheck"] = (array)json_decode($chapter["peerCheck"], true);

                $data["chapters"][$chapter["chapter"]] = $tmp;
            }

            if($data["event"][0]->sourceBible == "odb")
            {
                $data["odb"] = $this->_apiModel->getOtherSource(
                    "odb",
                    $data["event"][0]->bookCode,
                    $data["event"][0]->sourceLangID
                );
            }

            $data["members"] = $this->_model->getMembersForEvent($data["event"][0]->eventID);
            $data["out_members"] = [];

            // Include sun checkers that are not in the list of participants (usually superadmins)
            if($data["event"][0]->bookProject == "sun")
            {
                $tmpmems = [];
                foreach ($data["members"] as $key => $member) {
                    $kw = (array)json_decode($member["kwCheck"], true);
                    $cr = (array)json_decode($member["crCheck"], true);

                    foreach ($kw as $chap) {
                        $tmpmems[] = $chap["memberID"];
                    }

                    foreach ($cr as $chap) {
                        $tmpmems[] = $chap["memberID"];
                    }
                }

                $data["out_members"] = (array)$this->_membersModel->getMembers($tmpmems);
                $data["out_members"] = array_map(function ($item){ return (array)$item;}, $data["out_members"]);
            }

            if (isset($_POST) && !empty($_POST)) {
                if(!empty(array_filter($data["chapters"])))
                {
                    $updated = $this->_model->updateEvent(
                        array(
                            "state" => EventStates::TRANSLATING,
                            "dateFrom" => date("Y-m-d H:i:s", time())),
                            array("eventID" => $eventID));
                    if($updated)
                        Url::redirect("events/manage/".$eventID);
                }
                else
                {
                    $error[] = __("event_chapters_error");
                }
            }
        }
        else
        {
            $error[] = __("empty_or_not_permitted_event_error");
        }

        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;

        $page = $data["event"][0]->langInput ? "ManageLangInput" : "Manage";

        return View::make('Events/'.$page)
            ->shares("title", __("manage_event"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    public function manageL2($eventID)
    {
        if (!Session::get('isAdmin') && !Session::get("isSuperAdmin"))
        {
            Url::redirect("events");
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["event"] = $this->_model->getMemberEventsForAdmin(
            Session::get("memberID"),
            $eventID,
            Session::get("isSuperAdmin")
        );

        if(!empty($data["event"]))
        {
            $superadmins = (array)json_decode($data["event"][0]->superadmins, true);
            if(Session::get("isSuperAdmin") && !in_array(Session::get("memberID"), $superadmins))
                Url::redirect("events");

            if(!Session::get("isSuperAdmin"))
            {
                $adms = (array)json_decode($data["event"][0]->admins_l2, true);
                if(!in_array(Session::get("memberID"), $adms))
                {
                    Url::redirect("/events");
                }
            }

            if($data["event"][0]->state != EventStates::L2_RECRUIT && 
                $data["event"][0]->state != EventStates::L2_CHECK &&
                $data["event"][0]->state != EventStates::L2_CHECKED)
            {
                Url::redirect("events");
            }
            
            $data["chapters"] = [];
            
            for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
            {
                $data["chapters"][$i] = [];
            }

            $chapters = $this->_model->getChapters(
                $data["event"][0]->eventID,
                null,
                null,
                "l2"
            );

            foreach ($chapters as $chapter) {
                if($chapter["l2memberID"] == 0) continue;
                
                $tmp["l2chID"] = $chapter["l2chID"];
                $tmp["l2memberID"] = $chapter["l2memberID"];
                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["l2checked"] = $chapter["l2checked"];
                $tmp["sndCheck"] = (array)json_decode($chapter["sndCheck"], true);
                $tmp["peer1Check"] = (array)json_decode($chapter["peer1Check"], true);
                $tmp["peer2Check"] = (array)json_decode($chapter["peer2Check"], true);

                $data["chapters"][$chapter["chapter"]] = $tmp;
            }

            $data["members"] = $this->_model->getMembersForL2Event($data["event"][0]->eventID);
            $data["out_members"] = [];

            // Include l2 checkers that are not in the list of participants (usually superadmins)
            $tmpmems = [];
            foreach ($data["members"] as $key => $member) {
                $snd = (array)json_decode($member["sndCheck"], true);
                $peer1 = (array)json_decode($member["peer1Check"], true);
                $peer2 = (array)json_decode($member["peer2Check"], true);

                foreach ($snd as $chap) {
                    $tmpmems[] = $chap["memberID"];
                }

                foreach ($peer1 as $chap) {
                    $tmpmems[] = $chap["memberID"];
                }

                foreach ($peer2 as $chap) {
                    $tmpmems[] = $chap["memberID"];
                }
            }

            $data["out_members"] = $this->_membersModel->getMembers($tmpmems);
            $data["out_members"] = array_map(function ($item){ return (array)$item;}, $data["out_members"]);

            if (isset($_POST) && !empty($_POST)) {
                if(!empty(array_filter($data["chapters"])))
                {
                    $updated = $this->_model->updateEvent(
                        array(
                            "state" => EventStates::L2_CHECK,
                            /*"dateFrom" => date("Y-m-d H:i:s", time())*/),
                            array("eventID" => $eventID));
                    if($updated)
                        Url::redirect("events/manage-l2/".$eventID);
                }
                else
                {
                    $error[] = __("event_chapters_error");
                }
            }
        }
        else
        {
            $error[] = __("empty_or_not_permitted_event_error");
        }

        return View::make('Events/L2/Manage')
            ->shares("title", __("manage_event"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }


    public function manageL3($eventID)
    {
        if (!Session::get('isAdmin') && !Session::get("isSuperAdmin"))
        {
            Url::redirect("events");
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data["event"] = $this->_model->getMemberEventsForAdmin(
            Session::get("memberID"),
            $eventID,
            Session::get("isSuperAdmin")
        );

        if(!empty($data["event"]))
        {
            $superadmins = (array)json_decode($data["event"][0]->superadmins, true);
            if(Session::get("isSuperAdmin") && !in_array(Session::get("memberID"), $superadmins))
                Url::redirect("events");

            if(!Session::get("isSuperAdmin"))
            {
                $adms = (array)json_decode($data["event"][0]->admins_l3, true);
                if(!in_array(Session::get("memberID"), $adms))
                {
                    Url::redirect("/events");
                }
            }

            if($data["event"][0]->state != EventStates::L3_RECRUIT &&
                $data["event"][0]->state != EventStates::L3_CHECK &&
                $data["event"][0]->state != EventStates::COMPLETE)
            {
                Url::redirect("events");
            }

            $data["chapters"] = [];
            for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
            {
                $data["chapters"][$i] = [];
            }

            $chapters = $this->_model->getChapters(
                $data["event"][0]->eventID,
                null,
                null,
                "l3"
            );

            foreach ($chapters as $chapter) {
                if($chapter["l3memberID"] == 0) continue;

                $tmp["l3chID"] = $chapter["l3chID"];
                $tmp["l3memberID"] = $chapter["l3memberID"];
                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["l3checked"] = $chapter["l3checked"];
                $tmp["peerCheck"] = (array)json_decode($chapter["peerCheck"], true);

                $data["chapters"][$chapter["chapter"]] = $tmp;
            }

            $data["members"] = $this->_model->getMembersForL3Event($data["event"][0]->eventID);
            $data["out_members"] = [];

            // Include l3 checkers that are not in the list of participants (usually superadmins)
            $tmpmems = [];
            foreach ($data["members"] as $key => $member) {
                $peer = (array)json_decode($member["peerCheck"], true);
                foreach ($peer as $chap) {
                    $tmpmems[] = $chap["memberID"];
                }
            }

            $data["out_members"] = $this->_membersModel->getMembers($tmpmems);
            $data["out_members"] = array_map(function ($item){ return (array)$item;}, $data["out_members"]);

            if (isset($_POST) && !empty($_POST)) {
                if(!empty(array_filter($data["chapters"])))
                {
                    $updated = $this->_model->updateEvent(
                        array("state" => EventStates::L3_CHECK),
                        array("eventID" => $eventID));
                    if($updated)
                        Url::redirect("events/manage-l3/".$eventID);
                }
                else
                {
                    $error[] = __("event_chapters_error");
                }
            }
        }
        else
        {
            $error[] = __("empty_or_not_permitted_event_error");
        }

        return View::make('Events/ManageL3')
            ->shares("title", __("manage_event"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }


    public function moveStepBack()
    {
        $response = array("success" => false);

        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $memberID = isset($_POST["memberID"]) && $_POST["memberID"] != "" ? (integer)$_POST["memberID"] : null;
        $to_step = isset($_POST["to_step"]) && $_POST["to_step"] != "" ? $_POST["to_step"] : null;
        $prev_chunk = isset($_POST["prev_chunk"]) && $_POST["prev_chunk"] != "" ? filter_var($_POST["prev_chunk"], FILTER_VALIDATE_BOOLEAN) : false;
        $confirm = isset($_POST["confirm"]) && $_POST["confirm"] != "" ? filter_var($_POST["confirm"], FILTER_VALIDATE_BOOLEAN) : false;
        $manageMode = isset($_POST["manageMode"]) && $_POST["manageMode"] != "" ? $_POST["manageMode"] : "l1";

        if($eventID !== null && $memberID !== null &&
            $to_step !== null)
        {
            $userType = EventMembers::TRANSLATOR;
            $finishedState = EventStates::TRANSLATED;
            if($manageMode == "l2") {
                $userType = EventMembers::L2_CHECKER;
                $finishedState = EventStates::L2_CHECKED;
            }
            elseif($manageMode == "l3") {
                $userType = EventMembers::L3_CHECKER;
                $finishedState = EventStates::COMPLETE;
            }

            $member = $this->_model->getMemberEvents($memberID, $userType, $eventID, true, false);

            if(!empty($member))
            {
                if(EventStates::enum($member[0]->state) < EventStates::enum($finishedState))
                {
                    $mode = $manageMode == "l1" && $member[0]->langInput
                        ? "li"
                        : ($member[0]->sourceBible == "odb" ? "odb" : "").$member[0]->bookProject;

                    if(array_key_exists($to_step, EventSteps::enumArray($mode))
                        || array_key_exists($to_step, EventCheckSteps::enumArray("l2"))
                        || array_key_exists($to_step, EventCheckSteps::enumArray("l3")))
                    {
                        $postData = $this->moveMemberStepBack(
                            $member[0],
                            $to_step,
                            $confirm,
                            $prev_chunk
                        );

                        if(!empty($postData))
                        {
                            if(in_array("hasTranslation", $postData, true))
                            {
                                $response["confirm"] = true;
                                $response["message"] = __("chapter_has_translation");
                                echo json_encode($response);
                                return;
                            }

                            if(array_key_exists("translations", $postData))
                            {
                                unset($postData["translations"]);
                                $this->_translationModel->deleteTranslation(["trID" => $member[0]->trID, "chapter" => $member[0]->currentChapter]);
                                $this->_translationModel->deleteCommentsByEvent($eventID, $member[0]->currentChapter);
                            }

                            if($manageMode == "l1")
                                $this->_model->updateTranslator($postData,
                                    ["trID" => $member[0]->trID]);
                            elseif($manageMode == "l2")
                                $this->_model->updateL2Checker($postData,
                                    ["l2chID" => $member[0]->l2chID]);
                            elseif($manageMode == "l3")
                                $this->_model->updateL3Checker($postData,
                                    ["l3chID" => $member[0]->l3chID]);

                            $response["success"] = true;
                            $response["message"] = $member[0]->step != $to_step
                                ? __("moved_back_success") : __("checker_removed_success");
                        }
                        else
                        {
                            $response["error"] = __("not_allowed_action");
                        }
                    }
                    else
                    {
                        $response["error"] = __("wrong_parameters");
                    }
                }
                else
                {
                    $response["error"] = __("event_is_finished");
                }
            }
            else
            {
                $response["error"] = __("wrong_parameters");
            }
        }

        echo json_encode($response);
    }

    public function moveStepBackAlt()
    {
        $response = array("success" => false);

        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $memberID = isset($_POST["memberID"]) && $_POST["memberID"] != "" ? (integer)$_POST["memberID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $mode = isset($_POST["mode"]) && $_POST["mode"] != "" ? $_POST["mode"] : "snd_checker";
        $otherChk = isset($_POST["otherChk"]) && $_POST["otherChk"] != "" ? $_POST["otherChk"] : "";

        if($eventID !== null && $memberID !== null &&
            $chapter !== null)
        {
            $event = $this->_model->getEvent($eventID);

            if(!empty($event))
            {
                if($event[0]->bookProject == "sun")
                {
                    if(EventStates::enum($event[0]->state) < EventStates::enum(EventStates::TRANSLATED))
                    {
                        $chapters = $this->_model->getChapters($eventID, $memberID, $chapter, "sun");
                        $chap = [];

                        if(!empty($chapters))
                        {
                            $chap["trID"] = $chapters[0]["trID"];
                            $chap["memberID"] = $chapters[0]["memberID"];
                            $chap["chunks"] = json_decode($chapters[0]["chunks"], true);
                            $chap["checked"] = $chapters[0]["checked"];
                            $chap["kwCheck"] = (array)json_decode($chapters[0]["kwCheck"], true);
                            $chap["crCheck"] = (array)json_decode($chapters[0]["crCheck"], true);

                            $cr = !empty($chap["crCheck"])
                                && array_key_exists($chapter, $chap["crCheck"])
                                && $chap["crCheck"][$chapter]["memberID"] > 0;

                            switch ($mode)
                            {
                                case "kw_checker":
                                    if(!$cr)
                                    {
                                        $chap["kwCheck"][$chapter]["memberID"] = 0;
                                        $chap["kwCheck"][$chapter]["done"] = 0;
                                        unset($chap["crCheck"][$chapter]);
                                    }
                                    else
                                    {
                                        $response["error"] = __("wrong_parameters");
                                    }
                                    break;

                                case "cr_checker":
                                    if($cr)
                                    {
                                        $chap["crCheck"][$chapter]["memberID"] = 0;
                                        $chap["crCheck"][$chapter]["done"] = 0;
                                    }
                                    else
                                    {
                                        $response["error"] = __("wrong_parameters");
                                    }
                                    break;

                                default:
                                    $response["error"] = __("wrong_parameters");
                                    break;
                            }

                            if(!isset($response["error"]))
                            {
                                $postData = [
                                    "kwCheck" => json_encode($chap["kwCheck"]),
                                    "crCheck" => json_encode($chap["crCheck"]),
                                ];

                                $this->_model->updateTranslator($postData, [
                                    "eventID" => $eventID,
                                    "memberID" => $memberID
                                ]);

                                $response["message"] = __("checker_removed_success");
                                $response["success"] = true;
                            }
                        }
                        else
                        {
                            $response["error"] = __("wrong_parameters");
                        }
                    }
                    else
                    {
                        $response["error"] = __("event_is_finished");
                    }
                }
                else
                {
                    if(EventStates::enum($event[0]->state) < EventStates::enum(EventStates::L2_CHECKED))
                    {
                        $chapters = $this->_model->getChapters($eventID, $memberID, $chapter, "l2");
                        $chap = [];

                        if(!empty($chapters))
                        {
                            $chap["l2chID"] = $chapters[0]["l2chID"];
                            $chap["l2memberID"] = $chapters[0]["l2memberID"];
                            $chap["chunks"] = json_decode($chapters[0]["chunks"], true);
                            $chap["l2checked"] = $chapters[0]["l2checked"];
                            $chap["sndCheck"] = (array)json_decode($chapters[0]["sndCheck"], true);
                            $chap["peer1Check"] = (array)json_decode($chapters[0]["peer1Check"], true);
                            $chap["peer2Check"] = (array)json_decode($chapters[0]["peer2Check"], true);

                            $p1 = !empty($chap["peer1Check"])
                                && array_key_exists($chapter, $chap["peer1Check"])
                                && $chap["peer1Check"][$chapter]["memberID"] > 0;
                            $p2 = !empty($chap["peer2Check"])
                                && array_key_exists($chapter, $chap["peer2Check"])
                                && $chap["peer2Check"][$chapter]["memberID"] > 0;

                            switch ($mode)
                            {
                                case "snd_checker":
                                    if(!$p1)
                                    {
                                        $chap["sndCheck"][$chapter]["memberID"] = 0;
                                        $chap["sndCheck"][$chapter]["done"] = 0;
                                        unset($chap["peer1Check"][$chapter]);
                                        unset($chap["peer2Check"][$chapter]);
                                    }
                                    else
                                    {
                                        $response["error"] = __("wrong_parameters");
                                    }
                                    break;

                                case "p1_checker":
                                    if(!$p2)
                                    {
                                        $chap["peer1Check"][$chapter]["memberID"] = 0;
                                        $chap["peer1Check"][$chapter]["done"] = 0;
                                        $chap["peer2Check"][$chapter]["memberID"] = 0;
                                        $chap["peer2Check"][$chapter]["done"] = 0;
                                    }
                                    else
                                    {
                                        $response["error"] = __("wrong_parameters");
                                    }
                                    break;

                                case "p2_checker":
                                    if($p2)
                                    {
                                        $chap["peer1Check"][$chapter]["done"] = 0;
                                        $chap["peer2Check"][$chapter]["memberID"] = 0;
                                        $chap["peer2Check"][$chapter]["done"] = 0;
                                    }
                                    else
                                    {
                                        $response["error"] = __("wrong_parameters");
                                    }
                                    break;

                                default:
                                    $response["error"] = __("wrong_parameters");
                                    break;
                            }

                            if(!isset($response["error"]))
                            {
                                $postData = [
                                    "sndCheck" => json_encode($chap["sndCheck"]),
                                    "peer1Check" => json_encode($chap["peer1Check"]),
                                    "peer2Check" => json_encode($chap["peer2Check"]),
                                ];

                                $this->_model->updateL2Checker($postData, [
                                    "eventID" => $eventID,
                                    "memberID" => $memberID
                                ]);

                                $response["message"] = __("checker_removed_success");
                                $response["success"] = true;
                            }
                        }
                        else
                        {
                            $response["error"] = __("wrong_parameters");
                        }
                    }
                    else
                    {
                        $response["error"] = __("event_is_finished");
                    }
                }
            }
            else
            {
                $response["error"] = __("wrong_parameters");
            }
        }

        echo json_encode($response);
    }

    public function demo($page = null)
    {
        if(!isset($page))
            Url::redirect("events/demo/pray");

        $notifications = [];

        for($i=0; $i<3; $i++)
        {
            $notifObj = new stdClass();

            if($i==0)
                $notifObj->step = EventSteps::PEER_REVIEW;
            elseif($i==1)
                $notifObj->step = EventSteps::KEYWORD_CHECK;
            else
                $notifObj->step = EventSteps::CONTENT_REVIEW;

            $notifObj->currentChapter = 2;
            $notifObj->firstName = "Mark";
            $notifObj->lastName = "Patton";
            $notifObj->bookCode = "2ti";
            $notifObj->bookProject = "ulb";
            $notifObj->tLang = "English";
            $notifObj->bookName = "2 Timothy";
            $notifObj->manageMode = "l1";
            $notifObj->sourceBible = "ulb";

            $notifications[] = $notifObj;
        }

        $data["notifications"] = $notifications;
        $data["isDemo"] = true;
        $data["isLangInput"] = false;
        $data["menu"] = 5;

        $view = View::make("Events/L1/Demo/DemoHeader");
        $data["step"] = "";

        switch ($page)
        {
            case "pray":
                $view->nest("page", "Events/L1/Demo/Pray");
                $data["step"] = EventSteps::PRAY;
                break;

            case "consume":
                $view->nest("page", "Events/L1/Demo/Consume");
                $data["step"] = EventSteps::CONSUME;
                break;

            case "verbalize":
                $view->nest("page", "Events/L1/Demo/Verbalize");
                $data["step"] = EventSteps::VERBALIZE;
                break;

            case "verbalize_checker":
                $view->nest("page", "Events/L1/Demo/VerbalizeChecker");
                $data["step"] = EventSteps::VERBALIZE;
                $data["isCheckerPage"] = true;
                break;

            case "chunking":
                $view->nest("page", "Events/L1/Demo/Chunking");
                $data["step"] = EventSteps::CHUNKING;
                break;

            case "read_chunk":
                $view->nest("page", "Events/L1/Demo/ReadChunk");
                $data["step"] = EventSteps::READ_CHUNK;
                break;

            case "blind_draft":
                $view->nest("page", "Events/L1/Demo/BlindDraft");
                $data["step"] = EventSteps::BLIND_DRAFT;
                break;

            case "self_check":
                $view->nest("page", "Events/L1/Demo/SelfCheck");
                $data["step"] = EventSteps::SELF_CHECK;
                break;

            case "peer_review":
                $view->nest("page", "Events/L1/Demo/PeerReview");
                $data["step"] = EventSteps::PEER_REVIEW;
                break;

            case "peer_review_checker":
                $view->nest("page", "Events/L1/Demo/PeerReviewChecker");
                $data["step"] = EventSteps::PEER_REVIEW;
                $data["isCheckerPage"] = true;
                break;

            case "keyword_check":
                $view->nest("page", "Events/L1/Demo/KeywordCheck");
                $data["step"] = EventSteps::KEYWORD_CHECK;
                break;

            case "keyword_check_checker":
                $view->nest("page", "Events/L1/Demo/KeywordCheckChecker");
                $data["step"] = EventSteps::KEYWORD_CHECK;
                $data["isCheckerPage"] = true;
                break;

            case "content_review":
                $view->nest("page", "Events/L1/Demo/ContentReview");
                $data["step"] = EventSteps::CONTENT_REVIEW;
                break;

            case "content_review_checker":
                $view->nest("page", "Events/L1/Demo/ContentReviewChecker");
                $data["step"] = EventSteps::CONTENT_REVIEW;
                $data["isCheckerPage"] = true;
                break;

            case "final_review":
                $view->nest("page", "Events/L1/Demo/FinalReview");
                $data["step"] = EventSteps::FINAL_REVIEW;
                break;

            case "information":
                return View::make("Events/L1/Demo/Information")
                    ->shares("title", __("event_info"))
                    ->shares("data", $data);
                break;
        }

        return $view
            ->shares("title", __("demo"))
            ->shares("data", $data);
    }


    public function demoLangInput($page = null)
    {
        if(!isset($page))
            Url::redirect("events/demo-scripture-input/pray");

        $data["notifications"] = [];
        $data["isDemo"] = true;
        $data["isLangInput"] = true;
        $data["menu"] = 5;

        $view = View::make("Events/L1/Demo/DemoHeader");
        $data["step"] = "";

        switch ($page)
        {
            case "pray":
                $view->nest("page", "Events/L1/Demo/Pray");
                $data["step"] = EventSteps::PRAY;
                break;

            case "input":
                $view->nest("page", "Events/L1/Demo/LangInput");
                $data["step"] = EventSteps::MULTI_DRAFT;
                break;

            case "self_check":
                $view->nest("page", "Events/L1/Demo/SelfCheckLangInput");
                $data["step"] = EventSteps::SELF_CHECK;
                break;

            case "information":
                return View::make("Events/L1/Demo/Information")
                    ->shares("title", __("event_info"))
                    ->shares("data", $data);
                break;
        }

        return $view
            ->shares("title", __("demo"))
            ->shares("data", $data);
    }

    public function demoL2($page = null)
    {
        if(!isset($page))
            Url::redirect("events/demo-l2/pray");

        for($i=0; $i<2; $i++)
        {
            $notifObj = new stdClass();

            if($i==0)
                $notifObj->step = EventCheckSteps::SND_CHECK;
            else
                $notifObj->step = EventCheckSteps::PEER_REVIEW_L2;

            $notifObj->currentChapter = 2;
            $notifObj->firstName = "Mark";
            $notifObj->lastName = "Patton";
            $notifObj->bookCode = "2ti";
            $notifObj->bookProject = "ulb";
            $notifObj->tLang = "English";
            $notifObj->bookName = "2 Timothy";
            $notifObj->manageMode = "l2";
            $notifObj->sourceBible = "ulb";

            $notifications[] = $notifObj;
        }

        $data["notifications"] = $notifications;
        $data["isDemo"] = true;
        $data["menu"] = 5;
        $data["isCheckerPage"] = true;

        $view = View::make("Events/L2/Demo/DemoHeader");
        $data["step"] = "";

        switch ($page)
        {
            case "pray":
                $view->nest("page", "Events/L2/Demo/Pray");
                $data["step"] = EventCheckSteps::PRAY;
                break;

            case "consume":
                $view->nest("page", "Events/L2/Demo/Consume");
                $data["step"] = EventCheckSteps::CONSUME;
                break;

            case "fst_check":
                $view->nest("page", "Events/L2/Demo/FstCheck");
                $data["step"] = EventCheckSteps::FST_CHECK;
                break;

            case "snd_check":
                $view->nest("page", "Events/L2/Demo/SndCheck");
                $data["step"] = EventCheckSteps::SND_CHECK;
                break;

            case "keyword_check_l2":
                $view->nest("page", "Events/L2/Demo/KeywordCheck");
                $data["step"] = EventCheckSteps::KEYWORD_CHECK_L2;
                break;

            case "peer_review_l2":
                $view->nest("page", "Events/L2/Demo/PeerReview");
                $data["step"] = EventCheckSteps::PEER_REVIEW_L2;
                break;

            case "peer_review_l2_checker":
                $view->nest("page", "Events/L2/Demo/PeerReviewChecker");
                $data["step"] = EventCheckSteps::PEER_REVIEW_L2;
                unset($data["isCheckerPage"]);
                break;

            case "information":
                return View::make("Events/L2/Demo/Information")
                    ->shares("title", __("event_info"));
                break;
        }

        return $view
            ->shares("title", __("demo"))
            ->shares("data", $data);
    }

    public function demoL3($page = null)
    {
        if(!isset($page))
            Url::redirect("events/demo-l3/pray");

        $notifObj = new stdClass();
        $notifObj->step = EventCheckSteps::PEER_REVIEW_L3;
        $notifObj->currentChapter = 2;
        $notifObj->firstName = "Mark";
        $notifObj->lastName = "Patton";
        $notifObj->bookCode = "2ti";
        $notifObj->bookProject = "ulb";
        $notifObj->tLang = "Papuan Malay";
        $notifObj->bookName = "2 Timothy";
        $notifObj->manageMode = "l3";
        $notifObj->sourceBible = "ulb";

        $notifications[] = $notifObj;

        $data["notifications"] = $notifications;
        $data["isDemo"] = true;
        $data["menu"] = 5;
        $data["isCheckerPage"] = true;
        $data["isPeer"] = false;

        $view = View::make("Events/L3/Demo/DemoHeader");
        $data["step"] = "";

        switch ($page)
        {
            case "pray":
                $view->nest("page", "Events/L3/Demo/Pray");
                $data["step"] = EventCheckSteps::PRAY;
                break;

            case "peer_review_l3":
                $view->nest("page", "Events/L3/Demo/PeerReview");
                $data["step"] = EventCheckSteps::PEER_REVIEW_L3;
                break;

            case "peer_edit_l3":
                $view->nest("page", "Events/L3/Demo/PeerEdit");
                $data["step"] = EventCheckSteps::PEER_EDIT_L3;
                break;

            case "peer_review_l3_checker":
                $view->nest("page", "Events/L3/Demo/PeerReviewChecker");
                $data["step"] = EventCheckSteps::PEER_REVIEW_L3;
                $data["isPeer"] = true;
                break;

            case "peer_edit_l3_checker":
                $view->nest("page", "Events/L3/Demo/PeerEditChecker");
                $data["step"] = EventCheckSteps::PEER_EDIT_L3;
                $data["isPeer"] = true;
                break;

            case "information":
                return View::make("Events/L3/Demo/Information")
                    ->shares("title", __("event_info"));
                break;
        }

        return $view
            ->shares("title", __("demo"))
            ->shares("data", $data);
    }

    public function demoSun($page = null)
    {
        if(!isset($page))
            Url::redirect("events/demo-sun/pray");

        for($i=0; $i<2; $i++)
        {
            $notifObj = new stdClass();

            if($i==0)
                $notifObj->step = EventSteps::THEO_CHECK;
            else
                $notifObj->step = EventSteps::CONTENT_REVIEW;

            $notifObj->currentChapter = 2;
            $notifObj->firstName = "Mark";
            $notifObj->lastName = "Patton";
            $notifObj->bookCode = "mat";
            $notifObj->bookProject = "sun";
            $notifObj->tLang = "English";
            $notifObj->bookName = "Matthew";
            $notifObj->manageMode = "sun";
            $notifObj->sourceBible = "ulb";

            $notifications[] = $notifObj;
        }

        $data["notifications"] = $notifications;
        $data["isDemo"] = true;
        $data["isCheckerPage"] = false;
        $data["menu"] = 5;

        $this->_saildictModel = new SailDictionaryModel();

        $view = View::make("Events/SUN/Demo/DemoHeader");
        $data["step"] = "";

        switch ($page)
        {
            case "pray":
                $view->nest("page", "Events/SUN/Demo/Pray");
                $data["step"] = EventSteps::PRAY;
                break;

            case "consume":
                $view->nest("page", "Events/SUN/Demo/Consume");
                $data["step"] = EventSteps::CONSUME;
                break;

            case "chunking":
                $view->nest("page", "Events/SUN/Demo/Chunking");
                $data["step"] = EventSteps::CHUNKING;
                break;

            case "rearrange":
                $view->nest("page", "Events/SUN/Demo/WordsDraft");
                $data["step"] = EventSteps::REARRANGE;
                break;

            case "symbol-draft":
                $view->nest("page", "Events/SUN/Demo/SymbolsDraft");
                $data["step"] = EventSteps::SYMBOL_DRAFT;
                break;

            case "self-check":
                $view->nest("page", "Events/SUN/Demo/SelfCheck");
                $data["step"] = EventSteps::SELF_CHECK;
                break;

            case "theo_check_checker":
                $view->nest("page", "Events/SUN/Demo/TheoCheck");
                $data["step"] = EventSteps::THEO_CHECK;
                $data["isCheckerPage"] = true;
                break;

            case "content_review_checker":
                $view->nest("page", "Events/SUN/Demo/ContentReview");
                $data["step"] = EventSteps::CONTENT_REVIEW;
                $data["isCheckerPage"] = true;
                break;

            case "verse-markers":
                $view->nest("page", "Events/SUN/Demo/FinalReview");
                $data["step"] = EventSteps::FINAL_REVIEW;
                $data["isCheckerPage"] = true;
                break;

            case "information":
                return View::make("Events/SUN/Demo/Information")
                    ->shares("title", __("event_info"));
                break;
        }

        return $view
            ->shares("title", __("demo"))
            ->shares("data", $data);
    }

    public function demoSunOdb($page = null)
    {
        if(!isset($page))
            Url::redirect("events/demo-sun-odb/pray");

        for($i=0; $i<2; $i++)
        {
            $notifObj = new stdClass();

            if($i==0)
                $notifObj->step = EventSteps::THEO_CHECK;
            else
                $notifObj->step = EventSteps::CONTENT_REVIEW;

            $notifObj->currentChapter = 2;
            $notifObj->firstName = "Mark";
            $notifObj->lastName = "Patton";
            $notifObj->bookCode = "a01";
            $notifObj->bookProject = "sun";
            $notifObj->tLang = "English";
            $notifObj->bookName = "A01";
            $notifObj->manageMode = "sun-odb";
            $notifObj->sourceBible = "odb";

            $notifications[] = $notifObj;
        }

        $data["notifications"] = $notifications;
        $data["isDemo"] = true;
        $data["isCheckerPage"] = false;
        $data["menu"] = 5;

        $this->_saildictModel = new SailDictionaryModel();

        $view = View::make("Events/ODBSUN/Demo/DemoHeader");
        $data["step"] = "";

        switch ($page)
        {
            case "pray":
                $view->nest("page", "Events/ODBSUN/Demo/Pray");
                $data["step"] = EventSteps::PRAY;
                break;

            case "consume":
                $view->nest("page", "Events/ODBSUN/Demo/Consume");
                $data["step"] = EventSteps::CONSUME;
                break;

            case "rearrange":
                $view->nest("page", "Events/ODBSUN/Demo/WordsDraft");
                $data["step"] = EventSteps::REARRANGE;
                break;

            case "symbol-draft":
                $view->nest("page", "Events/ODBSUN/Demo/SymbolsDraft");
                $data["step"] = EventSteps::SYMBOL_DRAFT;
                break;

            case "self-check":
                $view->nest("page", "Events/ODBSUN/Demo/SelfCheck");
                $data["step"] = EventSteps::SELF_CHECK;
                break;

            case "theo_check_checker":
                $view->nest("page", "Events/ODBSUN/Demo/TheoCheck");
                $data["step"] = EventSteps::THEO_CHECK;
                $data["isCheckerPage"] = true;
                break;

            case "content_review_checker":
                $view->nest("page", "Events/ODBSUN/Demo/ContentReview");
                $data["step"] = EventSteps::CONTENT_REVIEW;
                $data["isCheckerPage"] = true;
                break;

            case "information":
                return View::make("Events/ODBSUN/Demo/Information")
                    ->shares("title", __("event_info"));
                break;
        }

        return $view
            ->shares("title", __("demo"))
            ->shares("data", $data);
    }

    public function news()
    {
        if (Session::get('loggedin') !== true)
        {
            Url::redirect("members/login");
        }

        if(Session::get("isDemo"))
        {
            Url::redirect('events/demo');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        $data["menu"] = 6;
        $data["notifications"] = $this->_notifications;
        $data["news"] = $this->_news;
        $data["newNewsCount"] = 0;

        return View::make('Events/News')
            ->shares("title", __("news_title"))
            ->shares("data", $data);
    }


    public function faqs()
    {
        $this->_newsModel = new NewsModel();
        $data["menu"] = 0;
        $data["faqs"] = $this->_newsModel->getFaqs();

        return View::make('Events/Faq')
            ->shares("title", __("faq_title"))
            ->shares("data", $data);
    }

    public function applyEvent()
    {
        $data["errors"] = array();
        $profile = Session::get("profile");

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST['eventID']) && $_POST['eventID'] != "" ? $_POST['eventID'] : null;
        $userType = isset($_POST['userType']) && $_POST['userType'] != "" ? $_POST['userType'] : null;
        $memberID = Session::get("memberID");

        if(Session::get("isAdmin"))
        {
            $memberID = isset($_POST['memberID']) && $_POST['memberID'] != "" ? $_POST['memberID'] : $memberID;
            if(isset($_POST['memberID']) && $_POST['memberID'] != "")
            {
                $pr = $this->_membersModel->getMemberWithProfile($_POST['memberID']);
                if(!empty($pr))
                    $profile = (array)$pr[0];
            }
        }
        
        $education = isset($_POST["education"]) && !empty($_POST["education"]) ? (array)$_POST["education"] : null;
        $ed_area = isset($_POST["ed_area"]) && !empty($_POST["ed_area"]) ? (array)$_POST["ed_area"] : array();
        $ed_place = isset($_POST["ed_place"]) && trim($_POST["ed_place"]) != "" ? trim($_POST["ed_place"]) : "";
        $hebrew_knwlg = isset($_POST["hebrew_knwlg"]) && preg_match("/^[0-4]{1}$/", $_POST["hebrew_knwlg"]) ? $_POST["hebrew_knwlg"] : 0;
        $greek_knwlg = isset($_POST["greek_knwlg"]) && preg_match("/^[0-4]{1}$/", $_POST["greek_knwlg"]) ? $_POST["greek_knwlg"] : 0;
        $church_role = isset($_POST["church_role"]) && !empty($_POST["church_role"]) ? (array)$_POST["church_role"] : array();

        if($eventID == null)
        {
            $error[] = __('wrong_event_parameters');
            echo json_encode(array("error" => $error));
            return;
        }

        if($userType == null || !preg_match("/^(".EventMembers::TRANSLATOR."|".EventMembers::L2_CHECKER."|".EventMembers::L3_CHECKER.")$/", $userType))
        {
            $error[] = __("wrong_event_parameters");
            echo json_encode(array("error" => $error));
            return;
        }

        if($userType == EventMembers::L2_CHECKER || $userType == EventMembers::L3_CHECKER)
        {
            if(!is_array($profile["education"]))
                $profile["education"] = (array)json_decode($profile["education"]);
            if($education === null && empty($profile["education"])) {
                $data["errors"]["education"] = true;
            }
            else
            {
                $education = $education == null ? $profile["education"] : $education;
                foreach ($education as $item) {
                    if(!preg_match("/^(BA|MA|PHD)$/", $item))
                    {
                        $data["errors"]["education"] = true;
                        break;
                    }
                }
            }
            
            if(!is_array($profile["ed_area"]))
                $profile["ed_area"] = (array)json_decode($profile["ed_area"]);
            if($ed_area === null && empty($profile["ed_area"]))
                $data["errors"]["ed_area"] = true;
            else
            {
                $ed_area = $ed_area == null ? $profile["ed_area"] : $ed_area;
                foreach ($ed_area as $item) {
                    if(!preg_match("/^(Theology|Pastoral Ministry|Bible Translation|Exegetics)$/", $item))
                    {
                        $data["errors"]["ed_area"] = true;
                        break;
                    }
                }
            }

            if($ed_place === null && empty($profile["ed_place"]))
                $data["errors"]["ed_place"] = true;

            if($hebrew_knwlg === null && empty($profile["hebrew_knwlg"]))
                $data["errors"]["hebrew_knwlg"] = true;

            if($greek_knwlg === null && empty($profile["greek_knwlg"]))
                $data["errors"]["greek_knwlg"] = true;

            if(!is_array($profile["church_role"]))
                $profile["church_role"] = (array)json_decode($profile["church_role"]);
            if($church_role === null && empty($profile["church_role"]))
                $data["errors"]["church_role"] = true;
            else
            {
                $church_role = $church_role == null ? $profile["church_role"] : $church_role;
                foreach ($church_role as $item) {
                    if(!preg_match("/^(Elder|Bishop|Pastor|Teacher|Denominational Leader|Seminary Professor)$/", $item))
                    {
                        $data["errors"]["church_role"] = true;
                        break;
                    }
                }
            }
        }

        if(empty($data["errors"]))
        {
            $event = $this->_model->getEvent($eventID, null, null, true);

            if(empty($event))
            {
                $error[] = __("event_notexist_error");
                echo json_encode(array("error" => $error));
                return;
            }

            $mode = $event[0]->bookProject;
            $exists = $this->_model->getEventMember($event[0]->eventID, $memberID);
            
            switch($userType)
            {
                case EventMembers::TRANSLATOR:

                    if($exists[0]->translator == null &&
                        $exists[0]->checker_l2 == null && $exists[0]->checker_l3 == null)   // can apply as translator only if not checker l2, l3
                    {
                        $chapter = in_array($mode, ["tn"]) ? -1 : 0;
                        $trData = array(
                            "memberID" => $memberID,
                            "eventID" => $event[0]->eventID,
                            "step" => EventSteps::NONE,
                            "currentChapter" => $chapter
                        );
                        $trID = $this->_model->addTranslator($trData);

                        if(is_numeric($trID))
                        {
                            echo json_encode(array("success" => __("successfully_applied")));
                        }
                        else
                        {
                            $error[] = __("error_ocured", array($trID));
                        }
                    }
                    else
                    {
                        $error[] = __("error_member_in_event");
                    }
                    break;

                case EventMembers::L2_CHECKER:
                    if($exists[0]->translator == null && $exists[0]->checker == null &&
                        $exists[0]->checker_l2 == null && $exists[0]->checker_l3 == null)   // can apply as checker L2 only if not translator or checker 7/8
                    {
                        $l2Data = array(
                            "memberID" => $memberID,
                            "eventID" => $event[0]->eventID,
                            "step" => EventSteps::NONE
                        );
                        $l2ID = $this->_model->addL2Checker($l2Data);

                        if(is_numeric($l2ID))
                        {
                            echo json_encode(array("success" => __("successfully_applied")));
                        }
                        else
                        {
                            $error[] = __("error_ocured", array($l2ID));
                        }
                    }
                    else
                    {
                        $error[] = __("error_member_in_event");
                    }
                    break;

                case EventMembers::L3_CHECKER:
                    if($exists[0]->translator == null && $exists[0]->checker == null &&
                        $exists[0]->checker_l2 == null && $exists[0]->checker_l3 == null)   // can apply as checker L3 only if not translator or checker 7/8
                    {
                        $chapter = in_array($mode, ["tn"]) ? -1 : 0;
                        $l3Data = array(
                            "memberID" => $memberID,
                            "eventID" => $event[0]->eventID,
                            "step" => EventSteps::NONE,
                            "currentChapter" => $chapter
                        );
                        $l3ID = $this->_model->addL3Checker($l3Data);

                        if(is_numeric($l3ID))
                        {
                            echo json_encode(array("success" => __("successfully_applied")));
                        }
                        else
                        {
                            $error[] = __("error_ocured", array($l3ID));
                        }
                    }
                    else
                    {
                        $error[] = __("error_member_in_event");
                    }
                    break;
            }

            if(isset($error))
            {
                echo json_encode(array("error" => $error));
            }
        }
        else
        {
            $error[] = __('empty_profile_error');
            echo json_encode(array("error" => $error, "errors" => $data["errors"]));
        }
    }


    public function autosaveChunk()
    {
        $response = array("success" => false);
        $post = $_REQUEST;
        $eventID = isset($post["eventID"]) && is_numeric($post["eventID"]) ? $post["eventID"] : null;

        if($eventID !== null)
        {
            $level = isset($post["level"]) && $post["level"] != "" ? $post["level"] : "l1";

            $memberType = EventMembers::TRANSLATOR;
            if($level == "l2" || $level == "l2continue")
                $memberType = EventMembers::L2_CHECKER;
            elseif($level == "l3")
                $memberType = EventMembers::L3_CHECKER;

            if(in_array($level, ["l1","l2","l3"]))
            {
                $event = $this->_model->getMemberEvents(Session::get("memberID"), $memberType, $eventID, false, false);
            }
            elseif($level == "l2continue")
            {
                if(isset($post["memberID"]) && isset($post["chapter"]))
                {
                    $event = $this->_model->getMemberEventsForCheckerL2(
                        Session::get("memberID"),
                        $eventID,
                        $post["memberID"],
                        $post["chapter"]
                    );
                }
                else
                {
                    $response["errorType"] = "error";
                    $response["error"] = "POST data incorrect: memberID, chapter";
                    echo json_encode($response);
                    exit;
                }
            }
            elseif ($level == "sunContinue")
            {
                if(isset($post["memberID"]) && isset($post["chapter"]))
                {
                    $event = $this->_model->getMemberEventsForSun(
                        Session::get("memberID"),
                        $eventID,
                        $post["memberID"],
                        $post["chapter"]
                    );
                }
                else
                {
                    $response["errorType"] = "error";
                    $response["error"] = "POST data incorrect: memberID, chapter";
                    echo json_encode($response);
                    exit;
                }
            }

            if(!empty($event))
            {
                $mode = $event[0]->bookProject;

                switch($event[0]->step)
                {
                    case EventSteps::BLIND_DRAFT:
                    case EventSteps::REARRANGE:
                    case EventSteps::SYMBOL_DRAFT:
                        if($event[0]->step == EventSteps::SYMBOL_DRAFT)
                            $post["draft"] = $post["symbols"];

                        if(isset($post["draft"]) && Tools::trim(Tools::strip_tags($post["draft"])) != "") {
                            $chunks = json_decode($event[0]->chunks, true);
                            $chunk = $chunks[$event[0]->currentChunk];

                            $post["draft"] = preg_replace("/[\\r\\n]/", " ", $post["draft"]);
                            $post["draft"] = Tools::html_entity_decode($post["draft"]);

                            $post["draft"] = Tools::htmlentities($post["draft"]);

                            $role = EventMembers::TRANSLATOR;

                            $translationData = $this->_translationModel->getEventTranslationByEventID(
                                $event[0]->eventID,
                                $event[0]->currentChapter,
                                $event[0]->currentChunk
                            );

                            $shoudUpdate = false;

                            if(!empty($translationData))
                            {
                                if($translationData[0]->chapter == $event[0]->currentChapter &&
                                    $translationData[0]->chunk == $event[0]->currentChunk)
                                {
                                    $translationVerses = json_decode($translationData[0]->translatedVerses, true);
                                    $shoudUpdate = true;
                                }
                            }

                            if(!$shoudUpdate)
                            {
                                $trArr = [];
                                if($mode == "sun")
                                {
                                    $trArr["words"] = trim($post["draft"]);
                                    $trArr["symbols"] = "";
                                    $trArr["bt"] = "";
                                    $trArr["verses"] = [];
                                }
                                else
                                {
                                    $trArr["blind"] = trim($post["draft"]);
                                    $trArr["verses"] = [];
                                }

                                $translationVerses = array(
                                    EventMembers::TRANSLATOR => $trArr,
                                    EventMembers::L2_CHECKER => array(
                                        "verses" => array()
                                    ),
                                    EventMembers::L3_CHECKER => array(
                                        "verses" => array()
                                    ),
                                );

                                $encoded = json_encode($translationVerses);
                                $json_error = json_last_error();
                                if($json_error === JSON_ERROR_NONE)
                                {
                                    $trData = array(
                                        "projectID" => $event[0]->projectID,
                                        "eventID" => $event[0]->eventID,
                                        "trID" => $event[0]->trID,
                                        "targetLang" => $event[0]->targetLang,
                                        "bookProject" => $event[0]->bookProject,
                                        "abbrID" => $event[0]->abbrID,
                                        "bookCode" => $event[0]->bookCode,
                                        "chapter" => $event[0]->currentChapter,
                                        "chunk" => $event[0]->currentChunk,
                                        "firstvs" => $chunk[0],
                                        "translatedVerses" => $encoded,
                                        "dateCreate" => date('Y-m-d H:i:s')
                                    );
    
                                    $tID = $this->_translationModel->createTranslation($trData);
    
                                    if (is_numeric($tID)) {
                                        $response["chapter"] = $event[0]->currentChapter;
                                        $response["chunk"] = $event[0]->currentChunk;
                                        $response["success"] = true;
                                    }
                                }
                                else 
                                {
                                    $response["errorType"] = "json";
                                    $response["error"] = "Json error: " . $json_error;
                                }
                            }
                            else
                            {
                                if ($mode == "sun")
                                {
                                    if($event[0]->step == EventSteps::SYMBOL_DRAFT)
                                        $translationVerses[$role]["symbols"] = trim($post["draft"]);
                                    else
                                        $translationVerses[$role]["words"] = trim($post["draft"]);
                                }
                                else
                                {
                                    $translationVerses[$role]["blind"] = trim($post["draft"]);
                                }

                                $encoded = json_encode($translationVerses);
                                $json_error = json_last_error();
                                if($json_error === JSON_ERROR_NONE)
                                {
                                    $trData = array(
                                        "translatedVerses"  => $encoded,
                                    );
    
                                    $this->_translationModel->updateTranslation($trData, array("tID" => $translationData[0]->tID));
                                    $response["chapter"] = $event[0]->currentChapter;
                                    $response["chunk"] = $event[0]->currentChunk;
                                    $response["success"] = true;
                                }
                                else 
                                {
                                    $response["errorType"] = "json";
                                    $response["error"] = "Json error: " . $json_error;
                                }
                            }
                        }
                        break;

                    case EventSteps::MULTI_DRAFT:
                    case EventSteps::SELF_CHECK:
                    case EventSteps::PEER_REVIEW:
                    case EventSteps::KEYWORD_CHECK:
                    case EventSteps::CONTENT_REVIEW:
                    case EventSteps::THEO_CHECK:
                        if(isset($post["chunks"]) && is_array($post["chunks"]) && !empty($post["chunks"]))
                        {
                            if($event[0]->step == EventSteps::PEER_REVIEW
                                || $event[0]->step == EventSteps::KEYWORD_CHECK 
                                || $event[0]->step == EventSteps::CONTENT_REVIEW)
                            {
                                if($event[0]->checkDone)
                                {
                                    $response["errorType"] = "checkDone";
                                    $response["error"] = __("not_possible_to_save_error");
                                    echo json_encode($response);
                                    exit;
                                }
                            }
                            
                            $role = EventMembers::TRANSLATOR;
                            $trID = $event[0]->trID;

                            $translationData = $this->_translationModel->getEventTranslation(
                                $trID, 
                                $event[0]->currentChapter);

                            if($event[0]->step == EventSteps::MULTI_DRAFT && empty($translationData))
                            {
                                $translationVerses = array(
                                    EventMembers::TRANSLATOR => array(
                                        "verses" => ""
                                    ),
                                    EventMembers::CHECKER => array(
                                        "verses" => ""
                                    ),
                                    EventMembers::L2_CHECKER => array(
                                        "verses" => array()
                                    ),
                                    EventMembers::L3_CHECKER => array(
                                        "verses" => array()
                                    ),
                                );
                                $encoded = json_encode($translationVerses);
                                $chunks = json_decode($event[0]->chunks, true);

                                foreach ($post["chunks"] as $key => $chunk) {
                                    $chunk = $chunks[$key];
                                    $trData = array(
                                        "projectID" => $event[0]->projectID,
                                        "eventID" => $event[0]->eventID,
                                        "trID" => $event[0]->trID,
                                        "targetLang" => $event[0]->targetLang,
                                        "bookProject" => $event[0]->bookProject,
                                        "abbrID" => $event[0]->abbrID,
                                        "bookCode" => $event[0]->bookCode,
                                        "chapter" => $event[0]->currentChapter,
                                        "chunk" => $key,
                                        "firstvs" => $chunk[0],
                                        "translatedVerses" => $encoded,
                                        "dateCreate" => date('Y-m-d H:i:s')
                                    );

                                    $this->_translationModel->createTranslation($trData);
                                }

                                $translationData = $this->_translationModel->getEventTranslation(
                                    $trID,
                                    $event[0]->currentChapter);
                            }

                            $translation = array();

                            foreach ($translationData as $tv) {
                                $arr = json_decode($tv->translatedVerses, true);
                                $arr["tID"] = $tv->tID;
                                $translation[] = $arr;
                            }

                            if(!empty($translation))
                            {
                                // Clean empty spaces
                                $post["chunks"] = array_map(function($elm) {
                                    return Tools::trim($elm);
                                }, $post["chunks"]);

                                // filter out empty chunks
                                $post["chunks"] = array_filter($post["chunks"], function($v) {
                                    return !empty(Tools::trim(Tools::strip_tags($v)));
                                });

                                $section = "blind";
                                $symbols = [];
                                if($mode == "sun")
                                {
                                    if($event[0]->step == EventSteps::SELF_CHECK)
                                        $section = "bt";
                                    elseif($event[0]->step == EventSteps::CONTENT_REVIEW)
                                        $section = "symbols";
                                    elseif($event[0]->step == EventSteps::THEO_CHECK || $event[0]->sourceBible == "odb")
                                        $section = "symbols";

                                    if(isset($post["symbols"]) && is_array($post["symbols"]) && !empty($post["symbols"]))
                                    {
                                        $post["symbols"] = array_map(function($elm) {
                                            return Tools::trim($elm);
                                        }, $post["symbols"]);
                                        $post["symbols"] = array_filter($post["symbols"], function($v) {
                                            return !empty(Tools::trim(strip_tags($v)));
                                        });

                                        $symbols = $post["symbols"];
                                    }
                                }

                                $updated = 0;
                                foreach ($translation as $key => $chunk) {
                                    if(!isset($post["chunks"][$key])) continue;

                                    $post["chunks"][$key] = Tools::html_entity_decode($post["chunks"][$key]);
                                    $post["chunks"][$key] = Tools::htmlentities($post["chunks"][$key]);

                                    $shouldUpdate = false;
                                    if($chunk[$role][$section] != $post["chunks"][$key])
                                        $shouldUpdate = true;

                                    if($mode == "sun" && !empty($symbols))
                                    {
                                        if(!isset($symbols[$key])) continue;

                                        if($chunk[$role]["symbols"] != $symbols[$key])
                                            $shouldUpdate = true;

                                        $symbols[$key] = htmlentities(html_entity_decode($symbols[$key]));
                                        $translation[$key][$role]["symbols"] = $symbols[$key];
                                    }

                                    $translation[$key][$role][$section] = $post["chunks"][$key];

                                    if($shouldUpdate)
                                    {
                                        $tID = $translation[$key]["tID"];
                                        unset($translation[$key]["tID"]);

                                        $encoded = json_encode($translation[$key]);
                                        $json_error = json_last_error();
                                        if($json_error === JSON_ERROR_NONE)
                                        {
                                            $trData = array(
                                                "translatedVerses"  => $encoded
                                            );
                                            $this->_translationModel->updateTranslation(
                                                $trData, 
                                                array(
                                                    "trID" => $trID, 
                                                    "tID" => $tID));
                                            $updated++;
                                        }
                                    }
                                }

                                if($updated)
                                    $response["success"] = true;
                                else
                                {
                                    $response["errorType"] = "noChange";
                                    $response["error"] = "no_change";
                                }
                            }
                        }
                        break;

                    case EventCheckSteps::FST_CHECK:
                    case EventCheckSteps::SND_CHECK:
                    case EventCheckSteps::PEER_REVIEW_L2:
                    case EventCheckSteps::PEER_EDIT_L3:
                        if(isset($post["chunks"]) && is_array($post["chunks"]) && !empty($post["chunks"]))
                        {
                            if($event[0]->step == EventCheckSteps::PEER_REVIEW_L2)
                            {
                                if($event[0]->peer == 1)
                                {
                                    $peer2Check = (array)json_decode($event[0]->peer2Check, true);
                                    if(array_key_exists($event[0]->currentChapter, $peer2Check))
                                    {
                                        if($peer2Check[$event[0]->currentChapter]["done"] == 1)
                                        {
                                            $response["errorType"] = "checkDone";
                                            $response["error"] = __("not_possible_to_save_error");
                                            echo json_encode($response);
                                            exit;
                                        }
                                    }
                                    else
                                    {
                                        echo json_encode($response);
                                        exit;
                                    }
                                }
                                else
                                {
                                    echo json_encode($response);
                                    exit;
                                }
                            }
                            elseif($event[0]->step == EventCheckSteps::PEER_EDIT_L3)
                            {
                                $peerCheck = (array)json_decode($event[0]->peerCheck, true);
                                if(array_key_exists($event[0]->currentChapter, $peerCheck) &&
                                    $peerCheck[$event[0]->currentChapter]["done"] == 2)
                                {
                                    $response["errorType"] = "checkDone";
                                    $response["error"] = __("not_possible_to_save_error");
                                    echo json_encode($response);
                                    exit;
                                }
                            }

                            $translationData = $this->_translationModel->getEventTranslationByEventID(
                                $event[0]->eventID,
                                $event[0]->currentChapter);
                            $translation = array();

                            foreach ($translationData as $tv) {
                                $arr = json_decode($tv->translatedVerses, true);
                                $arr["tID"] = $tv->tID;
                                $translation[] = $arr;
                            }

                            if(!empty($translation))
                            {
                                $post["chunks"] = array_filter($post["chunks"], function($chunk) {
                                    $verses = array_filter($chunk, function($v) {
                                        return !empty(Tools::strip_tags(trim($v)));
                                    });
                                    $isEqual = sizeof($chunk) == sizeof($verses);
                                    return !empty($chunk) && $isEqual;
                                });

                                $updated = 0;
                                foreach ($translation as $key => $chunk) {
                                    if(!isset($post["chunks"][$key])) continue;

                                    $post["chunks"][$key] = Tools::html_entity_decode($post["chunks"][$key]);

                                    $shouldUpdate = false;

                                    $post["chunks"][$key] = Tools::htmlentities($post["chunks"][$key]);
                                    if(is_array($post["chunks"][$key]))
                                    {
                                        foreach ($post["chunks"][$key] as $verse => $vText)
                                        {
                                            if(!isset($chunk[$memberType]["verses"][$verse])
                                                || $chunk[$memberType]["verses"][$verse] != $vText)
                                            {
                                                $shouldUpdate = true;
                                            }

                                        }
                                    }
                                    else
                                    {
                                        if($chunk[$memberType]["verses"] != $post["chunks"][$key])
                                        {
                                            $shouldUpdate = true;
                                        }
                                    }

                                    $translation[$key][$memberType]["verses"] = $post["chunks"][$key];

                                    if($shouldUpdate)
                                    {
                                        $tID = $translation[$key]["tID"];
                                        unset($translation[$key]["tID"]);

                                        $encoded = json_encode($translation[$key]);
                                        $json_error = json_last_error();
                                        if($json_error === JSON_ERROR_NONE)
                                        {
                                            $trData = array(
                                                "translatedVerses"  => $encoded
                                            );
                                            $this->_translationModel->updateTranslation(
                                                $trData,
                                                array(
                                                    "tID" => $tID));
                                            $updated++;
                                        }
                                    }
                                }

                                if($updated)
                                    $response["success"] = true;
                                else
                                {
                                    $response["errorType"] = "noChange";
                                    $response["error"] = "no_change";
                                }
                            }
                        }
                        break;
                }
            }
        }

        echo json_encode($response);
    }


    public function autosaveVerseLangInput()
    {
        $response = array("success" => false);
        $post = Gump::xss_clean($_REQUEST);
        $eventID = isset($post["eventID"]) && is_numeric($post["eventID"]) ? $post["eventID"] : null;

        if($eventID !== null)
        {
            $memberType = EventMembers::TRANSLATOR;
            $event = $this->_model->getMemberEvents(Session::get("memberID"), $memberType, $eventID, false, false);

            if(!empty($event))
            {
                switch($event[0]->step)
                {
                    case EventSteps::MULTI_DRAFT:
                    case EventSteps::SELF_CHECK:
                        if(is_array($post["verses"]) && !empty($post["verses"]))
                        {
                            $trID = $event[0]->trID;
                            $translationData = $this->_translationModel->getEventTranslation(
                                $trID,
                                $event[0]->currentChapter);

                            $translationVerses = array(
                                EventMembers::TRANSLATOR => array(
                                    "blind" => "",
                                    "verses" => ""
                                ),
                                EventMembers::L2_CHECKER => array(
                                    "verses" => array()
                                ),
                                EventMembers::L3_CHECKER => array(
                                    "verses" => array()
                                ),
                            );

                            // Store verses and their related ids
                            $ids = [];

                            foreach ($post["verses"] as $verse => $text)
                            {
                                $text = strip_tags(html_entity_decode($text));

                                if(empty(trim($text)) || !is_integer($verse) || $verse < 1)
                                {
                                    if($event[0]->step == EventSteps::SELF_CHECK)
                                    {
                                        $response["error"] = "empty imput";
                                        echo json_encode($response);
                                        exit;
                                    }
                                    else
                                    {
                                        continue;
                                    }
                                }

                                $updated = false;
                                foreach ($translationData as $chunk) {
                                    if($chunk->firstvs == $verse)
                                    {
                                        // Update verse
                                        $translationVerses[EventMembers::TRANSLATOR]["verses"] = [];
                                        $translationVerses[EventMembers::TRANSLATOR]["verses"][$verse] = $text;

                                        $encoded = json_encode($translationVerses);
                                        $json_error = json_last_error();
                                        if($json_error === JSON_ERROR_NONE)
                                        {
                                            $this->_translationModel->updateTranslation(
                                                ["translatedVerses" => $encoded],
                                                array(
                                                    "trID" => $trID,
                                                    "tID" => $chunk->tID));
                                            $ids[$verse] = $chunk->tID;
                                            $updated = true;
                                        }
                                        else
                                        {
                                            $response["errorType"] = "json";
                                            $response["error"] = "Json error: " . $json_error;
                                            echo json_encode($response);
                                            exit;
                                        }
                                        break;
                                    }
                                }

                                if(!$updated)
                                {
                                    // Create verse
                                    $translationVerses[EventMembers::TRANSLATOR]["verses"] = [];
                                    $translationVerses[EventMembers::TRANSLATOR]["verses"][$verse] = $text;

                                    $encoded = json_encode($translationVerses);
                                    $json_error = json_last_error();

                                    if($json_error === JSON_ERROR_NONE)
                                    {
                                        $trData = array(
                                            "projectID" => $event[0]->projectID,
                                            "eventID" => $event[0]->eventID,
                                            "trID" => $event[0]->trID,
                                            "targetLang" => $event[0]->targetLang,
                                            "bookProject" => $event[0]->bookProject,
                                            "abbrID" => $event[0]->abbrID,
                                            "bookCode" => $event[0]->bookCode,
                                            "chapter" => $event[0]->currentChapter,
                                            "chunk" => $verse-1,
                                            "firstvs" => $verse,
                                            "translatedVerses" => $encoded,
                                            "dateCreate" => date('Y-m-d H:i:s')
                                        );
                                        $id = $this->_translationModel->createTranslation($trData);
                                        if($id)
                                            $ids[$verse] = $id;
                                    }
                                    else
                                    {
                                        $response["errorType"] = "json";
                                        $response["error"] = "Json error: " . $json_error;
                                        echo json_encode($response);
                                        exit;
                                    }
                                }
                            }

                            $response["success"] = true;
                            $response["ids"] = $ids;
                        }
                        break;
                }
            }
        }

        echo json_encode($response);
    }


    public function deleteVerseLangInput()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && is_numeric($_POST["eventID"]) ? $_POST["eventID"] : null;
        $tID = isset($_POST["tID"]) && is_numeric($_POST["tID"]) ? $_POST["tID"] : null;

        if($eventID !== null && $tID !== null)
        {
            $memberType = EventMembers::TRANSLATOR;
            $event = $this->_model->getMemberEvents(Session::get("memberID"), $memberType, $eventID, false, false);

            if(!empty($event))
            {
                if($event[0]->step == EventSteps::MULTI_DRAFT)
                {
                    $deleted = $this->_translationModel->deleteTranslation([
                        "eventID" => $eventID,
                        "chapter" => $event[0]->currentChapter,
                        "tID" => $tID
                    ]);

                    if($deleted)
                    {
                        $response["success"] = true;
                    }
                }
            }
        }

        echo json_encode($response);
    }

    private function getComments($eventID, $chapter = null, $chunk = null)
    {
        $comments = $this->_translationModel->getCommentsByEvent($eventID, $chapter, $chunk);
        $commentsFinal = array();

        foreach ($comments as $comment) {
            $commentsFinal[$comment->chapter][$comment->chunk][] = $comment;
        }

        unset($comments);

        return $commentsFinal;
    }

    public function saveComment()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $chunk = isset($_POST["chunk"]) && $_POST["chunk"] != "" ? (integer)$_POST["chunk"] : null;
        $comment = isset($_POST["comment"]) ? $_POST["comment"] : "";
        $level = isset($_POST["level"]) ? (integer)$_POST["level"] : 1;
        $memberID = Session::get("memberID");

        if($eventID !== null && $chapter !== null && $chunk !== null)
        {
            $memberInfo = (array)$this->_model->getEventMemberInfo($eventID, $memberID);

            if(!empty($memberInfo) && ($memberInfo[0]->translator == $memberID ||
                    $memberInfo[0]->checker == $memberID ||
                    $memberInfo[0]->l2checker == $memberID || $memberInfo[0]->l3checker == $memberID))
            {
                $commentDB = (array)$this->_translationModel->getComment(
                    $eventID,
                    $chapter,
                    $chunk,
                    Session::get("memberID"),
                    $level);

                $postdata = array(
                    "text" => $comment,
                );

                if(!empty($commentDB))
                {
                    if($comment == "")
                    {
                        $result = $this->_translationModel->deleteComment(array("cID" => $commentDB[0]->cID));
                    }
                    else
                    {
                        $result = $this->_translationModel->updateComment($postdata,  array("cID" => $commentDB[0]->cID));
                    }
                }
                else
                {
                    $postdata += array(
                        "eventID" => $eventID,
                        "chapter" => $chapter,
                        "chunk" => $chunk,
                        "memberID" => Session::get("memberID"),
                        "level" => $level
                    );

                    $result = $this->_translationModel->createComment($postdata);
                }

                if($result)
                {
                    $response["success"] = true;
                    $response["text"] = $comment;
                }
            }
        }

        echo json_encode($response);
    }


    public function getKeywords()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $memberID = Session::get("memberID");

        if($eventID !== null && $chapter !== null)
        {
            $keywords = $this->_translationModel->getKeywords([
                "eventID" => $eventID,
                "chapter" => $chapter
            ]);

            if(!empty($keywords))
            {
                $response["success"] = true;
                $response["text"] = $keywords;
            }
        }

        echo json_encode($response);
    }

    public function saveKeyword()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $chunk = isset($_POST["chunk"]) && $_POST["chunk"] != "" ? (integer)$_POST["chunk"] : null;
        $index = isset($_POST["index"]) && $_POST["index"] != "" ? (integer)$_POST["index"] : null;
        $verse = isset($_POST["verse"]) ? $_POST["verse"] : "";
        $text = isset($_POST["text"]) ? $_POST["text"] : "";
        $remove = isset($_POST["remove"]) && $_POST["remove"] == "true";
        $memberID = Session::get("memberID");

        if($eventID !== null && $chapter !== null && $chunk !== null && $index > -1 && $verse != null)
        {
            $memberInfo = (array)$this->_model->getEventMemberInfo($eventID, $memberID);

            $canKeyword = false;
            $canCreate = true;

            if(!empty($memberInfo) || Session::get("isSuperAdmin"))
            {
                if(!in_array($memberInfo[0]->bookProject, ["tn"]))
                {
                    if($memberInfo[0]->checker == $memberID)
                    {
                        foreach ($memberInfo as $item) {
                            if($item->checkerStep == EventSteps::KEYWORD_CHECK)
                            {
                                if($chapter == $item->chkChapter)
                                {
                                    $canKeyword = true;
                                    break;			
                                }
                            }
                        }
                    }
                    elseif($memberInfo[0]->l2checker == $memberID)
                    {
                        $canCreate = false;
                        $events = $this->_model->getMemberEventsForCheckerL2(
                            $memberID,
                            $eventID,
                            null,
                            $chapter
                        );

                        foreach ($events as $event) {
                            if($event->step == EventCheckSteps::KEYWORD_CHECK_L2
                                || $event->step == EventCheckSteps::PEER_REVIEW_L2)
                            {
                                if($chapter == $event->currentChapter)
                                {
                                    $canKeyword = true;
                                    break;
                                }
                            }
                        }
                    }
                    elseif($memberInfo[0]->bookProject == "sun" && $memberInfo[0]->translator == $memberID)
                    {
                        $events = $this->_model->getMemberEventsForSun(
                            $memberID,
                            $eventID,
                            null,
                            $chapter
                        );

                        foreach ($events as $event) {
                            if($event->step == EventSteps::THEO_CHECK)
                            {
                                if($chapter == $event->currentChapter)
                                {
                                    $canKeyword = true;
                                    break;
                                }
                            }
                        }
                    }
                    elseif(Session::get("isSuperAdmin"))
                    {
                        $canKeyword = true;
                    }
                }
                else
                {
                    $events = $this->_model->getMemberEventsForNotes(
                        $memberID,
                        $eventID,
                        null,
                        $chapter
                    );

                    foreach ($events as $event) {
                        if($event->step == EventSteps::HIGHLIGHT)
                        {
                            if($chapter == $event->currentChapter)
                            {
                                $canKeyword = true;
                                break;
                            }
                        }
                    }
                }

                if($canKeyword)
                {
                    $result = null;

                    $keyword = $this->_translationModel->getKeywords([
                        "eventID" => $eventID,
                        "chapter" => $chapter,
                        "chunk" => $chunk,
                        "verse" => $verse,
                        "indexOrder" => $index,
                        "text" => $text
                    ]);
                    
                    if(!empty($keyword))
                    {
                        if($remove)
                        {
                            $response["type"] = "remove";
                            $result = $this->_translationModel->deleteKeyword($keyword[0]->kID);
                        }
                        else
                        {
                            $response["error"] = __("keyword_exists_error");
                            echo json_encode($response);
                            return;
                        }
                    }
                    else
                    {
                        if($canCreate)
                        {
                            $postdata = [
                                "eventID" => $eventID,
                                "chapter" => $chapter,
                                "chunk" => $chunk,
                                "verse" => $verse,
                                "indexOrder" => $index,
                                "text" => $text,
                                "memberID" => Session::get("memberID")
                            ];

                            $response["type"] = "add";
                            $result = $this->_translationModel->createKeyword($postdata);
                        }
                    }

                    if($result)
                    {
                        $response["success"] = true;
                        $response["text"] = $text;
                    }
                }
            }
        }

        echo json_encode($response);
    }


    /**
     * Make member a level 1 checker, who picks from notification area
     * @param $eventID
     * @param $memberID
     * @param $step
     * @return mixed
     */
    public function applyChecker($eventID, $memberID, $step)
    {
        $canApply = false;

        $profile = Session::get("profile");
        $langs = [];
        foreach ($profile["languages"] as $lang => $item) {
            $langs[] = $lang;
        }

        $allNotifications = $this->_model->getAllNotifications($langs);
        $allNotifications = Arrays::append(array_values($allNotifications), array_values($this->_notifications));
        $notif = null;
        
        foreach ($allNotifications as $notification) {
            if($eventID == $notification->eventID
                && $memberID == $notification->memberID
                && $step == $notification->step)
            {
                if($notification->checkerID == 0)
                {
                    $canApply = true;
                    $notif = $notification;
                    break;
                }
            }
        }
        
        if($canApply && $notif)
        {
            $postdata = ["checkerID" => Session::get("memberID"), "hideChkNotif" => true];
            $this->_model->updateTranslator($postdata, array("eventID" => $eventID, "memberID" => $memberID));
            Url::redirect('events/checker/'.$eventID.'/'.$memberID);
        }
        else
        {
            $error[] = __("cannot_apply_checker");
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;

        return View::make("Events/CheckerApply")
            ->shares("title", __("apply_checker_l1"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }


    /**
     * Make member a level 2 checker, who picks from notification area
     * @param $eventID
     * @param $memberID
     * @param $step
     * @param $chapter
     * @return mixed
     */
    public function applyCheckerL2L3($eventID, $memberID, $step, $chapter)
    {
        $canApply = false;

        $profile = Session::get("profile");
        $langs = [];
        foreach ($profile["languages"] as $lang => $item) {
            $langs[] = $lang;
        }

        $allNotifications = $this->_model->getAllNotifications($langs);
        $allNotifications = Arrays::append(array_values($allNotifications), array_values($this->_notifications));
        $notif = null;

        foreach ($allNotifications as $notification) {
            if($eventID == $notification->eventID
                && $memberID == $notification->memberID
                && $step == $notification->step
                && $chapter == $notification->currentChapter)
            {
                if($step == EventCheckSteps::SND_CHECK)
                {
                    $sndCheck = (array)json_decode($notification->sndCheck, true);
                    if(isset($sndCheck[$chapter]) && $sndCheck[$chapter]["memberID"] == 0)
                    {
                        $sndCheck[$chapter]["memberID"] = Session::get("memberID");
                        $notification->sndCheck = json_encode($sndCheck);
                        $notif = $notification;
                        $canApply = true;
                    }
                }
                elseif($step == EventCheckSteps::PEER_REVIEW_L2)
                {
                    $peer1Check = (array)json_decode($notification->peer1Check, true);
                    $peer2Check = (array)json_decode($notification->peer2Check, true);
                    if(isset($peer1Check[$chapter]))
                    {
                        if($peer1Check[$chapter]["memberID"] == 0)
                        {
                            $peer1Check[$chapter]["memberID"] = Session::get("memberID");
                            $notification->peer1Check = json_encode($peer1Check);
                            $notif = $notification;
                            $canApply = true;
                        }
                        else if($peer2Check[$chapter]["memberID"] == 0)
                        {
                            $peer2Check[$chapter]["memberID"] = Session::get("memberID");
                            $notification->peer2Check = json_encode($peer2Check);
                            $notif = $notification;
                            $canApply = true;
                        }
                    }
                }
                elseif($step == EventSteps::KEYWORD_CHECK)
                {
                    $kwCheck = (array)json_decode($notification->kwCheck, true);
                    if(isset($kwCheck[$chapter]) && $kwCheck[$chapter]["memberID"] == 0)
                    {
                        $kwCheck[$chapter]["memberID"] = Session::get("memberID");
                        $notification->kwCheck = json_encode($kwCheck);
                        $notif = $notification;
                        $canApply = true;
                    }
                }
                elseif($step == EventSteps::CONTENT_REVIEW)
                {
                    $crCheck = (array)json_decode($notification->crCheck, true);
                    if(isset($crCheck[$chapter]) && $crCheck[$chapter]["memberID"] == 0)
                    {
                        $crCheck[$chapter]["memberID"] = Session::get("memberID");
                        $notification->crCheck = json_encode($crCheck);
                        $notif = $notification;
                        $canApply = true;
                    }
                }
                elseif($step == EventCheckSteps::PEER_REVIEW_L3)
                {
                    $peerCheck = (array)json_decode($notification->peerCheck, true);
                    if(isset($peerCheck[$chapter]) && $peerCheck[$chapter]["memberID"] == 0)
                    {
                        $peerCheck[$chapter]["memberID"] = Session::get("memberID");
                        $notification->peerCheck = json_encode($peerCheck);
                        $notif = $notification;
                        $canApply = true;
                    }
                }
            }
        }

        if($canApply && $notif)
        {
            if($notif->manageMode == "l2")
            {
                $postdata = [
                    "sndCheck" => $notif->sndCheck,
                    "peer1Check" => $notif->peer1Check,
                    "peer2Check" => $notif->peer2Check,
                ];
                $this->_model->updateL2Checker($postdata, [
                    "eventID" => $eventID,
                    "memberID" => $memberID
                ]);

                Url::redirect('events/checker-l2/'.$eventID.'/'.$memberID.'/'.$chapter);
            }
            elseif($notif->manageMode == "l3")
            {
                $postdata = [
                    "peerCheck" => $notif->peerCheck,
                ];
                $this->_model->updateL3Checker($postdata, [
                    "eventID" => $eventID,
                    "memberID" => $memberID
                ]);

                Url::redirect('events/checker'.
                    (!in_array($notif->bookProject, ["ulb"])
                        ? "-".$notif->bookProject : "").'-l3/'.$eventID.'/'.$memberID.'/'.$chapter);
            }
        }
        else
        {
            $error[] = __("cannot_apply_checker");
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;

        return View::make("Events/CheckerApply")
            ->shares("title", __("apply_checker_l1"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    /**
     * Make member a SUN checker, who picks from notification area
     * @param $eventID
     * @param $memberID
     * @param $step
     * @param $chapter
     * @return mixed
     */
    public function applyCheckerSun($eventID, $memberID, $step, $chapter)
    {
        $canApply = false;

        $profile = Session::get("profile");
        $langs = [];
        foreach ($profile["languages"] as $lang => $item) {
            $langs[] = $lang;
        }

        $allNotifications = $this->_model->getAllNotifications($langs);
        $allNotifications = Arrays::append(
            array_values($allNotifications),
            array_values($this->_notifications));
        $notif = null;

        foreach ($allNotifications as $notification) {
            if($eventID == $notification->eventID
                && $memberID == $notification->memberID
                && $step == $notification->step
                && $chapter == $notification->currentChapter)
            {
                if($step == EventSteps::THEO_CHECK)
                {
                    $kwCheck = (array)json_decode($notification->kwCheck, true);
                    if(isset($kwCheck[$chapter]) && $kwCheck[$chapter]["memberID"] == 0)
                    {
                        $kwCheck[$chapter]["memberID"] = Session::get("memberID");
                        $notification->kwCheck = json_encode($kwCheck);
                        $notif = $notification;
                        $canApply = true;
                    }
                }
                elseif($step == EventSteps::CONTENT_REVIEW)
                {
                    $crCheck = (array)json_decode($notification->crCheck, true);
                    if(isset($crCheck[$chapter]) && $crCheck[$chapter]["memberID"] == 0)
                    {
                        $crCheck[$chapter]["memberID"] = Session::get("memberID");
                        $notification->crCheck = json_encode($crCheck);
                        $notif = $notification;
                        $canApply = true;
                    }
                }
            }
        }

        if($canApply && $notif)
        {
            $postdata = [
                "kwCheck" => $notif->kwCheck,
                "crCheck" => $notif->crCheck,
            ];
            $this->_model->updateTranslator($postdata, [
                "eventID" => $eventID,
                "memberID" => $memberID
            ]);

            Url::redirect('events/checker'.($notif->sourceBible == "odb" ? "-odb" : "").'-sun/'.$eventID.'/'.$memberID.'/'.$chapter);
        }
        else
        {
            $error[] = __("cannot_apply_checker");
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;

        return View::make("Events/CheckerApply")
            ->shares("title", __("apply_checker_sun"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    /**
     * Make member a verbalize checker
     */
    public function applyVerbChecker()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chkID = isset($_POST["chkID"]) && $_POST["chkID"] != "" ? (integer)$_POST["chkID"] : null;
        $chkName = isset($_POST["chkName"]) && preg_match("/^[^0-9!@#\$%\^&\*\(\)_\-\+=\.,\?\/\\\[\]\{\}\|\"]+$/", $_POST["chkName"]) ? trim($_POST["chkName"]) : null;
        $memberID = Session::get("memberID");

        if($eventID !== null && ($chkID != null || $chkName != null))
        {
            $event = $this->_model->getMemberEvents($memberID, EventMembers::TRANSLATOR, $eventID);
            if($chkID != null)
            {
                $chkMember = $this->_membersModel->getMembers([$chkID]);
                if(!empty($chkMember))
                    $chkName = $chkMember[0]->firstName . " " . mb_substr($chkMember[0]->lastName, 0, 1).".";
                else
                {
                    $chkID = null;
                    $chkName = null;
                }
            }

            if(!empty($event) && $chkName != null)
            {
                $verbCheck = (array)json_decode($event[0]->verbCheck, true);
                $checker = $chkID != null ? $chkID : $chkName;

                if($event[0]->step == EventSteps::VERBALIZE && $event[0]->checkDone == false
                    && !array_key_exists($event[0]->currentChapter, $verbCheck))
                {
                    $verbCheck[$event[0]->currentChapter] = $checker;
                    $postdata["verbCheck"] = json_encode($verbCheck);
                    $postdata["checkerID"] = $chkID;
                    $postdata["checkDone"] = true;

                    $upd = $this->_model->updateTranslator($postdata, array("eventID" => $eventID, "memberID" => $memberID));
                    if($upd)
                    {
                        $response["success"] = true;
                        $response["chkName"] = $chkName;
                    }
                    else
                    {
                        $response["error"] = "not_saved";
                    }
                }
                else
                {
                    $response["error"] = "wrong_step";
                }
            }
            else
            {
                $response["error"] = "wrong_event_or_member";
            }
        }
        else
        {
            $response["error"] = "forbidden_name_format";
        }

        echo json_encode($response);
    }

    public function getEventMembers()
    {
        $response = array("success" => false);

        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $mode = isset($_POST["mode"]) && $_POST["mode"] != "" ? $_POST["mode"] : "ulb";
        $memberIDs = isset($_POST["memberIDs"]) && $_POST["memberIDs"] != "" ? (array)$_POST["memberIDs"] : [];
        $manageMode = isset($_POST["manageMode"]) && $_POST["manageMode"] != "" ? $_POST["manageMode"] : "l1";
        
        if($eventID !== null && $memberIDs !== null)
        {
            $event = $this->_model->getEvent($eventID);

            if(!empty($event))
            {
                $admins = (array)json_decode($event[0]->admins, true);

                if(in_array(Session::get("memberID"), $admins) || Session::get('isSuperAdmin'))
                {
                    if($manageMode == "l1")
                        $members = $this->_model->getMembersForEvent($eventID);
                    else if($manageMode == "l2")
                        $members = $this->_model->getMembersForL2Event($eventID);
                    else if($manageMode == "l3")
                        $members = $this->_model->getMembersForL3Event($eventID);
                    
                    foreach ($members as $key => $member) {
                        $members[$key]["name"] = $member["firstName"] . " " . mb_substr($member["lastName"], 0, 1).".";

                        if(in_array($member["memberID"], $memberIDs))
                            unset($members[$key]);
                    }

                    $response["members"] = $members;
                    $response["success"] = true;
                }
                else
                {
                    $response["error"] = __("error_ocured", array("wrong parameters"));
                }
            }
            else
            {
                $response["error"] = __("error_ocured", array("wrong parameters"));
            }
        }
        else
        {
            $response["error"] = __("error_ocured", array("wrong parameters"));
        }

        echo json_encode($response);
    }


    public function checkEvent()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;

        if($eventID !== null)
        {
            $data["event"] = $this->_model->getMemberEvents(
                Session::get("memberID"),
                EventMembers::TRANSLATOR,
                $eventID
            );

            if(!empty($data["event"]))
            {
                if($data["event"][0]->state != "started")
                {
                    $response["success"] = true;
                }
            }
        }

        echo json_encode($response);
    }

    /**
     * Get notifications for user
     */
    public function getNotifications()
    {
        $data["notifs"] = array();

        if(!empty($this->_notifications))
        {
            foreach ($this->_notifications as $notification)
            {
                $text_data = [
                    "name" => $notification->firstName . " " . mb_substr($notification->lastName, 0, 1).".",
                    "step" => ($notification->step != "other" ? "(".__($notification->step .
                            ($notification->sourceBible == "odb"
                                ? "_odb" : "")).")" : ""),
                    "book" => $notification->bookName,
                    "chapter" => ($notification->currentChapter == 0
                        ? __("intro")
                        : $notification->currentChapter),
                    "language" => $notification->tLang,
                    "project" => ($notification->sourceBible == "odb"
                        ?__($notification->sourceBible)
                        : $notification->bookProject)
                ];

                $text = __('checker_apply', $text_data);

                $note["link"] = "/events/checker".(isset($notification->manageMode)
                    && in_array($notification->manageMode, ["sun"]) ? "-".$notification->manageMode : "")
                    ."/".$notification->eventID."/"
                    .$notification->memberID."/".$notification->step."/"
                    .(isset($notification->manageMode) ? $notification->currentChapter."/" : "")
                    ."apply";

                $note["anchor"] = "check:".$notification->eventID.":".$notification->memberID;
                $note["text"] = $text;
                $note["step"] = $notification->step;

                $data["notifs"][] = $note;
            }
        }
        else
        {
            $data["noNotifs"] = __("no_notifs_msg");
        }

        $data["success"] = true;
        echo json_encode($data);
    }

    
    /**
     * All notifications list page
     * @return mixed
     */
    public function allNotifications()
    {
        $data["notifications"] = $this->_notifications;

        $profile = Session::get("profile");
        $langs = array();
        foreach ($profile["languages"] as $lang => $item) {
            $langs[] = $lang;
        }

        $data["menu"] = 1;
        $data["all_notifications"] = $this->_model->getAllNotifications($langs);
        $data["all_notifications"] = Arrays::append($data["all_notifications"],
            $this->_notifications);

        $distinct = [];
        foreach($data["all_notifications"] as $notification)
        {
            $unique_key = $notification->eventID."_".$notification->step
                ."_".$notification->memberID."_".$notification->currentChapter;
            $distinct[$unique_key] = $notification;
        }

        $data["all_notifications"] = $distinct;

        return View::make("Events/Notifications")
            ->shares("title", __("all_notifications_title"))
            ->shares("data", $data);
    }

    /**
     * Add or remove chapter user translating
     */
    public function assignChapter()
    {
        $response = array("success" => false);

        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $memberID = isset($_POST["memberID"]) && $_POST["memberID"] != "" ? (integer)$_POST["memberID"] : null;
        $action = isset($_POST["action"]) && preg_match("/^(add|delete)$/", $_POST["action"]) ? $_POST["action"] : null;
        $manageMode = isset($_POST["manageMode"]) && $_POST["manageMode"] != "" ? $_POST["manageMode"] : "l1";

        if($eventID !== null && $chapter !== null && $memberID !== null && $action !== null)
        {
            $userType = EventMembers::TRANSLATOR;
            if($manageMode == "l2")
                $userType = EventMembers::L2_CHECKER;
            elseif($manageMode == "l3")
                $userType = EventMembers::L3_CHECKER;
            
            $data["event"] = $this->_model->getMemberEvents($memberID, $userType, $eventID, true);

            if(!empty($data["event"]))
            {
                $admins = $userType == EventMembers::L3_CHECKER ?
                    (array)json_decode($data["event"][0]->admins_l3, true) :
                    ($userType == EventMembers::L2_CHECKER ?
                        (array)json_decode($data["event"][0]->admins_l2, true) :
                        (array)json_decode($data["event"][0]->admins, true));

                if(in_array(Session::get("memberID"), $admins) || Session::get('isSuperAdmin'))
                {
                    $data["chapters"] = [];
                    if($data["event"][0]->bookProject == "tn")
                        $data["chapters"][0] = [];

                    for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                    {
                        $data["chapters"][$i] = [];
                    }

                    $chapters = $this->_model->getChapters($data["event"][0]->eventID);

                    foreach ($chapters as $chap) {
                        $tmp["trID"] = $chap["trID"];
                        $tmp["memberID"] = $chap["memberID"];
                        $tmp["chunks"] = json_decode($chap["chunks"], true);
                        $tmp["done"] = $chap["done"];
                        $tmp["l2memberID"] = $chap["l2memberID"];
                        $tmp["l2chID"] = $chap["l2chID"];
                        $tmp["l2checked"] = $chap["l2checked"];
                        $tmp["l3memberID"] = $chap["l3memberID"];
                        $tmp["l3chID"] = $chap["l3chID"];
                        $tmp["l3checked"] = $chap["l3checked"];

                        $data["chapters"][$chap["chapter"]] = $tmp;
                    }
                    
                    if(isset($data["chapters"][$chapter]) && empty($data["chapters"][$chapter]))
                    {
                        if($action == "add")
                        {
                            if($manageMode == "l2" || $manageMode == "l3")
                            {
                                $response["error"] = __("error_ocured", ["This chapter hasn't been translated."]);
                                echo json_encode($response);
                                exit;
                            }

                            $postdata = [
                                "eventID" => $eventID,
                                "trID" => $data["event"][0]->trID,
                                "memberID" => $data["event"][0]->myMemberID,
                                "chapter" => $chapter,
                                "chunks" => "[]",
                                "done" => false
                            ];
                            
                            $assignChapter = $this->_model->assignChapter($postdata);
                            $data["chapters"][$chapter] = $postdata;

                            $myChapters = array_filter($data["chapters"], function ($v) use($data) {
                                return isset($v["memberID"])
                                    && $v["memberID"] == $data["event"][0]->myMemberID
                                    && !$v["done"];
                            });

                            // Change translator's step to pray when at least one chapter is assigned to him or all chapters finished
                            if(sizeof($myChapters) == 1 || $data["event"][0]->step == EventSteps::FINISHED) {
                                $this->_model->updateTranslator(
                                    ["step" => EventSteps::PRAY, "translateDone" => false], 
                                    ["trID" => $data["event"][0]->trID]);
                            }

                            if($assignChapter)
                            {
                                $response["success"] = true;
                            }
                            else
                            {
                                $response["error"] = __("error_ocured", [$assignChapter]);
                            }
                        }
                        else
                        {
                            $response["error"] = __("error_ocured", ["wrong parameters"]);
                        }
                    }
                    else
                    {
                        if($action == "delete")
                        {
                            $translations = $this->_translationModel->getTranslationByEventID($eventID, $chapter);
                            // Check if chapter has translations
                            $hasTranslations = !empty($translations);
                            
                            // Check if chapter has L2 translations
                            if($manageMode == "l2")
                            {
                                $trVerses = (array) json_decode($translations[0]->translatedVerses);
                                $l2Verses = $trVerses[EventMembers::L2_CHECKER];
                                
                                $hasTranslations = !empty($l2Verses->verses);
                            }
                            // Check if chapter has L3 translations
                            if($manageMode == "l3")
                            {
                                $trVerses = (array) json_decode($translations[0]->translatedVerses);
                                $l3Verses = $trVerses[EventMembers::L3_CHECKER];

                                $hasTranslations = !empty($l3Verses->verses);
                            }
                            
                            if(!$hasTranslations)
                            {
                                if($manageMode == "l1")
                                {
                                    if($data["chapters"][$chapter]["memberID"] == $memberID)
                                    {
                                        $removeChapter = $this->_model->removeChapter([
                                            "eventID" => $eventID,
                                            "memberID" => $memberID,
                                            "chapter" => $chapter]);
                                        $data["chapters"][$chapter] = [];

                                        $trPostData = [];

                                        $noMoreChapters = empty(array_filter($data["chapters"], function ($v) use($data) {
                                            return isset($v["memberID"])
                                                && $v["memberID"] == $data["event"][0]->myMemberID
                                                && !$v["done"];
                                        }));

                                        // Clear translator data to default if current chapter was removed
                                        // Change translator's step to NONE when no chapter is assigned to him
                                        if($data["event"][0]->currentChapter == $chapter || $noMoreChapters)
                                        {
                                            $trPostData["step"] = $noMoreChapters ? EventSteps::NONE : EventSteps::PRAY;
                                            $trPostData["currentChapter"] = 0;
                                            $trPostData["currentChunk"] = 0;
                                            $trPostData["checkerID"] = 0;
                                            $trPostData["checkDone"] = 0;
                                            $trPostData["hideChkNotif"] = 1;

                                            $verbCheck = (array)json_decode($data["event"][0]->verbCheck, true);
                                            if(array_key_exists($chapter, $verbCheck))
                                            {
                                                unset($verbCheck[$chapter]);
                                                $trPostData["verbCheck"] = json_encode($verbCheck);
                                            }
                                        }

                                        if(!empty($trPostData))
                                        {
                                            $this->_model->updateTranslator($trPostData, 
                                                ["trID" => $data["event"][0]->trID]);
                                        }

                                        if($removeChapter)
                                        {
                                            $response["success"] = true;
                                        }
                                        else
                                        {
                                            $response["error"] = __("error_ocured", array($removeChapter));
                                        }
                                    }
                                    else
                                    {
                                        $response["error"] = __("error_ocured", array("wrong parameters"));
                                    }
                                }
                                else if($manageMode == "l2")
                                {
                                    if($data["chapters"][$chapter]["l2memberID"] == $memberID)
                                    {
                                        $removeChapter = $this->_model->updateChapter([
                                            "l2memberID" => 0,
                                            "l2chID" => 0
                                        ],[
                                            "eventID" => $eventID,
                                            "chapter" => $chapter
                                        ]);
                                        $data["chapters"][$chapter]["l2memberID"] = 0;
                                        $data["chapters"][$chapter]["l2chID"] = 0;

                                        $trPostData = [];

                                        $noMoreChapters = empty(array_filter($data["chapters"], function ($v) use($data) {
                                            return isset($v["l2memberID"])
                                            && $v["l2memberID"] == $data["event"][0]->memberID;
                                        }));

                                        // Clear checker's data to default if current chapter was removed
                                        // Change checker's step to NONE when no chapter is assigned to him
                                        if($data["event"][0]->currentChapter == $chapter || $noMoreChapters)
                                        {
                                            $trPostData["step"] = $noMoreChapters ? EventCheckSteps::NONE : EventCheckSteps::PRAY;
                                            $trPostData["currentChapter"] = 0;
                                        }

                                        if(!empty($trPostData))
                                        {
                                            $this->_model->updateL2Checker($trPostData, 
                                                ["l2chID" => $data["event"][0]->l2chID]);
                                        }

                                        if($removeChapter)
                                        {
                                            $response["success"] = true;
                                        }
                                        else
                                        {
                                            $response["error"] = __("error_ocured", array($removeChapter));
                                        }
                                    }
                                    else
                                    {
                                        $response["error"] = __("error_ocured", array("wrong parameters"));
                                    }
                                }
                                else if($manageMode == "l3")
                                {
                                    if($data["chapters"][$chapter]["l3memberID"] == $memberID)
                                    {
                                        $removeChapter = $this->_model->updateChapter([
                                            "l3memberID" => 0,
                                            "l3chID" => 0
                                        ],[
                                            "eventID" => $eventID,
                                            "chapter" => $chapter
                                        ]);
                                        $data["chapters"][$chapter]["l3memberID"] = 0;
                                        $data["chapters"][$chapter]["l3chID"] = 0;

                                        $trPostData = [];

                                        $noMoreChapters = empty(array_filter($data["chapters"], function ($v) use($data) {
                                            return isset($v["l3memberID"])
                                                && $v["l3memberID"] == $data["event"][0]->memberID;
                                        }));

                                        // Clear checker's data to default if current chapter was removed
                                        // Change checker's step to NONE when no chapter is assigned to him
                                        if($data["event"][0]->currentChapter == $chapter || $noMoreChapters)
                                        {
                                            $trPostData["step"] = $noMoreChapters ? EventCheckSteps::NONE : EventCheckSteps::PRAY;
                                            $trPostData["currentChapter"] = 0;
                                        }

                                        if(!empty($trPostData))
                                        {
                                            $this->_model->updateL3Checker($trPostData,
                                                ["l3chID" => $data["event"][0]->l3chID]);
                                        }

                                        if($removeChapter)
                                        {
                                            $response["success"] = true;
                                        }
                                        else
                                        {
                                            $response["error"] = __("error_ocured", array($removeChapter));
                                        }
                                    }
                                    else
                                    {
                                        $response["error"] = __("error_ocured", array("wrong parameters"));
                                    }
                                }
                            }
                            else
                            {
                                $response["error"] = __("event_translating_error");
                            }
                        }
                        else if($action == "add" && $manageMode == "l2")
                        {
                            if($data["chapters"][$chapter]["l2memberID"] == 0)
                            {
                                $postdata = [
                                    "l2chID" => $data["event"][0]->l2chID,
                                    "l2memberID" => $data["event"][0]->memberID
                                ];

                                $this->_model->updateChapter($postdata, [
                                    "eventID" => $eventID,
                                    "chapter" => $chapter
                                ]);

                                $this->_model->updateL2Checker([
                                    "step" => EventCheckSteps::PRAY,
                                ], [
                                    "eventID" => $eventID,
                                    "memberID" => $data["event"][0]->memberID
                                ]);

                                $response["success"] = true;
                            }
                            else
                            {
                                $response["error"] = __("chapter_aready_assigned_error");
                            }
                        }
                        else if($action == "add" && $manageMode == "l3")
                        {
                            if($data["chapters"][$chapter]["l3memberID"] == 0)
                            {
                                $postdata = [
                                    "l3chID" => $data["event"][0]->l3chID,
                                    "l3memberID" => $data["event"][0]->memberID
                                ];

                                $this->_model->updateChapter($postdata, [
                                    "eventID" => $eventID,
                                    "chapter" => $chapter
                                ]);

                                $this->_model->updateL3Checker([
                                    "step" => EventCheckSteps::PRAY,
                                ], [
                                    "eventID" => $eventID,
                                    "memberID" => $data["event"][0]->memberID
                                ]);

                                $response["success"] = true;
                            }
                            else
                            {
                                $response["error"] = __("chapter_aready_assigned_error");
                            }
                        }
                        else
                        {
                            $response["error"] = __("chapter_aready_assigned_error");
                        }
                    }
                }
                else
                {
                    $response["error"] = __("error_ocured", array("wrong parameters"));
                }
            }
            else
            {
                $response["error"] = __("error_ocured", array("wrong parameters"));
            }
        }
        else
        {
            $response["error"] = __("error_ocured", array("wrong parameters"));
        }

        echo json_encode($response);
    }

    /**
     * Delete user from event
     */
    public function deleteEventMember()
    {
        $response = array("success" => false);

        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $memberID = isset($_POST["memberID"]) && $_POST["memberID"] != "" ? (integer)$_POST["memberID"] : null;
        $mode = isset($_POST["mode"]) && $_POST["mode"] != "" ? $_POST["mode"] : "ulb";
        $manageMode = isset($_POST["manageMode"]) && $_POST["manageMode"] != "" ? $_POST["manageMode"] : "l1";

        if($eventID !== null && $memberID != null)
        {
            $event = $this->_model->getEvent($eventID);
            if(!empty($event))
            {
                $admins = (array)json_decode($event[0]->admins, true);

                if(in_array(Session::get("memberID"), $admins) || Session::get('isSuperAdmin'))
                {
                    $hasChapter = false;
                    $chapters = $this->_model->getChapters($event[0]->eventID);

                    foreach ($chapters as $chap) {
                        $index = "memberID";
                        if($manageMode == "l2")
                            $index = "l2memberID";
                        if($manageMode == "l3")
                            $index = "l3memberID";
                        if($chap[$index] == $memberID)
                        {
                            $hasChapter = true;
                            break;
                        }
                    }

                    if(!$hasChapter)
                    {
                        if($manageMode == "l2")
                            $this->_model->deleteL2Checkers(["eventID" => $eventID, "memberID" => $memberID]);
                        else if($manageMode == "l3")
                            $this->_model->deleteL3Checkers(["eventID" => $eventID, "memberID" => $memberID]);
                        else
                            $this->_model->deleteTranslators(["eventID" => $eventID, "memberID" => $memberID]);
                        $response["success"] = true;
                    }
                    else
                    {
                        $response["error"] = __("translator_has_chapter");
                    }
                }
                else
                {
                    $response["error"] = __("error_ocured", array("wrong parameters"));
                }
            }
            else
            {
                $response["error"] = __("error_ocured", array("wrong parameters"));
            }
        }
        else
        {
            $response["error"] = __("error_ocured", array("wrong parameters"));
        }

        echo json_encode($response);
    }

    public function checkBookFinished($chapters, $chaptersNum, $other = false, $level = 1)
    {
        if(isset($chapters) && is_array($chapters) && !empty($chapters))
        {
            $chaptersDone = 0;
            foreach ($chapters as $chapter) {
                $chk = $level == 3 ? "l3checked" : ($level == 2 ? "l2checked" : ($other ? "checked" : "done"));
                if(!empty($chapter) && $chapter[$chk])
                    $chaptersDone++;
            }

            if($chaptersNum == $chaptersDone)
                return true;
        }

        return false;
    }

    public function getTq($bookCode, $chapter, $lang = "en")
    {
        $data = [];
        $data["questions"] = $this->getTranslationQuestions(
            $bookCode,
            $chapter,
            $lang
        );

        $this->layout = "dummy";
        echo View::make("Events/Tools/Tq")
            ->shares("data", $data)
            ->renderContents();
    }

    public function getTn($bookCode, $chapter, $lang = "en", $totalVerses)
    {
        $data = [];
        $data["notes"] = $this->getTranslationNotes(
            $bookCode,
            $chapter,
            $lang
        );
        $data["totalVerses"] = $totalVerses;
        $data["notesVerses"] = $this->_apiModel->getNotesVerses($data);

        $this->layout = "dummy";
        echo View::make("Events/Tools/Tn")
            ->shares("data", $data)
            ->renderContents();
    }

    public function getTw($bookCode, $chapter, $lang = "en")
    {
        $data = [];
        $data["keywords"] = $this->getTranslationWords(
            $bookCode,
            $chapter,
            $lang
        );

        $this->layout = "dummy";
        echo View::make("Events/Tools/Tw")
            ->shares("data", $data)
            ->renderContents();
    }

    public function getRubric($lang)
    {
        $data = [];
        $data["rubric"] = $this->_apiModel->getCachedRubricFromApi($lang);

        $this->layout = "dummy";
        echo View::make("Events/Tools/Rubric")
            ->shares("data", $data)
            ->renderContents();
    }

    public function getSailDict()
    {
        $data = [];
        $data["saildict"] = $this->_saildictModel->getSunDictionary();

        $this->layout = "dummy";
        echo View::make("Events/Tools/SailDict")
            ->shares("data", $data)
            ->renderContents();
    }

    public function checkInternet()
    {
        return time();
    }

    //-------------------- Private functions --------------------------//

    /**
     * Get source text for chapter or chunk
     * @param $data
     * @param bool $getChunk
     * @return array|bool
     */
    private function getSourceText($data, $getChunk = false)
    {
        $currentChapter = $data["event"][0]->currentChapter;
        $currentChunk = $data["event"][0]->state == EventStates::TRANSLATING
            ? $data["event"][0]->currentChunk : 0;

        $usfm = $this->_apiModel->getCachedSourceBookFromApi(
            $data["event"][0]->sourceBible,
            $data["event"][0]->bookCode, 
            $data["event"][0]->sourceLangID,
            $data["event"][0]->abbrID);

        if($usfm && !empty($usfm["chapters"]))
        {
            $initChapter = 0;
            $currentChunkText = [];
            $chunks = json_decode($data["event"][0]->chunks, true);
            $data["chunks"] = $chunks;

            if($currentChapter == $initChapter)
            {
                $level = "l1";
                if($data["event"][0]->state == EventStates::L2_CHECK)
                {
                    $level = "l2";
                    $memberID = $data["event"][0]->memberID;
                }
                elseif($data["event"][0]->state == EventStates::L3_CHECK)
                {
                    $level = "l3";
                    $memberID = $data["event"][0]->memberID;
                }
                else
                {
                    $memberID = $data["event"][0]->myMemberID;
                }

                $nextChapter = $this->_model->getNextChapter(
                    $data["event"][0]->eventID,
                    $memberID,
                    $level);

                if(!empty($nextChapter))
                    $currentChapter = $nextChapter[0]->chapter;
            }

            if($currentChapter <= $initChapter) return false;

            if(!isset($usfm["chapters"][$currentChapter]))
            {
                return array("error" => __("no_source_error"));
            }

            foreach ($usfm["chapters"][$currentChapter] as $section) {
                foreach ($section as $v => $text) {
                    $data["text"][$v] = $text;
                }
            }

            $arrKeys = array_keys($data["text"]);
            $lastVerse = explode("-", end($arrKeys));
            $lastVerse = $lastVerse[sizeof($lastVerse)-1];
            $data["totalVerses"] = !empty($data["text"]) ?  $lastVerse : 0;
            $data["currentChapter"] = $currentChapter;
            $data["currentChunk"] = $currentChunk;

			// TODO write function to return chapters array
			$data["chapters"] = [];
            for($i=1; $i <= sizeof($usfm["chapters"]); $i++)
            {
                $data["chapters"][$i] = [];
            }

            $chapters = $this->_model->getChapters($data["event"][0]->eventID);

            foreach ($chapters as $chapter) {
                $tmp["trID"] = $chapter["trID"];
                $tmp["memberID"] = $chapter["memberID"];
                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["done"] = $chapter["done"];

                $data["chapters"][$chapter["chapter"]] = $tmp;
            }

            if($getChunk)
            {
                $chapData = $chunks;
                $chunk = $chapData[$currentChunk];
                $fv = $chunk[0];
                $lv = $chunk[sizeof($chunk)-1];

                $data["no_chunk_source"] = true;

                foreach ($data["text"] as $verse => $text) {
                    $v = explode("-", $verse);
                    $map = array_map(function($value) use ($fv, $lv) {
                        return $value >= $fv && $value <= $lv;
                    }, $v);
                    $map = array_unique($map);

                    if($map[0])
                    {
                        $currentChunkText[$verse] = $text;
                        $data["no_chunk_source"] = false;
                    }
                }

                $data["chunks"] = $chapData;
                $data["chunk"] = $chunk;
                $data["totalVerses"] = sizeof($chunk);

                $data["text"] = $currentChunkText;
            }
            
            return $data;
        }
        else
        {
            return array("error" => __("no_source_error"));
        }
    }


    public function getOtherSourceText($data, $getChunk = false)
    {
        $currentChapter = $data["event"][0]->currentChapter;
        $currentChunk = $data["event"][0]->state == EventStates::TRANSLATING
            ? $data["event"][0]->currentChunk : 0;

        $source = $this->_apiModel->getOtherSource(
            $data["event"][0]->sourceBible,
            $data["event"][0]->bookCode,
            $data["event"][0]->sourceLangID);

        if(!empty($source))
        {
            $initChapter = 0;
            $currentChunkText = [];
            $chunks = json_decode($data["event"][0]->chunks, true);
            $data["chunks"] = $chunks;

            if($currentChapter == $initChapter)
            {
                $memberID = $data["event"][0]->myMemberID;

                $nextChapter = $this->_model->getNextChapter(
                    $data["event"][0]->eventID,
                    $memberID);
                if(!empty($nextChapter))
                    $currentChapter = $nextChapter[0]->chapter;
            }

            if($currentChapter <= $initChapter) return false;

            if(!isset($source["chapters"][$currentChapter]))
            {
                return array("error" => __("no_source_error"));
            }

            $data["text"] = $source["chapters"][$currentChapter];

            $lastVerse = sizeof($data["text"]);
            $data["totalVerses"] = $lastVerse;
            $data["currentChapter"] = $currentChapter;
            $data["currentChunk"] = $currentChunk;

            $data["chapters"] = [];
            for($i=1; $i <= sizeof($source["chapters"]); $i++)
            {
                $data["chapters"][$i] = [];
            }

            $chapters = $this->_model->getChapters($data["event"][0]->eventID);

            foreach ($chapters as $chapter) {
                $tmp["trID"] = $chapter["trID"];
                $tmp["memberID"] = $chapter["memberID"];
                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["done"] = $chapter["done"];

                $data["chapters"][$chapter["chapter"]] = $tmp;
            }

            if($getChunk)
            {
                $chapData = $chunks;
                $chunk = $chapData[$currentChunk];
                $fv = $chunk[0];
                $lv = $chunk[sizeof($chunk)-1];

                $data["no_chunk_source"] = true;

                if(isset($data["text"][$fv]))
                {
                    $currentChunkText[$fv] = $data["text"][$fv];
                    $data["no_chunk_source"] = false;
                }

                $data["chunks"] = $chapData;
                $data["chunk"] = $chunk;
                $data["totalVerses"] = sizeof($chunk);

                $data["text"] = $currentChunkText;
            }

            return $data;
        }
        else
        {
            return array("error" => __("no_source_error"));
        }
    }

    private function getTranslationNotes($book, $chapter, $lang = "en")
    {
        $tn_cache_notes = "tn_".$lang."_".$book."_".$chapter;
        $tNotes = [];

        if(Cache::has($tn_cache_notes))
        {
            $tn_source = Cache::get($tn_cache_notes);
            $tNotes = json_decode($tn_source, true);
        }
        else
        {
            $tNotesBook = $this->_apiModel->getTranslationNotes($book, $lang);
            if(isset($tNotesBook[$chapter]))
                $tNotes = $tNotesBook[$chapter];

            ksort($tNotes);

            if(!empty($tNotes))
                Cache::add($tn_cache_notes, json_encode($tNotes), 365*24*7);
        }

        return $tNotes;
    }

    private function getTranslationWords($book, $chapter, $lang = "en")
    {
        $tw_cache_words = "tw_".$lang."_".$book."_".$chapter;

        if(Cache::has($tw_cache_words))
        {
            $tw_source = Cache::get($tw_cache_words);
            $tWords = json_decode($tw_source, true);
        }
        else
        {
            $tWords = $this->_apiModel->getTranslationWords($book, $chapter, $lang);

            if(!empty($tWords))
                Cache::add($tw_cache_words, json_encode($tWords), 365*24*7);
        }

        return $tWords;
    }


    private function getTranslationWordsByCategory($category, $lang = "en", $onlyNames = false)
    {
        $tw_cache_words = "tw_".$lang."_".$category . ($onlyNames ? "_names" : "");

        if(Cache::has($tw_cache_words))
        {
            $tw_source = Cache::get($tw_cache_words);
            $tWords = json_decode($tw_source, true);
        }
        else
        {
            $tWords = $this->_apiModel->getTranslationWordsByCategory($category, $lang, $onlyNames);

            if(!empty($tWords))
                Cache::add($tw_cache_words, json_encode($tWords), 365*24*7);
        }

        return $tWords;
    }


    private function getTranslationQuestions($book, $chapter, $lang = "en")
    {
        $tq_cache_questions = "tq_".$lang."_".$book."_".$chapter;

        $tQuestions = [];

        if(Cache::has($tq_cache_questions))
        {
            $tq_source = Cache::get($tq_cache_questions);
            $tQuestions = json_decode($tq_source, true);
        }
        else
        {
            $tQuestionsBook = $this->_apiModel->getTranslationQuestions($book, $lang);
            if(isset($tQuestionsBook[$chapter]))
            {
                $tQuestions = $tQuestionsBook[$chapter];
                ksort($tQuestions);
            }

            if(!empty($tQuestions))
                Cache::add($tq_cache_questions, json_encode($tQuestions), 365*24*7);
        }

        return $tQuestions;
    }


    private function moveMemberStepBack($member, $toStep, $confirm, $prevChunk = false)
    {
        $manageMode = "l1";
        if(isset($member->state) && $member->state == EventStates::L2_CHECK)
            $manageMode = "l2";
        elseif(isset($member->state) && $member->state == EventStates::L3_CHECK)
            $manageMode = "l3";

        $mode = $manageMode == "l1" && $member->langInput
            ? "li"
            : ($member->sourceBible == "odb" ? "odb" : "").$member->bookProject;

        $postData = [];

        // Level 2
        if($manageMode == "l2")
        {
            // do not allow move from "none" and "preparation" steps
            if(EventCheckSteps::enum($member->step, $manageMode) < 2)
                return [];

            // Do not allow to move back more than one step at a time
            if((EventCheckSteps::enum($member->step, $manageMode) - EventCheckSteps::enum($toStep, $manageMode)) > 1)
                return [];

            switch ($toStep)
            {
                case EventCheckSteps::PRAY:
                    $postData["step"] = EventSteps::PRAY;
                    break;

                case EventCheckSteps::CONSUME:
                    $postData["step"] = EventSteps::CONSUME;
                    break;
            }

            return $postData;
        }
        elseif ($manageMode == "l3")
        {
            // do not allow move from "none" and "preparation" steps
            if(EventCheckSteps::enum($member->step, $manageMode) < 2)
                return [];

            // Do not allow to move back more than one step at a time
            if((EventCheckSteps::enum($member->step, $manageMode) - EventCheckSteps::enum($toStep, $manageMode)) > 1)
                return [];

            switch ($toStep)
            {
                case EventCheckSteps::PRAY:
                    $postData["step"] = EventSteps::PRAY;

                    $peerCheck = (array)json_decode($member->peerCheck, true);
                    if(array_key_exists($member->currentChapter, $peerCheck))
                        unset($peerCheck[$member->currentChapter]);

                    $postData["peerCheck"] = json_encode($peerCheck);
                    break;

                case EventCheckSteps::PEER_REVIEW_L3:
                    $postData["step"] = EventCheckSteps::PEER_REVIEW_L3;

                    $peerCheck = (array)json_decode($member->peerCheck, true);
                    if(array_key_exists($member->currentChapter, $peerCheck))
                        $peerCheck[$member->currentChapter]["done"] = 0;

                    $postData["peerCheck"] = json_encode($peerCheck);
                    break;
            }

            return $postData;
        }

        // Level 1
        // do not allow to move from "none" and "preparation" steps
        if(EventSteps::enum($member->step, $mode) < 2)
            return [];

        // Do not allow to move back more than one step at a time
        if((EventSteps::enum($member->step, $mode) - EventSteps::enum($toStep, $mode)) > 1)
            return [];

        // Do not allow to move forward, exception from READ_CHUNK to BLIND_DRAFT of previous chunk
        if(EventSteps::enum($toStep, $mode) >= EventSteps::enum($member->step, $mode)
            && ($toStep == EventSteps::BLIND_DRAFT && !$prevChunk))
            return [];

        switch ($toStep)
        {
            case EventSteps::PRAY:
                $postData["step"] = EventSteps::PRAY;
                break;

            case EventSteps::CONSUME:
                $postData["step"] = EventSteps::CONSUME;
                $postData["checkerID"] = 0;
                $postData["checkDone"] = false;
                $postData["hideChkNotif"] = true;

                if($mode == "odbsun")
                {
                    $postData["currentChunk"] = 0;
                }
                else
                {
                    $verbCheck = (array)json_decode($member->verbCheck, true);
                    if(array_key_exists($member->currentChapter, $verbCheck))
                        unset($verbCheck[$member->currentChapter]);
                    $postData["verbCheck"] = json_encode($verbCheck);
                }
                break;

            case EventSteps::VERBALIZE:
                $postData["step"] = EventSteps::VERBALIZE;

                $verbCheck = (array)json_decode($member->verbCheck, true);
                if(array_key_exists($member->currentChapter, $verbCheck))
                {
                    $postData["checkerID"] = $verbCheck[$member->currentChapter];
                    unset($verbCheck[$member->currentChapter]);
                }
                $postData["verbCheck"] = json_encode($verbCheck);
                $postData["checkerID"] = 0;
                $postData["checkDone"] = false;
                break;

            case EventSteps::CHUNKING:
                $trans = $this->_translationModel->getEventTranslation($member->trID, $member->currentChapter);

                if(!empty($trans) && !$confirm)
                    return ["hasTranslation"];

				$this->_model->updateChapter(["chunks" => "[]"], [
				    "eventID" => $member->eventID,
                    "chapter" => $member->currentChapter]);

                $postData["step"] = EventSteps::CHUNKING;
                $postData["currentChunk"] = 0;
                $postData["translations"] = true;
                break;

            case EventSteps::READ_CHUNK:
                $postData["step"] = EventSteps::READ_CHUNK;
                break;

            case EventSteps::BLIND_DRAFT:
                $postData["step"] = EventSteps::BLIND_DRAFT;
                if($prevChunk)
                {
                    $chunk = $member->currentChunk-1;
                    $postData["currentChunk"] = max(0, $chunk);
                }
                break;

            case EventSteps::REARRANGE:
                $postData["step"] = EventSteps::REARRANGE;
                $postData["currentChunk"] = 0;
                if($prevChunk)
                {
                    $chunks = (array)json_decode($member->chunks, true);
                    if($member->step == EventSteps::SYMBOL_DRAFT && $member->currentChunk == 0)
                        $chunk = sizeof($chunks)-1;
                    else
                        $chunk = $member->currentChunk-1;

                    $postData["currentChunk"] = max(0, $chunk);
                }
                break;

            case EventSteps::SYMBOL_DRAFT:
                $postData["step"] = EventSteps::SYMBOL_DRAFT;
                $postData["currentChunk"] = 0;
                if($prevChunk)
                {
                    $chunks = (array)json_decode($member->chunks, true);
                    if($member->step == EventSteps::SELF_CHECK)
                        $chunk = sizeof($chunks)-1;
                    else
                        $chunk = $member->currentChunk-1;
                    $postData["currentChunk"] = max(0, $chunk);
                }
                break;

            case EventSteps::SELF_CHECK:
                $postData["step"] = EventSteps::SELF_CHECK;
                $postData["checkerID"] = 0;
                $postData["checkDone"] = false;
                $postData["hideChkNotif"] = true;

                $peerCheck = (array)json_decode($member->peerCheck, true);
                if(array_key_exists($member->currentChapter, $peerCheck))
                    unset($peerCheck[$member->currentChapter]);
                $postData["peerCheck"] = json_encode($peerCheck);
                break;

            case EventSteps::MULTI_DRAFT:
                $postData["step"] = EventSteps::MULTI_DRAFT;
                break;

            case EventSteps::PEER_REVIEW:
                $postData["step"] = EventSteps::PEER_REVIEW;

                $kwCheck = (array)json_decode($member->kwCheck, true);
                if(array_key_exists($member->currentChapter, $kwCheck))
                    unset($kwCheck[$member->currentChapter]);
                $postData["kwCheck"] = json_encode($kwCheck);

                $peerCheck = (array)json_decode($member->peerCheck, true);
                if(array_key_exists($member->currentChapter, $peerCheck))
                {
                    $postData["checkerID"] = $peerCheck[$member->currentChapter];
                    unset($peerCheck[$member->currentChapter]);
                }
                $postData["peerCheck"] = json_encode($peerCheck);
                $postData["hideChkNotif"] = true;

                if($confirm)
                {
                    $postData["checkerID"] = 0;
                    $postData["hideChkNotif"] = false;
                }

                if(in_array($mode, ["tn"]))
                    $postData["translateDone"] = false;

                $postData["checkDone"] = false;
                break;

            case EventSteps::KEYWORD_CHECK:
                $postData["step"] = EventSteps::KEYWORD_CHECK;

                $crCheck = (array)json_decode($member->crCheck, true);
                if(array_key_exists($member->currentChapter, $crCheck))
                    unset($crCheck[$member->currentChapter]);
                $postData["crCheck"] = json_encode($crCheck);

                $kwCheck = (array)json_decode($member->kwCheck, true);
                if(array_key_exists($member->currentChapter, $kwCheck))
                {
                    $postData["checkerID"] = $kwCheck[$member->currentChapter];
                    unset($kwCheck[$member->currentChapter]);
                }
                $postData["kwCheck"] = json_encode($kwCheck);
                $postData["hideChkNotif"] = true;

                if($confirm)
                {
                    $postData["checkerID"] = 0;
                    $postData["hideChkNotif"] = false;
                }

                $postData["checkDone"] = false;
                break;

            case EventSteps::CONTENT_REVIEW:
                $postData["step"] = EventSteps::CONTENT_REVIEW;

                $crCheck = (array)json_decode($member->crCheck, true);
                if(array_key_exists($member->currentChapter, $crCheck))
                {
                    $postData["checkerID"] = $crCheck[$member->currentChapter];
                    unset($crCheck[$member->currentChapter]);
                }
                $postData["crCheck"] = json_encode($crCheck);
                $postData["hideChkNotif"] = true;

                if($confirm)
                {
                    $postData["checkerID"] = 0;
                    $postData["hideChkNotif"] = false;
                }

                $postData["checkDone"] = false;
                break;

            case EventSteps::FINAL_REVIEW:
                $crCheck = (array)json_decode($member->crCheck, true);
                end($crCheck);
                $lastChapter = key($crCheck);

                $translationData = $this->_translationModel->getLastEventTranslation($member->trID);
                $lastChunk = 0;

                if(!empty($translationData))
                    $lastChunk = $translationData[0]->chunk;

                $postData["step"] = EventSteps::FINAL_REVIEW;
                $postData["currentChapter"] = $lastChapter;
                $postData["currentChunk"] = $lastChunk;
                $postData["translateDone"] = false;

                $this->_model->updateChapter(["done" => false], [
                    "eventID" => $member->eventID,
                    "chapter" => $member->currentChapter]);
                break;
        }
        
        return $postData;
    }

    function getTurnCredentials() {
        $turnSecret = $this->_membersModel->getTurnSecret();
        $turnUsername = (time() + 3600) . ":odbtranslation";
        $turnPassword = "";

        if(!empty($turnSecret))
        {
            if(($turnSecret[0]->expire - time()) < 0)
            {
                $pass = $this->_membersModel->generateStrongPassword(22);
                if($this->_membersModel->updateTurnSecret(["value" => $pass, "expire" => time() + (30*24*3600)]))
                {
                    $turnSecret[0]->value = $pass;
                }
            }

            $turnPassword = hash_hmac("sha1", $turnUsername, $turnSecret[0]->value, true);
        }

        return [$turnUsername, base64_encode($turnPassword)];
    }
}
