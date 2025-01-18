<?php

namespace App\Exceptions;

use App\Models\ErrorLog;
use Exception;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Context;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class BaseException extends Exception
{
    private const DEFAULT_EXCEPTION_CODE = 557731;

    protected $dontReport = [
        //AuthException::class
    ];

    /**
     * CustomException constructor.
     *
     * @param  string  $message  Message of the exception
     * @param  int  $code  Code of the exception
     * @param  array|null  $payload  Optional exception payload
     */
    public function __construct(
        string $message = '',
        public int $statusCode = 500,
        public ?array $payload = null
    ) {
        parent::__construct($message, $statusCode);
    }

    public function report(): void
    {
        $currentAction = Route::currentRouteAction();
        $action = '';
        if ($currentAction !== null) {
            $action = explode('@', $currentAction)[1] ?? 'nova';
        }
        $friendlyName = (new \ReflectionClass($this))->getShortName();

        if ($this->shouldReport($this)) {
            try {
                    ErrorLog::store($friendlyName, $this->getFile(), $this->getLine(), $this->getMessage(),
                    $action,
                    request()?->user()?->id,
                    Context::get('request_log_id', null),
                    json_encode($this?->getTrace()),
                );  
            } catch (\Exception $exception) {
                    ErrorLog::create([
                    'exception' => 'ServerError',
                    'file' => $exception?->getFile(),
                    'line' => $exception?->getLine(),
                    'message' => "The error cannot be created by the error handler. \nOriginal message: ".$exception->getMessage(),
                    'action_name' => 'ErrorHandler@report',
                    'user_id' =>  request()?->user()?->id ?? null,
                    'request_log_id' => Context::get('request_log_id', null),
                    'trace' => $this?->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Indicates if the exception should be reported.
     *
     * @param  Throwable  $e
     * @return bool
     */
    public function shouldReport(Throwable $e): bool
    {
        return ! in_array($e::class, $this->dontReport);
    }

    public function render(Request $request): Response
    {
        return response(ErrorLog::getErrorResponse($this));
    }
}
