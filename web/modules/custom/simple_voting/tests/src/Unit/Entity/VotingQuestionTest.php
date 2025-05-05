<?php

namespace Drupal\Tests\simple_voting\Unit\Entity;

use Drupal\Tests\UnitTestCase;
use Drupal\simple_voting\Entity\VotingQuestion;

/**
 * Unit test for VotingQuestion entity.
 *
 * @coversDefaultClass \Drupal\simple_voting\Entity\VotingQuestion
 * @group simple_voting
 */
class VotingQuestionTest extends UnitTestCase {

  /**
   * The VotingQuestion entity to test.
   *
   * @var \Drupal\simple_voting\Entity\VotingQuestion
   */
  protected $votingQuestion;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->votingQuestion = $this->getMockBuilder(VotingQuestion::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Tests the question title getter and setter.
   */
  public function testQuestionTitle() {
    $title = 'Test Question Title';

    $this->votingQuestion->expects($this->once())
      ->method('get')
      ->with('title')
      ->willReturn(['value' => $title]);

    $this->votingQuestion->expects($this->once())
      ->method('set')
      ->with('title', $title)
      ->willReturnSelf();

    $this->assertEquals($title, $this->votingQuestion->get('title')['value']);
    $this->assertEquals($this->votingQuestion, $this->votingQuestion->set('title', $title));
  }

  /**
   * Tests the status getter and setter.
   */
  public function testStatus() {
    $status = TRUE;

    $this->votingQuestion->expects($this->once())
      ->method('get')
      ->with('status')
      ->willReturn($status);

    $this->votingQuestion->expects($this->once())
      ->method('set')
      ->with('status', $status)
      ->willReturnSelf();

    $this->assertEquals($status, $this->votingQuestion->get('status'));
    $this->assertEquals($this->votingQuestion, $this->votingQuestion->set('status', $status));
  }

}
