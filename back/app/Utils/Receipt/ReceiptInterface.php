<?php

namespace App\Utils\Receipt;

interface ReceiptInterface
{
    public function toReceipt(): ReceiptData;
}
