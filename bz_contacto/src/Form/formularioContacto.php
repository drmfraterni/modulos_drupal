<?php

namespace Drupal\bz_contacto\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
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

      $form['datos_personales'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Datos personales'),
      ];
      $form['datos_personales']['ms_nombre'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Nombre'),
          '#maxlength' => 64,
          '#size' => 64,
          '#required' => TRUE,
      ];
      $form['datos_personales']['ms_apellidos'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Apellidos'),
          '#maxlength' => 64,
          '#size' => 64,
      ];
      $form['datos_personales']['ms_correo'] = [
          '#type' => 'email',
          '#title' => $this->t('Correo electrónico'),
          '#default_value' => $form_state->getValue('ms_correo', ''),
          '#description' => $this->t('Introduce el correo electrónico.'),
          '#required' => TRUE,
      ];

      // este campo nos sirva para evitar el spam. Si esta relleno no se enviará
      $form['entity_id'] = [
          '#type' => 'hidden',
          '#value' => $entity_id,
      ];

    }

    // Step 2 - PASO 2 RELLENAR EL MENSAJE
    if($this->step == 2) {

      $form['Escribe Mensaje'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Escribe el mensaje'),
      ];
      $form['Escribe Mensaje']['mensaje']= [
          '#title' => t('Mensaje'),
          '#type' => 'textarea',
          '#description' => 'Escribe el mensaje',
          '#rows' => 10,
          '#cols' => 60,
          '#resizable' => TRUE,
      ];
      // Este campo nos sirve para indicar a la plantilla de twig que estamos en el paso 2.
      $form['controlPaso'] = 2;

    }

    //
    if($this->step < 2) {
      $button_label = $this->t('Siguiente');
    }
    else {
      $button_label = $this->t('Enviar');
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
      // si está variable esta rellena es que es spam
      $cp=$this->campoPasos['entity_id'];

      if ($cp==null){
      	$this->step++;
      }else{
      	$this->step=1;
      }
      $verMensaje = \Drupal::config('bz_config.settings')->get('mensaje');

      //drupal_set_message('PASO 1 COMPLETADO!!');
      //drupal_set_message($verMensaje);

    }else{
        $this->step++;

        //$this->campoPasos=$form_state->getValues();

        //drupal_set_message('Estimada/o: '.$this->campoPasos['ms_nombre']. ' ' .$this->campoPasos['ms_apellidos']);
        //drupal_set_message('Ha enviado el correo de forma correcta.');
        $messenger = \Drupal::messenger();
        $messenger->addMessage('Estimada/o: '.$this->campoPasos['ms_nombre']. ' ' .$this->campoPasos['ms_apellidos']);
        $messenger->addMessage('Ha enviado el correo de forma correcta.');


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

    if ($this->step==3 ){

      $fechaHoy=date('Ymd');

      // REGISTRAMOS LA ENTRADA EN LA BASE DE DATOS
      $node = Node::create([
        'type' => 'bz_mensaje', // local
        //'type' => 'bz_contacto', // servidor
        'title' => $fechaHoy.' - MM - '.$this->campoPasos['ms_nombre']. ' ' .$this->campoPasos['ms_apellidos'],
        'field_ms_nombre'=>$this->campoPasos['ms_nombre'],
        'field_ms_apellidos'=>$this->campoPasos['ms_apellidos'],
        'field_ms_correo'=> $this->campoPasos['ms_correo'],
        'body' => [
        'value' => $this->campoPasos['mensaje'],
        'format' => 'basic_html',
          ],
        'uid' => 0, //el codigo del usuario es autonumérico.
        ]);

        // si campo oculto va relleno es spam y no lo grabamos.
        if (empty($comprobacion)) {
            $node->save();
        }

        // ENVIAMOS EL CORREO AL USUARIO INDICANDO QUE NOS HA ENVIADO UN MENSAJE
        $module = 'bz_contacto';
        $key = 'contact_message';
        $to = $this->campoPasos['ms_correo'];
        $params['Bcc'] = 'robledomorante@gmail.com'; 
        $params['titulo']='Envio desde el formulario de contato de Boulder Zone Alcala';
        $params['mensaje']=\Drupal::config('bz_config.settings')->get('email_body_contacto')['value'];
        $params['mensaje'].='<br>'.$this->campoPasos['mensaje'].'<br>';
        $params['mensaje'].=\Drupal::config('bz_config.settings')->get('email_body_firma')['value'];
        //$params = $this->campoPasos;
        $from = \Drupal::config('system.site')->get('mail');
        //drupal_set_message('esto es de: '.$to);
        //$from = 'drmfraterni@gmail.com';
        $language_code ='es';




        $result = \Drupal::service('plugin.manager.mail')->mail($module, $key, $to, $language_code, $params, $from, TRUE);
			if ($result['result'] == TRUE) {
        //drupal_set_message($this->t($params['mensaje']));
				drupal_set_message($this->t('Hemos enviado correctamente el mensaje.'));
			}
			else {
				drupal_set_message($this->t('There was a problem sending your message and it was not sent.'), 'error');
			}

        //Envio de correo




    }



  }

}
