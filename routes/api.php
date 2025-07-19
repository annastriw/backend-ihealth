<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DMController;
use App\Http\Controllers\DiscussionCommentAnswerController;
use App\Http\Controllers\DiscussionCommentController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\HTController;
use App\Http\Controllers\ModuleContentController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PatientController; // Tambahkan import ini
use App\Http\Controllers\PersonalInformationController;
use App\Http\Controllers\PostTestController;
use App\Http\Controllers\PreTestController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionSetController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\SubModuleController;
use App\Http\Controllers\UserAnswerPostTestController;
use App\Http\Controllers\UserAnswerPreTestController;
use App\Http\Controllers\UserAnswerScreeningController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserHistoryPostTestController;
use App\Http\Controllers\UserHistoryPreTestController;
use App\Http\Controllers\UserHistoryScreeningController;
use App\Http\Controllers\ScreeningScoringController;
use App\Http\Controllers\UserAnswerScreeningScoringController;
use App\Http\Controllers\UserHistoryScreeningScoringController;
use App\Http\Controllers\UserModuleContentOpenController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/auth/get-auth', [AuthController::class, 'getAuth']);
    Route::put('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::put('/auth/update-account', [AuthController::class, 'updateAccount']);
    Route::get('/auth/location', [UserController::class, 'getLocation']);
    Route::put('/auth/update-location', [UserController::class, 'updateLocation']);

    // Get FAQ for users
    Route::get('/faqs/{id}', [FAQController::class, 'show']);
    Route::get('/faqs', [FAQController::class, 'index']);

    // Get medical personal users
    Route::get('/users/medical-personal', [UserController::class, 'getMedicalPersonals']);

    Route::get('/modules', [ModuleController::class, 'index']);
    Route::get('/modules/users', [ModuleController::class, 'getAllModules']);
    Route::get('/modules/users', [ModuleController::class, 'getByType']);
    Route::get('/modules/users', [ModuleController::class, 'users']);

    Route::get('/modules/type', [ModuleController::class, 'getByType']);
    Route::get('/modules/{id}', [ModuleController::class, 'show']);

    Route::get('/discussion', [DiscussionController::class, 'index']);
    Route::post('/discussion', [DiscussionController::class, 'store']);
    Route::get('/discussion/private', [DiscussionController::class, 'showPrivateDiscussions']);
    Route::get('/discussion/{id}', [DiscussionController::class, 'show']);

    // Discussion Comment routes
    Route::get('/discussion/comment/me', [DiscussionCommentController::class, 'getMyDiscussionComments']);
    Route::get('/discussion/comment/{id}', [DiscussionCommentController::class, 'getByDiscussionId']);
    Route::get('/discussion/comment/detail/{id}', [DiscussionCommentController::class, 'show']);
    Route::post('/discussion/comment', [DiscussionCommentController::class, 'store']);
    Route::put('/discussion/comment/{id}', [DiscussionCommentController::class, 'update']);
    Route::delete('/discussion/comment/{id}', [DiscussionCommentController::class, 'destroy']);

    // Discussion Comment Answer routes
    Route::prefix('discussion/comment/answer')->middleware('auth:sanctum')->group(function () {
        Route::get('/{discussion_comment_id}', [DiscussionCommentAnswerController::class, 'getByCommentId']);
        Route::post('/', [DiscussionCommentAnswerController::class, 'store']);
        Route::delete('/{id}', [DiscussionCommentAnswerController::class, 'destroy']);
    });

    Route::get('/sub-modules', [SubModuleController::class, 'index']);
    Route::get('/sub-modules/{id}', [SubModuleController::class, 'show']);
    Route::get('/sub-modules/category/{module_id}', [SubModuleController::class, 'getByModule']);

    // Module content public routes (read access)
    Route::get('/module-content', [ModuleContentController::class, 'index']);
    Route::get('/module-content/{id}', [ModuleContentController::class, 'show']);
    Route::get('/module-content/sub/{sub_module_id}', [ModuleContentController::class, 'getBySubModule']);

    Route::post('/module-content/{id}/opened', [ModuleContentController::class, 'markAsOpened']);


    // History screening public routes (read access)
    Route::get('/screening/history', [UserHistoryScreeningController::class, 'index']);
    Route::get('/screening/history/{id}', [UserHistoryScreeningController::class, 'show']);

    // Screening public routes (read access)
    Route::get('/screening', [ScreeningController::class, 'index']);
    Route::get('/screening/{id}', [ScreeningController::class, 'show']);

    // Submit screening routes
    Route::post('/screening/submit', [UserAnswerScreeningController::class, 'submit']);

    Route::get('/screening-scorings/history', [UserHistoryScreeningScoringController::class, 'index']);
    Route::get('/screening-scorings/history/{id}', [UserHistoryScreeningScoringController::class, 'show']);
    Route::post('/screening-scorings/submit', [UserAnswerScreeningScoringController::class, 'submit']);

    Route::get('/screening-scorings', [ScreeningScoringController::class, 'index']);
    Route::get('/screening-scorings/{id}', [ScreeningScoringController::class, 'show']);

    // History pre test public routes (read access)
    Route::get('/pre-test/history', [UserHistoryPreTestController::class, 'index']);
    Route::get('/pre-test/history/{id}', [UserHistoryPreTestController::class, 'show']);

    // Pre Test public routes (read access)
    Route::get('/pre-test', [PreTestController::class, 'index']);
    Route::get('/pre-test/{id}', [PreTestController::class, 'show']);
    Route::get('/pre-test/sub/{sub_module_id}', [PreTestController::class, 'getBySubModule']);

    // Submit pretest routes
    Route::post('/pre-test/submit', [UserAnswerPreTestController::class, 'submit']);

    // History post test public routes (read access)
    Route::get('/post-test/history', [UserHistoryPostTestController::class, 'index']);
    Route::get('/post-test/history/{id}', [UserHistoryPostTestController::class, 'show']);

    // Post Test public routes (read access)
    Route::get('/post-test', [PreTestController::class, 'index']);
    Route::get('/post-test/{id}', [PostTestController::class, 'show']);

    // Post test public routes (read access)
    Route::get('/post-test', [PostTestController::class, 'index']);
    Route::get('/post-test/{id}', [PostTestController::class, 'show']);
    Route::get('/post-test/sub/{sub_module_id}', [PostTestController::class, 'getBySubModule']);

    // Submit post test routes
    Route::post('/post-test/submit', [UserAnswerPostTestController::class, 'submit']);

    // Question Set public routes (read access)
    Route::get('/question-set', [QuestionSetController::class, 'index']);
    Route::get('/question-set/{id}', [QuestionSetController::class, 'show']);

    // Question public routes (read access)
    Route::get('/question', [QuestionController::class, 'index']);
    Route::get('/question/{id}', [QuestionController::class, 'show']);

    // Personal information routes
    Route::prefix('personal')->group(function () {
        Route::post('/', [PersonalInformationController::class, 'store']);

        // Get personal information of the authenticated userlogin
        Route::get('/me', [PersonalInformationController::class, 'showAuthenticatedUserPersonalInformation']);

        // Check if authenticated user has personal information
        Route::get('/check', [PersonalInformationController::class, 'checkUserPersonalInformation']);

        Route::get('/{id}', [PersonalInformationController::class, 'show']);
        Route::put('/', [PersonalInformationController::class, 'update']);
        Route::get('/user/{user_id}', [PersonalInformationController::class, 'getPersonalInformationByUserId']);
    });

    Route::post('/users/location', [UserController::class, 'storeLocation']);
    Route::get('/users/location/check', [UserController::class, 'checkUserLocation']);
    Route::get('/users/location/maps', [UserController::class, 'getAllUsersLocationInfo']);

    Route::middleware(['role:admin'])->group(function () {

        Route::get('/admin/location/{id}', [UserController::class, 'getUserLocationById']);
        // FAQ admin routes
        Route::post('/faqs', [FAQController::class, 'store']);
        Route::put('/faqs/{id}', [FAQController::class, 'update']);
        Route::delete('/faqs/{id}', [FAQController::class, 'destroy']);

        // Module admin routes
        Route::post('/modules', [ModuleController::class, 'store']);
        Route::put('/modules/{id}', [ModuleController::class, 'update']);
        Route::delete('/modules/{id}', [ModuleController::class, 'destroy']);

        // Discussion admin routes
        Route::get('/discussion/admin/{id}', [DiscussionController::class, 'showForAdmin']);
        Route::put('/discussion/{id}', [DiscussionController::class, 'update']);
        Route::delete('/discussion/{id}', [DiscussionController::class, 'destroy']);

        // Sub Module admin routes
        Route::post('/sub-modules', [SubModuleController::class, 'store']);
        Route::put('/sub-modules/{id}', [SubModuleController::class, 'update']);
        Route::delete('/sub-modules/{id}', [SubModuleController::class, 'destroy']);

        // Module content admin routes
        Route::post('/module-content', [ModuleContentController::class, 'store']);
        Route::put('/module-content/{id}', [ModuleContentController::class, 'update']);
        Route::delete('/module-content/{id}', [ModuleContentController::class, 'destroy']);

        // Screening admin routes
        Route::post('/screening', [ScreeningController::class, 'store']);
        Route::put('/screening/{id}', [ScreeningController::class, 'update']);
        Route::delete('/screening/{id}', [ScreeningController::class, 'destroy']);

        Route::post('/screening-scorings', [ScreeningScoringController::class, 'store']);
        Route::put('/screening-scorings/{id}', [ScreeningScoringController::class, 'update']);
        Route::delete('/screening-scorings/{id}', [ScreeningScoringController::class, 'destroy']);

        // Pre Test admin routes
        Route::post('/pre-test', [PreTestController::class, 'store']);
        Route::put('/pre-test/{id}', [PreTestController::class, 'update']);
        Route::delete('/pre-test/{id}', [PreTestController::class, 'destroy']);

        // Post Test admin routes
        Route::post('/post-test', [PostTestController::class, 'store']);
        Route::put('/post-test/{id}', [PostTestController::class, 'update']);
        Route::delete('/post-test/{id}', [PostTestController::class, 'destroy']);

        // Question Set admin routes
        Route::post('/question-set', [QuestionSetController::class, 'store']);
        Route::put('/question-set/{id}', [QuestionSetController::class, 'update']);
        Route::delete('/question-set/{id}', [QuestionSetController::class, 'destroy']);

        // Question admin routes
        Route::post('/question', [QuestionController::class, 'store']);
        Route::put('/question/{id}', [QuestionController::class, 'update']);
        Route::get('/question/{id}', [QuestionController::class, 'show']);
        Route::delete('/question/{id}', [QuestionController::class, 'destroy']);

        // Personal information routes
        Route::prefix('personal')->group(function () {
            Route::get('/', [PersonalInformationController::class, 'index']);
            Route::delete('/{id}', [PersonalInformationController::class, 'destroy']);
        });

        Route::prefix('history')->group(function () {
            Route::get('/screening', [UserHistoryScreeningController::class, 'getAllHistory']);
            Route::get('/screening/users/{screeningId}', [UserHistoryScreeningController::class, 'getByScreeningId']);
            Route::delete('/screening/users/history/{id}', [UserHistoryScreeningController::class, 'destroy']);
            Route::get('/pre-test', [UserHistoryPreTestController::class, 'getAllHistory']);
            Route::get('/pre-test/users/{preTestId}', [UserHistoryPreTestController::class, 'getByPreTestId']);
            Route::delete('/pre-test/users/history/{id}', [UserHistoryPreTestController::class, 'destroy']);
            Route::get('/post-test', [UserHistoryPostTestController::class, 'getAllHistory']);
            Route::get('/post-test/users/{postTestId}', [UserHistoryPostTestController::class, 'getByPostTestId']);
            Route::delete('/post-test/users/history/{id}', [UserHistoryPostTestController::class, 'destroy']);
            Route::get('/screening-scorings', [UserHistoryScreeningScoringController::class, 'getAllHistory']);
            Route::get('/screening-scorings/users/{screeningScoringId}', [UserHistoryScreeningScoringController::class, 'getByScreeningId']);
            Route::delete('/screening-scorings/users/history/{id}', [UserHistoryScreeningScoringController::class, 'destroy']);
        });

        // Users admin routes
        Route::apiResource('users', UserController::class);

        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
    });
});