<?php

use App\Filament\App\Pages\InvitationRegister;
use Illuminate\Support\Facades\Route;

Route::get('/invitation/{token}', InvitationRegister::class)->name('invitation-register')->middleware('signed');
Route::fallback(function () {abort(404);});
