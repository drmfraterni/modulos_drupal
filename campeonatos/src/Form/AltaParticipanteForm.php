<?php

namespace Drupal\campeonatos\Form;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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



class AltaParticipanteForm extends FormBase {

  protected $envioconf = FALSE;

  protected $clave;

  protected $textoHtmlForm = '';



  public function getFormId(){
    // NOMBRE DEL FORMULARIO
    return 'form_alta_participante';
  }

  public function buildForm(array $form, FormStateInterface $form_state){


      if (!empty($this->envioconf)){

        global $base_url;
        $ruta = $base_url.'/campeonato/'.$this->clave;
        $enlace = '<a href='.$ruta.'>ENTRAR EN LA PUNTUACIÓN</a>';

        $textoInformativo = '<p>Se ha dado de alta en la Liga Interna de Boulder Zone Retiro/p>
        <p>Su clave para acceder a la puntuación es <strong>'.$this->clave.'</strong><p>
        <p>Para llevar los puntos en la competición haga clic en el siguiente enlace:<p>
        <p align="center"><h2>'.$enlace.'</h2><p>';




        //var_dump($ruta);



        $form ['final']= array (
          '#type' => 'markup',
          '#markup' => $textoInformativo,
          '#format' => 'full_html',

      );



      }else{

        $this->textoHtmlForm = "Formulario de alta para la prueba de Liga Interna de Boulder Zone Retiro";

        $form ['text_inf']= array (
            '#type'     => 'fieldset',
            '#title'    => $this->t('TEXTO INFORMATIVO'),
        );

        $form ['text_inf']['texto_inf']= array (
            '#type' => 'markup',
            '#markup' => $this->t($this->textoHtmlForm),
            '#format' => 'full_html',
            '#required' => FALSE,

        );
        $form ['datos_participante']= array (
            '#type'     => 'fieldset',
            '#title'    => $this->t('DATOS PERSONALES'),
        );
        $form ['datos_participante']['nombre'] = array (
          '#type'     => 'textfield',
          '#title'    => $this->t('Nombre'),
          '#required' => TRUE,
        );
        $form ['datos_participante']['apellidos'] = array (
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
          '#required' => TRUE,
        );
        $form ['otrosDatos']['cd_tarjeta'] = array (
          '#type'     => 'textfield',
          '#title'    => $this->t('CARNET de SOCIO/A'),
          '#required' => FALSE,
        );
        $form['otrosDatos']['socio'] = array(
          '#type' => 'checkbox',
          '#title' => $this->t('SOCIO'),
          );
        /*
        type select taxonomy drupal 8
        $dropdown_vocab = \Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('vocab_one_name');

        foreach ($dropdown_vocab as $term) {
          //var_dump($term->tid);
        }

        $element['subfield_one'] = array(
          '#type' => 'select',
          '#empty_option' => 'Select...',
          '#options' => $dropdown_vocab,  // the vocabulary populates the select field here
          '#title' => t('Your field title'),
          '#size' => 1,
          '#required' => FALSE,
          '#default_value' => isset($items[$delta]->subfield_one) ? $items[$delta]->subfield_one : NULL,
      );


        */
        /*$form['otrosDatos']['categoria']= [
          '#type' => 'entity_autocomplete',
          '#target_type' => 'node',
          '#title' => $this->t('Categoria'),
          '#description' => $this->t('Introduce la tu categoría .'),
          '#tags' => TRUE,
          '#selection_settings' => array(
              'target_bundles' => array('page', 'headquarter'),
          ),

        ];*/

        $form ['submit'] = [
            '#type'  => 'submit',
            '#value' => $this->t('Enviar'),
        ];

      }

      return $form;
  }

  /**
 * {@inheritdoc}
 */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // Uno de los campos tiene que estar relleno
    // el campo nombre no puede tener caracteres numéricos
    // el campo cd_tarjeta no puede tener carácteres alfabéticos.


    $control=FALSE;
    $patron_texto = '/^[a-zA-ZáéíóúÁÉÍÓÚäëïöüÄËÏÖÜàèìòùÀÈÌÒÙ\s]+$/';
    $patron_carnet='/^[0-9]{9}$/';

