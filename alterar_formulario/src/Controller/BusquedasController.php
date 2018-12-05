<?php
namespace Drupal\alterar_formulario\Controller;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;

class BusquedasController extends ControllerBase {


    public function busqueda_usuario() {
// Utilizamos el formulario
        $form = $this->formBuilder()->getForm('Drupal\alterar_formulario\Form\BusquedaUsuarioForm');
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
