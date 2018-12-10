<?php

namespace Drupal\gestion_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Defines HelloController class.
 */
class buscarUsuario extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
   public function formdebusqueda() {
// Utilizamos el formulario
      $form = $this->formBuilder()->getForm('Drupal\gestion_form\Form\BusquedaUsuarioForm');
      //$nombre=$form_state->getValue('nombre');
      //$ape=$form_state->getValue('apellido');
      //$carnet=$form_state->getValue('carnet');

      //$ruta='/admin/gestion/vista-all-usuarios';

      //drupal_set_message('nombre: '.t($nombre));
       //$form = $this->formBuilder()->getForm('Drupal\alterar_formulario\Form\BusquedaUsuarioForm');
       //ksm($form);
       //drupal_set_message(t('Formulario: '.$nombre), 'status', FALSE);

// Le pasamos el formulario y demás a la vista (tema configurado en el module)
       return [
           '#theme' => 'busqueda',
           '#titulo' => $this->t('Formulario para la Búsqueda de Usuarios'),
           '#descripcion' => 'Formulario para la búsqueda de usuarios para añadir',
           '#formulario' => $form
       ];
   }

  public function formderegistros() {

    $nombre = \Drupal::request()->query->get('nom');
    $apellido = \Drupal::request()->query->get('ape');
    $carnet = \Drupal::request()->query->get('car');
    drupal_set_message('nombre='.$nombre);
    drupal_set_message('apellido='.$apellido);
    drupal_set_message('Carnet='.$carnet);
    $usuarios = array();
    $query = \Drupal::entityQuery('node')
                    ->condition('type', 'bz_usuarios')
                    ->condition('field_nombre', $nombre, '=')
                    ->condition('field_cd_tarjeta', $carnet, '=')
                    ->execute();
    if (!empty($query)) {
        foreach ($query as $usuId) {
          $usu = Node::load($usuId);
          $usuarios[] = $usu;
        }
    }

    //drupal_set_message('todos los ids que tenemos= '.$usuarios);
    return [
        '#theme' => 'verRegistros',
        '#titulo' => $this->t('Resultados de la búsqueda'),
        '#descripcion' => 'vemos el registro de todos los usuarios',
        '#usuarios' => $usuarios

      ];

  }

}

?>
