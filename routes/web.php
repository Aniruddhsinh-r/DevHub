<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {return redirect('/home');})->name('/');
// Route::livewire('/invitation/expire', 'livewirecomponent.pages.already-exist')->name('invite.exist');
Route::livewire('/invitation/{email}', 'livewirecomponent.invitation')->name('invitation');