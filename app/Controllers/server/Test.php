<?php

namespace App\Controllers\server;

use App\Services\Controller;

class Test extends Controller
{
    public function execute(){
        $this->view('debug.tpl', [
            'name' => 'test',
            'spent_time' => null,
            'data' => [
                'old_data' => [
                    'data' => [
                        1 => 'ГСБ',
                        2 => 'ГВ'
                    ],
                    'important' => true
                ],
                'new_data' => [
                    'data' => [
                        1 => 'СИ',
                        2 => 'НР'
                    ],
                    'important' => true
                ]
            ],
//            'local_date' => new \DateTime(),
            'type' => 'compare'
        ]);
    }
}