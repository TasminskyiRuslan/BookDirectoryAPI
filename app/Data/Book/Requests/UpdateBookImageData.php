<?php

namespace App\Data\Book\Requests;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\Validation\File;
use Spatie\LaravelData\Attributes\Validation\Image;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UpdateBookImageData extends Data
{
    /**
     * @param UploadedFile $image
     */
    public function __construct(
        #[Required]
        #[File]
        #[Image]
        #[Mimes('jpg', 'jpeg', 'png')]
        #[Max(2048)]
        public UploadedFile $image,
    ) {}
}
