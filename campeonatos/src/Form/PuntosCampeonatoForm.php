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



class PuntosCampeonatoForm extends FormBase {

  protected $envioconf = FALSE;

  protected $clave;

  protected $puntos = 0;

  protected $datos = array();

  //CONTROLAMOS LOS ERRORES DE LA CLAVE
  protected $control = NULL;

  protected $textoHtmlForm = '';



  public function getFormId(){
    // NOMBRE DEL FORMULARIO
    return 'form_puntos_campeonatos';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $clave = NULL){



      $this->clave = $clave;

      //COMPROBAMOS QUE LA CLAVE NO EXISTE
      //SI NO EXISTE LA CLAVE
      if (empty($this->clave)){

        drupal_set_message(t('Para poder acceder al formulario necesitas
        la clave de inscripción'), 'status', FALSE);

        //CONTROLES
        $this->envioconf = TRUE;
        $this->control = 'NO EXISTE CLAVE';


      }else{
        // COMPROBAMOS QUE HAY CLAVE - AVERIGUAMOS PARTICIPANTE
        drupal_set_message(t('La clave de inscripción: '.$this->clave), 'status', FALSE);

        $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
        $query->condition('type', 'bz_participantes')
        ->condition('field_par_clave', $this->clave);
        $nids = $query->execute();

        foreach ($nids as $n) {
          $nid = $n;
        }

        $node = Node::load($nid);

        //SI LA CLAVE VINIERA ERRÓNEA O NO ENCUENTRA AL USUARIO
        if ($node == NULL){
          //CONTROLES DE ERRORES
          $this->control = 'CLAVE ERRONEA';
          $this->envioconf = TRUE;

          //var_dump($this->control);
          drupal_set_message(t('CLAVE ERRÓNEA: '.$this->clave), 'status', FALSE);
        }
        //DATOS DEL PARTICIPANTE
        $this->datos['participante'] = $node->title->value;
        $this->datos['idparticipante'] = $nid;

        // SACAR DATOS DE LA TAXONOMÍA LIGA INTERNA
        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', 'liga_interna');
        $tids = $query->execute();
        $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
        $term = NULL;
        foreach ($terms as $term) {
          // Si queremos sacar todos términos de la taxonomía
          // de Liga Interna tendriamos lo tendriamos que hacer de la siguiente manera:
          /*
          $taxname[] = $term->get('name')->getString();
          $taxtid[] = $term->get('tid')->getString();
          */
          $taxname = $term->get('name')->getString();
          $taxtid = $term->get('tid')->getString();
        }
        //var_dump($taxname);
        // DATOS DE LA TAXONOMIA DEL TERMINO CAMPEONATO
        $this->datos['liga'] = $taxname;
        $this->datos['idliga'] = $taxtid;

        // SACAR DATOS DE LA TAXONOMÍA
        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', 'categoria');
        $tids = $query->execute();
        $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
        $term = NULL;

        $cat = array();
        foreach ($terms as $term) {
          $cat['name'][] = $term->get('name')->getString();
          $cat['id'][] = $term->get('tid')->getString();

        }

        //var_dump($cat);
        //var_dump($catid);
        //die();

      }

      if (!empty($this->envioconf)){

        //var_dump($this->control);

        if ($this->control == "NO EXISTE CLAVE"){

          $textoInformativo = '<p>Estimado/a participante: </p>
          <p><strong>Está intentando entrar en la ficha de puntos de  Liga Interna de Boulder Zone Retiro</strong></p>
          <p>Necesita entrar a la aplicación con el código que generó al darse de alta<p>
          <p>Tiene que entrar a la siguiente dirección: http://www.bzr.bouldezoneretiro.es/CLAVE <p>
          <p>Para recuperar la clave tiene que ir al siguiente formulario e introducir su correo<p>';

        }else if ($this->control == 'CLAVE ERRONEA'){

          $textoInformativo = '<p>Estimado/a participante: </p>
          <p><strong>Está intentando entrar en la ficha de puntos de  Liga Interna de Boulder Zone Retiro</strong></p>
          <p>La claver que ha introducido es errónea<p>
          <p>Tiene que entrar a la siguiente dirección: http://www.bzr.bouldezoneretiro.es/CLAVE <p>
          <p>Para recuperar la clave tiene que ir al siguiente formulario e introducir su correo<p>';

        } else {

          $textoInformativo = '<p>Estimado '.$this->datos['participante'].'</p>
          <p><strong>Ha finalizado la primera Liga Interna de Boulder Zone Retiro</strong></p>
          <p>Su PUNTUACIÓN HA SIDO: <strong><h1>'.$this->puntos.' PUNTOS </h1></strong><p>
          <p>Esperamos que te haya gustado nuestro LIGA. Como sabes todavía quedan dos pruebas<p>
          <p>En breve podrá ver todos los resultados<p>';


        }


        //global $base_url;
        //$ruta = $base_url.'/campeonato/'.$this->clave;
        //$enlace = '<a href='.$ruta.'>ENTRAR EN LA PUNTUACIÓN</a>';

        //var_dump($ruta);



        $form ['final']= array (
          '#type' => 'markup',
          '#markup' => $textoInformativo,
          '#format' => 'full_html',

      );



      }else{

        $this->textoHtmlForm = "Este FORMULARIO sirve para llevar el control del
        puntos de la LIGA INTERNA";

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
        /*
        $form['centros']['centro']= [
            '#type' => 'entity_autocomplete',
            '#target_type' => 'node',
            '#title' => $this->t('Centro'),
            '#description' => $this->t('Seleccione un centro.'),
            '#tags' => TRUE,
            '#selection_settings' => array(
                'target_bundles' => array('page', 'headquarter'),
            ),
            '#weight' => '0',
        ];

        */
        $form ['datos_participante']= array (
            '#type'     => 'fieldset',
            '#title'    => $this->t('DATOS PARTICIPANTE'),
        );

        $form ['datos_participante']['nombre'] = array (
          '#type'     => 'textfield',
          '#title'    => $this->t('PARTICIPANTE'),
          '#default_value' => $this->datos['participante'],
          '#required' => FALSE,
        );

        /*
        $form['entity_id'] = array(
            '#type' => 'entity_autocomplete',
            '#title' => $this->t('PRUEBA PARTICIPANTE'),
            '#entity_type' => 'bz_participantes',
            //'#bundles' => $bundles,
            //'#default_value' => $this->clave,
        );
        */

        $form ['datos_campeonato']= array (
            '#type'     => 'fieldset',
            '#title'    => $this->t('LIGA - CAMPEONATO'),
        );

        $form ['datos_campeonato']['campeonato'] = array (
          '#type'     => 'textfield',
          '#title'    => $this->t('PRUEBA'),
          '#default_value' => $this->datos['liga'],
          '#required' => FALSE,
        );

        //$this->datos['liga']


        $form['datos_categoria']['categoria'] = [
          '#type' => 'select',
          '#title' => $this->t('SELECCIONA UNA CATEGORÍA'),
          '#options' => [
            '3' => $this->t('PRO'),
            '4' => $this->t('INICIACIÓN'),
            '5' => $this->t('ROOKIE'),
          ],
        ];
        // HORA DE COMIENZO DE LA COMPETICIÓN
        // HORA DE TERMINO DE LA COMPETICIÓN

        $form ['bloques']= array (
            '#type'     => 'fieldset',
            '#title'    => $this->t('PUNTUACIÓN DE LOS BLOQUES'),
        );

        # the options to display in our checkboxes
        /*
        $toppings = array(
          'sinPuntos' => t('--Si Puntos --'),
          'top' => t('TOP'),
          'bonus' => t('BONUS'),

        );
        */

        # the drupal checkboxes form field definition
        /*
        $form['bloques']['b1'] = array(
          '#title' => t('BLOQUE 1'),
          '#type' => 'select',
          '#description' => t('Selecciona el resultado.'),
          '#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6"><div class="form-group">',
          '#suffix'     => '</div>',
        );
        */
        $form['bloques']['b1'] = array(
          '#title' => t('BLOQUE 1'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 3 puntos.'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );
        $form['bloques']['b2'] = array(
          '#title' => t('BLOQUE 2'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 3 puntos.'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b3'] = array(
          '#title' => t('BLOQUE 3'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 3 puntos.'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b4']  = array(
          '#title' => t('BLOQUE 4'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 3 puntos.'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b5']  = array(
          '#title' => t('BLOQUE 5'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 3 puntos.'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b6']  = array(
          '#title' => t('BLOQUE 6'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 3 puntos.'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b7']  = array(
          '#title' => t('BLOQUE 7'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 3 puntos.'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b8']  = array(
          '#title' => t('BLOQUE 8'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 3 puntos.'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b9']  = array(
          '#title' => t('BLOQUE 9'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 3 puntos.'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b10']  = array(
          '#title' => t('BLOQUE 10'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 3 puntos.'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b11']  = array(
          '#title' => t('BLOQUE 11'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 10 puntos.'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b12']  = array(
          '#title' => t('BLOQUE 12'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 7 puntos. (6A+)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b13']  = array(
          '#title' => t('BLOQUE 13'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 7 puntos. (6A+)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b14']  = array(
          '#title' => t('BLOQUE 14'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 7 puntos. (6A+)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b15']  = array(
          '#title' => t('BLOQUE 15'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 9 puntos. (6B)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b16']  = array(
          '#title' => t('BLOQUE 16'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 9 puntos. (6B)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b17']  = array(
          '#title' => t('BLOQUE 17'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 9 puntos. (6B)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b18']  = array(
          '#title' => t('BLOQUE 18'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 11 puntos. (6B+)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b19']  = array(
          '#title' => t('BLOQUE 19'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 11 puntos. (6B+)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b20']  = array(
          '#title' => t('BLOQUE 20'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 13 puntos. (6C)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b21']  = array(
          '#title' => t('BLOQUE 21'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 13 puntos. (6C)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b22']  = array(
          '#title' => t('BLOQUE 22'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 15 puntos. (6C+)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b23']  = array(
          '#title' => t('BLOQUE 23'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 15 puntos. (6C+)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b24']  = array(
          '#title' => t('BLOQUE 23'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 17 puntos. (7A)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b25']  = array(
          '#title' => t('BLOQUE 25'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 19 puntos. (7A+)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b26']  = array(
          '#title' => t('BLOQUE 26'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 19 puntos. (7A+)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b27']  = array(
          '#title' => t('BLOQUE 27'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 21 puntos. (7B)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b28']  = array(
          '#title' => t('BLOQUE 28'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 21 puntos. (7B)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b29']  = array(
          '#title' => t('BLOQUE 29'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 23 puntos. (7B+)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
        );

        $form['bloques']['b30']  = array(
          '#title' => t('BLOQUE 30'),
          '#type' => 'checkbox',
          '#description' => t('Bloque de 25 puntos. (7C)'),
          //'#attributes' => array('checked' => 'checked'),
          //'#options' => $toppings,
          '#prefix'     => '<div class="row"><div class="col-md-6">',
          '#suffix'     => '</div></div>',
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

    // Uno de los campos tiene que estar relleno
    // el campo nombre no puede tener caracteres numéricos
    // el campo cd_tarjeta no puede tener carácteres alfabéticos.


    $control=FALSE;

    if (empty($form_state->getValue('nombre'))) {
      $control=FALSE;
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

    //PARAMETROS FIJOS
    $idpart = $this->datos['idparticipante'];
    $part = $this->datos['participante'];
    $liga = $this->datos['liga'];
    $idliga = $this->datos['idliga'];
    //VALORES
    /*
    $spuntos = array();
    $spuntos['b1']['sinPuntos']= 0;
    $spuntos['b1']['top']= 10;
    $spuntos['b1']['bonus']= 5;
    */
    // CREAMOS LA PUNTUACIÓN DE LOS BLOQUES


    $spuntos['b1']= 3;
    $spuntos['b2']= 3;
    $spuntos['b3']= 3;
    $spuntos['b4']= 3;
    $spuntos['b5']= 3;

    $spuntos['b6']= 3;
    $spuntos['b7']= 3;
    $spuntos['b8']= 5;
    $spuntos['b9']= 5;
    $spuntos['b10']= 5;

    $spuntos['b11']= 10;
    $spuntos['b12']= 7;
    $spuntos['b13']= 7;
    $spuntos['b14']= 7;
    $spuntos['b15']= 9;

    $spuntos['b16']= 9;
    $spuntos['b17']= 9;
    $spuntos['b18']= 11;
    $spuntos['b19']= 11;
    $spuntos['b20']= 13;

    $spuntos['b21']= 13;
    $spuntos['b22']= 15;
    $spuntos['b23']= 15;
    $spuntos['b24']= 17;
    $spuntos['b25']= 19;

    $spuntos['b26']= 19;
    $spuntos['b27']= 21;
    $spuntos['b28']= 21;
    $spuntos['b29']= 23;
    $spuntos['b30']= 25;




    //VEMOS LOS BLOQUES QUE HAN SIDO ACTIVADOS Y POR LO TANTO HECHOS POR EL PARTICIPANTE.
    $block = array();
    for ($i = 1; $i <= 30; $i++) {
      $block[$i] = $form_state->getValue('b'.$i);
    }
    //CREAMOS EL TÍTULO DE NODO DE PUNTOS DE CAMPEONATO
    $title = $liga.' - '.$part;
    $terCategoria = $form_state->getValue('categoria');
    /*
    $b1=$form_state->getValue('b1');
    $b2=$form_state->getValue('b2');
    $b3=$form_state->getValue('b3');
    $b4=$form_state->getValue('b4');
    $b5=$form_state->getValue('b5');

    $b6=$form_state->getValue('b6');
    $b7=$form_state->getValue('b7');
    $b8=$form_state->getValue('b8');
    $b9=$form_state->getValue('b9');
    $b10=$form_state->getValue('b10');
    */

    //var_dump($block);

    $subTotal=array();
    $totalPuntos = 0;
    for ($i = 1; $i <= 30; $i++) {
      $subTotal[$i] = $spuntos['b'.$i] * $block[$i];
      $totalPuntos = $totalPuntos + $subTotal[$i];
    }

    $this->puntos = $totalPuntos;

    /*
    $subTotalB1 = $spuntos['b1'] * $b1;
    $subTotalB2 = $spuntos['b2'] * $b2;
    $subTotalB3 = $spuntos['b3'] * $b3;
    $subTotalB4 = $spuntos['b4'] * $b4;
    $subTotalB5 = $spuntos['b5'] * $b5;

    $subTotalB6 = $spuntos['b6'] * $b6;
    $subTotalB7 = $spuntos['b7'] * $b7;
    $subTotalB8 = $spuntos['b8'] * $b8;
    $subTotalB9 = $spuntos['b9'] * $b9;
    $subTotalB10 = $spuntos['b10'] * $b10;
    */

    //var_dump($subTotal);
    //var_dump($totalPuntos);

    /*
    $totalPuntos = ($subTotalB1 + $subTotalB2 + $subTotalB3 + $subTotalB4 + $subTotalB5 );
    $totalPuntos = ($totalPuntos + $subTotalB6 + $subTotalB7 + $subTotalB8 + $subTotalB9 + $subTotalB10 );
    $this->puntos = $totalPuntos;
    */
    //var_dump($totalPuntos);
    $this->messenger()->addStatus($this->t('TITULO @titulo', ['@titulo' => $title]));


    // CREAMOS UN NUEVO CONSENTIMIENTO INFORMADO
    //var_dump($catid);

    $node = Node::create([
      'type' => 'bz_plantilla_bloques_competicion',
      'title' => $title,
      'field_ficha_bloque_1' => $subTotal[1],
      'field_ficha_bloque_2' => $subTotal[2],
      'field_ficha_bloque_3' => $subTotal[3],
      'field_ficha_bloque_4' => $subTotal[4],
      'field_ficha_bloque_5' => $subTotal[5],
      'field_ficha_bloque_6' => $subTotal[6],
      'field_ficha_bloque_7' => $subTotal[7],
      'field_ficha_bloque_8' => $subTotal[8],
      'field_ficha_bloque_9' => $subTotal[9],
      'field_ficha_bloque_10' => $subTotal[10],
      'field_ficha_bloque_11' => $subTotal[11],
      'field_ficha_bloque_12' => $subTotal[12],
      'field_ficha_bloque_13' => $subTotal[13],
      'field_ficha_bloque_14' => $subTotal[14],
      'field_ficha_bloque_15' => $subTotal[15],
      'field_ficha_bloque_16' => $subTotal[16],
      'field_ficha_bloque_17' => $subTotal[17],
      'field_ficha_bloque_18' => $subTotal[18],
      'field_ficha_bloque_19' => $subTotal[19],
      'field_ficha_bloque_20' => $subTotal[20],
      'field_ficha_bloque_21' => $subTotal[21],
      'field_ficha_bloque_22' => $subTotal[22],
      'field_ficha_bloque_23' => $subTotal[23],
      'field_ficha_bloque_24' => $subTotal[24],
      'field_ficha_bloque_25' => $subTotal[25],
      'field_ficha_bloque_26' => $subTotal[26],
      'field_ficha_bloque_27' => $subTotal[27],
      'field_ficha_bloque_28' => $subTotal[28],
      'field_ficha_bloque_29' => $subTotal[29],
      'field_ficha_bloque_30' => $subTotal[30],
      'field_ficha_total' => $this->puntos,
      'field_ficha_campeonato' => $idliga,
      'field_ficha' => $idpart,
      'field_ficha_categoria' => $terCategoria,

      'uid' => 0,
    ]);
    $node->status = 1;
    $node->save();

    $form_state->setRebuild();

  }

}




?>
