<?php

declare(strict_types=1);

namespace Drupal\Tests\field\Functional;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Parent class for Field API tests.
 */
abstract class FieldTestBase extends BrowserTestBase {

  /**
   * Generate random values for a field_test field.
   *
   * @param int $cardinality
   *   Number of values to generate.
   *
   * @return array
   *   An array of random values, in the format expected for field values.
   */
  public function _generateTestFieldValues($cardinality) {
    $values = [];
    for ($i = 0; $i < $cardinality; $i++) {
      // field_test fields treat 0 as 'empty value'.
      $values[$i]['value'] = mt_rand(1, 127);
    }
    return $values;
  }

  /**
   * Assert that a field has the expected values in an entity.
   *
   * This function only checks a single column in the field values.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to test.
   * @param string $field_name
   *   The name of the field to test
   * @param array $expected_values
   *   The array of expected values.
   * @param string $langcode
   *   (Optional) The language code for the values. Defaults to
   *   \Drupal\Core\Language\LanguageInterface::LANGCODE_DEFAULT.
   * @param string $column
   *   (Optional) The name of the column to check. Defaults to 'value'.
   */
  public function assertFieldValues(EntityInterface $entity, $field_name, $expected_values, $langcode = LanguageInterface::LANGCODE_DEFAULT, $column = 'value') {
    // Re-load the entity to make sure we have the latest changes.
    $storage = $this->container->get('entity_type.manager')
      ->getStorage($entity->getEntityTypeId());
    $storage->resetCache([$entity->id()]);
    $e = $storage->load($entity->id());

    $field = $values = $e->getTranslation($langcode)->$field_name;
    // Filter out empty values so that they don't mess with the assertions.
    $field->filterEmptyItems();
    $values = $field->getValue();
    $this->assertSameSize($expected_values, $values, 'Expected number of values were saved.');
    foreach ($expected_values as $key => $value) {
      $this->assertEquals($value, $values[$key][$column], "Value $value was saved correctly.");
    }
  }

}
