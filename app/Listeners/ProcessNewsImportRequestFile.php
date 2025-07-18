<?php

namespace App\Listeners;

use App\Events\ImportRequestCreated;
use App\Services\ImportRequestService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessNewsImportRequestFile implements ShouldQueue
{
    use InteractsWithQueue;

    private ImportRequestService $importRequestService;

    /**
     * Create the event listener.
     */
    public function __construct(ImportRequestService $importRequestService)
    {
        $this->importRequestService = $importRequestService;
    }

    /**
     * Handle the event.
     */
    public function handle(ImportRequestCreated $event): void
    {
        Log::info('Handling Import Request created event ' . $event->importRequest);

        $this->importRequestService->processImportRequest($event->importRequest);

        Log::info('Finished Import Request created event handling');
    }
}
