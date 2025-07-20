<?php

namespace App\Events;

use App\Models\ImportRequest;
use Illuminate\Foundation\Events\Dispatchable;

class ImportRequestCreated
{
    use Dispatchable;

    public ImportRequest $importRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(ImportRequest $importRequest)
    {

        $this->importRequest = $importRequest;
    }

}
