<?php

namespace Drupal\bz_mensajes\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form with two steps.
 *
 * This example demonstrates a multistep form with text input elements. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class MultistepForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bz_mensajes_multistep_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if ($form_state->has('page_num') && $form_state->get('page_num') == 2) {
      return self::fapiExamplePageTwo($form, $form_state);
    }

    $form_state->set('page_num', 1);

    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('FORMULARIO DE CONTACTO (page 1)'),
    ];

    $form['ms_nombre'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#description' => $this->t('Introduce el nombre.'),
      '#default_value' => $form_state->getValue('ms_nombre', ''),
      '#required' => TRUE,
    ];

    $form['ms_correo'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Correo electrónico'),
      '#description' => $this->t('Introduce el correo electrónico.'),
      '#default_value' => $form_state->getValue('ms_correo', ''),
      '#required' => TRUE,
    ];


    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      // Custom submission handler for page 1.
      '#submit' => ['::fapiExampleMultistepFormNextSubmit'],
      // Custom validation handler for page 1.
      '#validate' => ['::fapiExampleMultistepFormNextValidate'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $page_values = $form_state->get('page_values');

    $this->messenger()->addMessage($this->t('El formulario enviado es de name="@nombre correo electrónico: @email"', [
      '@nombre' => $page_values['ms_nombre'],
      '@email' => $page_values['ms_correo'],
      //'@year_of_birth' => $page_values['birth_year'],
    ]));
    $this->messenger()->addMessage($this->t('El mansaje enviado es', ['@mensaje' => $form_state->getValue('ms_mensaje')]));
    drupal_set_message('Esto es una prueba de lo que se puede hacer');

    
  }

  /**
   * Provides custom validation handler for page 1.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function fapiExampleMultistepFormNextValidate(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Provides custom submission handler for page 1.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function fapiExampleMultistepFormNextSubmit(array &$form, FormStateInterface $form_state) {
    $form_state
      ->set('page_values', [
        // Keep only first step values to minimize stored data.
        'ms_nombre' => $form_state->getValue('ms_nombre'),
        'ms_correo' => $form_state->getValue('ms_correo'),
        //'birth_year' => $form_state->getValue('birth_year'),
      ])
      ->set('page_num', 2)
      // Since we have logic in our buildForm() method, we have to tell the form
      // builder to rebuild the form. Otherwise, even though we set 'page_num'
      // to 2, the AJAX-rendered form will still show page 1.
      ->setRebuild(TRUE);
  }

  /**
   * Builds the second step form (page 2).
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function fapiExamplePageTwo(array &$form, FormStateInterface $form_state) {

    $form['ms_mensaje'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mensaje'),
      '#cols' => 5,
      '#resizable' => TRUE,
      '#description' => $this->t('Introduce el mensaje.'),
      '#default_value' => $form_state->getValue('ms_mensaje', ''),
      '#required' => TRUE,
    ];


    $form['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      // Custom submission handler for 'Back' button.
      '#submit' => ['::fapiExamplePageTwoBack'],
      // We won't bother validating the required 'color' field, since they
      // have to come back to this page to submit anyway.
      '#limit_validation_errors' => [],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Provides custom submission handler for 'Back' button (page 2).
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function fapiExamplePageTwoBack(array &$form, FormStateInterface $form_state) {
    $form_state
      // Restore values for the first step.
      ->setValues($form_state->get('page_values'))
      ->set('page_num', 1)
      // Since we have logic in our buildForm() method, we have to tell the form
      // builder to rebuild the form. Otherwise, even though we set 'page_num'
      // to 1, the AJAX-rendered form will still show page 2.
      ->setRebuild(TRUE);
  }

}
