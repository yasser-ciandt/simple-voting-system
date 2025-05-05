<?php

namespace Drupal\simple_voting;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for voting options.
 */
class VotingOptionListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['question'] = $this->t('Question');
    $header['votes'] = $this->t('Votes');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\simple_voting\Entity\VotingOption $entity */
    $row['id'] = $entity->id();
    $row['title'] = $entity->get('title')->value;

    $question = $entity->get('question_id')->entity;
    $row['question'] = $question ? $question->label() : $this->t('N/A');

    $row['votes'] = $entity->get('votes')->value;
    return $row + parent::buildRow($entity);
  }

}
