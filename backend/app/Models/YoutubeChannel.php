<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class YoutubeChannel extends Model
{
    /** @use HasFactory<\Database\Factories\YoutubeChannelFactory> */
    use HasUuids, HasFactory;

    protected $table = 'youtube_channels';
}
