<?php

namespace App\Jobs;

use App\Actions\Product\ImportProductsAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ImportProductsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public int $userId
    ) {}

    public function handle(ImportProductsAction $action): void
    {
        $action->execute($this->filePath, $this->userId);

        // Clean up file after import
        Storage::delete($this->filePath);
    }
}
