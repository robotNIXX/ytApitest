<?php

namespace Database\Seeders;

use App\Models\YoutubeChannel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class YoutubeChannelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            YoutubeChannel::truncate();
            $dataFile = fopen(Storage::disk('local')->path('youtube_channels.csv'), 'r');
            $index = 0;
            while ($row = fgetcsv($dataFile, null, ',')) {
                if ($index > 0) {
                    YoutubeChannel::factory()->create([
                        'id' => $row[0],
                        'title' => $row[1],
                        'category' => $row[2],
                        'subscribers_count' => $row[3],
                        'average_views' => $row[4],
                        'engagement_rate' => $row[5],
                        'language' => $row[6],
                        'region' => $row[7],
                        'last_video_published_at' => $row[8],
                    ]);
                }
                $index++;
            }
            fclose($dataFile);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
}
