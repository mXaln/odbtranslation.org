<?php

namespace App\Modules\Alma\Models;

use App\Modules\Alma\Models\Word;
use Database\ORM\Model;

/**
 * Description of Synonym
 *
 * @author Arman Zil
 */
class Synonym extends Model
{
    protected $table    = 'alma_synonyms';
    public $timestamps  = true;
    protected $fillable = ['word_id', 'title'];
    
    public function word()
    {
        $this->belongsTo(Word::class);
    }
}
