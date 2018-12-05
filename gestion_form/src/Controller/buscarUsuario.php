<?php

namespace Drupal\gestion_form\Controller;

use Drupal\Core\Controller\ControllerBase;

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

}

?>
