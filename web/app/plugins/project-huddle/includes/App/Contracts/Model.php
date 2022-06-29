<?php

namespace PH\Contracts;

interface Model
{
    public function get_meta($force = false);
    public static function get($id = 0);
}
