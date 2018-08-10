<?php
/**
 * Created by PhpStorm.
 * User: mmwebaze
 * Date: 8/28/2017
 * Time: 7:37 AM
 */

namespace Drupal\community_notifier\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CommunityNotifierSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'community_notifier_settings';
  }
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['community_notifier_settings'];
  }
  /*
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('community_notifier.settings');
    $daily = array(
      0 => $this->t('anytime'), 1 => $this->t('0000 - 0800 hrs'),
      2 => $this->t('0800 - 1600 hrs'), 3 => $this->t('1600 - 2400 hrs')
    );
    $weekly = array(
      0 => $this->t('Monday'), 1 => $this->t('Tuesday'),
      2 => $this->t('Wednesday'), 3 => $this->t('Thursday'),
      4 => $this->t('Friday'), 5 => $this->t('Saturday'),
      6 => $this->t('Sunday')
    );
    $settings = $config->get('settings');
   // var_dump($settings['frequency']['daily']);die();
    $form['message_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Message settings'),
    );
    $form['message_settings']['send'] = array(
      '#type' => 'textfield',
      '#size' => 3,
      '#maxlength' => 4,
      '#title' => $this->t('Number of comments to send out per email. (0) means everthing.'),
      '#default_value' => isset($settings['messages']['send']) ? $settings['messages']['send'] : 0,
      '#required' => TRUE,
    );
    $form['message_settings']['length'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Length of each comment body sent out in email. (0) means entire comment body.'),
      '#size' => 3,
      '#maxlength' => 4,
      '#default_value' => isset($settings['messages']['length']) ? $settings['messages']['length'] : 0,
      '#required' => TRUE,
    );
    $form['message_settings']['separator'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email message comment separator.'),
      '#size' => 1,
      '#maxlength' => 1,
      '#default_value' => isset($settings['messages']['separator'])? $settings['messages']['separator'] : '*',
      '#required' => TRUE,
    );
    $form['frequency_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Email frequency settings'),
    );
    $form['frequency_settings']['daily'] = array(
      '#type' => 'radios',
      '#title' => $this->t('daily'),
      '#default_value' => isset($settings['frequency']['daily']) ? $settings['frequency']['daily'] : 0,
      '#options' => $daily,
    );
    $form['frequency_settings']['weekly'] = array(
      '#type' => 'radios',
      '#title' => $this->t('weekly'),
      '#default_value' => isset($settings['frequency']['weekly'])? $settings['frequency']['weekly'] : 6,
      '#options' => $weekly,
    );

    return parent::buildForm($form, $form_state);
  }
  /*
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('community_notifier.settings');
    $config->set('settings.messages.send', $form_state->getValue('send'));
    $config->set('settings.messages.length', $form_state->getValue('length'));
    $config->set('settings.messages.separator', $form_state->getValue('separator'));
    $config->set('settings.frequency.daily', $form_state->getValue('daily'));
    $config->set('settings.frequency.weekly', $form_state->getValue('weekly'));
    $config->save();
    parent::submitForm($form, $form_state);
  }
}