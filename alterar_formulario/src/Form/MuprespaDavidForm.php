<?php

namespace Drupal\alterar_formulario\Form;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\taxonomy\Entity\Term;




class MuprespaDavidForm extends FormBase {

  protected $envioconf = FALSE;



  public function getFormId(){
    // NOMBRE DEL FORMULARIO
    return 'formulario_de_david';
  }

  public function buildForm(array $form, FormStateInterface $form_state){

    //$this->envioconf = TRUE;

    if (!empty($this->envioconf)){
      drupal_set_message('EL FORMULARIO SE HA ENVIDO');
    }else{

      $form ['prueba']= array (
          '#type'     => 'fieldset',
          $fechaHoy
      );
      $form ['prueba']['nombre'] = array (
        '#type'     => 'textfield',
        '#title'    => $this->t('Nombre'),
        '#required' => TRUE,
      );
      $form ['prueba']['apellidos'] = array (
        '#type'     => 'textfield',
        '#title'    => $this->t('Apellidos'),
        '#required' => TRUE,
      );
      $form ['otrosDatos']= array (
          '#type'     => 'fieldset',
          '#title'    => $this->t('OTROS DATOS'),
      );
      $form ['otrosDatos']['email'] = array (
        '#type'     => 'email',
        '#title'    => $this->t('Email'),
      );
      $form ['otrosDatos']['telefono'] = array (
        '#type'     => 'tel',
        '#title'    => $this->t('Teléfono'),
      );

      $form ['submit'] = [
          '#type'  => 'submit',
          '#value' => $this->t('Enviar'),
      ];


      return $form;

    }


  }

  /**
 * {@inheritdoc}
 */
  public function validateForm(array &$form, FormStateInterface $form_state) {


    $patron_texto = '/^[a-zA-ZáéíóúÁÉÍÓÚäëïöüÄËÏÖÜàèìòùÀÈÌÒÙ\s]+$/';
    $patron_tel='/^[0-9]{9}$/';
    // Hacemos las validaciones necesarias
    // Validación del MONBRE
    if (empty($form_state->getValue('nombre'))) {
        $form_state->setErrorByName('nombre', $this->t('Es necesario introducir un nombre'));
    }else if (!preg_match($patron_texto, $form_state->getValue('nombre'))){
        //print_r(preg_match($patron_texto, $form_state->getValue('nombre')));
        $form_state->setErrorByName('nombre', $this->t('No puede contener números'));
    }



  }



/**
* {@inheritdoc}
*/

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->envioconf = TRUE;
    foreach ($form_state->getValues() as $key => $value) {
        drupal_set_message($key . ': ' . $value);
    }

  }






}


?>
