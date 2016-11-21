<?php

namespace App\Modules\Alma\Controllers;

/**
 * Description of AlmaController
 *
 * @author Arman Zil
 */

use App\Core\Controller;
use App\Modules\Alma\Models\Synonym;
use App\Modules\Alma\Models\Translation;
use App\Modules\Alma\Models\Word;
use Support\Facades\Input;
use Support\Facades\Response;


class AlmaController extends Controller
{
    public function index()
    {
        return $this
                ->getView()
                ->shares('title', 'Alma Module')
                ->with('text', $this->example_text)
                ;
    }
    
    public function listWords()
    {
        $words = Word::with(['variants', 'synonyms', 'translations'])
                ->where('parent_id', null)
                ->orderBy('title')
                ->get();
        
        
        return $words;
    }
    
    public function addTerm($type)
    {
        $input = Input::only('title', 'word_id');
        
        
        
        // TODO: validation
        
        
        
        //TODO: check if there is such word
        
        
        
        switch ($type) {
            case 'word':
            case 'variant':
                $term = new Word();
                if (isset($input['word_id'])) {
                    $term->parent_id = $input['word_id'];
                } else {
                    $term->locale = 'ru';
                }
                break;
            
            case 'synonym':
            case 'translation':
                if ($type === 'synonym') {
                    $term = new Synonym();
                } else {
                    $term = new Translation();
                    $term->votes = 0;
                }
                
                if (isset($input['word_id']) && $input['word_id'] > 0) {
                    $term->word_id = $input['word_id'];
                    
                    $word = Word::find($input['word_id']);
                }
                break;
            
            default: break;
        }
        
        if (empty($term)) return Response::json([
            'error'   => true,
            'message' => 'Ошибка: неизвестный тип данных'
        ]);
        
        
        $term->title = $input['title'];
        $term->save();
        
        
        return Response::json([
            'term' => $term
        ]);
    }

    

    public $example_text = '<ol><li value="1">Павел, волею Божиею Апостол Иисуса Христа, находящимся в Ефесе святым и верным во Христе Иисусе: </li><li value="2">благодать вам и мир от Бога Отца нашего и Господа Иисуса Христа. </li><li value="3">Благословен Бог и Отец Господа нашего Иисуса Христа, благословивший нас во Христе всяким духовным благословением в небесах, </li><li value="4">так как Он избрал нас в Нем прежде создания мира, чтобы мы были святы и непорочны пред Ним в любви, </li><li value="5">предопределив усыновить нас Себе чрез Иисуса Христа, по благоволению воли Своей, </li><li value="6">в похвалу славы благодати Своей, которою Он облагодатствовал нас в Возлюбленном, </li><li value="7">в Котором мы имеем искупление Кровию Его, прощение грехов, по богатству благодати Его, </li><li value="8">каковую Он в преизбытке даровал нам во всякой премудрости и разумении, </li><li value="9">открыв нам тайну Своей воли по Своему благоволению, которое Он прежде положил в Нем, </li><li value="10">в устроении полноты времен, дабы все небесное и земное соединить под главою Христом. </li><li value="11">В Нем мы и сделались наследниками, быв предназначены к тому по определению Совершающего все по изволению воли Своей, </li><li value="12">дабы послужить к похвале славы Его нам, которые ранее уповали на Христа. </li><li value="13">В Нем и вы, услышав слово истины, благовествование вашего спасения, и уверовав в Него, запечатлены обетованным Святым Духом, </li><li value="14">Который есть залог наследия нашего, для искупления удела Его, в похвалу славы Его. </li><li value="15">Посему и я, услышав о вашей вере во Христа Иисуса и о любви ко всем святым, </li><li value="16">непрестанно благодарю за вас Бога, вспоминая о вас в молитвах моих, </li><li value="17">чтобы Бог Господа нашего Иисуса Христа, Отец славы, дал вам Духа премудрости и откровения к познанию Его, </li><li value="18">и просветил очи сердца вашего, дабы вы познали, в чем состоит надежда призвания Его, и какое богатство славного наследия Его для святых, </li><li value="19">и как безмерно величие могущества Его в нас, верующих по действию державной силы Его, </li><li value="20">которою Он воздействовал во Христе, воскресив Его из мертвых и посадив одесную Себя на небесах, </li><li value="21">превыше всякого Начальства, и Власти, и Силы, и Господства, и всякого имени, именуемого не только в сем веке, но и в будущем, </li><li value="22">и все покорил под ноги Его, и поставил Его выше всего, главою Церкви, </li><li value="23">которая есть Тело Его, полнота Наполняющего все во всем. </li></ol>';
}
