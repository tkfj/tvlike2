<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ADLController;

Route::redirect('/', '/programs');

Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');

Route::get('/programs/{id}', [ProgramController::class, 'show'])->name('programs.show');
Route::post('/programs/{id}', [ProgramController::class, 'interact'])->name('programs.interact');

Route::get('/settings', [SettingController::class, 'chars'])->name('settings.chars');
Route::post('/settings', [SettingController::class, 'charsUpdate'])->name('settings.charsUpdate');

Route::get('/adl', [ADLController::class, 'adl'])->name('adl.adl');
Route::post('/adl', [ADLController::class, 'adlUpdate'])->name('adl.adlUpdate');
