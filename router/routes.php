<?php

use App\Controllers\server\Test;
use App\Services\Router;

use App\Controllers\HomePage;
use App\Controllers\Legacy;
use App\Controllers\Authentication;
use App\Controllers\Calendar;

use App\Controllers\admin\Admin;

use App\Controllers\server\Calendar_Update;
use App\Controllers\server\Absences_Update;
use App\Controllers\server\Staff_Update;

// public
Router::page('/', [HomePage::class]);
Router::page('/legacy', [Legacy::class]);
Router::page('/login', [Authentication::class, 'login']);
Router::page('/login/google', [Authentication::class, 'login_google']);
Router::page('/register', [Authentication::class, 'register']);
Router::page('/calendar', [Calendar::class]);

// admin
Router::page('/admin', [Admin::class]);

// server
Router::page('/server/calendar_update', [Calendar_Update::class]);
Router::page('/server/absences_update', [Absences_Update::class]);
Router::page('/server/staff_update', [Staff_Update::class]);

// maints
Router::page('/test', [Test::class]);

Router::enable();