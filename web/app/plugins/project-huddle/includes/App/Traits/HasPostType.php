<?php

namespace PH\Traits;

trait HasPostType
{
    protected $simple_type, $slug, $singularName;
    protected $model;

    public function getPostTypeObject()
    {
        return get_post_type_object($this->post_type);
    }

    public function getSingularName()
    {
        if ($this->singularName) {
            return $this->singularName;
        }
        return $this->singularName = $this->getPostTypeObject()->labels->singular_name;
    }

    public function getSlug()
    {
        if ($this->slug) {
            return $this->slug;
        }
        return $this->slug = isset($this->getPostTypeObject()->slug) ? $this->getPostTypeObject()->slug : null;
    }

    public function getSimpleType()
    {
        if ($this->simple_type) {
            return $this->simple_type;
        }
        $post_type_object = $this->getPostTypeObject();
        return $this->simple_type = $post_type_object ? $post_type_object->simple_slug : false;
    }

    /**
     * Is the post a specific type?
     *
     * @param string $type Simple type name (project, item, thread, comment);
     * @return boolean
     */
    public function isSimpleType($type)
    {
        return $type === $this->getSimpleType();
    }

    public function isProject()
    {
        return $this->isSimpleType('project');
    }

    public function getModel()
    {
        if ($this->model) {
            return $this->model;
        }
        return $this->model = $this->getPostTypeObject()->model;
    }
}
