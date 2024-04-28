<?php

namespace App\Filemaker;

use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI as FMDataAPI;

class ShippingMethods
{
    private $filemaker;

    public function __construct()
    {
        // Docs: https://github.com/msyk/FMDataAPI/blob/master/samples/FMDataAPI_Sample.php
        $this->filemaker = new FMDataAPI('Products', $_ENV['FILEMAKER_UNAME'], $_ENV['FILEMAKER_PW'], $_ENV['FILEMAKER_URL']);
        $this->filemaker->setTimeout(15);
    }

    /**
     * Fetch all shipping records for web
     *
     * @return FileMakerRelation[]
     */
    public function fetchAllRecords()
    {
        $query = [
            [
                'Device Type' => "Shipping",
                'Model' => 'Webship*'
            ]
        ];
        $result = $this->filemaker->layout(rawurlencode('View List'))->query($query);

        return $result->getRecords();
    }
}
