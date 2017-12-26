<?php

/**
 * @file
 * Contains \Drupal\json2node\Form\PornstarEntryForm.
 */

namespace Drupal\json2node\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

/**
 * Class PornstarEntryForm.
 *
 * @package Drupal\json2node\Form
 */
class PornstarEntryForm extends ConfigFormBase {

  public function __construct(
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($config_factory);
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'json2node.baseurl',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'baseurl_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('json2node.baseurl');
    $form['baseurl'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('baseurl'),
      '#description' => $this->t(''),
      '#default_value' => $config->get('baseurl'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('json2node.baseurl')
      ->set('baseurl', $form_state->getValue('baseurl'))
      ->save();

    $client = \Drupal::httpClient();
    $baseurl = $this->config('json2node.baseurl')->get('baseurl');

    try {
      $response = \Drupal::httpClient()->get($baseurl, array('headers' => array('Accept' => 'text/plain')));
      $data = $response->getBody();

      if (empty($data)) {
        drupal_set_message('Empty response.');
      }
      else {
        $this->createPorn($data);
      }
    }
    catch (RequestException $e) {
      watchdog_exception('json2node', $e);
    }
  }

  /**
   * Create nodes from JSON feed.
   */
  protected function createPorn(string $json) {

    $jsonout = json_decode($json, TRUE);

    $stars = $jsonout[stars];

    foreach ($stars as $pornstar) {

      $node = Node::create(array(
        'type' => 'pornstar',
        'langcode' => 'en',
        'uid' => '1',
        'status' => 1,
        'title' => $pornstar['star']['star_name'],
      ));

      $node->save();

    }

  }

}
