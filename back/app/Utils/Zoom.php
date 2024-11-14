<?php

namespace App\Utils;

use App\Models\Lesson;
use Exception;
use Illuminate\Support\Facades\Http;

class Zoom
{
    public static function createMeeting(Lesson $lesson)
    {
        $accessToken = self::getAccessToken();
        $userId = $lesson->cabinet->getZoomUserId();
        if ($userId === null) {
            throw new Exception('Cannot get ZoomUserId for cabinet ' . $lesson->cabinet->value);
        }

        $topic = join(' ', [
            $lesson->date_time->format('H:i'),
            $lesson->teacher->last_name,
            $lesson->group->program->getName(),
            'ГР-' . $lesson->group_id
        ]);

        // Now you can use the $accessToken in your Zoom API requests
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://api.zoom.us/v2/users/$userId/meetings", [
            'default_password' => null,
            'duration' => $lesson->group->program->getDuration() + 10, // 10 минут запас
            'password' => str()->random(6),
            'start_time' => $lesson->date_time->format('Y-m-d\TH:i:s'),
            'timezone' => 'Europe/Moscow',
            'topic' => $topic,
            'agenda' => $lesson->topic ?? $topic, // детальное описание
            'type' => 2,
//            'settings' => [
//                'host_video' => true,
//                'participant_video' => true,
//                'join_before_host' => true,
//                'mute_upon_entry' => false,
//            ],
        ]);

        if (!$response->successful()) {
            throw new Exception('Error creating meeting:' . $response->body());
        }

        return $response->json();
    }

    /**
     * Удобно для получение соответствий кабинет–userId
     *
     * App\Enums\Cabinet::getZoomUserId()
     */
    public static function getUsers()
    {
        $accessToken = self::getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->get('https://api.zoom.us/v2/users', [
            'page_size' => 1000
        ]);

        return $response->json('users');
    }

    private static function getAccessToken()
    {
        return cache()->remember('zoom_access_token', now()->addHour(), function () {
            // Encode Client ID and Client Secret
            $encodedCredentials = base64_encode(join(':', [
                config('zoom.client_id'),
                config('zoom.client_secret')
            ]));

            // Send the request to get the token
            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . $encodedCredentials,
            ])->asForm()->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => config('zoom.account_id')
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            } else {
                // Handle the error appropriately
                throw new Exception('Error fetching Zoom access token: ' . $response->body());
            }
        });
    }
}