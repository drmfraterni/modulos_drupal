<?php
namespace Drupal\bz_suscripcion\Controller;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;

class BzsuscripcionController extends ControllerBase {




  public function consentimiento() {
  // Utilizamos el formulario
          $form = $this->formBuilder()->getForm('Drupal\bz_suscripcion\Form\ConsentimientoAutorizado');
          //ksm($form);
          //drupal_set_message(t('Formulario: '.$nombre), 'status', FALSE);

  // Le pasamos el formulario y demás a la vista (tema configurado en el module)
          return [
              '#theme' => 'busqueda',
              '#titulo' => $this->t('Formulario Consentimiento Autorizado'),
              '#descripcion' => 'Formulario para rellenar de forma automática el consentimiento autorizado',
              '#formulario' => $form
          ];

  }

}

?>
