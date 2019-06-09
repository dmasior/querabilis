<?php declare(strict_types=1);

namespace Initx\Querabilis\Driver;

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

trait HasFallbackSerializer
{
    public function fallbackSerializer(?SerializerInterface $serializer = null): SerializerInterface
    {
        if (!$serializer) {
            $separator = DIRECTORY_SEPARATOR;
            $metaDir = sprintf('%s%s..%s..%sconfig%sjms', __DIR__, $separator, $separator, $separator, $separator);
            $serializer = SerializerBuilder::create()
                ->addMetadataDir($metaDir, 'Initx\Querabilis')
                ->build();
        }

        return $serializer;
    }
}
