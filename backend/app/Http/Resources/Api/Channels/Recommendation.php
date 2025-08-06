<?php

namespace App\Http\Resources\Api\Channels;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Recommendation extends JsonResource
{
    public static $wrap = [];
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'subscribers_count' => $this->subscribers_count,
            'average_views' => $this->average_views,
            'engagement_rate' => floatval($this->engagement_rate),
            'language' => $this->language,
            'region' => $this->region,
            'last_video_published_at' => $this->last_video_published_at,
        ];
    }
}
