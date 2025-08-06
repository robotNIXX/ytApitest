<?php

namespace App\Services;

use App\DTO\RecommendationsDTO;
use App\Enum\LastVideoPeriodEnum;
use App\Models\YoutubeChannel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use function Symfony\Component\String\b;

class ChannelsService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function findTheRecommendations(RecommendationsDTO $recommendationsDTO)
    {
        $where = [];
        $query = YoutubeChannel::query();
        if ($recommendationsDTO->category) {
            $where[] = [
                'category', '=', $recommendationsDTO->category,
            ];
        }
        if ($recommendationsDTO->language) {
            $where[] = [
                'language', '=', $recommendationsDTO->language,
            ];
        }
        if ($recommendationsDTO->region) {
            $where[] = [
                'region', '=', $recommendationsDTO->region,
            ];
        }
        if ($recommendationsDTO->max_subscribers) {
            $where[] = [
                'subscribers_count', '<=', $recommendationsDTO->max_subscribers,
            ];
        }
        if ($recommendationsDTO->min_subscribers) {
            $where[] = [
                'subscribers_count', '<=', $recommendationsDTO->min_subscribers,
            ];
        }
        if ($recommendationsDTO->last_video_period) {
            switch ($recommendationsDTO->last_video_period) {
                default:
                case LastVideoPeriodEnum::LAST_7_DAYS:
                    $selectedDate = Carbon::now()->subDays(7);
                    break;
                case LastVideoPeriodEnum::LAST_MONTH:
                    $selectedDate = Carbon::now()->subMonths(1);
                    break;
                case LastVideoPeriodEnum::LAST_YEAR:
                    $selectedDate = Carbon::now()->subYears(1);
                    break;
            }
            $where[] = [
                'last_video_published_at', '>=', $selectedDate,
            ];
        }
        if (count($where) > 0) {
            $query->where($where);
        }
        if ($recommendationsDTO->sort_key) {
            $query->orderBy($recommendationsDTO->sort_key, $recommendationsDTO->sort_direction);
        }

        $results = $query->get();

        return $results;
    }
}
