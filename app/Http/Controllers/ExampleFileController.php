<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\ExampleFile\ExampleFileStoreRequest;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use App\Enums\GcsPath;
use App\Helpers\FileHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExampleFileController extends ApiController
{

    public function storageFile(ExampleFileStoreRequest $request)
    {
        $file_name = 'test_file' . time() . '.csv';
        $file_path = GcsPath::EXAMPLE_PATH->value . '/' . $file_name;
        $stored = FileHelper::storeFile($file_path, $request->file('file')->get());
        if($stored){
            return $this->successResponse(['ok']);
        }
        $this->errorResponse("Error");

    }
}
