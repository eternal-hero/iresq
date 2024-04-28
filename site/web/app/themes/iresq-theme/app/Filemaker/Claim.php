<?php

namespace App\Filemaker;

use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI as FMDataAPI;

class Claim
{
    private $filemaker;

    public function __construct()
    {
        // Docs: https://github.com/msyk/FMDataAPI/blob/master/samples/FMDataAPI_Sample.php
        $this->filemaker = new FMDataAPI('Invoices', $_ENV['FILEMAKER_UNAME'], $_ENV['FILEMAKER_PW'], $_ENV['FILEMAKER_URL']);
        $this->filemaker->setTimeout(15);
    }

    /**
     * Fetch invoice by "PO no".
     *
     * @param int|string $claimNo
     * @param string     $email
     * @param string     $zip
     *
     * @return FileMakerRelation
     */
    public function fetchFirstInvoiceRecordByClaimNo($claimNo, $email, $zip)
    {
        if (empty($claimNo) || empty($email) || empty($zip)) {
            return null;
        }
        
        $query[] = [
            'PO No' => "{$claimNo}",
            'ACTUALSHIPADDRESS::Zip' => "{$zip}",
            'ACTUALSHIPADDRESS::Email' => "\"{$email}\"",
        ];
        
        $result = $this->filemaker->layout('Invoices_api')->query($query);
        if (!is_null($result)) {
            return $result->getFirstRecord();
        }

        return $result;
    }
}
