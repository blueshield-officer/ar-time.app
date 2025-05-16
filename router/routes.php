<?php

use App\Controllers\Absences;
use App\Controllers\Authentication;
use App\Controllers\Calendar;
use App\Controllers\Countdowns;
use App\Controllers\Development;
use App\Controllers\Home;
use App\Controllers\Legacy;
use App\Controllers\Notifications;
use App\Controllers\Schedules;
use App\Controllers\server\AbsencesUpdate;
use App\Controllers\server\CalendarUpdate;
use App\Controllers\server\RedisUpdate;
use App\Controllers\server\ScheduleDefault;
use App\Controllers\server\StaffUpdate;
use App\Controllers\services\Auth;
use App\Controllers\services\ServerFetch;
use App\Controllers\services\ServerRequest;
use App\Controllers\Settings;
use App\Services\Router;


// public
Router::page('/', [Home::class], 'none');
Router::page('/legacy', [Legacy::class], 'none');
Router::page('/{dateMDY}', [Home::class], 'none');
Router::page('/home', [Home::class], 'none');
Router::page('/home/{dateMDY}', [Home::class], 'none');
Router::page('/absences', [Absences::class], 'none');
Router::page('/absences/{dateMDY}', [Absences::class], 'none');
Router::page('/calendar', [Calendar::class], 'none', 'code');
Router::page('/calendar/{dateMY}', [Calendar::class], 'none');
Router::page('/schedules', [Schedules::class], 'none');
Router::page('/countdowns', [Countdowns::class], 'none');
Router::page('/development', [Development::class], 'none');
Router::page('/notifications', [Notifications::class], 'account');
Router::page('/settings', [Settings::class], 'none');

//Router::page('/games', [Games::class], 'none');


// auth
Router::page('/login', [Authentication::class, 'login'], 'noAccount');
Router::page('/register', [Authentication::class, 'signup'], 'noAccount');
Router::page('/signup', [Authentication::class, 'signup'], 'noAccount');
Router::page('/google-auth', [Authentication::class, 'googleAuth'], 'noAccount');
Router::page('/password-change', [Authentication::class, 'changePassword'],'none');

// admin
//Router::page('/admin', [Admin::class], 'admin');
//Router::page('/admin/schedule', [CustomScheduleLoader::class], 'admin');

// server
Router::page('/server/redis_update', [RedisUpdate::class], 'server');
Router::page('/server/calendar_update', [CalendarUpdate::class], 'server');
Router::page('/server/absences_update', [AbsencesUpdate::class], 'server');
Router::page('/server/staff_update', [StaffUpdate::class], 'server');
Router::page('/server/schedule_default_update', [ScheduleDefault::class], 'server');


// services
//Router::page('/services/upload_schedule', [ScheduleUploader::class], 'server');

Router::page('/services/server-fetch', [ServerFetch::class, 'fetch'], 'account');
Router::page('/services/server-request', [ServerFetch::class, 'request'], 'none');



Router::enable();