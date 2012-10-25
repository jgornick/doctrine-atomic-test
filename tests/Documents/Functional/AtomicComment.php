<?php

namespace Documents\Functional;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class AtomicComment
{
    /**
     * @ODM\String
     * @ODM\UniqueIndex(sparse=true)
     */
    private $title;

    /**
     * @ODM\String
     */
    private $body;

    /**
     * Sets the title for the comment.
     * @param string $title
     * @return AtomicComment
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Returns the title for the comment.
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the body for the comment.
     * @param string $body
     * @return AtomicComment
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Returns the body for the comment.
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}