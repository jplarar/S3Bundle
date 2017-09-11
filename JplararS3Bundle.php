<?php
namespace Jplarar\S3Bundle;

use Jplarar\S3Bundle\DependencyInjection\JplararS3Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JplararS3Bundle extends Bundle
{
    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function getContainerExtension()
    {
        // this allows us to have custom extension alias
        // default convention would put a lot of underscores
        if (null === $this->extension) {
            $this->extension = new JplararS3Extension();
        }

        return $this->extension;
    }
}