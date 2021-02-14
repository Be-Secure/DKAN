<?php

namespace Drupal\metastore_entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\metastore_entity\Entity\MetastoreSchema;
use Opis\JsonSchema\Schema;

/**
 * Class MetastoreSchemaForm.
 */
class MetastoreSchemaForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $metastore_schema = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $metastore_schema->label(),
      '#description' => $this->t("Label for the Metastore schema."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $metastore_schema->id(),
      '#machine_name' => [
        'exists' => '\Drupal\metastore_entity\Entity\MetastoreSchema::load',
      ],
      '#disabled' => !$metastore_schema->isNew(),
    ];

    $form['behaviors'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Behaviors'),
      '#default_value' => $metastore_schema->getBehaviors(),
      '#options' => [
        MetastoreSchema::BEHAVIOR_DATASET => $this->t('Dataset'),
        MetastoreSchema::BEHAVIOR_RESOURCE => $this->t('Resource'),
      ],
    ];

    $form['json_schema'] = [
      '#title' => $this->t('JSON Schema'),
      '#type' => 'text_format',
      '#format' => 'json',
      '#allowed_formats' => ['json'],
      '#default_value' => $metastore_schema->getSchema(),
      '#description' => $this->t('Validation schema'),
      '#element_validate' => [[$this, 'validateSchema']],
      '#required' => TRUE,
    ];

    $form['ui_schema'] = [
      '#title' => $this->t('UI Schema'),
      '#type' => 'text_format',
      '#format' => 'json',
      '#allowed_formats' => ['json'],
      '#default_value' => $metastore_schema->getSchema(),
      '#description' => $this->t('UI schema'),
      '#element_validate' => [[$this, 'validateSchema']],
    ];

    return $form;
  }

  /**
   * Validate the JSON schema. 
   *
   * @param mixed $element
   *   Form element.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param mixed $form
   *   Form array.
   */
  public function validateSchema($element, FormStateInterface $form_state, $form) {
    try {
      Schema::fromJsonString($form_state->getValue('json_schema')['value']);
    }
    catch (\Exception $e) {
      $form_state->setError($element, t('Schema failed validation with message: ":msg"', [':msg' => $e->getMessage()]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $metastore_schema = $this->entity;
    $status = $metastore_schema->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Metastore schema.', [
          '%label' => $metastore_schema->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Metastore schema.', [
          '%label' => $metastore_schema->label(),
        ]));
    }
    $form_state->setRedirectUrl($metastore_schema->toUrl('collection'));
  }

}