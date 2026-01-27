<?php

namespace App\Utils\Receipt;

use App\Enums\Company;
use App\Models\ContractPayment;

readonly class ReceiptData
{
    public string $externalId;

    public int $sum;

    public string $receiptNumber;

    public bool $isReturn;

    public function __construct(
        ContractPayment $model,
        public string $purpose,
        public string $name,
        public Company $company,
    ) {
        // contract_payments_123 || other_payments_123
        $this->externalId = $model->getTable().'_'.$model->id;
        $this->sum = (int) $model->sum;
        $this->receiptNumber = $model->receipt_number;
        $this->isReturn = (bool) $model->is_return;
    }
}
