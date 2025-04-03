<?php

use App\Controllers\Absences;
use App\Controllers\admin\Admin;
use App\Controllers\admin\CustomScheduleLoader;
use App\Controllers\Authentication;
use App\Controllers\Calendar;
use App\Controllers\Countdowns;
use App\Controllers\Home;
use App\Controllers\Legacy;
use App\Controllers\Notifications;
use App\Controllers\Roadmap;
use App\Controllers\Schedules;
use App\Controllers\server\AbsencesUpdate;
use App\Controllers\server\CalendarUpdate;
use App\Controllers\server\RedisUpdate;
use App\Controllers\server\ScheduleDefault;
use App\Controllers\server\StaffUpdate;
use App\Controllers\server\Test;
use App\Controllers\services\AccountFetch;
use App\Controllers\services\Auth;
use App\Controllers\services\ScheduleUploader;
use App\Controllers\Settings;
use App\Services\Router;



// public
Router::page('/', [Home::class], 'none');
Router::page('/{dateMDY}', [Home::class], 'none');
Router::page('/home', [Home::class], 'none');
Router::page('/home/{dateMDY}', [Home::class], 'none');

Router::page('/absences', [Absences::class], 'none');
Router::page('/absences/{dateMDY}', [Absences::class], 'none');

Router::page('/calendar', [Calendar::class], 'none');
Router::page('/calendar/{dateMY}', [Calendar::class], 'none');

Router::page('/schedules', [Schedules::class], 'none');

Router::page('/countdowns', [Countdowns::class], 'none');

Router::page('/roadmap', [Roadmap::class], 'none');

Router::page('/notifications', [Notifications::class], 'account');

Router::page('/settings', [Settings::class], 'none');


// auth
Router::page('/login', [Authentication::class, 'login'], 'none');
Router::page('/signup', [Authentication::class, 'signup'], 'none');
Router::page('/google-auth', [Authentication::class, 'login_google'], 'none');


// admin
Router::page('/admin', [Admin::class], 'admin');
Router::page('/admin/schedule', [CustomScheduleLoader::class], 'admin');


// server
Router::page('/server/redis_update', [RedisUpdate::class], 'server');
Router::page('/server/calendar_update', [CalendarUpdate::class], 'server');
Router::page('/server/absences_update', [AbsencesUpdate::class], 'server');
Router::page('/server/staff_update', [StaffUpdate::class], 'server');
Router::page('/server/schedule_default_update', [ScheduleDefault::class], 'server');


// services
Router::page('/services/upload_schedule', [ScheduleUploader::class], 'server');

Router::page('/services/account-fetch', [AccountFetch::class], 'account');



Router::enable();