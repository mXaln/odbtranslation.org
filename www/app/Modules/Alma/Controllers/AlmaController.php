<?php

namespace App\Modules\Alma\Controllers;

/**
 * Description of AlmaController
 *
 * @author Arman Zil
 */

use App\Core\Controller;
use App\Models\User;
use App\Modules\Alma\Models\Synonym;
use App\Modules\Alma\Models\Translation;
use App\Modules\Alma\Models\Vote;
use App\Modules\Alma\Models\Word;
use Auth;
use Input;
use Support\Facades\DB;
use Helpers\Session;
use Redirect;
use Response;
use Helpers\Data;
use App\Models\EventsModel;
use Support\Facades\Cache;
use Helpers\UsfmParser;


class AlmaController extends Controller
{
    public function index()
    {
        return $this
                ->getView()
                ->shares('title', 'Alma Module')
                ;
    }
    
    public $signs = [' ', ',', '.', '?', '!', ':', ';', '"'];
    public function postMainText()
    {
        $text = $this->getBook("ulb", 1, "gen", "ru"); // Get book of Genesis RSB
        //$text = $this->example_text;
        
        
        $words = Word::with('translations')
                ->orderBy('title')
                ->get();
        
        foreach ($words as $word) {
            $id   = empty($word->parent_id) ? $word->id : $word->parent_id; 
            foreach ($this->signs as $sign) {
                $text = str_replace(
                        $word->title . $sign,
                        '<span class="btn-warning" ng-click="wordClick('. $id .')">'. $word->title .'</span> ',
                        $text
                    );
            }
        }
        
        
        return Response::json([
            'mainText' => $text
        ]);
    }
    
    public function listWords()
    {
        $words = Word::with('translations', 'variants')
                ->where('parent_id', null)
                ->orderBy('title')
                ->get();
        
        
        return $words;
    }
    
    public function addTerm($type)
    {
        $input = Input::only('title', 'word_id');
        
        foreach ($input as $key => $value) {
            $input[$key] = trim( strip_tags( $value ) );
        }
        
        if (empty($input['title'])) return Response::json([
            'error'   => true,
            'type'    => 'empty',
            'message' => 'Ошибка: не указано слово'
        ]);
        
        
        // check if there is such word
        if ($type != 'word') {
            if (empty($input['word_id'])) {
                return Response::json([
                    'error'   => true,
                    'type'    => 'unknown_word',
                    'message' => 'Не указано основное слово для термина.',
                    'term'    => $input['title']
                ]);
            } else {
                $word = Word::find($input['word_id']);

                if (empty($word)) return Response::json([
                    'error'   => true,
                    'type'    => 'unknown_word',
                    'message' => 'Указанное основное слово для термина не найдено.',
                    'term'    => $input['title']
                ]);
            }
        }


        $term_exists = false;
        switch ($type) {
            case 'word':
            case 'variant':
                $term = Word::where('title', $input['title'])->first();
                if (!empty($term)) {
                    $term_exists = true;
                    break;
                }
                
                $term = new Word();
                if (empty($word)) {
                    $term->locale = 'ru';
                } else {
                    $term->parent_id = $word->id;
                }
                
                break;
            
            case 'synonym':
                $term = Synonym::where('title', $input['title'])->first();
                if (!empty($term)) {
                    $term_exists = true;
                    break;
                }
                
                $term = new Synonym();
                $term->word_id = $term->id;
                break;
            
            case 'translation':
                $term = Translation::where('title', $input['title'])->first();
                if (!empty($term)) {
                    $term_exists = true;
                    break;
                }
                
                $term = new Translation();
                $term->word_id = $word->id;
                break;
            
            default: 
                return Response::json([
                    'error'   => true,
                    'type'    => 'type',
                    'message' => 'Ошибка: неизвестный тип данных'
                ]);
                break;
        }
        
        if ($term_exists) return Response::json([
            'error'   => true,
            'type'    => 'exists',
            'message' => 'Такой термин уже существует',
            'term'    => (empty($term->parent_id) ? $term : $term->parent)
        ]);
        
        $term->title = $input['title'];
        $term->save();
        
        
        return Response::json([
            'term' => $term
        ]);
    }


    private function getMember()
    {
        if (Session::get('loggedin')) {
            $member = DB::table('members')->where('memberID', Session::get('memberID'))->first();
        }

        return isset($member) ? $member : null;
    }

    public function vote($term_id)
    {
        $member = $this->getMember();
        
        if (empty($member)) return Response::json([
            'error'   => true,
            'type'    => 'user',
            'message' => 'Ошибка: авторизуйтесь, пожалуйста.'
        ]);
        
        if ($term_id <= 0) return Response::json([
            'error'   => true,
            'type'    => 'type',
            'message' => 'Ошибка: неизвестный тип данных.'
        ]);
        
        $term = Translation::find($term_id);
        
        if (empty($term)) return Response::json([
            'error'   => true,
            'type'    => 'term',
            'message' => 'Ошибка: термин не найден.'
        ]);
        
        $word = Word::find($term->word_id);
        $vote = $word->votes()->where('user_id', $member->memberID)->first();

        if (Input::get('action_type') == 'vote_back') {
            if (empty($vote)) return Response::json([
                'error'   => true,
                'type'    => 'vote',
                'message' => 'Ошибка: Вы еще не проголосовали.'
            ]);

            $vote->delete();
            $term->votes = --$term->votes;
        } else {
            if (!empty($vote)) return Response::json([
                'error'   => true,
                'type'    => 'vote',
                'message' => 'Ошибка: Вы уже проголосовали.'
            ]);

            $vote = new Vote();
            $vote->user_id = $member->memberID;
            $word->votes()->save($vote);
            $term->votes = ++$term->votes;
        }
        
        $term->save();
        
        $word = Word::with('translations', 'variants')
                ->where('id', $word->id)
                ->first();
        
        return Response::json([
            'word' => $word
        ]);
    }

