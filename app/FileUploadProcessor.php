<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class FileUploadProcessor
{
    /**
     * @var array
     */
    protected $data;

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
        $this->data = [$file];
    }

    public function handle()
    {
        $validator = Validator::make($this->data, [
            'file' => 'file'
        ]);

        if (!$validator->fails()) {
            $file = request()->file('file');
            $path = $file->store('');
        }

        return $path;
    }
}
