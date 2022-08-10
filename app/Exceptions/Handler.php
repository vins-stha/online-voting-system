<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\FlareClient\Http\Exceptions\NotFound;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }



    public function render($request, Throwable $e)
    {
        if($e instanceof NotFoundResourceException)
        {
            return response()->json([
                'data' => $e->getMessage(),
                'code'=> 404
            ], 404);
        }

        if($e instanceof DuplicateDataException)
        {
            return response()->json([
                'data' => $e->getMessage(),
                'code'=> 400
            ], 400);
        }



        return parent::render($request, $e);
    }
    // public function renders($request, Exception $e)
    // {
    //     if($e instanceof NotFoundResourceException)
    //     {
    //         return response()->json([
    //             'data' => "Not found",
    //             'code'=> 404
    //         ], 404);
    //     }

    //     return parent::render($request, $e);
    // }

}
