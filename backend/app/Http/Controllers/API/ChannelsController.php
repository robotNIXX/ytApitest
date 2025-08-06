<?php

namespace App\Http\Controllers\API;

use App\DTO\RecommendationsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChannelsRequest;
use App\Http\Resources\Api\Channels\Recommendation;
use App\Services\ChannelsService;
use Illuminate\Support\Facades\Cache;

class ChannelsController extends Controller
{
    public function __construct(
        private ChannelsService $channelsService
    )
    {

    }


    public function recommendations(ChannelsRequest $request) {
        $recommendationsDTO = new RecommendationsDTO(...$request->all());
        $results = ['data' => json_decode(Cache::get($recommendationsDTO->getTheKey()), true)];
        if (!$results['data']) {
            $records = $this->channelsService->findTheRecommendations($recommendationsDTO);
            $results = Recommendation::collection($records);
            Cache::put($recommendationsDTO->getTheKey(), $results->toJson(), now()->addMinutes(10));
        }
        return $results;
    }
}
