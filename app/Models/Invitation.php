<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;

use Illuminate\Database\Eloquent\Model;

#[Fillable(['email'])]
#[Hidden(['token'])]
class Invitation extends Model
{
    //
}
