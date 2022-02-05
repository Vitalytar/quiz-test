<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $questionApi = new Controllers\GetQuestion;
    $apiResponse = $questionApi->getQuestionFromApi();

    return view('welcome', [
        'quizData' => $apiResponse,
        'question' => $apiResponse['question']
    ]);
});

Route::get('getQuestion', 'GetQuestion@getQuestionFromApi');
Route::post('flushQuiz', [Controllers\FlushUserSession::class, 'execute'])->name('flushQuiz');
Route::post('SaveAnswerAndRetrieveNewQuestion', [Controllers\SaveAnswerAndRetrieveNewQuestion::class, 'execute'])
    ->name('save.and.retrieve');
Route::post('FinalQuizStats', [Controllers\FinalQuizStats::class, 'execute'])
->name('get.final.stats');
