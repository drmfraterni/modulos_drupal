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



class BuscarParticipanteForm extends FormBase {

  protected $envioconf = FALSE;

  protected $clave;

  protected $token;

  protected $textoHtmlForm = '';



  public function getFormId(){
    // NOMBRE DEL FORMULARIO
    return 'form_buscar_participante';
  }

  public function buildForm(array $form, FormStateInterface $form_state){

      //var_dump($this->envioconf);

      if (!empty($this->envioconf)){

        //GENERAMOS TODOS LOS ENLACES A LISTADOS
        $ruta = array(); // todas las rutas del listado
        global $base_url;

        // IMPRIMIMOS PARA IR A LA NUEVA PRUEBA
        $ruta['link'][] = $base_url.'/campeonato/'.$this->token;
        $ruta['tit'][] = '<div class="ficha-puntuacion">FICHA DE PUNTUACIÓN<br/>DE LA SEGUNDA PRUEBA<br/></div>';


        $ruta['link'][] = $base_url.'/admin/listado-totales-puntos/all';
        $ruta['tit'][] = 'listado totales de puntos';
        $ruta['link'][] = $base_url.'/listado-totales-puntos/fem_pro';
        $ruta['tit'][] = 'listado Femenino PRO';
        $ruta['link'][] = $base_url.'/listado-totales-puntos/mas_pro';
        $ruta['tit'][] = 'listado Masculino PRO';
        $ruta['link'][] = $base_url.'/listado-totales-puntos/fem_rookie';
        $ruta['tit'][] = 'listado Femenino ROOKIE';
        $ruta['link'][] = $base_url.'/listado-totales-puntos/mas_rookie';
        $ruta['tit'][] = 'listado Masculino ROOKIE';


        //var_dump($this->clave);

        //TERMINAMOS ENLACES GENERALES

        // CREAMOS ENLACE A MIS PUNTOS //
        $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
        //$query->condition('type', 'bz_plantilla_competicion') // local FM
        $query->condition('type', 'bz_plantilla_bloques_competicion ') // servidor ionos
        ->condition('field_ficha', $this->clave);
        $nids = $query->execute();

        $nPrueba = 1;
        foreach ($nids as $n) {
          $nid = $n;
          $ruta['link'][] = $base_url.'/node/'.$n;
          $ruta['tit'][] = 'Mis PUNTAZOS en la Prueba'.$nPrueba;
          $nPrueba++;
        }



        // IMPRIMIMOS LA PRUEBAS REALIZADAS
        $enlace = "";
        for($i=0; $i<count($ruta['link']); $i++){
          $enlace .= '<a href='.$ruta['link'][$i].'>'.$ruta['tit'][$i].'</a><br/>';
        }




        $textoInformativo = '<p>LISTADOS DE PUNTOS POR CATEGORÍA: </p>
        <p align="center"><h2>CÓDIGO DE USUARIO'.$this->clave.'</h2><p>
        <p align="center"><h2>'.$enlace.'</h2><p>';

        //var_dump($ruta);

        $form ['final']= array (
          '#type' => 'markup',
          '#markup' => $textoInformativo,
          '#format' => 'full_html',
        );

      }else{

        $this->textoHtmlForm = "Formulario de Búsqueda de participación para la Interna de Boulder Zone Retiro";

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

        $form ['otrosDatos']= array (
            '#type'     => 'fieldset',
            '#title'    => $this->t('CORREO DEL PARTICIPANTE'),
        );
        $form ['otrosDatos']['email'] = array (
          '#type'     => 'email',
          '#title'    => $this->t('Email'),
          '#required' => FALSE,
        );
        $form ['otrosDatos']['cd_tarjeta'] = array (
          '#type'     => 'textfield',
          '#title'    => $this->t('CARNET de SOCIO/A'),
          '#required' => FALSE,
        );

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

    $control=FALSE;
    $patron_texto = '/^[a-zA-ZáéíóúÁÉÍÓÚäëïöüÄËÏÖÜàèìòùÀÈÌÒÙ\s]+$/';
    $patron_carnet='/^[0-9]{9}$/';


    if (!preg_match($patron_carnet, $form_state->getValue('cd_tarjeta'))&&($control=FALSE)){
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


    //posibles parámetros para la Búsqueda
    $tarjeta = $form_state->getValue('cd_tarjeta');
    $correo = $form_state->getValue('email');


    //COMPROBAMOS QUE EL USUARIO NO ESTÉ YA DADO DE ALTA.


    $query = \Drupal::entityQuery('node');
      $group = $query->orConditionGroup()
        ->condition('field_par_correo', $correo)
        ->condition('field_par_carnet', $tarjeta);
      $entity_ids = $query
        ->condition('type', 'bz_participantes')
        ->condition($group)
        ->execute();

    if (!empty($entity_ids)){

      $num = count($entity_ids);

      if ($num > 1){
        $this->messenger()->addStatus($this->t('HEMOS COMPROBADO QUE NO COINCIDE EL NÚMERO
        DE CARNET CON EL USUARIO DE CORREO'));
        $this->messenger()->addStatus($this->t('CORREO: @correo', ['@correo' => $correo]));
        $this->messenger()->addStatus($this->t('CARNET DE SOCIO/A: @carnet', ['@carnet' => $tarjeta]));
        $this->envioconf = FALSE;
      }else{

        foreach ($entity_ids as $n) {
          $nid = $n;

          $this->envioconf = TRUE;
          //var_dump($this->envioconf);


          $node = Node::load($nid);
          $conCorreo = $node->get('field_par_correo')->value;
          $conClave = $node->get('field_par_clave')->value;
          //var_dump($node->get('field_par_correo')->value);
          $this->clave = $nid;
          $this->token = $conClave;



          $this->messenger()->addStatus($this->t('HEMOS COMPROBADO QUE USTED ESTE DADO DE ALTA'));
          //$this->messenger()->addStatus($this->t('CLAVE: @clave', ['@clave' => $this->clave]));
          $this->messenger()->addStatus($this->t('CORREO: @correo', ['@correo' => $correo]));
          $this->messenger()->addStatus($this->t('CARNET DE SOCIO/A: @carnet', ['@carnet' => $tarjeta]));
        }

      }


    }else{

      $this->messenger()->addStatus($this->t('USTED NO ESTÁ DADO DE ALTA CON LOS DATOS PROPORCIONADOS: '));
      $this->messenger()->addStatus($this->t('CORREO: @correo', ['@correo' => $correo]));
      $this->messenger()->addStatus($this->t('CARNET DE SOCIO/A: @carnet', ['@carnet' => $tarjeta]));
      $this->envioconf = FALSE;

    }


    $form_state->setRebuild();

  }

}




?>