    public function approve($term_id)
    {
        $member = $this->getMember();
        
        if (empty($member)) return Response::json([
            'error'   => true,
            'type'    => 'user',
            'message' => 'Ошибка: авторизуйтесь, пожалуйста.'
        ]);
        
        if ($term_id <= 0) return Response::json([
            'error'   => true,
            'type'    => 'type',
            'message' => 'Ошибка: неизвестный тип данных.'
        ]);
        
        $term = Translation::find($term_id);
        
        if (empty($term)) return Response::json([
            'error'   => true,
            'type'    => 'term',
            'message' => 'Ошибка: термин не найден.'
        ]);
        
        $is_approved = Translation::where('word_id', $term->word_id)->where('is_approved', 1)->count();
        
        if ($is_approved > 0 && Input::get('action_type') != 'approve_back') return Response::json([
            'error'   => true,
            'type'    => 'term',
            'message' => 'Ошибка: перевод уже утвержден.'
        ]);
        
        $term->is_approved = (Input::get('action_type') == 'approve_back') ? 0 : 1;
        
        $term->save();
        
        $word = Word::with('translations', 'variants')
                ->where('id', $term->word_id)
                ->first();
        
        return Response::json([
            'word' => $word
        ]);
    }
    
    public function addComment()
    {
		$input = Input::only('id', 'comment');
		
		$term = Translation::find($input['id']);
		
		if (empty($term)) return Response::json([
            'error'   => true,
            'type'    => 'term',
            'message' => 'Ошибка: термин не найден.'
        ]);
        
        $term->comment = trim( strip_tags( $input['comment'] ) );
        $term->save();
        
        return Response::json([
            'term' => $term
        ]);
	}

    private function getBook($bookProject, $bookNum, $bookCode, $sourceLang)
    {
        $eventsModel = new EventsModel();
        $cache_keyword = $bookCode."_".$sourceLang."_".$bookProject."_usfm";
        $bookText = "Нет исходного текста";

		
		if(Cache::has($cache_keyword))
        {
            $source = Cache::get($cache_keyword);
            $usfm = json_decode($source, true);
        }
        else
        {
            $source = $eventsModel->getSourceBookFromApiUSFM($bookProject, $bookNum, $bookCode, $sourceLang);
            $usfm = UsfmParser::parse($source);

            if(!empty($usfm))
                Cache::add($cache_keyword, json_encode($usfm), 60*24*7);
        }
		
        if(!empty($usfm) && !empty($usfm["chapters"]))
        {
            $bookText = '<h2>'.$usfm["toc1"].'</h2>';
            foreach ($usfm["chapters"] as $chapter => $chunks) {
                $bookText .= '<h3>Глава '.$chapter.'</h3><ol>';
                foreach ($chunks as $verses) {
                    foreach ($verses as $verse => $text) {
                        $bookText .= '<li value="'.$verse.'">'.$text.'</li>';
                    }
                }
                $bookText .= "</ol>";
            }
        }

        return $bookText;
    }


    //public $example_text = '<ol><li value="1">И вас, мертвых по преступлениям и грехам вашим, </li><li value="2">в которых вы некогда жили, по обычаю мира сего, по воле князя, господствующего в воздухе, духа, действующего ныне в сынах противления, </li><li value="3">между которыми и мы все жили некогда по нашим плотским похотям, исполняя желания плоти и помыслов, и были по природе чадами гнева, как и прочие, </li><li value="4">Бог, богатый милостью, по Своей великой любви, которою возлюбил нас, </li><li value="5">и нас, мертвых по преступлениям, оживотворил со Христом, — благодатью вы спасены, — </li><li value="6">и воскресил с Ним, и посадил на небесах во Христе Иисусе, </li><li value="7">дабы явить в грядущих веках преизобильное богатство благодати Своей в благости к нам во Христе Иисусе. </li><li value="8">Ибо благодатью вы спасены через веру, и сие не от вас, Божий дар:</li><li value="9">не от дел, чтобы никто не хвалился. </li><li value="10">Ибо мы — Его творение, созданы во Христе Иисусе на добрые дела, которые Бог предназначил нам исполнять. </li><li value="11">Итак помните, что вы, некогда язычники по плоти, которых называли необрезанными так называемые обрезанные плотским обрезанием, совершаемым руками, </li><li value="12">что вы были в то время без Христа, отчуждены от общества Израильского, чужды заветов обетования, не имели надежды и были безбожники в мире. </li><li value="13">А теперь во Христе Иисусе вы, бывшие некогда далеко, стали близки Кровию Христовою. </li><li value="14">Ибо Он есть мир наш, соделавший из обоих одно и разрушивший стоявшую посреди преграду, </li><li value="15">упразднив вражду Плотию Своею, а закон заповедей учением, дабы из двух создать в Себе Самом одного нового человека, устрояя мир, </li><li value="16">и в одном теле примирить обоих с Богом посредством креста, убив вражду на нем. </li><li value="17">И, придя, благовествовал мир вам, дальним и близким, </li><li value="18">потому что через Него и те и другие имеем доступ к Отцу, в одном Духе. </li><li value="19">Итак вы уже не чужие и не пришельцы, но сограждане святым и свои Богу, </li><li value="20">быв утверждены на основании Апостолов и пророков, имея Самого Иисуса Христа краеугольным камнем, </li><li value="21">на котором все здание, слагаясь стройно, возрастает в святый храм в Господе, </li><li value="22">на котором и вы устрояетесь в жилище Божие Духом. </li></ol>';
}
