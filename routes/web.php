<?php

use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/programs');

// 一覧画面
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');

// 詳細画面
Route::get('/programs/{pgm_uid}', [ProgramController::class, 'show'])->name('programs.show');
Route::post('/programs/{pgm_uid}', [ProgramController::class, 'interact'])->name('programs.interact');