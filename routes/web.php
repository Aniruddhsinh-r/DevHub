<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/invitation/{email}', 'livewirecomponent.invitation')->name('invitation');