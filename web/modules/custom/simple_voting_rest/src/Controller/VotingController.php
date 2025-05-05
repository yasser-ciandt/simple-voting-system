<?php

namespace Drupal\simple_voting_rest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Controller for the voting system REST API endpoints.
 */
class VotingController extends ControllerBase implements ContainerInjectionInterface {

/**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a VotingController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database, AccountProxyInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('current_user')
    );
  }

  /**
   * Get all active voting questions.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getQuestions(Request $request) {
    $questions = $this->entityTypeManager->getStorage('voting_question')
      ->loadByProperties(['status' => TRUE]);

    $data = [];
    $current_user = $this->currentUser;
    foreach ($questions as $question) {
      // For each question, only show votes if show_results is enabled AND the current user has already voted for this question.
      $show_votes = FALSE;
      if ($question->get('show_results')->value && $current_user && $current_user->id() != 0) {
        $user_voted = $this->database->select('simple_voting_user_votes', 'v')
          ->fields('v', ['option_id'])
          ->condition('uid', $current_user->id())
          ->condition('question_id', $question->id())
          ->execute()
          ->fetchField();
        if ($user_voted) {
          $show_votes = TRUE;
        }
      }
      $options = $this->entityTypeManager->getStorage('voting_option')
        ->loadByProperties(['question_id' => $question->id()]);

      $option_data = [];
      foreach ($options as $option) {
        $option_data[] = [
          'id' => $option->id(),
          'title' => $option->get('title')->value,
          'description' => $option->get('description')->value,
          'image' => $option->get('image')->entity ? $option->get('image')->entity->createFileUrl() : NULL,
          'votes' => $show_votes ? $option->get('votes')->value : NULL,
        ];
      }

      $data[] = [
        'id' => $question->id(),
        'title' => $question->get('title')->value,
        'identifier' => $question->get('identifier')->value,
        'show_results' => (bool) $question->get('show_results')->value,
        'options' => $option_data,
      ];
    }

    return new JsonResponse($data);
  }

  /**
   * Get a specific voting question.
   *
   * @param \Drupal\simple_voting\Entity\VotingQuestion $voting_question
   *   The voting question entity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getQuestion($voting_question) {
    if (is_string($voting_question) || is_int($voting_question)) {
      $voting_question_entity = $this->entityTypeManager->getStorage('voting_question')->load($voting_question);
      if (!$voting_question_entity) {
        throw new AccessDeniedHttpException('Voting question not found.');
      }
      $voting_question = $voting_question_entity;
    }
    if (!$voting_question->get('status')->value) {
      throw new AccessDeniedHttpException('This question is not active.');
    }

    $options = $this->entityTypeManager->getStorage('voting_option')
      ->loadByProperties(['question_id' => $voting_question->id()]);

    $option_data = [];
    foreach ($options as $option) {
      // Only show votes if show_results is enabled and the user has already voted for this question.
      $show_votes = FALSE;
      $current_user = $this->currentUser;
      if ($voting_question->get('show_results')->value && $current_user && $current_user->id() != 0) {
        $user_voted = $this->database->select('simple_voting_user_votes', 'v')
          ->fields('v', ['option_id'])
          ->condition('uid', $current_user->id())
          ->condition('question_id', $voting_question->id())
          ->execute()
          ->fetchField();
        if ($user_voted) {
          $show_votes = TRUE;
        }
      }
      $option_data[] = [
        'id' => $option->id(),
        'title' => $option->get('title')->value,
        'description' => $option->get('description')->value,
        'image' => $option->get('image')->entity ? $option->get('image')->entity->createFileUrl() : NULL,
        'votes' => $show_votes ? $option->get('votes')->value : NULL,
      ];
    }

    $data = [
      'id' => $voting_question->id(),
      'title' => $voting_question->get('title')->value,
      'identifier' => $voting_question->get('identifier')->value,
      'show_results' => (bool) $voting_question->get('show_results')->value,
      'options' => $option_data,
    ];

    // If the user is authenticated, include the voted option if it exists.
    $current_user = $this->currentUser;
    $voted_option_id = NULL;
    $data['voted_option_id'] = NULL;
    if ($current_user && $current_user->id() != 0) {
      $voted_option_id = $this->database->select('simple_voting_user_votes', 'v')
        ->fields('v', ['option_id'])
        ->condition('uid', $current_user->id())
        ->condition('question_id', $voting_question->id())
        ->execute()
        ->fetchField();
      if ($voted_option_id) {
        $data['voted_option_id'] = (int) $voted_option_id;
      }
    }

    return new JsonResponse($data);
  }

  /**
   * Vote on a question.
   *
   * @param \Drupal\simple_voting\Entity\VotingQuestion $voting_question
   *   The voting question entity.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function vote($voting_question, Request $request) {
    // Always define $current_user at the beginning to avoid undefined variable warnings.
    $current_user = $this->currentUser;
    // Ensure $voting_question is an entity, not a string (ID)
    if (is_string($voting_question) || is_int($voting_question)) {
      $voting_question_entity = $this->entityTypeManager->getStorage('voting_question')->load($voting_question);
      if (!$voting_question_entity) {
        throw new AccessDeniedHttpException('Voting question not found.');
      }
      $voting_question = $voting_question_entity;
    }
    if (!$this->config('simple_voting.settings')->get('system_enabled')) {
      throw new AccessDeniedHttpException('The voting system is currently disabled.');
    }

    if (!$voting_question->get('status')->value) {
      throw new AccessDeniedHttpException('This question is not active.');
    }

    $data = json_decode($request->getContent(), TRUE);
    if (!isset($data['option_id'])) {
      throw new BadRequestHttpException('Missing option_id in request body.');
    }

    $option = $this->entityTypeManager->getStorage('voting_option')
      ->load($data['option_id']);
    if (!$option || $option->get('question_id')->target_id != $voting_question->id()) {
      throw new BadRequestHttpException('Invalid option_id.');
    }

    // Prevent double voting for authenticated users.
    if ($current_user && $current_user->id() != 0) {
      $existing_vote = $this->database->select('simple_voting_user_votes', 'v')
        ->fields('v', ['option_id'])
        ->condition('uid', $current_user->id())
        ->condition('question_id', $voting_question->id())
        ->execute()
        ->fetchField();
      if ($existing_vote) {
        return new JsonResponse([
          'error' => 'You have already voted for this question.',
          'voted_option_id' => $existing_vote,
        ], 400);
      }
      $current_user = $this->currentUser;
      if ($voting_question->get('show_results')->value && $current_user && $current_user->id() != 0) {
        $user_voted = $this->database->select('simple_voting_user_votes', 'v')
          ->fields('v', ['option_id'])
          ->condition('uid', $current_user->id())
          ->condition('question_id', $voting_question->id())
          ->execute()
          ->fetchField();
        if ($user_voted) {
          $show_votes = TRUE;
        }
      }
      if ($show_votes) {
        return new JsonResponse([
          'votes' => $option->get('votes')->value,
        ]);
      }

      return new JsonResponse(['status' => 'success']);
    }
  }
}
