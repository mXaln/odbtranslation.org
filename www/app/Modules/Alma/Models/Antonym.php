<?php

namespace App\Modules\Alma\Models;

use App\Modules\Alma\Models\Word;
use Database\ORM\Model;


/**
 * Description of Word
 *
 * @author Arman Zil
 */
class Antonym extends Model
{
    protected $table    = 'alma_antonyms';
    public $timestamps  = true;
    protected $fillable = ['word_id', 'title'];
    
    public function word()
    {
        return $this->belongsTo(Word::class);
    }
}
