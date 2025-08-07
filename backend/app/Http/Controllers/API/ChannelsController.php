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
    ) {}


    /**
     * @OA\Get(
     *     path="/api/recommendations",
     *     summary="Get recommendations",
     *     tags={"Channels"},
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="min_subscribers",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),   
     *     @OA\Parameter(
     *         name="max_subscribers",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ), 
     *     @OA\Parameter(
     *         name="region",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="last_video_period",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_key",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function recommendations(ChannelsRequest $request)
    {
        $recommendationsDTO = new RecommendationsDTO(...$request->all());
        $timeStart = Carbon::now();
        $results = ['data' => json_decode(Cache::get($recommendationsDTO->getTheKey()), true)];
        $timeEnd = Carbon::now();
        Log::channel('db')->info('CACHE GET OPERATION DURATION: {diff} milliseconds', ['diff' => $timeStart->diffInMilliseconds($timeEnd)]);
        if (!$results['data']) {
            $records = $this->channelsService->findTheRecommendations($recommendationsDTO);
            $results = Recommendation::collection($records);
            $timeStart = Carbon::now();
            Cache::put($recommendationsDTO->getTheKey(), $results->toJson(), now()->addMinutes(10));
            $timeEnd = Carbon::now();
            Log::channel('db')->info('CACHE PUT OPERATION DURATION: {diff} milliseconds', ['diff' => $timeStart->diffInMilliseconds($timeEnd)]);
        }
        return $results;
    }
}
