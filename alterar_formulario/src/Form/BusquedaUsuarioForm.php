<?php

namespace Drupal\alterar_formulario\Form;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
use Drupal\node\Entity\Node;




class BusquedaUsuarioForm extends FormBase {

  protected $envioconf = FALSE;



  public function getFormId(){
    // NOMBRE DEL FORMULARIO
    return 'Busqueda usuarios';
  }

  public function buildForm(array $form, FormStateInterface $form_state){

    //$this->envioconf = TRUE;

    if (!empty($this->envioconf)){
      drupal_set_message('EL FORMULARIO SE HA ENVIDO');
    }else{

      $form ['nombre'] = array (
        '#type'     => 'textfield',
        '#title'    => $this->t('Nombre'),
        '#required' => FALSE,
      );
      $form ['apellidos'] = array (
        '#type'     => 'textfield',
        '#title'    => $this->t('Apellidos'),
        '#required' => FALSE,
      );
      $form ['cd_tarjeta'] = array (
        '#type'     => 'number',
        '#title'    => $this->t('cd_tarjeta'),
        '#required' => FALSE,
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

    $control=FALSE;
    $patron_texto = '/^[a-zA-ZáéíóúÁÉÍÓÚäëïöüÄËÏÖÜàèìòùÀÈÌÒÙ\s]+$/';

    if (empty($form_state->getValue('nombre'))) {
      $control=FALSE;
    }else if (!preg_match($patron_texto, $form_state->getValue('nombre'))){
      $form_state->setErrorByName('nombre', $this->t('No puede contener números'));
    }else{
      $control=TRUE;
    }

    if (empty($form_state->getValue('apellidos'))&&($control==FALSE)) {
      $control=FALSE;
    }else if (!preg_match($patron_texto, $form_state->getValue('apellidos'))&&($control=FALSE)){
      $form_state->setErrorByName('apellidos', $this->t('No puede contener números'));
    }else{
      $control=TRUE;
    }

    if (empty($form_state->getValue('cd_tarjeta'))&&($control==FALSE)) {
      $control=FALSE;
    }else{
      $control=TRUE;
    }
    // Hacemos las validaciones necesarias
    // Validación del MONBRE
    if ($control==FALSE){
      $form_state->setErrorByName('control', $this->t('hay que rellenar alguno de los campos'));
    }

  }



/**
* {@inheritdoc}
*/


  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message($this->t('Su número telefónico es: @number', array('@number' => $form_state->getValue('phone_number'))));

    global $base_url;

    //posibles parámetros para la Búsqueda
    $nom=$form_state->getValue('nombre');
    $ape=$form_state->getValue('apellidos');
    $carnet=$form_state->getValue('cd_tarjeta');

    $cond=false;
    dpm('condicion0: '.$cond);

    //$ruta='/admin/gestion/vista-all-usuarios/'.$form_state->getValue('nombre');
    //$ruta='/admin/gestion/vista-all-usuarios?title='.$form_state->getValue('nombre');
    $ruta='/admin/gestion/vista-all-usuarios';


    if ($nom!=null){
      $ruta.='?title='.$nom;
      $cond=true;
    }
    dpm('condicion1: '.$cond);
    if ($ape!=null){
      if ($cond==true){
        $ruta.=' '.$ape;
      }else{
        $ruta.='?title='.$ape;
        $cond=true;
      }
      dpm('condicion2: '.$cond);
    }

    if ($carnet!=null){
      if ($cond==true){
        $ruta.='&field_idusuario_value='.$carnet;
      }else{
        $ruta.='?field_idusuario_value='.$carnet;
      }

    }


    $response = new RedirectResponse($base_url."".$ruta);
    $response->send();
    return;

  }

}


?>
