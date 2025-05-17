<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;

Route::get('/', [EventController::class, 'index'])->name('index');
Route::get('/create', [EventController::class, 'create'])->name('create');
Route::post('/create', [EventController::class, 'save'])->name('store'); // Points to 'save'
Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
Route::put('/events/{event}', [EventController::class, 'save'])->name('events.update'); // Points to 'save'
Route::get('/ical.ics', [EventController::class, 'ical'])->name('ical');
