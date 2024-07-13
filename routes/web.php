<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\homeController;
use App\Http\Middleware\AuthCheck;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'account'], function() {

    // Route::group(['middleware' => 'guest'], function() {
        Route::get('/login', [AccountController::class, 'login'])->name('account.login');
        Route::get('/register', [AccountController::class, 'registration'])->name('account.registration');
        Route::post('/process-register', [AccountController::class, 'processRegistration'])->name('account.processRegistration');
        Route::post('/authenticate', [AccountController::class, 'authenticate'])->name('account.authenticate'); 
        
        
    // });

    Route::group(['middleware' => AuthCheck::class], function() {
        Route::get('/authUser', [AccountController::class, 'authUser'])->name('account.authUser');
        Route::put('/account/update-profile',[AccountController::class,'updateProfile'])->name('account.updateProfile');
        Route::get('/account/logout',[AccountController::class,'logout'])->name('account.logout');
        Route::get('/create-job', [AccountController::class, 'createJob'])->name('account.createJob');
        Route::post('/save-job', [AccountController::class, 'saveJobs'])->name('account.saveJobs');
        Route::get('/my-job', [AccountController::class, 'myJobs'])->name('account.myJobs');
    });
        Route::post('/account/updateProfile-pic',[AccountController::class,'updateProfilePic'])->name('account.updateProfilePic');

});
