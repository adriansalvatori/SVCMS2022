<?php

namespace PH\Models;


if (!defined('ABSPATH')) {
    exit;
}

abstract class Model implements \PH\Contracts\Model
{
    /**
     * Required args
     *
     * @var array
     */
    protected $required = [];

    public static function get($id = 0)
    {
        return new static($id);
    }

    function checkRequired($input)
    {
        $missing = [];
        foreach ($this->required as $k => $v) {
            if (empty($input[$k])) {
                $missing[] = $k;
            } else if (is_array($v)) {
                $missing = array_merge($this->checkRequired($input[$k], $v), $missing);
            }
        }
        return $missing;
    }

    public function prepareArgsForDatabase($args)
    {
        // check required args
        $missing = $this->checkRequired($args);
        if (!empty($missing)) {
            return new \WP_Error(implode(', ', $missing) . ' are required.');
        }

        // separate post and meta args
        $post_args = array_filter($args, function ($k) {
            return in_array($k, $this->post_fields);
        });
        $meta_args = array_filter($args, function ($k) {
            return !in_array($k, $this->post_fields);
        });

        // add the rest as meta args
        $post_args['meta_input'] = $meta_args;

        return $post_args;
    }
}
