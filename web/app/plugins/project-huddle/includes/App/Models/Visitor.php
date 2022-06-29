<?php

namespace PH\Models;

use PH\Models\Post;
use PH\Models\User;
use PH\Models\Model;

if (!defined('ABSPATH')) {
    exit;
}

class Visitor
{
    protected $validator;

    public static function get($id = 0)
    {
        $self = new self();
        return $self;
    }

    public static function current()
    {
        $self = new self();
        return $self;
    }

    /**
     * Can the visitor access the model
     *
     * @param  $model
     * @return boolean
     */
    public function canAccess(Post $model)
    {
        // can access if project allows guests
        if ($model->project()->allowsGuests()) {
            return true;
        }

        // otherwise check access token
        $tokens = PH()->session->get('project_access');
        $token = isset($tokens[$model->projectId()]) ? $tokens[$model->projectId()] : '';
        if ($token && $model->project()->getToken() === $token) {
            return true;
        }

        // otherwise check if user is logged in and has access
        if (is_user_logged_in()) {
            if (User::current()->can($model->project(), 'read')) {
                return true;
            }
        }

        return false;
    }

    public function saveToken($model_id, $token)
    {
        if (is_a($model_id, Post::class)) {
            $model_id = $model_id->ID;
        }

        $access = PH()->session->get('project_access');
        $access[$model_id] = $token;
        PH()->session->set('project_access', $access);
    }
}
