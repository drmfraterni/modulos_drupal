<?php

namespace Drupal\form_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements an example form.
 */
class addform extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gestion_form_formBusqueda';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    //SI QUEREMOS INSERTAR CSS Y JAVASCRIPT EN NUESTRO MÓDULO.
    //$form['#attached']['library'][] = 'form_example/form_example_libraries';

    $form['datos_personales'] = array(
      '#type' => 'fieldset',
      '#title' => t('Datos Personales'),
      '#attributes'=>array(
        'class'=>array('mi_clase')
      ),
    );

    $form['datos_personales']['nombre'] = array(
      '#type' => 'textfield',
      '#title' =>$this-> t('Introduzca su nombre'),
      //'#default_value' => $node->title,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );
    $form['datos_personales']['apellido'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Introduzca su Apellido'),
      //'#default_value' => $node->title,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => FALSE,
    );
    $form['datos_personales']['carnet'] = array(
      '#type' => 'number',
      '#title' => $this->t('carnet'),
    );

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      //'#attributes'=>array(
      //  'class'=>array('mibotonprincipal')
      //),
    ];
    $form['actions']['cancelar'] =array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('form_example_cancelar'),
      '#limit_validation_errors' => array(),
      //'#attributes'=>array(
      //  'class'=>array('mibotonprincipal')
      //),

    );



    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!is_numeric($form_state->getValue('carnet')) {
      $form_state->setErrorByName('carnet', $this->t('Debemos introducir un número.'));
    }


  }

  /**
   * {@inheritdoc}
   */



  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message($this->t('El número de teléfono es  @number', ['@number' => $form_state->getValue('telefono')]));

    global $base_url;

    $response = new RedirectResponse($base_url);
    $response->send();

  }

}









 ?>
