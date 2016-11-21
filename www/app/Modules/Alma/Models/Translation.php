<?php

namespace App\Modules\Alma\Models;

use App\Modules\Alma\Models\Word;
use Database\ORM\Model;

/**
 * Description of Translation
 *
 * @author Arman Zil
 */
class Translation extends Model
{
    protected $table    = 'alma_translations';
    public $timestamps  = true;
    protected $fillable = ['word_id', 'title', 'votes'];
    
    public function word()
    {
        $this->belongsTo(Word::class);
    }
}
