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




class BuscarUsuriosForm extends FormBase {

  protected $envioconf = FALSE;



  public function getFormId(){
    // NOMBRE DEL FORMULARIO
    return 'Buscar Usuarios';
  }

  public function buildForm(array $form, FormStateInterface $form_state){

    //$this->envioconf = TRUE;

    if (!empty($this->envioconf)){
      drupal_set_message('EL FORMULARIO SE HA ENVIDO');
    }else{

      $form ['nombre'] = array (
        '#type'     => 'textfield',
        '#title'    => $this->t('Nombre'),
        '#required' => TRUE,
      );
      $form ['apellidos'] = array (
        '#type'     => 'textfield',
        '#title'    => $this->t('Apellidos'),
        '#required' => TRUE,
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
    drupal_set_message($this->t('Su número telefónico es: @number', array('@number' => $form_state->getValue('phone_number'))));

    global $base_url;

    $ruta='/admin/gestion/vista-all-usuarios/'.$form_state->getValue('nombre');
    //$ruta='/node/add/bz_presencia?id='.$form_state->getValue('nombre');
    //dpm($base_url);

    $response = new RedirectResponse($base_url."".$ruta);
    $response->send();
    return;

  }

}


?>
