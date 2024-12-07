<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quicktest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'percentage_disease_1', 'percentage_disease_2', 'percentage_disease_3'];
}
