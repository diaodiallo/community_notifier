<?php

namespace Drupal\community_notifier\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Community notifier frequency entity.
 *
 * @ingroup community_notifier
 *
 * @ContentEntityType(
 *   id = "community_notifier_frequency",
 *   label = @Translation("Community notifier frequency"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\community_notifier\CommunityNotifierFrequencyListBuilder",
 *     "views_data" = "Drupal\community_notifier\Entity\CommunityNotifierFrequencyViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\community_notifier\Form\CommunityNotifierFrequencyForm",
 *       "add" = "Drupal\community_notifier\Form\CommunityNotifierFrequencyForm",
 *       "edit" = "Drupal\community_notifier\Form\CommunityNotifierFrequencyForm",
 *       "delete" = "Drupal\community_notifier\Form\CommunityNotifierFrequencyDeleteForm",
 *     },
 *     "access" = "Drupal\community_notifier\CommunityNotifierFrequencyAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\community_notifier\CommunityNotifierFrequencyHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "community_notifier_frequency",
 *   admin_permission = "administer community notifier frequency entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/community_notifier_frequency/{community_notifier_frequency}",
 *     "add-form" = "/admin/structure/community_notifier_frequency/add",
 *     "edit-form" = "/admin/structure/community_notifier_frequency/{community_notifier_frequency}/edit",
 *     "delete-form" = "/admin/structure/community_notifier_frequency/{community_notifier_frequency}/delete",
 *     "collection" = "/admin/structure/community_notifier_frequency",
 *   },
 *   field_ui_base_route = "community_notifier_frequency.settings"
 * )
 */
class CommunityNotifierFrequency extends ContentEntityBase implements CommunityNotifierFrequencyInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Community notifier frequency entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Community notifier frequency entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['flag_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Flag Id'))
      ->setDescription(t('The flag id.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['entity_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Flagged Entity Id'))
      ->setDescription(t('The flagged entity id.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      //->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'integer',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['entity_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity Name'))
      ->setDescription(t('The flagged entity name.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setReadOnly(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['frequency'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Frequency'))
      ->setDescription(t('The frequency of the notifier (immediately, daily or weekly).'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('immediately')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Community notifier frequency is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }
  public function setFrequency($frequency){
    $this->set('frequency', $frequency);
    return $this;
  }
  public function getFrequency() {
    return $this->get('frequency')->value;
  }
  public function setFlaggedEntityId($entity_id){
    $this->set('entity_id', $entity_id);
    return $this;
  }
  public function getFlaggedEntityId() {
    return $this->get('entity_id')->value;
  }
  public function setFlagId($flag_id){
    $this->set('flag_id', $flag_id);
    return $this;
  }
  public function getFlagId() {
    return $this->get('flag_id')->value;
  }
  public function setFlaggedEntityName($entity_name){
    $this->set('entity_name', $entity_name);
    return $this;
  }
  public function getFlaggedEntityName() {
    return $this->get('entity_name')->value;
  }
}
