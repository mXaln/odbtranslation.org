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


class AlmaController extends Controller
{
    public function index()
    {
        return $this
                ->getView()
                ->shares('title', 'Alma Module')
                ;
    }
    
    public function getMainText()
    {
        $text = $this->example_text;
        
        
        $words = Word::with('translations')
                ->where('parent_id', null)
                ->orderBy('title')
                ->get();
        
        foreach ($words as $word) {
            $id   = empty($word->parent_id) ? $word->id : $word->parent_id; 
            $text = str_replace(
                    $word->title,
                    '<span class="btn-warning" ng-click="wordClick('. $id .')">'. $word->title .'</span>',
                    $text
                );
        }
        
        return "<div>$text</div>";
    }
    
    public function postMainText()
    {
        $text = $this->example_text;
        
        
        $words = Word::with('translations')
                ->where('parent_id', null)
                ->orderBy('title')
                ->get();
        
        foreach ($words as $word) {
            $id   = empty($word->parent_id) ? $word->id : $word->parent_id; 
            $text = str_replace(
                    $word->title,
                    '<span class="btn-warning" ng-click="wordClick('. $id .')">'. $word->title .'</span>',
                    $text
                );
        }
        
        return Response::json([
            'mainText' => $text
        ]);
    }
    
    public function listWords()
    {
        $words = Word::with('translations')
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
                    $term->parent_id = $term->id;
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

    
    public function vote($term_id)
    {
        $no_member = false;
        if (Session::get('loggedin')) {
            $member = DB::table('members')->where('memberID', Session::get('memberID'))->first();
            if (empty($member)) $no_member = true;
        } else {
            $no_member = true;
        }
        
        if ($no_member) return Response::json([
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

        if (!empty($vote)) return Response::json([
            'error'   => true,
            'type'    => 'vote',
            'message' => 'Ошибка: Вы уже проголосовали.'
        ]);
        
        $vote = new Vote();
        $vote->user_id = $member->memberID;
        $word->votes()->save($vote);
        
        $term->votes = $word->votes()->count();
        $term->save();
        
        return Response::json([
            'term' => $term,
            'word' => $word
        ]);
    }

    
    public $example_text = '<ol><li value="1">Павел, волею Божиею Апостол Иисуса Христа, находящимся в Ефесе святым и верным во Христе Иисусе: </li><li value="2">благодать вам и мир от Бога Отца нашего и Господа Иисуса Христа. </li><li value="3">Благословен Бог и Отец Господа нашего Иисуса Христа, благословивший нас во Христе всяким духовным благословением в небесах, </li><li value="4">так как Он избрал нас в Нем прежде создания мира, чтобы мы были святы и непорочны пред Ним в любви, </li><li value="5">предопределив усыновить нас Себе чрез Иисуса Христа, по благоволению воли Своей, </li><li value="6">в похвалу славы благодати Своей, которою Он облагодатствовал нас в Возлюбленном, </li><li value="7">в Котором мы имеем искупление Кровию Его, прощение грехов, по богатству благодати Его, </li><li value="8">каковую Он в преизбытке даровал нам во всякой премудрости и разумении, </li><li value="9">открыв нам тайну Своей воли по Своему благоволению, которое Он прежде положил в Нем, </li><li value="10">в устроении полноты времен, дабы все небесное и земное соединить под главою Христом. </li><li value="11">В Нем мы и сделались наследниками, быв предназначены к тому по определению Совершающего все по изволению воли Своей, </li><li value="12">дабы послужить к похвале славы Его нам, которые ранее уповали на Христа. </li><li value="13">В Нем и вы, услышав слово истины, благовествование вашего спасения, и уверовав в Него, запечатлены обетованным Святым Духом, </li><li value="14">Который есть залог наследия нашего, для искупления удела Его, в похвалу славы Его. </li><li value="15">Посему и я, услышав о вашей вере во Христа Иисуса и о любви ко всем святым, </li><li value="16">непрестанно благодарю за вас Бога, вспоминая о вас в молитвах моих, </li><li value="17">чтобы Бог Господа нашего Иисуса Христа, Отец славы, дал вам Духа премудрости и откровения к познанию Его, </li><li value="18">и просветил очи сердца вашего, дабы вы познали, в чем состоит надежда призвания Его, и какое богатство славного наследия Его для святых, </li><li value="19">и как безмерно величие могущества Его в нас, верующих по действию державной силы Его, </li><li value="20">которою Он воздействовал во Христе, воскресив Его из мертвых и посадив одесную Себя на небесах, </li><li value="21">превыше всякого Начальства, и Власти, и Силы, и Господства, и всякого имени, именуемого не только в сем веке, но и в будущем, </li><li value="22">и все покорил под ноги Его, и поставил Его выше всего, главою Церкви, </li><li value="23">которая есть Тело Его, полнота Наполняющего все во всем. </li></ol>';
}
