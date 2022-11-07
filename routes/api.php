<?php


use App\Http\Controllers\Api\V1\Auth\NewPasswordController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\External\SearchController;
use App\Http\Controllers\Api\V1\User\BookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//    return $request->user();
//});


//real user application Apis
Route::group(['namespace' => 'Api\V1'], function () {
    //External Route
    Route::get('/external-books', [SearchController::class, 'search']);

    Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1'], function () {
        //Authentication Routes
        Route::group(['prefix' => 'auth'], function () {
            Route::post('register', [RegisteredUserController::class, 'store'])->middleware('guest');
            Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
            Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
            Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('guest');
            Route::post('reset-password', [NewPasswordController::class, 'store'])->middleware('guest');
        });
        //Internal books routes
        Route::group(['prefix' => 'books', 'middleware' => 'auth:sanctum'], function () {
            Route::get('/', [BookController::class, 'index']);
            Route::post('/', [BookController::class, 'store']);
            Route::get('/{id}', [BookController::class, 'show']);
            Route::patch('/{id}', [BookController::class, 'update']);
            Route::delete('/{id}', [BookController::class, 'destroy']);
        });

        Route::middleware(['auth:sanctum'])->group(function () {



        });

    });


        Route::fallback(function () {
        return response()->json(['message' => 'Page Not Found'], 404);
    });
});
