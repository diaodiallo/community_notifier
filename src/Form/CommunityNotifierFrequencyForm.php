<?php

namespace Drupal\community_notifier\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Community notifier frequency edit forms.
 *
 * @ingroup community_notifier
 */
class CommunityNotifierFrequencyForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\community_notifier\Entity\CommunityNotifierFrequency */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Community notifier frequency.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Community notifier frequency.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.community_notifier_frequency.canonical', ['community_notifier_frequency' => $entity->id()]);
  }

}
