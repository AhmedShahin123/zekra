<?php

namespace App\Services\Album;

use Auth;
use App\Repositories\Album\AlbumRepositoryInterface;

class AlbumService implements AlbumServiceInterface
{
    private $albumRepository;

    public function __construct(AlbumRepositoryInterface $albumRepository)
    {
        $this->albumRepository = $albumRepository;
    }

    // cart related business functionality
}
