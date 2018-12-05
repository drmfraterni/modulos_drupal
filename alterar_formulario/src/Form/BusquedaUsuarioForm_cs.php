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
    $this->envioconf = TRUE;

    $nombre=$form_state->getValue('nombre');
    $apellido=$form_state->getValue('apellidos');
    $tarjeta=$form_state->getValue('cd_tarjeta');
    drupal_set_message("nombre: ".$nombre);
    drupal_set_message("Apellido: ".$apellido);
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'bz_usuarios');
    $query->condition('field_nombre', $nombre,'=');
    $query->condition('field_apellido1', '%'.$apellido.'%', 'like');

    //$query = \Drupal::entityQuery('node');
    //$query->condition('status', 1);
    //$query->condition('type', 'bz_pruebas');
    //$query->condition('field_pr_nombre', $nombre,'=');
    //$query->condition('field_pr_apellido', $apellido,'=');
    //$query->condition('field_pr_apellido', '%'.$apellido.'%', 'like');

    /*$query->condition('status', 1);
    $query->condition('type', 'bz_usuarios');
    $query->condition('field_nombre', $nombre, '=');
    $query->condition('field_apellido1', $apellido, '=');
    $query->condition('field_cd_tarjeta', $tarjeta, '=');*/
    $result = $query->execute();
    dpm($result);

    //Pasamos todos los nid obtenidos a una variable
    //$nids=array_keys($result);

    //Se cargan todos los nodos a traves de sus id
    $nodes = Node::loadMultiple($result);

    //Cargamos todos los nodos a través de sus id
    drupal_set_message("el codigo es: ".$nodes);
    //dpm($nodes);

    foreach($nodes as $key => $node) {
          drupal_set_message($node);
    }


    //$nodes = \Drupal::entityTypeManager()->getStorage('node')->load($nuevoCodigo);
    //drupal_set_message('NOMBRE : ' . $nodes->get('title'));
    //drupal_set_message('VARIABLE 1 : '.$var1 . ': VARIABLE 2:' . $var2);



  }


}


?>
