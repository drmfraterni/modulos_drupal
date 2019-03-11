<?php
namespace Drupal\bz_contacto\Controller;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class FormcontactoController extends ControllerBase {


    public function formulario_contacto() {
    // Utilizamos el formulario
        $form = $this->formBuilder()->getForm('Drupal\bz_contacto\Form\formularioContacto');
        //ksm($form);
        //drupal_set_message(t('Formulario: '.$nombre), 'status', FALSE);

    // Le pasamos el formulario y demás a la vista (tema configurado en el module)
        return [
            '#theme' => 'temaFormulario',
            '#titulo' => $this->t('FORMULARIO DE CONTACTO'),
            '#descripcion' => 'Si quiere ponerse en contacto con nosotros, rellene en formulario de contacto <br/> y en breve nos pondremos en contacto con usted',
            '#formulario' => $form
        ];
    }

}

?>
