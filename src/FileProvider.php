<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony;

use Graphpinator\Upload\FileProvider as FileProviderInterface;
use GuzzleHttp\Psr7\UploadedFile;
use Infinityloop\Utils\Json;
use Symfony\Component\HttpFoundation\Request;

final class FileProvider implements FileProviderInterface
{
    public function __construct(
        private Request $request,
    )
    {
    }

    public function getMap() : ?Json
    {
        $map = $this->request->get('map');

        return \is_string($map)
            ? Json::fromString($map)
            : null;
    }

    public function getFile(string $key) : UploadedFile
    {
        $file = $this->request->files->get($key);

        return new UploadedFile(
            $file->getPathname(),
            $file->getSize(),
            $file->getError(),
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
        );
    }
}
