<?php

namespace Drupal\simple_voting\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Voting Option entity.
 *
 * @ContentEntityType(
 *   id = "voting_option",
 *   label = @Translation("Voting Option"),
 *   base_table = "voting_option",
 *   data_table = "voting_option_field_data",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "title",
 *     "langcode" = "langcode",
 *   },
 *   bundle_entity_type = NULL,
 *   fieldable = TRUE,
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\simple_voting\VotingOptionListBuilder",
 *     "form" = {
 *       "default" = "Drupal\simple_voting\Form\VotingOptionForm",
 *       "add" = "Drupal\simple_voting\Form\VotingOptionForm",
 *       "edit" = "Drupal\simple_voting\Form\VotingOptionForm",
 *       "delete" = "Drupal\simple_voting\Form\VotingOptionDeleteForm",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/voting-options/{voting_option}",
 *     "add-form" = "/admin/structure/voting-options/add",
 *     "edit-form" = "/admin/structure/voting-options/{voting_option}/edit",
 *     "delete-form" = "/admin/structure/voting-options/{voting_option}/delete",
 *     "collection" = "/admin/structure/voting-options",
 *   },
 *   admin_permission = "administer voting options"
 * )
 */
class VotingOption extends ContentEntityBase implements EntityInterface {

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

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setCardinality(1);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setDescription(t('Option image'))
      ->setTranslatable(TRUE)
      ->setSettings([
        'file_directory' => 'voting_options/[date:custom:Y-m]',
        'alt_field' => TRUE,
        'alt_field_required' => TRUE,
        'file_extensions' => 'png jpg jpeg',
        'max_filesize' => '',
        'max_resolution' => '',
        'min_resolution' => '',
        'default_image' => [
          'uuid' => NULL,
          'alt' => '',
          'title' => '',
          'width' => NULL,
          'height' => NULL,
        ],
        'title_field' => TRUE,
        'title_field_required' => FALSE,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'image',
        'settings' => [
          'image_style' => 'medium',
          'image_link' => '',
        ],
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'settings' => [
          'preview_image_style' => 'thumbnail',
          'progress_indicator' => 'throbber',
        ],
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setCardinality(1);

    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setDescription(t('The question this option belongs to.'))
      ->setSetting('target_type', 'voting_question')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['votes'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Votes'))
      ->setDescription(t('Number of votes for this option.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'number_integer',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the option was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the option was last edited.'));

    return $fields;
  }

}
