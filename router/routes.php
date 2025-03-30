<?php

use App\Controllers\Absences;
use App\Controllers\admin\Admin;
use App\Controllers\admin\CustomScheduleLoader;
use App\Controllers\Authentication;
use App\Controllers\Calendar;
use App\Controllers\Countdowns;
use App\Controllers\Home;
use App\Controllers\Legacy;
use App\Controllers\Roadmap;
use App\Controllers\Schedules;
use App\Controllers\server\AbsencesUpdate;
use App\Controllers\server\CalendarUpdate;
use App\Controllers\server\RedisUpdate;
use App\Controllers\server\ScheduleDefault;
use App\Controllers\server\StaffUpdate;
use App\Controllers\server\Test;
use App\Controllers\services\Auth;
use App\Controllers\services\ScheduleUploader;
use App\Controllers\Settings;
use App\Services\Router;



// public
Router::page('/', [Home::class]);
Router::page('/{dateMDY}', [Home::class]);
Router::page('/home', [Home::class]);
Router::page('/home/{dateMDY}', [Home::class]);

Router::page('/absences', [Absences::class]);
Router::page('/absences/{dateMDY}', [Absences::class]);

Router::page('/calendar', [Calendar::class]);
Router::page('/calendar/{dateMY}', [Calendar::class]);

Router::page('/schedules', [Schedules::class]);

Router::page('/countdowns', [Countdowns::class]);

Router::page('/roadmap', [Roadmap::class]);

Router::page('/settings', [Settings::class]);


// auth
Router::page('/login', [Authentication::class, 'login']);
Router::page('/signup', [Authentication::class, 'signup']);
Router::page('/login/google', [Authentication::class, 'login_google']);


// admin
Router::page('/admin', [Admin::class]);
Router::page('/admin/schedule', [CustomScheduleLoader::class]);


// server
Router::page('/server/redis_update', [RedisUpdate::class]);
Router::page('/server/calendar_update', [CalendarUpdate::class]);
Router::page('/server/absences_update', [AbsencesUpdate::class]);
Router::page('/server/staff_update', [StaffUpdate::class]);
Router::page('/server/schedule_default_update', [ScheduleDefault::class]);


// services
Router::page('/services/upload_schedule', [ScheduleUploader::class]);


// maints
Router::page('/test', [Test::class]);


Router::enable();