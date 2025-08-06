<?php

namespace App\DTO;

use Illuminate\Support\Facades\Hash;

class RecommendationsDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public ?string $category = null,
        public ?int $min_subscribers = null,
        public ?int $max_subscribers = null,
        public ?string $language = null,
        public ?string $region = null,
        public ?string $last_video_period = null,
        public ?string $sort_key = null,
        public ?string $sort_direction = null,
    )
    {
    }

    public function getTheKey() {
        $vars = get_object_vars($this);
        $hashString = "";
        foreach ($vars as $key => $value) {
            if (!is_null($value)) {
                $hashString .= $key . "=" . $value;
            }
        }

        return $hashString;
    }
}
