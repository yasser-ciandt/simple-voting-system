<?php

namespace Drupal\simple_voting;

use Drupal\Core\Url;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for voting questions.
 */
class VotingQuestionListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['identifier'] = $this->t('Identifier');
    $header['status'] = $this->t('Status');
    $header['show_results'] = $this->t('Show Results');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\simple_voting\Entity\VotingQuestion $entity */
    $row['id'] = $entity->id();
    $row['title'] = $entity->get('title')->value;
    $row['identifier'] = $entity->get('identifier')->value;
    $row['status'] = $entity->get('status')->value ? $this->t('Active') : $this->t('Inactive');
    $row['show_results'] = $entity->get('show_results')->value ? $this->t('Yes') : $this->t('No');
    return $row + $this->getParentRow($entity);
  }

  /**
   * Gets the parent row.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for this row of the list.
   *
   * @return array
   *   The parent row.
   */
  protected function getParentRow(EntityInterface $entity) {
    return parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    $operations['manage_options'] = [
      'title' => $this->t('Manage Options'),
      'weight' => 15,
      'url' => Url::fromRoute('entity.voting_question.manage_options', [
        'voting_question' => $entity->id(),
      ]),
    ];

    return $operations;
  }

}
