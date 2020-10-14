<?php

namespace Drupal\bz_suscripcion\Form;

//require_once DRUPAL_ROOT .'\vendor\dompdf\dompdf\src\Autoloader.php';
//Autoloader::register();

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\Entity\Node;
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
use Dompdf\Dompdf;
use Dompdf\Autoloader;
use Dompdf\Options;




class ConsentimientoAutorizado extends FormBase {

  protected $envioconf = FALSE;

  protected $token;

  protected $ficha = array();
  
  protected $todoCampos = array ();

  protected $textoHtmlConsent;




  public function getFormId(){
    // NOMBRE DEL FORMULARIO
    return 'formulario_consentimiento_autorizado';
  }

  public function buildForm(array $form, FormStateInterface $form_state){


    if (!empty($this->envioconf)){
      //$this->messenger()->addMessage('EL FORMULARIO SE HA ENVIDO');
      $textHtml = $config = \Drupal::config('bz_suscripcion.settings');
      $textoConsent = $textHtml->get('mensaje_contestacion.value');

      $correo = $this->ficha['correo'];
      $nombreCompleto = $this->ficha['nombreCompleto'];
      //var_dump($correo);
      $search = [
        '[%nombreCompleto%]',
        '[%email%]',
      ];
      $replace = [
        $nombreCompleto,
        $correo,
      ];
      $textoConsent = str_replace($search, $replace, $textoConsent);

      $form ['final']= array (
          '#type' => 'markup',
          '#markup' => $textoConsent,
          '#format' => 'full_html',

      );

    }else{


      $textHtml = $config = \Drupal::config('bz_suscripcion.settings');
      $this->textoHtmlConsent = $textHtml->get('consentimiento.value');

      //var_dump($textoConsent);
      //die();

      $fecha_actual = '2019-09-16';

      $form ['text_inf']= array (
          '#type'     => 'fieldset',
          '#title'    => $this->t('TEXTO INFORMATIVO'),
      );

      $form ['text_inf']['texto_inf']= array (
          '#type' => 'markup',
          '#markup' => $this->t($this->textoHtmlConsent),
          '#format' => 'full_html',

      );

      $form['text_inf']['accept'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I accept the terms of use of the site'),
      '#description' => $this->t('Please read and accept the terms of use'),
      '#required' => TRUE,
      ];

      $form ['consentimiento']= array (
          '#type'     => 'fieldset',
          '#title'    => $this->t('DATOS PERSONALES'),
      );
      $form ['consentimiento']['nombre'] = array (
        '#type'     => 'textfield',
        '#title'    => $this->t('Nombre'),
        '#required' => TRUE,
      );
      $form ['consentimiento']['apellido1'] = array (
        '#type'     => 'textfield',
        '#title'    => $this->t('Primer apellido'),
        '#required' => TRUE,
      );
      $form ['consentimiento']['apellido2'] = array (
        '#type'     => 'textfield',
        '#title'    => $this->t('Segundo apellido'),
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

      $form ['submit'] = [
          '#type'  => 'submit',
          '#value' => $this->t('Enviar'),
      ];


      //return $form;

    }

    return $form;

  }

  /**
 * {@inheritdoc}
 */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // COMPROBAR SI EXISTE ESE CORREO //
    $validarCorreo = FALSE;
    $nid = \Drupal::entityQuery('node')
    ->condition('type', 'bz_consentimiento')
    ->condition('field_con_email', $form_state->getValue('email'), '=')
    ->execute();
    
    
    // si existe el correo nos da TRUE y por lo tanto no nos lo deja introducir.
    empty($nid) ? $validarCorreo = FALSE : $validarCorreo = TRUE;
    //var_dump($nid);
    //var_dump($validarCorreo);
   

    $control=FALSE;
    $patron_texto = '/^[a-zA-ZáéíóúÁÉÍÓÚäëïöüÄËÏÖÜàèìòùÀÈÌÒÙ\s]+$/';

    if (empty($form_state->getValue('nombre'))) {
      $control=FALSE;
    }else if (!preg_match($patron_texto, $form_state->getValue('nombre'))){
      $form_state->setErrorByName('nombre', $this->t('No puede contener números'));
    }else{
      $control=TRUE;
    }

    if (empty($form_state->getValue('apellido1'))&&($control==FALSE)) {
      $control=FALSE;
    }else if (!preg_match($patron_texto, $form_state->getValue('apellido1'))&&($control=FALSE)){
      $form_state->setErrorByName('apellido1', $this->t('No puede contener números'));
    }else{
      $control=TRUE;
    }

    // El apellio 2 no es obligatorio - extranjeros
    if ($form_state->getValue('apellido2') == NULL){
      $control = TRUE;
    }

    //validar campo correo
    if (empty($form_state->getValue('email'))&&($control==FALSE)) {
      $control=FALSE;
    }else if ($validarCorreo == TRUE) {
      $form_state->setErrorByName('email', t('Este correo está asociado a un consentimiento informado por lo que debe existir la solicitud.'));
    }else if (!valid_email_address($form_state->getValue('email')) && ($control = FALSE )){
      $form_state->setErrorByName('email', t('That is not a valid e-mail address.'));
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

    $this->envioconf = FALSE;

    // RECOGEMOS LOS DATOS DEL FORMULARIO

    $field_apellido1= strip_tags($form_state->getValue('apellido1'));
    $field_apellido2= strip_tags($form_state->getValue('apellido2'));
    $field_nombre= strip_tags($form_state->getValue('nombre'));
    $fechaActual = date('d/m/Y\TH:i:s');
    $fechaCodificada = date('Ymd');

    if(!empty($field_apellido2)){
      $title = $fechaCodificada."_".$field_nombre."_".$field_apellido1."_".$field_apellido2;
      $this->ficha['nombreCompleto'] = $field_nombre." ".$field_apellido1." ".$field_apellido2;
    }else{
      $title = $fechaCodificada."_".$field_nombre."_".$field_apellido1;
      $this->ficha['nombreCompleto'] = $field_nombre." ".$field_apellido1;
    }

    $field_email = $form_state->getValue('email');

  

    
        // CREAMOS UN NUEVO CONSENTIMIENTO INFORMADO
    $node = Node::create([
      'type' => 'bz_consentimiento',
      'title' => $title,
      'field_nombre' => $field_nombre,
      'field_apellido1' => $field_apellido1,
      'field_apellido2' => $field_apellido2,
      'field_con_email' => $field_email,
      'field_fecha_de_consentimiento' => $fechaActual,
      'field_firmado'=>0,
      'uid' => 0,
    ]);    	
    	
    $node->save();

    

    // ENVIAMOS EL CORREO DE CONFIRMACIÓN.

    // FORMACIÓN DEL TOKEN PARA LA SEGURIDAD DE LA FIRMA
    $clavetoken = \Drupal::config('bz_suscripcion.settings')->get('codigo_seguridad');
    $nid_user = $node->id();
    $this->token = hash("sha256",$nid_user.$clavetoken);
    // FIN DEL TOKEN

    $this->ficha['correo'] = $field_email;
    
	//$form_state->setRebuild();
	
	//******campos para parametrizar el correo***//
	
  	$this->todoCampos['title_nodo'] = $title;
  	$this->todoCampos['nom_completo'] = $this->ficha['nombreCompleto'];
  	$this->todoCampos['date_nicio'] = $fechaActual;
  	$this->todoCampos['token'] = $this->token;
	
	
    //********************PDF******************//
    
    $search = [
      '[%start_date%]',
      '[%nombreCompleto%]',
    ];
    $replace = [
      $fechaActual,
      $this->ficha['nombreCompleto'],

    ];

  	$textoConsent = str_replace($search, $replace, $this->textoHtmlConsent);
  	$nombreConsInf = strtolower($field_apellido1."_".$field_apellido2."_".$field_nombre);
  	$nombrePDF =$fechaCodificada."_".$nombreConsInf.'.pdf';
  	$file = 'sites/default/files/pdf/';
  	$html = $textoConsent;


  	// CON EL COMPOSER COMPOSER
  	//$mpdf = new \Mpdf\Mpdf(['tempDir' => 'sites/default/files/tmp']); 
  	
  	//$mpdf = new \Mpdf\Mpdf();
  	//$mpdf->WriteHTML($html);
  	//$mpdf->Output('file.pdf', 'D');
  	//$mpdf->Output($file  . $nombrePDF, 'F');
  	//$params['attachments'] = $file.$nombrePDF;


		
    //*************ENVIO DE CORREO************************//

    
    $module = 'bz_suscripcion';
    $key = 'email_consentimiento';
    $to = $field_email;
    $params['subject'] = 'CONSENTIMIENTO INFORMADO ';
    $params['titulo']='Envio desde el formulario de contato de Boulder Zone Alcala';
		$params['message'] =  $textoConsent;

		//$params['attachments'] = $file.$nombrePDF;
		
    $params = $this->todoCampos + $params;
    

    $from = \Drupal::config('system.site')->get('mail');
        //drupal_set_message('esto es de: '.$to);
        //$from = 'drmfraterni@gmail.com';
    $language_code ='es';

    //$form_state->setRebuild();

 
    $result = \Drupal::service('plugin.manager.mail')->mail($module, $key, $to, $language_code, $params, $from, TRUE);
		if ($result['result'] == TRUE) {
    //drupal_set_message($this->t($params['mensaje']));
		  drupal_set_message($this->t('Hemos enviado correctamente el mensaje.'));
		}
		else {
			drupal_set_message($this->t('There was a problem sending your message and it was not sent.'), 'error');
		}

	$nc = $this->ficha['nombreCompleto'];	
	$tk = $this->token;
	//$form_state->setRedirect('bz_suscripcion.enviocorreo', ['rdf_entity' => $entity->id()]);
	$form_state->setRedirect('bz_suscripcion.enviocorreo', ['nombre' => $nc, 'token' => $tk]);

  }



}


?>
