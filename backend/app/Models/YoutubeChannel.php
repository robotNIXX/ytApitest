<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class YoutubeChannel extends Model
{
    use HasUuids;
    
    protected $table = 'youtube_channels';
}
