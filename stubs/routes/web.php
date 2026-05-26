<?php

use Illuminate\Support\Facades\Auth;

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
]);