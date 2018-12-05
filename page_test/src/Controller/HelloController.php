<?php

namespace Drupal\page_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines HelloController class.
 */
class HelloController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello, World!'),
    ];
  }

  public function pagina() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Esto es la nueva página'),
    ];
  }

  public function pagina1() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Esto es la página 1'),
    ];
  }



}
