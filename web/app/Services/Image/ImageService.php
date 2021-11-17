<?php

namespace App\Services\Image;

use Auth;
use App\Repositories\Image\ImageRepositoryInterface;

class ImageService implements ImageServiceInterface
{
    private $imageRepository;

    public function __construct(ImageRepositoryInterface $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    // cart related business functionality
}
