<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppUserController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\ClassRoomsController;
use App\Http\Controllers\MajorGroupController;
use App\Http\Controllers\MediumGroupController;
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

    Route::get('/home', [AdminController::class, 'adminhome'])->name('adminhome');

    // 섹션 관리
    Route::get('/classroom/{class_room}/section/new', [SectionsController::class, 'createSection'])
        ->name('createSection');

    Route::post('/section/{id}/delete', [SectionsController::class, 'deleteSection'])
        ->name('deleteSection');

    Route::post('/classroom/{class_room}/section/new', [SectionsController::class, 'storeSection'])
        ->name('storeSection');

    Route::get('/section/{section}/edit', [SectionsController::class, 'editSection'])
        ->name('editSection');

    Route::post('/section/{section}/edit', [SectionsController::class, 'updateSection'])
        ->name('updateSection');

    Route::get('/sections', [SectionsController::class, 'listSection'])
        ->name('listSection');

    Route::get('/section/{section}', [SectionsController::class, 'detailSection'])
        ->name('detailSection');

    Route::get('/section/{section}/score', [SectionsController::class, 'scoreSection'])
        ->name('scoreSection');

    // 문제 관리
    Route::get('/section/{section}/question/new', [QuestionsController::class, 'createQuestion'])
        ->name('createQuestion');

    Route::post('/section/{section}/question/new', [QuestionsController::class, 'storeQuestion'])
        ->name('storeQuestion');

    Route::get('/question/{question}', [QuestionsController::class, 'detailQuestion'])
        ->name('detailQuestion');

    Route::get('/question/{question}/edit', [QuestionsController::class, 'editQuestion'])
        ->name('editQuestion');

    Route::post('/question/{question}/edit', [QuestionsController::class, 'updateQuestion'])
        ->name('updateQuestion');

    Route::post('/question/{id}/delete', [QuestionsController::class, 'deleteQuestion'])
        ->name('deleteQuestion');

    Route::get('/section/{section}/question/omrsheet', [QuestionsController::class, 'createOMRSheet'])
        ->name('createOMRSheet');

    Route::get('/section/{section}/question/answersheet', [QuestionsController::class, 'createAnswerSheet'])
        ->name('createAnswerSheet');

    Route::get('/section/{section}/question/listening', [QuestionsController::class, 'createListeningTest'])
        ->name('createListeningTest');

    Route::get('/section/{section}/question/{quiz_header}/score', [QuestionsController::class, 'scoreQuestion'])
        ->name('scoreQuestion');

    // ClassRoom 관리
    Route::get('/mediumgroup/{medium_group}/classroom/new', [ClassRoomsController::class, 'createClassRoom'])
        ->name('createClassRoomWithMediumGroup');

    Route::get('/classroom/new', [ClassRoomsController::class, 'create'])
        ->name('createClassRoom');

    Route::post('/mediumgroup/{medium_group}/classroom', [ClassRoomsController::class, 'storeWithMediumGroup'])
        ->name('storeClassRoomWithMediumGroup');

    Route::post('/classroom/new', [ClassRoomsController::class, 'store'])
        ->name('storeClassRoom');

    Route::post('/classroom/{id}/delete', [ClassRoomsController::class, 'deleteClassRoom'])
        ->name('deleteClassRoom');

    Route::get('/classroom/{class_room}/edit', [ClassRoomsController::class, 'editClassRoom'])
        ->name('editClassRoom');

    Route::post('/classroom/{class_room}/edit', [ClassRoomsController::class, 'updateClassRoom'])
        ->name('updateClassRoom');

    Route::get('/classrooms', [ClassRoomsController::class, 'listClassRoom'])
        ->name('listClassRoom');

    Route::get('/classroom/{class_room}', [ClassRoomsController::class, 'detailClassRoom'])
        ->name('detailClassRoom');

    // MajorGroup 관리
    Route::get('/majorgroup/new', [MajorGroupController::class, 'create'])
        ->name('createMajorGroup');

    Route::post('/majorgroup/{id}/delete', [MajorGroupController::class, 'delete'])
        ->name('deleteMajorGroup');

    Route::post('/majorgroup/new', [MajorGroupController::class, 'store'])
        ->name('storeMajorGroup');

    Route::get('/majorgroup/{major_group}/edit', [MajorGroupController::class, 'edit'])
        ->name('editMajorGroup');

    Route::post('/majorgroup/{major_group}/edit', [MajorGroupController::class, 'update'])
        ->name('updateMajorGroup');

    Route::get('/majorgroups', [MajorGroupController::class, 'list'])
        ->name('listMajorGroups');

    Route::get('/majorgroup/{major_group}', [MajorGroupController::class, 'detail'])
        ->name('detailMajorGroup');

    // MediumGroup 관리
    Route::get('/majorgroup/{major_group}/mediumgroup/new', [MediumGroupController::class, 'createWithMajorGroup'])
        ->name('createMediumGroupWithMajorGroup');

    Route::post('/majorgroup/{major_group}/mediumgroup/', [MediumGroupController::class, 'storeWithMajorGroup'])
        ->name('storeMediumGroupWithMajorGroup');

    Route::get('/mediumgroup/new', [MediumGroupController::class, 'create'])
        ->name('createMediumGroup');
    Route::post('/mediumgroup/new', [MediumGroupController::class, 'store'])
        ->name('storeMediumGroup');

    Route::post('/mediumgroup/{id}/delete', [MediumGroupController::class, 'delete'])
        ->name('deleteMediumGroup');


    Route::get('/mediumgroup/{medium_group}/edit', [MediumGroupController::class, 'edit'])
        ->name('editMediumGroup');

    Route::post('/mediumgroup/{medium_group}/update', [MediumGroupController::class, 'update'])
        ->name('updateMediumGroup');

    Route::get('/mediumgroups', [MediumGroupController::class, 'list'])
        ->name('listMediumGroups');
    Route::get('/mediumgroup/{medium_group}/', [MediumGroupController::class, 'detail'])
        ->name('detailMediumGroup');
});

Route::middleware(['auth', 'verified', 'role:admin|user'])->prefix('appuser')->group(function () {

    Route::get('/quiz/home', [AppUserController::class, 'userQuizHome'])
        ->name('userQuizHome');

    Route::get('/quiz/{id}', [AppUserController::class, 'userQuizDetails'])
        ->name('userQuizDetails');

    Route::post('/quiz/{id}/delete', [AppUserController::class, 'deleteUserQuiz'])
        ->name('deleteUserQuiz');

});

Route::middleware(['auth', 'verified', 'role:admin|user'])->prefix('quiz')->group(function () {
    Route::get('/start/major-{major_group?}/medium-{medium_group?}/class-{class_room?}', [AppUserController::class, 'startQuiz'])
        ->name('startQuizWithClassRoom');
    Route::get('/start/major-{major_group?}/medium-{medium_group?}', [AppUserController::class, 'startQuiz'])
        ->name('startQuizWithMedium');
    Route::get('/start/major-{major_group?}', [AppUserController::class, 'startQuiz'])
        ->name('startQuizWithMajor');
    Route::get('/start', [AppUserController::class, 'startQuiz'])
        ->name('startQuiz');

});
