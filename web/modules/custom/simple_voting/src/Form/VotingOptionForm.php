<?php

namespace Drupal\simple_voting\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Voting Option forms.
 */
class VotingOptionForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $entity = $this->entity;

    // Get the question_id from the route parameters.
    $question_id = \Drupal::request()->query->get('question_id');
    if ($question_id && $entity->isNew()) {
      $entity->set('question_id', $question_id);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Voting Option.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Voting Option.', [
          '%label' => $entity->label(),
        ]));
    }

    // Redirect to the question's options page.
    $form_state->setRedirect('entity.voting_question.manage_options', [
      'voting_question' => $entity->get('question_id')->target_id,
    ]);
  }

}
