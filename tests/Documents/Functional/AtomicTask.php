<?php

namespace Documents\Functional;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ODM\EmbeddedDocument
 */
class AtomicTask
{
    /**
     * @ODM\String
     * @ODM\UniqueIndex(sparse=true)
     */
    private $title;

    /**
     * @ODM\ReferenceOne(targetDocument="AtomicIssue")
     */
    private $issue;

    /**
     * @ODM\EmbedMany(targetDocument="AtomicComment")
     */
    private $comments;

    /**
     * AtomicTask constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection;
    }

    /**
     * Sets the title for the task.
     * @param string $title
     * @return AtomicTask
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Returns the title for the task.
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the related issue for the task.
     * @param AtomicIssue $issue
     * @return AtomicTask
     */
    public function setIssue(AtomicIssue $issue)
    {
        $this->issue = $issue;
        return $this;
    }

    /**
     * Returns the issue the task is embedded in.
     * @return AtomicIssue
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Adds the specified comment to the task.
     * @param AtomicComment $comment
     * @return AtomicTask
     */
    public function addComment(AtomicComment $comment)
    {
        $this->comments->add($comment);
        return $this;
    }

    /**
     * Returns a collection containing the comments embedded in the task.
     *
     * Optionally, you can specify a filter Closure.
     *
     * @param  [Closure] $filter
     * @return ArrayCollection
     */
    public function getComments($filter = null)
    {
        if ($filter == null) {
            $filter = function() {
                return true;
            };
        }

        return $this->comments->filter($filter);
    }

    /**
     * Removes the specified comment from the task.
     * @param  AtomicComment $comment
     * @return AtomicTask
     */
    public function removeComment(AtomicComment $comment)
    {
        $this->comments->removeElement($comment);
        return $this;
    }
}