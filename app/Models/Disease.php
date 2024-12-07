<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;
    protected $fillable = ['disease_name', 'no_of_symptoms'];
    public function symptoms()
    {
        return $this->hasMany(Symptom::class,'disease_id', 'id');
    }

}
