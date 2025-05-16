<?php

namespace App\Controllers\server;

use App\Services\ARHS_Data_Extractor\Calendar\CalendarManager;
use App\Services\Controller;
use App\Services\ARHS_Data_Extractor\Calendar;
use App\Services\DBC;

class CalendarUpdate extends Controller
{
    public function execute()
    {

        $DBC = DBC::getInstance();
        $calendarManager = new CalendarManager($DBC);

        $calendarManager->setDate(null);

        $calendarManager->loadSimpleDomLib();

        $calendarManager->registerTable('calendar_schedules'); // fix that, do not delete odd stuff
        $calendarManager->registerTable('calendar_events');
        $calendarManager->registerTable('calendar_sports');

        $calendarManager->registerTable('calendar_day_off');
        $calendarManager->registerTable('calendar_retreats');
        $calendarManager->registerTable('calendar_academics');


        $calendarManager->loadCalendarMonthsHTML(12, 'schedule');
        $calendarManager->loadCalendarMonthsHTML(12, 'sport');

        $calendarManager->process('schedule');
        $calendarManager->process('sport');

        $calendarManager->updateSQL('calendar_events', true);
        $calendarManager->updateSQL('calendar_schedules', false);
        $calendarManager->updateSQL('calendar_sports', true);
        $calendarManager->updateSQL('calendar_day_off', false);
//        $calendarManager->updateSQL('calendar_academics', false);
        $calendarManager->updateSQL('calendar_retreats', false);
    }
}