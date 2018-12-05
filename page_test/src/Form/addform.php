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
    return 'form_example_addform';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['elemento_imagen'] = array(

      '#markup' =>'<img class="mimagen" src="https://comoobtenercredito.com/wp-content/uploads/2018/03/datos-personales.png">',



    );

    $form['#attached']['library'][] = 'form_example/form_example_libraries';

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
    $form['datos_personales']['email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Email'),
    );
    $form['datos_institucionales'] = array(
      '#type' => 'details',
      '#title' => $this->t('Datos Institucionales'),
      '#open'=>true,
    );
    $form['datos_institucionales']['telefono'] = [
      '#type' => 'tel',
      '#title' => $this->t('Introduzca su teléfono'),
    ];
    $form['datos_institucionales']['fecha_contratacion'] = array(
      '#type' => 'date',
      '#title' => $this ->t('Fecha de contratación'),

    );




    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#attributes'=>array(
        'class'=>array('mibotonprincipal')
      ),
    ];
    $form['actions']['cancelar'] =array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('form_example_cancelar'),
      '#limit_validation_errors' => array(),
      '#attributes'=>array(
        'class'=>array('mibotonprincipal')
      ),

    );



    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('telefono')) < 3) {
      $form_state->setErrorByName('telefono', $this->t('Este número de teléfono es muy corto. Por favor introduzca un número de teléfono mayor.'));
    }

    $mystring = $form_state->getValue('email');
    $findme   = '@';
    $pos = strpos($mystring, $findme);

    // Nótese el uso de ===. Puesto que == simple no funcionará como se espera
    // porque la posición de 'a' está en el 1° (primer) caracter.
    if ($pos === false) {
        $form_state->setErrorByName('email', $this->t('El corre electrónico no es válido.'));
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
