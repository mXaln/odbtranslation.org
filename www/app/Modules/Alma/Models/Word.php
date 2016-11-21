<?php

namespace App\Modules\Alma\Models;

use App\Modules\Alma\Models\Antonym;
use App\Modules\Alma\Models\Synonym;
use App\Modules\Alma\Models\Translation;
use Database\ORM\Model;

/**
 * Description of Word
 *
 * @author Arman Zil
 */
class Word extends Model
{
    protected $table    = 'alma_words';
    public $timestamps  = true;
    protected $fillable = ['locale', 'title'];
    

    public function variants()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function antonyms()
    {
        return $this->hasMany(Antonym::class);
    }
    
    public function synonyms()
    {
        return $this->hasMany(Synonym::class);
    }
    
    public function translations()
    {
        return $this->hasMany(Translation::class);
    }
}
