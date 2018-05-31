<?php

namespace App\Modules\Alma\Models;

use App\Modules\Alma\Models\Word;
use Database\ORM\Model;

/**
 * Description of Translation
 *
 * @author Arman Zil
 */
class Vote extends Model
{
    protected $table    = 'alma_votes_track';
    public $timestamps  = true;
    
    
    public function votable()
    {
        return $this->morphTo();
    }
    
}
