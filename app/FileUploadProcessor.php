<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class FileUploadProcessor
{
    /**
     * FileUploadProcessor constructor.
     *
     * Upload the file, as long as it is a file
     * and return the path it is stored at.
     *
     * @param $file
     */
    public function __construct($file)
    {
        $data = [$file];

        $validator = Validator::make($data, [
            'file' => 'file',
        ]);

        if (! $validator->fails()) {
            $file = request()->file('file');
            $path = $file->store('');
        }

        return $path;
    }
}
