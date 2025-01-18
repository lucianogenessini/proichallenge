<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasFactory;

    public const LOGIN_URI = 'auth.login';
    public const URIS_TO_EXCLUDE = [
        'notifications.list'
    ];

    protected $fillable = [
        'uri',
        'friendly_name',
        'method',
        'body',
        'endpoint',
        'user_id',
        'user_email',
        'user_full_name',
        'response_status',
        'user_agent',
    ];

    public static function createOrUpdate(
        int $request_log_id,
        string|null $uri,
        string|null $friendly_name,
        string|null $method,
        string|null $body,
        string|null $endpoint,
        int|null $response_status,
        string|null $user_agent,
        User|null $user,
    ): RequestLog
    {
        if (self::isLoginRequest($friendly_name, $response_status)) {
            $body = json_decode($body);
            $user = User::findByEmail($body->email);
            $body = json_encode($body);
        }

        $request_log = self::updateOrCreate(
            [
                'id' => $request_log_id,
            ],
            [
                'uri' => $uri,
                'friendly_name' => $friendly_name,
                'method' => $method,
                'body' => $body,
                'endpoint' => $endpoint,
                'response_status' => $response_status,
                'user_agent' => $user_agent,
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'user_full_name' => $user?->name." ".$user?->last_name,
        ]);

        return $request_log;
    }

    public static function isLoginRequest(string|null $friendly_name, int|null $response_status): bool
    {
        if ($friendly_name != self::LOGIN_URI) {
            return false;
        }

        if ($response_status != 200) {
            return false;
        }

        return true;
    }

    public static function isExcluded(string|null $uri): bool
    {
        if (is_null($uri)){
            return false;
        }
        return in_array($uri, self::URIS_TO_EXCLUDE) || str_contains($uri, 'list.');
    }
}