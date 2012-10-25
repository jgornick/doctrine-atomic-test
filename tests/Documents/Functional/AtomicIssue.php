<?php

namespace Documents\Functional;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ODM\Document
 */
class AtomicIssue
{
    /**
     * @ODM\Id
     */
    private $id;

    /**
     * @ODM\String
     * @ODM\UniqueIndex()
     */
    private $title;

    /**
     * @ODM\EmbedMany(targetDocument="AtomicTask")
     */
    private $tasks;

    /**
     * @ODM\ReferenceMany(targetDocument="AtomicIssue")
     */
    private $related;

    /**
     * @ODM\EmbedMany(targetDocument="AtomicComment")
     */
    private $comments;

    /**
     * AtomicIssue constructor.
     */
    public function __construct()
    {
        $this->tasks = new ArrayCollection;
        $this->comments = new ArrayCollection;
    }

    /**
     * Sets the title for the issue.
     * @param string $title
     * @return AtomicIssue
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Returns the title for the issue.
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Adds the specified task to the issue.
     * @param AtomicTask $task
     * @return AtomicIssue
     */
    public function addTask(AtomicTask $task)
    {
        $task->setIssue($this);
        $this->tasks->add($task);
        return $this;
    }

    /**
     * Returns a collection containing the task embedded in the issue.
     *
     * Optionally, you can specify a filter Closure.
     *
     * @param  [Closure] $filter
     * @return ArrayCollection
     */
    public function getTasks($filter = null)
    {
        if ($filter == null) {
            $filter = function() {
                return true;
            };
        }

        return $this->tasks->filter($filter);
    }

    /**
     * Removes the specified task from the issue.
     * @param  AtomicTask $task
     * @return AtomicIssue
     */
    public function removeTask(AtomicTask $task)
    {
        $this->tasks->removeElement($task);
        return $this;
    }

    /**
     * Adds the specified comment to the issue.
     * @param AtomicComment $comment
     * @return AtomicIssue
     */
    public function addComment(AtomicComment $comment)
    {
        $this->comments->add($comment);
        return $this;
    }

    /**
     * Returns a collection containing the comments embedded in the issue.
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
     * Removes the specified comment from the issue.
     * @param  AtomicComment $comment
     * @return AtomicIssue
     */
    public function removeComment(AtomicComment $comment)
    {
        $this->comments->removeElement($comment);
        return $this;
    }
}