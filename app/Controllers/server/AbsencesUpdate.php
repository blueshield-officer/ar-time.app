<?php

namespace App\Controllers\server;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\ARHS_Data_Extractor\Absents\AbsencesManager;
use App\Services\DBC;
use App\Services\Notificator;
use App\Services\RedisOperator;

class AbsencesUpdate extends Controller
{
    public function execute()
    {
        $DBC = DBC::getInstance();
        $redis = RedisOperator::getInstance();
        $cacheOperator = new CacheOperator($DBC, $redis);
        $notificator = new Notificator();


        $absenceManager = new AbsencesManager(
            $DBC,
            $redis,
            $cacheOperator,
            $notificator
        );

        // ==== 1 ====
        $absenceManager->setDate(null);
        $absenceManager->loadGoogleDocument( '1KhmTALivDbAvLusQjZQOOQmPKDm-2Do-gVfvAdf_6WA');
        if ($absenceManager->documentFindDateIndex(10000)){
            // ==== 2 ====
            $absenceManager->documentFindTargetStrings('start', 3);
            $absenceManager->loadPeopleNew(
                $absenceManager->documentFindTargetStrings('teachers', 30),
                $absenceManager->documentFindTargetStrings('staff', 30)
            );
            $absenceManager->loadRoomChangesAndAdditionalNew(
                $absenceManager->documentFindTargetStrings('rooms', 30)
            );

            // ==== 3 ====
            // Loading old data from SQL
            $absenceManager->loadPeopleOld();
            $absenceManager->loadRoomChangesOld();
            $absenceManager->loadAdditionalOld();

            // ==== 4 ====
            // find difference
            $absenceManager->loadPeopleDifference();
            $absenceManager->loadRoomChangesDifference();

            // ==== 5 ====
            // SQL update
            $absenceManager->updateSQLAdditional();
            $absenceManager->updateSQLRoomChanges();
            $absenceManager->updateSQLPeople();

            $absenceManager->checkOnErrors();

            $absenceManager->retreat();

            $absenceManager->notify();
        }

//        $absenceManager->testDoc();
    }
}