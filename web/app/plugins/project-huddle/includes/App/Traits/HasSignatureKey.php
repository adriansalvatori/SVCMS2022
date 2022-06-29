<?php

namespace PH\Traits;

trait HasSignatureKey
{
    public function getSignatureKey()
    {
        return get_post_meta($this->ID, 'security-signature', true);
    }
}
