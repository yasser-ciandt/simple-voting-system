<?php

namespace Drupal\Tests\simple_voting\Unit\Entity;

use Drupal\Tests\UnitTestCase;
use Drupal\simple_voting\Entity\VotingOption;

/**
 * Unit test for VotingOption entity.
 *
 * @coversDefaultClass \Drupal\simple_voting\Entity\VotingOption
 * @group simple_voting
 */
class VotingOptionTest extends UnitTestCase {

  /**
   * The VotingOption entity to test.
   *
   * @var \Drupal\simple_voting\Entity\VotingOption
   */
  protected $votingOption;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->votingOption = $this->getMockBuilder(VotingOption::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Tests the option title getter and setter.
   */
  public function testOptionTitle() {
    $title = 'Test Option Title';

    $this->votingOption->expects($this->once())
      ->method('get')
      ->with('title')
      ->willReturn(['value' => $title]);

    $this->votingOption->expects($this->once())
      ->method('set')
      ->with('title', $title)
      ->willReturnSelf();

    $this->assertEquals($title, $this->votingOption->get('title')['value']);
    $this->assertEquals($this->votingOption, $this->votingOption->set('title', $title));
  }

  /**
   * Tests the vote count getter and setter.
   */
  public function testVoteCount() {
    $votes = 42;

    $this->votingOption->expects($this->once())
      ->method('get')
      ->with('votes')
      ->willReturn($votes);

    $this->votingOption->expects($this->once())
      ->method('set')
      ->with('votes', $votes)
      ->willReturnSelf();

    $this->assertEquals($votes, $this->votingOption->get('votes'));
    $this->assertEquals($this->votingOption, $this->votingOption->set('votes', $votes));
  }

}
