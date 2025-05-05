<?php

namespace Drupal\simple_voting\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Voting Question entity.
 *
 * @ContentEntityType(
 *   id = "voting_question",
 *   label = @Translation("Voting Question"),
 *   base_table = "voting_question",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "title",
 *   },
 *   fieldable = TRUE,
 *   links = {
 *     "canonical" = "/admin/structure/voting-questions/{voting_question}",
 *     "add-form" = "/admin/structure/voting-questions/add",
 *     "edit-form" = "/admin/structure/voting-questions/{voting_question}/edit",
 *     "delete-form" = "/admin/structure/voting-questions/{voting_question}/delete",
 *     "collection" = "/admin/structure/voting-questions",
 *     "manage-options" = "/admin/structure/voting-questions/{voting_question}/options"
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\simple_voting\VotingQuestionListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\simple_voting\Form\VotingQuestionForm",
 *       "add" = "Drupal\simple_voting\Form\VotingQuestionForm",
 *       "edit" = "Drupal\simple_voting\Form\VotingQuestionForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "links" = {
 *       "canonical" = "/admin/structure/voting-questions/{voting_question}",
 *       "add-form" = "/admin/structure/voting-questions/add",
 *       "edit-form" = "/admin/structure/voting-questions/{voting_question}/edit",
 *       "delete-form" = "/admin/structure/voting-questions/{voting_question}/delete",
 *       "collection" = "/admin/structure/voting-questions",
 *       "manage-options" = "/admin/structure/voting-questions/{voting_question}/options",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/voting-questions/{voting_question}",
 *     "add-form" = "/admin/structure/voting-questions/add",
 *     "edit-form" = "/admin/structure/voting-questions/{voting_question}/edit",
 *     "delete-form" = "/admin/structure/voting-questions/{voting_question}/delete",
 *     "collection" = "/admin/structure/voting-questions",
 *   },
 *   admin_permission = "administer voting questions"
 * )
 */
class VotingQuestion extends ContentEntityBase implements EntityInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['identifier'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Identifier'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 64)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->addConstraint('UniqueField')
      ->setDisplayConfigurable('form', TRUE);

    $fields['show_results'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Show Results'))
      ->setDescription(t('Whether to show voting results to users after voting.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setDescription(t('Whether the question is active.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the question was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the question was last edited.'));

    return $fields;
  }

}
