<?php

namespace PH\Traits;

/**
 * Can be approved
 */
trait CanBeApproved
{
    protected static $commented = false;
    protected $meta_key = 'approved';

    /**
     * Approve the item
     *
     * @return boolean
     */
    public function approve()
    {
        return $this->saveApprovalStatus(1);
    }

    /**
     * Reject the item
     *
     * @return boolean
     */
    public function unapprove()
    {
        return $this->saveApprovalStatus(0);
    }

    /**
     * Is the item approved
     *
     * @return boolean
     */
    public function isApproved()
    {
        return $this->getApprovalStatus();
    }

    /**
     * Clear the approval status
     *
     * @return boolean
     */
    public function clearApprovalStatus()
    {
        return $this->saveApprovalStatus(null);
    }

    /**
     * Get the approval status from the database
     *
     * @return object
     */
    public function getApprovalStatus()
    {
        return PH()->approvals->getStatus($this->ID);
    }

    /**
     * Get approval history for post
     *
     * @return array|false
     */
    public function getApprovalHistory($args)
    {
        return PH()->approvals->getHistory($this->ID);
    }

    /**
     * Set the approval status in the database
     *
     * @param string $status
     * @return void
     */
    public function saveApprovalStatus($approved)
    {
        PH()->approvals->save($this, (bool) $approved);
        return $this;
    }

    /**
     * Are it's siblings approved?
     *
     * @return bool
     */
    public function siblingsApproved()
    {
        return (bool) PH()->approvals->siblingsApproved($this->parentId(), $this->projectType());
    }
}
