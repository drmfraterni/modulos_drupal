<?php
namespace Drupal\campeonatos\Controller;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;

class CampeonatosController extends ControllerBase {

  public function info_participante($clave, $token) {

    //$ruta = array(); // todas las rutas del listado
    global $base_url;

      // IMPRIMIMOS PARA IR A LA NUEVA PRUEBA
      /*
      $ruta = array (
        'link' => array (
          $base_url.'/campeonato/'.$token,
          $base_url.'/admin/listado-totales-puntos/all',
          $base_url.'/listado-totales-puntos/fem_pro',
          $base_url.'/listado-totales-puntos/mas_pro',
          $base_url.'/listado-totales-puntos/fem_rookie',
          $base_url.'/listado-totales-puntos/mas_rookie'

        ),
        'tit' => array (
          '<div class="ficha-puntuacion">FICHA DE PUNTUACIÓN<br/>DE LA SEGUNDA PRUEBA<br/></div>',
          'listado totales de puntos',
          'listado Femenino PRO',
          'listado Masculino PRO',
          'listado Femenino ROOKIE',
          'listado Masculino ROOKIE'
        )
      );
      */


      $ruta['link'][] = $base_url.'/campeonato/'.$token;
      $ruta['tit'][] = 'FICHA DE PUNTUACIÓN DE LA SEGUNDA PRUEBA';
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

      //TERMINAMOS ENLACES GENERALES

      // CREAMOS ENLACE A MIS PUNTOS //
      $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
      //$query->condition('type', 'bz_plantilla_competicion') // local FM
      $query->condition('type', 'bz_plantilla_bloques_competicion ') // servidor ionos
      ->condition('field_ficha', $clave);
      $nids = $query->execute();

      $nPrueba = 1;
      foreach ($nids as $n) {
        $nid = $n;
        $ruta['link'][] = $base_url.'/node/'.$n;
        $ruta['tit'][] = 'Mis PUNTAZOS en la Prueba'.$nPrueba;
        $nPrueba++;
      }

      $enlaces = $ruta;

      return [
          '#theme' => 'infoparticipante',
          '#titulo' => $this->t('Formulario alta de participantes'),
          '#descripcion' => 'Formulario para el alta de participantes',
          '#enlaces' => $enlaces,
          /* '#rutas' => $rutas */
      ];


  }

  public function alta_participante() {
  // Utilizamos el formulario
          $form = $this->formBuilder()->getForm('Drupal\campeonatos\Form\AltaParticipanteForm');
          //ksm($form);
          //drupal_set_message(t('Formulario: '.$nombre), 'status', FALSE);

  // Le pasamos el formulario y demás a la vista (tema configurado en el module)
          return [
              '#theme' => 'participante',
              '#titulo' => $this->t('Formulario alta de participantes'),
              '#descripcion' => 'Formulario para el alta de participantes',
              '#formulario' => $form
          ];


  }

  public function buscar_participante() {
  // Utilizamos el formulario
          $form = $this->formBuilder()->getForm('Drupal\campeonatos\Form\BuscarParticipanteForm');
          //ksm($form);
          //drupal_set_message(t('Formulario: '.$nombre), 'status', FALSE);

  // Le pasamos el formulario y demás a la vista (tema configurado en el module)
          return [
              '#theme' => 'participante',
              '#titulo' => $this->t('BUSCAR PARTICIPANTE'),
              '#descripcion' => 'Formulario para buscar participante',
              '#formulario' => $form
          ];


  }

  public function puntos_campeonato($clave) {
  // Utilizamos el formulario
          $form = $this->formBuilder()->getForm('Drupal\campeonatos\Form\PuntosCampeonatoForm', $clave);
          //ksm($form);
          //drupal_set_message(t('Formulario: '.$nombre), 'status', FALSE);

  // Le pasamos el formulario y demás a la vista (tema configurado en el module)
          return [
              '#theme' => 'participante',
              '#titulo' => $this->t('BLOQUES REALIZADOS'),
              '#descripcion' => 'Formulario de puntos de la Liga interna',
              '#formulario' => $form
          ];


  }

}

?>
