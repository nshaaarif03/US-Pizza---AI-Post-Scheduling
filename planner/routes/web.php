<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SocialMediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
Route::post('/campaigns', [CampaignController::class, 'store'])->middleware('throttle:5,1')->name('campaigns.store');
Route::resource('posts', PostController::class)->except(['create', 'store']);
Route::post('/posts/{post}/approve', [PostController::class, 'approve'])->name('posts.approve');
Route::post('/posts/{post}/mark-posted', [PostController::class, 'markPosted'])->name('posts.markPosted');
Route::post('/posts/{post}/schedule', [ScheduleController::class, 'store'])->name('posts.schedule');
Route::delete('/posts/{post}/schedule', [ScheduleController::class, 'cancel'])->name('posts.cancelSchedule');
Route::post('/posts/{post}/platform', [SocialMediaController::class, 'store'])->name('posts.platform');
Route::get('/calendar', CalendarController::class)->name('calendar');
Route::get('/scheduled', [ScheduleController::class, 'index'])->name('scheduled.index');
Route::get('/settings', SettingsController::class)->name('settings');
