<?php declare(strict_types=1);

namespace Initx\Driver;

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

/**
 * @property SerializerInterface|null $serializer
 */
trait HasFallbackSerializer
{
    public function fallbackSerializer()
    {
        if (!$this->serializer) {
            $separator = DIRECTORY_SEPARATOR;
            $metaDir = sprintf('%s%s..%s..%sconfig%sjms', __DIR__, $separator, $separator, $separator, $separator);
            $this->serializer = SerializerBuilder::create()
                ->addMetadataDir($metaDir, 'Initx')
                ->build();
        }
    }
}
