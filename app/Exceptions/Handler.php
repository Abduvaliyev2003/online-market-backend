<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        if (! config('app.debug'))
                return;
        $this->reportable(function (Throwable $e) {
            Http::withOptions(['verify' => false])->post('https://api.telegram.org/bot5918560165:AAFqSTYREDZQT5aYwdd27ooquige0I7c0sU/sendMessage', [
                'chat_id' => '-991150848',
                'text' => "title: " . $e->getMessage() . "\n\nFile: " . $e->getFile() . "\nLine: " . $e->getLine()
            ]);
        });
        $this->renderable(function (NotFoundHttpException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        });
    }
}
