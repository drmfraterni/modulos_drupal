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
  public function verpagina($idpagina = null) {
    return [
      '#type' => 'markup',
      '#markup' =>'El parámetro recibido es: '.$idpagina,
    ];
  }
  public function verpaginacustom($custom_arg) {
    return [
      '#type' => 'markup',
      '#markup' =>'El parámetro recibido es: '.$custom_arg,
    ];
  }
  public function varias() {

    $contenido=array();

    $contenido['linea1']=array (
      '#markup' =>'<strong>Linea 1</strong></br>',
    );
    $contenido['linea2']=array (
      '#markup' =>'<i>Linea 2</i></br></br>',
    );

    $contenido['linea3']=array (
      '#markup' =>'<i>Linea 3</i></br></br>',
    );

    return $contenido;

  }

  public function form() {

    $contenido=array();

    $contenido['linea1']=array (
      '#markup' =>'<strong>Linea 1</strong></br>',
    );
    $contenido['linea2']=array (
      '#markup' =>'<i>Linea 2</i></br></br>',
    );

    $contenido['linea3']=array (
      '#markup' =>'<i>Linea 3</i></br></br>',
    );

    $contenido['linea4']=\Drupal::formBuilder()->getForm('Drupal\form_example\Form\addform');

    return $contenido;

  }

  public function template() {

    $form=\Drupal::formBuilder()->getForm('Drupal\form_example\Form\addform');

      return [
        '#theme' => 'my_template',
        '#mi_variable' => $this->t('Esta es la variable'),
        '#form' => $form,
      ];

    }

}
