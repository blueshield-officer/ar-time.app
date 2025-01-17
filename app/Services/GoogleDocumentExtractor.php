<?php

namespace App\Services;

use Google\Client;
use Google\Service\Docs;

class GoogleDocumentExtractor
{
    private $client;
    public function __construct($authConfig)
    {
        $this->client = new Client();
        $this->client->setAuthConfig($authConfig);
    }
    public function extract($id)
    {
        $this->client->setScopes([Docs::DOCUMENTS]);
        $this->client->setAccessType('offline');
        $service = new Docs($this->client);
        $doc = $service->documents->get($id);

        $allText = [];
        foreach ($doc->body->content as $structuralElement) {
            if ($structuralElement->paragraph) {
                foreach ($structuralElement->paragraph->elements as $paragraphElement) {
                    if ($paragraphElement->textRun && strlen(trim($paragraphElement->textRun->content)) > 0) {
                        $allText[] = trim($paragraphElement->textRun->content);
                    }
                }
            }
        }
        return $allText;
    }
}