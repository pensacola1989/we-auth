<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use MyHelper;

class UploadJob extends Job
{
    private $tempFilePath;

    private $fileName;

    /**
     * Create a new job instance.
     *
     * @param $fileName
     * @param $tempFilePath
     */
    public function __construct($fileName, $tempFilePath)
    {
        $this->tempFilePath = $tempFilePath;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        MyHelper::uploadAliOSS($this->fileName, $this->tempFilePath);
    }
}
