<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;
use Throwable;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'exception',
        'file',
        'line',
        'message',
        'action_name',
        'user_id',
        'request_log_id',
        'trace',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requestLog()
    {
        return $this->belongsTo(RequestLog::class, 'request_log_id');
    }

    public static function store(
        string|null $exeption, 
        string|null $file,
        string|null $line,
        string|null $message,
        string|null $action_name,
        int|null $user_id,
        int|null $request_log_id,
        string|null $trace,
    ): ErrorLog
    {
        return self::create([
            'exception' => $exeption,
            'file' => $file,
            'line' => $line,
            'message' => $message,
            'action_name' => $action_name,
            'user_id' => $user_id,
            'request_log_id' => $request_log_id,
            'trace' => $trace,
        ]);
    }

    public static function storeExceptionError(Exception $e): ErrorLog
    {
        return self::store(
            'ServerError',
            $e->getFile(),
            $e->getLine(),
            "The error cannot be created by the error handler. \nOriginal message: ".$e->getMessage(),
            'ErrorHandler@report',
            request()?->user()?->id,
            Context::get('request_log_id', null),
            $e?->getTraceAsString(),
        );
    }

    public static function getErrorResponse(Throwable $e): array
    {
        return config('app.debug') ? [
            'success' => false,
            'message' => $e->getMessage(),
            'exception' => get_class($e),
        ] : [
            'success' => false,
            'message' => $e->getMessage(),
        ];
    }
}