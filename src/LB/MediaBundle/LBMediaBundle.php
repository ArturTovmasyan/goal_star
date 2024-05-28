<?php

namespace LB\MediaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LBMediaBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'CoopTilleulsCKEditorSonataMediaBundle';
    }
}
