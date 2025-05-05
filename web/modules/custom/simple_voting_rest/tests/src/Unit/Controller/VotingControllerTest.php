<?php

namespace Drupal\Tests\simple_voting_rest\Unit\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Database\Connection;
use Drupal\Tests\UnitTestCase;
use Drupal\simple_voting_rest\Controller\VotingController;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\simple_voting\Entity\VotingQuestion;

/**
 * @coversDefaultClass \Drupal\simple_voting_rest\Controller\VotingController
 * @group simple_voting_rest
 */
class VotingControllerTest extends UnitTestCase {

  /**
   * The voting controller.
   *
   * @var \Drupal\simple_voting_rest\Controller\VotingController
   */
  protected $controller;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityStorage = $this->createMock(EntityStorageInterface::class);

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityTypeManager->method('getStorage')
      ->with('voting_question')
      ->willReturn($this->entityStorage);

    $mockDatabase = $this->createMock(Connection::class);
    $mockCurrentUser = $this->createMock(AccountProxyInterface::class);
    $this->controller = new VotingController($this->entityTypeManager, $mockDatabase, $mockCurrentUser);
  }

  /**
   * Tests the listing of voting questions (should return JsonResponse).
   */
  public function testListQuestions() {
    $questionStorage = $this->createMock(EntityStorageInterface::class);
    $optionStorage = $this->createMock(EntityStorageInterface::class);

    $this->entityTypeManager->method('getStorage')
      ->willReturnCallback(function ($entity_type) use ($questionStorage, $optionStorage) {
        if ($entity_type === 'voting_question') {
          return $questionStorage;
        }
        if ($entity_type === 'voting_option') {
          return $optionStorage;
        }
        return NULL;
      });

    $mockTitle = new \stdClass();
    $mockTitle->value = 'Test';

    $mockQuestion = $this->getMockBuilder(VotingQuestion::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['id', 'get'])
      ->getMock();
    $mockQuestion->method('id')->willReturn(1);
    $mockQuestion->method('get')->with('title')->willReturn($mockTitle);

    $questionStorage->method('loadByProperties')
      ->with(['status' => TRUE])
      ->willReturn([$mockQuestion, $mockQuestion]);

    $optionStorage->expects($this->any())
      ->method('loadByProperties')
      ->willReturn([]);

    $request = $this->createMock(Request::class);
    $response = $this->controller->getQuestions($request);
    $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
    $data = json_decode($response->getContent(), TRUE);
    $this->assertIsArray($data);
  }

}
