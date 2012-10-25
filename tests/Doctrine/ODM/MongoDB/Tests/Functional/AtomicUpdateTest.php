<?php

namespace Doctrine\ODM\MongoDB\Tests\Functional;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Tests\BaseTest;
use Documents\Functional\AtomicIssue;
use Documents\Functional\AtomicTask;
use Documents\Functional\AtomicComment;

class AtomicUpdateTest extends BaseTest
{
    public function setUp()
    {
        parent::setUp();

        $this->dm->getSchemaManager()->ensureDocumentIndexes('Documents\Functional\AtomicIssue');

        $issue = new AtomicIssue();
        $issue->setTitle('Issue1');
        $this->dm->persist($issue);

        $issue = new AtomicIssue();
        $issue->setTitle('Issue2');
        $this->dm->persist($issue);

        $this->dm->flush();
    }

    /**
     * Test that an issue unique index violation prevents embedded documents
     * from being added.
     */
    public function testViolateIssueUniqueIndexAndAddEmbed()
    {
        $issue2 = $this->getIssueByTitle('Issue2');

        // cause the unique index violation
        $issue2->setTitle('Issue1');

        // add our task locally, however, this shouldn't be flushed
        $this->addTaskToIssue($issue2, 'Issue2Task1');

        try {
            $this->dm->flush();
            $this->fail('Expecting a MongoCursorException to be thrown.');
        } catch (\MongoCursorException $e) {
            $this->dm->refresh($issue2);

            // assert the task wasn't added even though
            $this->assertCount(0, $issue2->getTasks());
        }
    }

    /**
     * Test that an issue unique index violation prevents embedded documents
     * from being updated.
     */
    public function testViolateIssueUniqueIndexAndUpdateEmbed()
    {
        $issue2 = $this->getIssueByTitle('Issue2');
        $this->addTaskToIssue($issue2, 'Issue2Task1');

        $this->dm->flush();
        $this->dm->refresh($issue2);

        // cause the unique index violation
        $issue2->setTitle('Issue1');

        // update the existing task, however, this won't be flushed
        $task = $issue2->getTasks()->first();
        $task->setTitle('UpdatedIssue2Task1');

        try {
            $this->dm->flush();
            $this->fail('Expecting a MongoCursorException to be thrown.');
        } catch (\MongoCursorException $e) {
            $this->dm->refresh($issue2);

            // assert we still have one task and the task title hasn't changed
            $this->assertCount(1, $issue2->getTasks());
            $this->assertEquals('Issue2Task1', $issue2->getTasks()->first()->getTitle());
        }
    }

    /**
     * Test that an issue unique index violation prevents embedded documents
     * from being removed.
     */
    public function testViolateIssueUniqueIndexAndDeleteEmbed()
    {
        $issue2 = $this->getIssueByTitle('Issue2');
        $this->addTaskToIssue($issue2, 'Issue2Task1');

        $this->dm->flush();
        $this->dm->refresh($issue2);

        // cause the unique index violation
        $issue2->setTitle('Issue1');

        // remove the task from the issue, however, this won't be flushed
        $task = $issue2->getTasks()->first();
        $issue2->removeTask($task);

        try {
            $this->dm->flush();
            $this->fail('Expecting a MongoCursorException to be thrown.');
        } catch (\MongoCursorException $e) {
            $this->dm->refresh($issue2);

            // assert we still have one task and the task wasn't removed
            $this->assertCount(1, $issue2->getTasks());
            $this->assertEquals('Issue2Task1', $issue2->getTasks()->first()->getTitle());
        }
    }

    /**
     * Test that our issue document changes aren't persisted when an embedded document
     * unique index fails by adding a task with the same title
     *
     * @internal This will not pass until atomic updates are added.
     */
    public function testViolateTaskAddUniqueIndexAndUpdateTitle()
    {
        $issue2 = $this->getIssueByTitle('Issue2');
        $this->addTaskToIssue($issue2, 'Issue2Task1');

        $this->dm->flush();
        $this->dm->refresh($issue2);

        $issue1 = $this->getIssueByTitle('Issue1');
        $issue1->setTitle('UpdatedIssue1');

        // cause the task unique index violation by adding another task with same title
        $this->addTaskToIssue($issue1, 'Issue2Task1');

        try {
            $this->dm->flush();
            $this->fail('Expecting a MongoCursorException to be thrown.');
        } catch (\MongoCursorException $e) {
            $this->dm->refresh($issue1);

            // assert our title didn't change
            $this->assertEquals('Issue1', $issue1->getTitle());
        }
    }

    /**
     * Test that our issue document changes aren't persisted when an embedded document
     * unique index fails by updating a task to have the same title
     *
     * @internal When there is an update to the parent doument and an update to a specific
     * embedded document, then the unit of work will use the $set operator which will not
     * persist the changes to the parent document.
     *
     * This test will produce the following query:
     * db.AtomicIssue.update({ ... }, {
     *   "$set" : {
     *     "title" : "UpdatedIssue1",
     *     "tasks.0.title" : "Issue2Task1"
     *   }
     * });
     */
    public function testViolateTaskUpdateUniqueIndexAndUpdateTitle()
    {
        // add a task to Issue2 and return the issue
        $issue2 = $this->getIssueByTitle('Issue2');
        $this->addTaskToIssue($issue2, 'Issue2Task1');

        $this->dm->flush();
        $this->dm->refresh($issue2);

        $issue1 = $this->getIssueByTitle('Issue1');
        $this->addTaskToIssue($issue1, 'Issue1Task1');

        $this->dm->flush();
        $this->dm->refresh($issue1);

        $issue1->setTitle('UpdatedIssue1');

        $task = $issue1->getTasks()->first();
        $task->setTitle('Issue2Task1');

        try {
            $this->dm->flush();
            $this->fail('Expecting a MongoCursorException to be thrown.');
        } catch (\MongoCursorException $e) {
            $this->dm->refresh($issue1);

            // assert our title didn't change
            $this->assertEquals('Issue1', $issue1->getTitle());
        }
    }

    /**
     * Returns an issue by the specified title.
     * @param  string $title
     * @return AtomicIssue|null
     */
    protected function getIssueByTitle($title)
    {
        $issueRepo = $this->dm->getRepository('Documents\Functional\AtomicIssue');

        return $issueRepo->findOneBy(array(
            'title' => $title
        ));
    }

    /**
     * Adds a task with the specified title to the issue.
     * @param AtomicIssue $issue
     * @param string $taskTitle
     */
    protected function addTaskToIssue(AtomicIssue $issue, $taskTitle)
    {
        $task = new AtomicTask();
        $task->setTitle($taskTitle);
        $issue->addTask($task);
    }
}