    if (empty($form_state->getValue('nombre'))) {
      $control=FALSE;
    }else if (!preg_match($patron_texto, $form_state->getValue('nombre'))){
      $form_state->setErrorByName('nombre', $this->t('No puede contener números'));
    }else{
      $control=TRUE;
    }

    if (empty($form_state->getValue('cd_tarjeta'))&&($control==FALSE)) {
      $control=FALSE;
    }else if (!preg_match($patron_carnet, $form_state->getValue('cd_tarjeta'))&&($control=FALSE)){
      $form_state->setErrorByName('cd_tarjeta', $this->t('Sólo puede contener números'));
    }else{
      $control=TRUE;
    }

    if ($control==FALSE){
      $form_state->setErrorByName('control', $this->t('hay que rellenar alguno de los campos'));
    }


  }



/**
* {@inheritdoc}
*/

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->envioconf = TRUE;



    //posibles parámetros para la Búsqueda
    $nombre=$form_state->getValue('nombre');
    $apellidos = $form_state->getValue('apellidos');
    $tarjeta = $form_state->getValue('cd_tarjeta');
    $correo = $form_state->getValue('email');
    $socio = $form_state->getValue('socio');
    //$categoria = $form_state->getValue('categoria');

    //*******GENERAL CLAVE*******//
    $this->clave = self::generateRandomString();
    //******GENERAR CLAVE********//
    $title = $apellidos.', '.$nombre.' - '.$this->clave;

    /*
    $this->messenger()->addStatus($this->t('NOMBRE @nombre', ['@nombre' => $form_state->getValue('nombre')]));
    $this->messenger()->addStatus($this->t('APLLIDOS @apellidos', ['@apellidos' => $form_state->getValue('apellidos')]));
    $this->messenger()->addStatus($this->t('SOCIO @socio', ['@socio' => $form_state->getValue('socio')]));
    $this->messenger()->addStatus($this->t('TARJETA @tarjeta', ['@tarjeta' => $form_state->getValue('cd_tarjeta')]));
    //$this->messenger()->addStatus($this->t('CATEGORIA: @categoria', ['@categoria' => $form_state->getValue('categoria')]));
    $this->messenger()->addStatus($this->t('CLAVE: @clave', ['@clave' => $this->clave]));
    */



    //COMPROBAMOS QUE EL USUARIO NO ESTÉ YA DADO DE ALTA.

    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'bz_participantes')
    ->condition('field_par_correo', $correo);
    $nids = $query->execute();

    /* foreach ($nids as $n) {
      $nid = $n;
    }

    var_dump($nids);

    $node = Node::load($nid);
    $conCorreo = $node->get('field_par_correo')->value;
    $conClave = $node->get('field_par_clave')->value;

    */
    //var_dump($nids);

    if (!empty($nids)){
      var_dump($nids);
      foreach ($nids as $n) {
        $nid = $n;
      }

      $node = Node::load($nid);
      $conCorreo = $node->get('field_par_correo')->value;
      $conClave = $node->get('field_par_clave')->value;
      //var_dump($node->get('field_par_correo')->value);
      $this->clave = $conClave;
      $this->messenger()->addStatus($this->t('HEMOS COMPROBADO QUE USTED ESTE DADO DE ALTA'));
      $this->messenger()->addStatus($this->t('CLAVE: @clave', ['@clave' => $this->clave]));
      $this->messenger()->addStatus($this->t('CORREO: @correo', ['@correo' => $conCorreo]));

    } else {

      // CREAMOS UN NUEVO USUARIO Y CLAVE DE PUNTOS
      $node = Node::create([
        'type' => 'bz_participantes',
        'title' => $title,
        'field_par_nombre' => $nombre,
        'field_par_apellidos' => $apellidos,
        'field_par_correo' => $correo,
        'field_par_clave' => $this->clave,
        'field_par_carnet' => $tarjeta,
        //falta categoria//
        'field_par_socio'=>$socio,
        'uid' => 0,
      ]);
      $node->status = 1;
      $node->save();

    }

    //$node->save();

    //global $base_url;
    //$response = new RedirectResponse($base_url."".$ruta);
    //$response->send();

    $form_state->setRebuild();

    //return;

  }


  //*******FUNCIÓN PARA GENERAR CLAVE*************//
  function generateRandomString($length = 6 ) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


}




?>
