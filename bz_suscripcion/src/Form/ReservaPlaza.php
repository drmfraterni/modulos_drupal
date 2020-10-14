<?php

namespace Drupal\bz_suscripcion\Form;

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



class ReservaPlaza extends FormBase {

  protected $envioconf = FALSE;

  protected $textoHtmlForm = '';



  public function getFormId(){
    // NOMBRE DEL FORMULARIO
    return 'form_reserva_plaza';
  }

  public function buildForm(array $form, FormStateInterface $form_state){


      if (!empty($this->envioconf)){

        global $base_url;


        //$ruta = $base_url.'/campeonato/'.$this->clave;
        //$enlace = '<a href='.$ruta.'>ENTRAR EN LA PUNTUACIÓN</a>';

        $nombre = $form_state->getValue('nombre');
        $apellidos = $form_state->getValue('apellido1')." ".$form_state->getValue('apellido2');
        $nombreCompleto = $nombre. " ".$apellidos;
        $correo = $form_state->getValue('email');

        $textoInformativo = '<p>Estimad@ '.$nombreCompleto.': </p>
        <p>Ha reservado plaza para realizar el entrenamineto en nuestra sala Boulder Zone Retiro
        <br/> Los datos que hemos recibido de su reservas son:
        <ul>
          <li>Nombre: '. $nombreCompleto .'</li>
          <li>Email: '. $correo .'</li>
        </ul>
        En breve recibirá un correo con toda la información al respecto de la reserva
        </p>';



        $form ['final'] = array (
          '#type' => 'markup',
          '#markup' => $textoInformativo,
          '#format' => 'full_html',

        );



      }else{

        // Texto que vienen de la configuraciónode_access
        // Configuración > Servicio web > bz_consentimiento

        $textHtml = $config = \Drupal::config('bz_suscripcion.settings');
        $textoConsent = $textHtml->get('proteccion_datos.value');

        // OBTENGO LOS DATOS DE LA TABLA DE GRUPOS

        $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
        $query->condition('type', 'grupo');
        $nids = $query->execute();
        $nodes = entity_load_multiple('node', $nids);
        foreach($nodes as $node) {
          $options[$node->get('nid')->value] = $node->get('title')->value;
        }

        // COMIENZA LOS CAMPOS DEL FORMULARIO

        $this->textoHtmlForm = "Place reservation form in the Boulder Zone Alcalá Climbing Wall";

        $form ['text_inf']= array (
            '#type'     => 'fieldset',
            '#title'    => $this->t('INFORMATION TEXT'),
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
          '#title'    => $this->t('name'),
          '#required' => TRUE,
        );
        $form ['datos_participante']['apellido1'] = array (
          '#type'     => 'textfield',
          '#title'    => $this->t('Primer Apellido'),
          '#required' => TRUE,
        );
        $form ['datos_participante']['apellido2'] = array (
          '#type'     => 'textfield',
          '#title'    => $this->t('Segundo Apellido'),
          '#required' => FALSE,
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
        $form['otrosDatos']['socio'] = array(
          '#type' => 'checkbox',
          '#title' => $this->t('SOCIO'),
          );
        $form['otrosDatos']['turno'] = array(
          '#type' => 'select',
          '#title' => $this->t('Turno preferente'),
          '#options' => $options,
        );
        $form['otrosDatos']['observaciones'] = array(
          '#title' => $this->t('Observations'),
          '#type' => 'textarea',
          '#description' => $this->t('Si has elegido <strong>PASE DE DÍA</strong>
           indica el día y la hora aprox o si tienes que comentarnos algo'),
          '#default_value' => 'No hay observaciones...',
          '#rows' => 5,
          '#cols' => 40,
          '#resizable' => TRUE,
        );
        $form ['proteccion']= array (
            '#type'     => 'fieldset',
            '#title'    => $this->t('PROTECCIÓN DE DATOS'),
        );
        $form ['proteccion']['protec_datos']= array (
            '#type' => 'markup',
            '#markup' => $this->t($textoConsent),
            '#format' => 'full_html',

        );
        $form['proteccion']['accept'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('I accept the terms of use of the site'),
        '#description' => $this->t('Please read and accept the terms of use'),
        '#required' => TRUE,
        ];

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

    if (empty($form_state->getValue('apellido1'))) {
      $control=FALSE;
    }else if (!preg_match($patron_texto, $form_state->getValue('apellido1'))){
      $form_state->setErrorByName('apellido1', $this->t('No puede contener números'));
    }else{
      $control=TRUE;
    }

    if (!$form_state->getValue('email') || !filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      $control=FALSE;
      $form_state->setErrorByName('email', $this->t('La dirección de correo no es válida.'));
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



    //Recogemos los campos rellenos del formulario.

    $nombre=$form_state->getValue('nombre');
    $apellido1 = $form_state->getValue('apellido1');
    $apellido2 = $form_state->getValue('apellido2');
    $correo = $form_state->getValue('email');
    $socio = $form_state->getValue('socio');
    $turno = $form_state->getValue('turno');
    $protec_datos = $form_state->getValue('protec_datos');
    $obs = $form_state->getValue('observaciones');
    $reserva = true;

    //*******GENERAL CLAVE*******//
    //$this->clave = self::generateRandomString();
    //******GENERAR CLAVE********//

    // Rellenamos el título del nodo
    if ($apellido2){
      $title = $nombre.' '.$apellido1.' '.$apellido2;
    }else{
      $title = $nombre.', '.$apellido1;
    }

    // BUSCAR EL TURNO ELEGIDO

    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'grupo');
    $query->condition('nid', $turno);
    $nidturnos = $query->execute();
    foreach ($nidturnos as $n) {
      $nid = $n;
    }

    $nodesturno = Node::load($nid);
    $titTurno = $nodesturno->get('title')->value;


    //COMPROBAMOS QUE EL USUARIO NO ESTÉ YA DADO DE ALTA.
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'bz_participantes')
    ->condition('field_par_correo', $correo);
    $nids = $query->execute();

    if (!empty($nids)){
      foreach ($nids as $n) {
        $nid = $n;
      }
      $idusuario = $nid;
      $node = Node::load($nid);

      // SACAMOS LA INFORMACIÓN DEL NODO QUE ES NECESARIA.
      $conNombre = $node->get('field_par_nombre')->value;
      $conCorreo = $node->get('field_par_correo')->value;
      if (!$conCorreo){
        $conCorreo = $correo;
      }

      // ACTUALIZAMOS LA INFORMACIÓN DEL NODO

      $node->title->value = $title;
      $node->field_par_nombre->value = $nombre;
      $node->field_par_apellidos->value = $apellido1.' '.$apellido2;
      //$node->field_apellido2->value = $apellido2;
      $node->field_reserva_observaciones->value = $obs;
      //$node->field_par_correo->value = $conCorreo;
      $node->field_usuario_activo->value = true;
      $node->field_protec_datos->value = true;
      $node->save();


      $this->messenger()->addMessage($this->t('HEMOS COMPROBADO QUE USTED ESTE DADO DE ALTA'));
      $this->messenger()->addStatus($this->t('NOMBRE: @nombre', ['@nombre' => $conNombre]));
      $this->messenger()->addWarning($this->t('CORREO: @correo', ['@correo' => $conCorreo]));


    } else {

      // MENSAJES DEL envio

      $this->messenger()->addMessage($this->t('NOS HA ENVIDO SU RESERVA. GRACIAS!!'));
      $this->messenger()->addStatus($this->t('NOMBRE: @nombre', ['@nombre' => $nombre]));
      $this->messenger()->addWarning($this->t('CORREO: @correo', ['@correo' => $correo]));

      // CREAMOS UN NUEVO USUARIO Y CLAVE DE PUNTOS
      $node = Node::create([
        'type' => 'bz_participantes',
        'title' => $title,
        'field_par_nombre' => $nombre,
        'field_par_apellidos' => $apellido1.' '.$apellido2,
        //'field_apellido2' => $apellido2,
        'field_par_correo' => $correo,
        'field_par_socio' => true,
        'field_usuario_activo' => true,
        'field_protec_datos' =>  true,
        'uid' => 0,
      ]);
      $node->status = 1;
      $node->save();

      $idusuario = $node->get('nid')->value;


    }

    $titleReserva = date("Ymd")."-".$title;
    $node = Node::create([
      'type' => 'bz_reservas',
      'title' => $titleReserva,
      'field_reserva_observaciones' => $obs,
      'field_reserva_usuario' => $idusuario,
      'field_reserva_turno' => $turno,
      'uid' => 0,
    ]);
    $node->save();
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
