<?php

namespace Drupal\simple_voting\Controller;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\simple_voting\Entity\VotingQuestion;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for managing voting options.
 */
class VotingQuestionController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a VotingQuestionController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('messenger')
    );
  }

  /**
   * Gets the title for the options management page.
   *
   * @param \Drupal\simple_voting\Entity\VotingQuestion $voting_question
   *   The voting question entity.
   *
   * @return string
   *   The page title.
   */
  public function getOptionsPageTitle(VotingQuestion $voting_question) {
    return $this->t('Manage options for: @title', ['@title' => $voting_question->label()]);
  }

  /**
   * Displays the options management page.
   *
   * @param \Drupal\simple_voting\Entity\VotingQuestion $voting_question
   *   The voting question entity.
   *
   * @return array
   *   A render array.
   */
  public function manageOptions(VotingQuestion $voting_question) {
    // Add a link to create a new option for this question.
    $build['add_option'] = [
      '#type' => 'link',
      '#title' => $this->t('Add new option'),
      '#url' => Url::fromRoute('entity.voting_option.add_form', [
        'question_id' => $voting_question->id(),
      ]),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
      '#prefix' => '<div class="action-links">',
      '#suffix' => '</div>',
    ];

    // Get all options for this question.
    $options = $this->entityTypeManager->getStorage('voting_option')
      ->loadByProperties(['question_id' => $voting_question->id()]);

    // Build a table of options.
    $header = [
      'title' => $this->t('Title'),
      'votes' => $this->t('Votes'),
      'percentage' => $this->t('Percentage'),
      'operations' => $this->t('Operations'),
    ];

    // Calculate total votes for percentage calculation.
    $total_votes = 0;
    foreach ($options as $opt) {
      $total_votes += (int) $opt->get('votes')->value;
    }

    $rows = [];
    foreach ($options as $option) {
      $row = [];
      $row['title'] = $option->label();
      $row['votes'] = $option->get('votes')->value;

      // Calculate and display the percentage of votes for this option.
      if ($total_votes > 0) {
        $percentage = ($option->get('votes')->value / $total_votes) * 100;
        $row['percentage'] = number_format($percentage, 0) . '%';
      }
      else {
        $row['percentage'] = '0%';
      }

      // Build operations.
      $operations = [];
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'url' => $option->toUrl('edit-form'),
      ];
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'url' => $option->toUrl('delete-form'),
      ];

      // Add a message using the messenger service.
      $this->messenger->addMessage($this->t('Created the %label Voting Question.', [
        '%label' => $option->label(),
      ]));

      $row['operations'] = [
        'data' => [
          '#type' => 'operations',
          '#links' => $operations,
        ],
      ];

      $rows[] = $row;
    }

    $build['options_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No options available. Add some options to this question.'),
    ];

    return $build;
  }

}
