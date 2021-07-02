<?php

namespace App\Commands\User\Bank\Deposit\Valid;

use App\Commands\AbstractCommand;
use App\DataSource\User\Bank\Deposit\Mapper;

class Command extends AbstractCommand
{

    private $mapper;


    public function __construct(Filter $filter, Mapper $mapper)
    {
        $this->filter = $filter;
        $this->mapper = $mapper;
    }


    protected function run(
        float $amount,
        string $email,
        float $fee,
        int $organization,
        string $processor,
        array $processorTransaction,
        string $processorTransactionId,
        int $user
    ) : bool
    {
        $deposit = $this->mapper->create(compact($this->filter->getFields()));

        if (!$this->mapper->isUniqueTransaction($deposit->getProcessorTransactionId())) {
            $this->filter->writeDuplicateTransactionMessage();
        }

        if (!$this->filter->hasErrors()) {
            $this->mapper->insert($deposit);

            $this->mapper->scheduleProcessBankDepositJob([
                'id' => $deposit->getId()
            ]);
        }

        return !$this->filter->hasErrors();
    }
}
