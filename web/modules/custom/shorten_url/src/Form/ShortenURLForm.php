<?php

namespace Drupal\shorten_url\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class ShortenURLForm.
 */
class ShortenURLForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shorten_url_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Long URL.
    $form['field_long_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter URL'),
      '#required' => TRUE,
    ];

    // Is vanity URL.
    $form['field_is_vanity_url'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add Vanity Name'),
    ];

    // Vanity Name.
    $form['field_vanity_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Vanity Name'),
      '#maxlength' => 9,
      '#states' => [
        'visible' => [
          ':input[name="field_is_vanity_url"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $field_long_url = $form_state->getValue('field_long_url');
    $field_is_vanity_url = $form_state->getValue('field_is_vanity_url');
    $field_vanity_name = $form_state->getValue('field_vanity_name');

    // Long URL validation.
    if (mb_strlen($field_long_url) > 4) {
      if (!strpos($field_long_url, '.', 1)) {
        $form_state->setErrorByName('url', $this->t('Please enter a valid URL.'));
      }
    }
    else {
      $form_state->setErrorByName('url', $this->t('Please enter a valid URL.'));
    }

    // Vanity URL validation.
    if ($field_is_vanity_url == 1) {
      if (empty($field_vanity_name)) {
        $form_state->setErrorByName('vanity_name', $this->t('Please enter a vanity name.'));
      }
      else {
        // Check short code if already exist.
        $row_count = \Drupal::database()
          ->select('short_urls', 'su')
          ->condition('su.short_code', $field_vanity_name, '=')
          ->countQuery()
          ->execute()
          ->fetchField();

        if ($row_count > 0) {
          $form_state->setErrorByName('vanity_name', $this->t('Vanity name %vanity_name already exist. Please try with another name.', ['%vanity_name' => $field_vanity_name]));
        }
      }
    }
  }

  /**
   * Required submitForm function.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $field_long_url = $form_state->getValue('field_long_url');
    $field_is_vanity_url = $form_state->getValue('field_is_vanity_url');
    $field_vanity_name = $form_state->getValue('field_vanity_name');

    if ($field_is_vanity_url == 1) {
      $short_code = $field_vanity_name;
    }
    else {
      // Called gen shorten url function.
      $short_code = $this->genShortenUrl(7);
    }

    if (!empty($short_code)) {
      $fields = [
        'long_url' => $field_long_url,
        'short_code' => $short_code,
        'is_vanity_url' => $field_is_vanity_url,
        'uid' => \Drupal::currentUser()->id(),
        'created' => strtotime(date('d-m-y h:i:s')),
      ];

      // Insert in database.
      $url_id = \Drupal::database()
        ->insert('short_urls')
        ->fields($fields)
        ->execute();

      \Drupal::messenger()->addMessage($this->t('%original was shortened to %shortened.',
        ['%original' => $field_long_url, '%shortened' => $short_code]));

      // Url to redirect.
      $path = '/shorten-url/' . $url_id;
      $form_state->setRedirectUrl(Url::fromUserInput($path));
    }
    else {
      \Drupal::messenger()->addError("Oops! something went wrong.");
    }
  }

  /**
   * Generate unique short code.
   */
  public function genShortenUrl($length_of_string) {
    // String of all alphanumeric character.
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    // Shufle the $str_result and returns substring of specified length.
    $short_code = substr(str_shuffle($str_result), 0, $length_of_string);

    // Check short code if already exist.
    $row_count = \Drupal::database()
      ->select('short_urls', 'su')
      ->condition('su.short_code', $short_code, '=')
      ->countQuery()
      ->execute()
      ->fetchField();

    if ($row_count == 0) {
      return $short_code;
    }
    else {
      $this->genShortenUrl($length_of_string);
    }
  }

}
