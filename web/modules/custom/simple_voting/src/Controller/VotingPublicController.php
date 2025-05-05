<?php

namespace Drupal\simple_voting\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\simple_voting\Entity\VotingQuestion;
use Drupal\simple_voting\Entity\VotingOption;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for public voting interface.
 */
class VotingPublicController extends ControllerBase {

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
   * Constructs a VotingPublicController object.
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
   * Gets the title for a voting question page.
   *
   * @param \Drupal\simple_voting\Entity\VotingQuestion $voting_question
   *   The voting question entity.
   *
   * @return string
   *   The page title.
   */
  public function getQuestionTitle(VotingQuestion $voting_question) {
    return $voting_question->label();
  }

  /**
   * Lists all active voting questions.
   *
   * @return array
   *   A render array.
   */
  public function listQuestions() {
    // If the user is not logged in, show a message and a link to the login page.
    if ($this->currentUser->isAnonymous()) {
      $login_url = Url::fromRoute('user.login')->toString();
      return [
        '#markup' => '<div class="alert alert-warning" role="alert">' . $this->t('You must <a href=":login">log in</a> to view the voting questions.', [':login' => $login_url]) . '</div>',
      ];
    }

    // If the voting system is disabled, show a message instead of questions.
    if (!$this->config('simple_voting.settings')->get('system_enabled')) {
      return [
        '#markup' => '<div class="alert alert-danger" role="alert">' . $this->t('The voting system is currently disabled.') . '</div>',
      ];
    }
    // Get all active questions.
    $questions = $this->entityTypeManager->getStorage('voting_question')
      ->loadByProperties(['status' => 1]);

    $items = [];
    foreach ($questions as $question) {
      $items[] = [
        '#markup' => '<div class="card mb-4 shadow-sm"><div class="card-body d-flex justify-content-between align-items-center">'
        . '<span class="h5 mb-0">' . htmlspecialchars($question->label()) . '</span>'
        . '<a href="' . Url::fromRoute('simple_voting.question', ['voting_question' => $question->id()])->toString() . '" class="btn btn-success ms-3">'
        . $this->t('Vote') . '</a></div></div>',
      ];
    }

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['container', 'py-5']],
      'list' => [
        '#type' => 'markup',
        '#markup' => empty($items)
          ? '<div class="alert alert-info">' .
        $this->t('No voting questions available.') . '</div>'
          : implode("\n", array_map(function ($item) {
            return $item['#markup'];
          }, $items)),
      ],
    ];
  }

  /**
   * Displays a voting question and its options.
   *
   * @param \Drupal\simple_voting\Entity\VotingQuestion $voting_question
   *   The voting question entity.
   *
   * @return array
   *   A render array.
   */
  public function viewQuestion(VotingQuestion $voting_question) {
    // If the voting system is disabled, show a message instead of question details.
    if (!$this->config('simple_voting.settings')->get('system_enabled')) {
      return [
        '#markup' => '<div class="alert alert-danger" role="alert">' . $this->t('The voting system is currently disabled.') . '</div>',
      ];
    }
    // Only show active questions.
    if (!$voting_question->get('status')->value) {
      return [
        '#markup' => $this->t('This voting question is not active.'),
      ];
    }

    // Return the voting form.
    return $this->formBuilder()->getForm('Drupal\simple_voting\Form\VotingForm', $voting_question);
  }

  /**
   * Records a vote for an option.
   *
   * @param \Drupal\simple_voting\Entity\VotingQuestion $voting_question
   *   The voting question entity.
   * @param \Drupal\simple_voting\Entity\VotingOption $voting_option
   *   The voting option entity.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response.
   */
  public function vote(VotingQuestion $voting_question, VotingOption $voting_option, Request $request) {
    // Verify the option belongs to the question.
    if ($voting_option->get('question_id')->target_id != $voting_question->id()) {
      $this->messenger()->addError($this->t('Invalid voting option.'));
      return new RedirectResponse($voting_question->toUrl()->toString());
    }

    // Record the vote.
    $current_votes = $voting_option->get('votes')->value;
    $voting_option->set('votes', $current_votes + 1);
    $voting_option->save();

    $this->messenger()->addStatus($this->t('Your vote has been recorded.'));

    // Redirect back to the question page.
    if ($request->isXmlHttpRequest()) {
      return [
        '#type' => 'ajax',
        '#commands' => [
          [
            'command' => 'redirect',
            'url' => $voting_question->toUrl()->toString(),
          ],
        ],
      ];
    }

    return new RedirectResponse($voting_question->toUrl()->toString());
  }

  /**
   * Displays the voting system documentation.
   *
   * @return array
   *   A render array.
   */
  public function documentation() {
    // Replace 1 with the actual NID of your documentation node.
    $nid = 1;
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    if ($node) {
      return $this->entityTypeManager->getViewBuilder('node')->view($node, 'full');
    }
    // Fallback if node not found.
    return [
      '#markup' => $this->t('Documentation page not found.'),
    ];
  }

}
