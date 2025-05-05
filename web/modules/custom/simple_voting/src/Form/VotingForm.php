<?php

namespace Drupal\simple_voting\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Component\Datetime\TimeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\simple_voting\Entity\VotingQuestion;

/**
 * Provides a voting form.
 */
class VotingForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   *
   * @see \Drupal\simple_voting\Form\VotingForm::__construct()
   */
  protected $entityTypeManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   *
   * @see \Drupal\simple_voting\Form\VotingForm::__construct()
   */
  protected $messenger;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   *
   * @see \Drupal\simple_voting\Form\VotingForm::__construct()
   */
  protected $database;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   *
   * @see \Drupal\simple_voting\Form\VotingForm::__construct()
   */
  protected $currentUser;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   *
   * @see \Drupal\simple_voting\Form\VotingForm::__construct()
   */
  protected $time;

  /**
   * Constructs a new VotingForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger, Connection $database, AccountProxyInterface $current_user, TimeInterface $time) {
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
    $this->database = $database;
    $this->currentUser = $current_user;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('database'),
      $container->get('current_user'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_voting_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?VotingQuestion $voting_question = NULL) {
    $user = $this->currentUser;
    $is_admin = $user->hasPermission('administer voting questions');

    // Check if user has already voted.
    $already_voted = FALSE;
    if (!$is_admin && $user->id() != 0) {
      $existing_vote = $this->database->select('simple_voting_user_votes', 'v')
        ->fields('v', ['option_id'])
        ->condition('uid', $user->id())
        ->condition('question_id', $voting_question->id())
        ->execute()
        ->fetchField();
      if ($existing_vote) {
        $already_voted = TRUE;
      }
    }

    if (!$voting_question) {
      return [];
    }

    // Store the question ID in the form for later use.
    $form['question_id'] = [
      '#type' => 'value',
      '#value' => $voting_question->id(),
    ];

    // Show info alert if already voted (below the title)
    if (!empty($already_voted)) {
      $form['already_voted_info'] = [
        '#type' => 'markup',
        '#markup' => '<div class="alert alert-warning mt-2 mb-4" role="alert">' . $this->t('You have already voted on this question.') . '</div>',
        '#weight' => 1,
      ];
    }

    // Cargar todas las opciones de votación para calcular los porcentajes.
    $voting_options = $this->entityTypeManager
      ->getStorage('voting_option')
      ->loadByProperties(['question_id' => $voting_question->id()]);

    $total_votes = 0;
    foreach ($voting_options as $opt) {
      $total_votes += $opt->get('votes')->value;
    }

    // Get all options for this question.
    $options = $this->entityTypeManager->getStorage('voting_option')
      ->loadByProperties(['question_id' => $voting_question->id()]);

    $radio_options = [];
    $form['options_container'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    foreach ($options as $option) {
      $option_container = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['voting-option-wrapper'],
        ],
      ];

      // Option label.
      $option_container['label'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $option->label(),
        '#attributes' => [
          'class' => ['option-label'],
        ],
      ];

      // Description if available.
      if ($description = $option->get('description')->value) {
        $option_container['description'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $description,
          '#attributes' => [
            'class' => ['option-description'],
          ],
        ];
      }

      // Image if available.
      if ($image = $option->get('image')->entity) {
        $option_container['image'] = [
          '#theme' => 'image_style',
          '#style_name' => 'medium',
          '#uri' => $image->getFileUri(),
          '#alt' => $option->get('image')->alt,
          '#title' => $option->get('image')->title,
        ];
      }

      // Show votes ONLY if the user already voted and show_results is enabled.
      if (!empty($already_voted) && $voting_question->get('show_results')->value) {
        $option_container['votes'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->t('Votes: @count', ['@count' => $option->get('votes')->value]),
          '#attributes' => [
            'class' => ['voting-count'],
          ],
        ];
      }

      $option_votes = $option->get('votes')->value;
      $percentage = $total_votes > 0 ? round(($option_votes / $total_votes) * 100, 2) : 0;

      $option_text = $option->label();

      if ($is_admin) {
        $option_text .= ' (' . $this->t('@count votes', ['@count' => $option_votes]) . ' - ' . $percentage . '%)';
      }
      $radio_options[$option->id()] = $option_text;
      $form['options_container'][$option->id()] = $option_container;
    }

    $form['selected_option'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select an option'),
      '#options' => $radio_options,
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Vote'),
      '#button_type' => 'primary',
    ];

    // Add some CSS.
    $form['#attached']['library'][] = 'simple_voting/voting_form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $question_id = $form_state->getValue('question_id');
    $option_id = $form_state->getValue('selected_option');

    // Load the selected option.
    $option = $this->entityTypeManager->getStorage('voting_option')->load($option_id);
    if (!$option) {
      $this->messenger->addError($this->t('Invalid option selected.'));
      return;
    }

    // Verify the option belongs to the question.
    if ($option->get('question_id')->target_id != $question_id) {
      $this->messenger->addError($this->t('Invalid voting option.'));
      return;
    }

    $user = $this->currentUser;

    // Check if user has already voted (non-admin users only)
    if (!$user->hasPermission('administer voting questions') && $user->id() != 0) {
      $existing_vote = $this->database->select('simple_voting_user_votes', 'v')
        ->fields('v', ['id'])
        ->condition('uid', $user->id())
        ->condition('question_id', $question_id)
        ->execute()
        ->fetchField();

      if ($existing_vote) {
        $this->messenger->addError($this->t('You have already voted on this question.'));
        $form_state->setRedirect('simple_voting.question', ['voting_question' => $question_id]);
        return;
      }
    }

    // Record the vote.
    $current_votes = $option->get('votes')->value;
    $option->set('votes', $current_votes + 1);
    $option->save();

    // Record the user's vote if they are logged in.
    if ($user->id() != 0) {
      $this->database->insert('simple_voting_user_votes')
        ->fields([
          'uid' => $user->id(),
          'question_id' => $question_id,
          'option_id' => $option_id,
          'timestamp' => $this->time->getRequestTime(),
        ])
        ->execute();
    }

    $this->messenger->addStatus($this->t('Your vote has been recorded.'));
    $form_state->setRedirect('simple_voting.question', ['voting_question' => $question_id]);
  }

}
