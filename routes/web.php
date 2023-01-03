<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppUserController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\ClassRoomsController;

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
    return view('welcome');
})->name('welcome');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {

    //Route::resource('users', ManageUserController::class);

    Route::get('/users', [ManageUserController::class, 'index'])->name('usersIndex');
    Route::get('/users/{id}', [ManageUserController::class, 'detailUser'])->name('detailUser');

    Route::get('/adminhome', [AdminController::class, 'adminhome'])->name('adminhome');

    // 섹션 관리
    Route::get('/createSection/{class_room}', [SectionsController::class, 'createSection'])
        ->name('createSection');

    Route::post('/deleteSection/{id}', [SectionsController::class, 'deleteSection'])
        ->name('deleteSection');

    Route::post('/storeSection/{class_room}', [SectionsController::class, 'storeSection'])
        ->name('storeSection');

    Route::get('/editSection/{section}', [SectionsController::class, 'editSection'])
        ->name('editSection');

    Route::post('/updateSection/{section}', [SectionsController::class, 'updateSection'])
        ->name('updateSection');

    Route::get('/listSection', [SectionsController::class, 'listSection'])
        ->name('listSection');

    Route::get('/detailSection/{section}', [SectionsController::class, 'detailSection'])
        ->name('detailSection');

    Route::get('/scoreSection/{section}', [SectionsController::class, 'scoreSection'])
        ->name('scoreSection');

    // 문제 관리
    Route::get('/createQuestion/{section}', [QuestionsController::class, 'createQuestion'])
        ->name('createQuestion');

    Route::get('/detailQuestion/{question}', [QuestionsController::class, 'detailQuestion'])
        ->name('detailQuestion');

    Route::get('/editQuestion/{question}', [QuestionsController::class, 'editQuestion'])
        ->name('editQuestion');

    Route::post('/updateQuestion/{question}', [QuestionsController::class, 'updateQuestion'])
        ->name('updateQuestion');

    Route::post('/storeQuestion/{section}', [QuestionsController::class, 'storeQuestion'])
        ->name('storeQuestion');

    Route::post('/deleteQuestion/{id}', [QuestionsController::class, 'deleteQuestion'])
        ->name('deleteQuestion');

    Route::get('/scoreQuestion/{section}/{quiz_header}', [QuestionsController::class, 'scoreQuestion'])
        ->name('scoreQuestion');

    // ClassRoom 관리
    Route::get('/createClassRoom', [ClassRoomsController::class, 'createClassRoom'])
        ->name('createClassRoom');

    Route::post('/deleteClassRoom/{id}', [ClassRoomsController::class, 'deleteClassRoom'])
        ->name('deleteClassRoom');

    Route::post('/storeClassRoom/class', [ClassRoomsController::class, 'storeClassRoom'])
        ->name('storeClassRoom');

    Route::get('/editClassRoom/{class_room}', [ClassRoomsController::class, 'editClassRoom'])
        ->name('editClassRoom');

    Route::post('/updateClassRoom/{class_room}', [ClassRoomsController::class, 'updateClassRoom'])
        ->name('updateClassRoom');

    Route::get('/listClassRoom', [ClassRoomsController::class, 'listClassRoom'])
        ->name('listClassRoom');
    Route::get('/detailClassRoom/{class_room}', [ClassRoomsController::class, 'detailClassRoom'])
        ->name('detailClassRoom');
});

Route::middleware(['auth', 'verified', 'role:admin|user'])->prefix('appuser')->group(function () {

    Route::get('/userQuizHome', [AppUserController::class, 'userQuizHome'])
        ->name('userQuizHome');

    Route::get('/userQuizDetails/{id}', [AppUserController::class, 'userQuizDetails'])
        ->name('userQuizDetails');

    Route::post('/deleteUserQuiz/{id}', [AppUserController::class, 'deleteUserQuiz'])
        ->name('deleteUserQuiz');

    Route::get('/startQuiz', [AppUserController::class, 'startQuiz'])
        ->name('startQuiz');
});
