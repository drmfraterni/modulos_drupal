<?php

namespace Drupal\page_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines HelloController class.
 */
class pagina extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function paginadeotrocontrolador() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Página desde otro controlador'),
    ];
  }



}
