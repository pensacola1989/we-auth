<?php

namespace App\Services\KdCrm;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 5/21/17
 * Time: 4:19 PM
 */

interface KdCrmContract
{
    public function bindUserToCrm($openId);

    public function isUserbind($customerNumber);

    public function getCustomerNumber($mobile);

    public function createProfile(array $profile);

    public function getOpenIdBycustomerNumber($customerNumber);

    public function updateSocialProfile($customerNumber, array $socialProfile);

    public function publshProfileToken($openId);

    public function deleteProfileToken($customerNumber, $mediaCode);

    public function getMemberNumber($customerNumber);

    public function uploadReceipt();

    public function getMemberSummary($customerNujmber);

    public function getTicketHistory($profileToken, $memberNumber, $maxNo = 20);

    public function getReceiptImage($receiptName);

    public function signIn($customNumber, $password);

    public function publishRedeem($phoneNumber, $score);
}
