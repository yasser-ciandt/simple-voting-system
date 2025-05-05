<?php

namespace Drupal\simple_voting\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Voting Question forms.
 */
class VotingQuestionForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
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
        $this->messenger()->addMessage($this->t('Created the %label Voting Question.', [
          '%label' => $entity->label(),
        ]));
        // Redirect to options management page for new questions.
        $form_state->setRedirect('entity.voting_question.manage_options', [
          'voting_question' => $entity->id(),
        ]);
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Voting Question.', [
          '%label' => $entity->label(),
        ]));
        // Stay on edit form for existing questions.
        $form_state->setRedirectUrl($entity->toUrl('edit-form'));
    }
  }

}
