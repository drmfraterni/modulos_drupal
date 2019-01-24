<?php

namespace Drupal\bz_contacto\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\node\Entity\Node;

/**
 * Class formularioContacto.
 *
 * @package Drupal\bz_contacto\Form
 */
class formularioContacto extends FormBase {

  protected $step = 1;


  protected $entity_id = null;
  /**
     * Devuelve los valores entre los pasos
     * @var array
  */
  protected $campoPasos=array();


  /**
   * Drupal\Core\Entity\EntityManager definition.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;
  public function __construct(
    EntityManager $entity_manager
  ) {
    $this->entityManager = $entity_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'formulario_de_contacto';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $form_state->setValues($this->campoPasos);

    // Step 1
    if($this->step == 1) {

      $form['Datos personales'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Datos personales'),
      ];
      $form['Datos personales']['ms_nombre'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Nombre'),
          '#maxlength' => 64,
          '#size' => 64,
          '#required' => TRUE,
      ];
      $form['Datos personales']['ms_apellidos'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Apellidos'),
          '#maxlength' => 64,
          '#size' => 64,
      ];
      $form['Datos personales']['ms_correo'] = [
          '#type' => 'email',
          '#title' => $this->t('Correo electrónico'),
          '#default_value' => $form_state->getValue('ms_correo', ''),
          '#description' => $this->t('Introduce el correo electrónico.'),
          '#required' => TRUE,
      ];
      $form['entity_id'] = [
          '#type' => 'hidden',
          '#value' => $entity_id,
      ];


    }

    // Step 2
    if($this->step == 2) {

      $form['Escribe Mensaje'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Address information'),
      ];
      $form['Escribe Mensaje']['mensaje']= [
          '#title' => t('Mensaje'),
          '#type' => 'textarea',
          '#description' => 'Escribe el mensaje',
          '#rows' => 10,
          '#cols' => 60,
          '#resizable' => TRUE,
      ];

    }

    //
    if($this->step < 2) {
      $button_label = $this->t('Siguiente');
    }
    else {
      $button_label = $this->t('Enviar!!');
    }

    $form['submit'] = [
        '#type' => 'submit',
        '#value' => $button_label,
    ];

    return $form;
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
    // con esto recogemos los datos del primer formulario y del segundo.
    $this->campoPasos = $this->campoPasos + $form_state->getValues();
    // Display result.
    if($this->step < 2 ) {
      $form_state->setRebuild();
      //$this->campoPasos=$form_state->getValues();
      $this->step++;
      drupal_set_message('Step 1 Completado!!');
    }else{
        //$this->campoPasos=$form_state->getValues();
        drupal_set_message('Step 2 Completado!!');
        /*foreach ($form_state->getValues() as $key => $value) {
          drupal_set_message($key . ': ' . $value);
        }*/

    }

    $comprobacion=$this->campoPasos['entity_id'];
    /*if (empty($comprobacion)) {
        drupal_set_message('se puede enviar');
    }*/

    /*foreach ($this->campoPasos as $key => $value) {
      drupal_set_message('REGISTRO');
      drupal_set_message($key . ': ' . $value);
    }*/
    /*drupal_set_message('REGISTRO: '.$this->campoPasos['ms_nombre']);*/

    if ($this->step==2){
      drupal_set_message('ENTRAMOS AQUÍ');
      $node = Node::create([
        'type' => 'bz_mensaje',
        'ms_nombre' => $this->campoPasos['ms_nombre'],
        'ms_correo' => $this->campoPasos['ms_correo'],
        'body' => $this->campoPasos['mensaje'],

      ]);

    }



  }

}
