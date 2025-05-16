<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;

Route::get('/', [EventController::class, 'index'])->name('index');
Route::get('/create', [EventController::class, 'create'])->name('create');
Route::post('/create', [EventController::class, 'store'])->name('store');
Route::get('/ical.ics', [EventController::class, 'ical'])->name('index');
