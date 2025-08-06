<?php

namespace App\Http\Controllers\API;

use App\DTO\RecommendationsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChannelsRequest;
use App\Http\Resources\Api\Channels\Recommendation;
use App\Services\ChannelsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChannelsController extends Controller
{
    public function __construct(
        private ChannelsService $channelsService
    )
    {

    }


    public function recommendations(ChannelsRequest $request) {
        $recommendationsDTO = new RecommendationsDTO(...$request->all());
        $timeStart = Carbon::now();
        $results = ['data' => json_decode(Cache::get($recommendationsDTO->getTheKey()), true)];
        $timeEnd = Carbon::now();
        Log::info('CACHE GET OPERATION DURATION: {diff} milliseconds', ['diff' => $timeStart->diffInMilliseconds($timeEnd)]);
        if (!$results['data']) {
            $records = $this->channelsService->findTheRecommendations($recommendationsDTO);
            $results = Recommendation::collection($records);
            $timeStart = Carbon::now();
            Cache::put($recommendationsDTO->getTheKey(), $results->toJson(), now()->addMinutes(10));
            $timeEnd = Carbon::now();
            Log::info('CACHE PUT OPERATION DURATION: {diff} milliseconds', ['diff' => $timeStart->diffInMilliseconds($timeEnd)]);
        }
        return $results;
    }
}
