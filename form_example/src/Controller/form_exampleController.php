<?php

namespace Drupal\form_example\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\form_example\Form\editform;


/**
 * Defines HelloController class.
 */
class form_exampleController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */

  public function mostrarunregisro($arg){

    $contenido=array();

    $contenido['linea1']=array (
      '#markup' =>'<strong>En esta información es confidencial. Y la variable que recibo es '.$arg.'</strong></br>',
    );

    $registro=array();

    $registro=editform::listarunregistro($arg);

    ksm($registro);

    return $contenido;

  }

  public function mostrartodo() {

    $contenido=array();

    $contenido['linea1']=array (
      '#markup' =>'<strong>En esta sección se administrará los datos personales de los usuarios</strong></br>',
    );

    //esta construcción nos sirve para crear un botón que nos lleva a una dirección de url
    //Lo que hace es que coge la referencia del routing y por lo tanto da igual que varíe la url que tiene.
    // https://drupal.stackexchange.com/questions/144992/how-do-i-create-a-link


    $url = Url::fromRoute('form_example.addform');
    $project_link = Link::fromTextAndUrl(t('Crear Nuevo Registro'), $url);
    $project_link = $project_link->toRenderable();
    // If you need some attributes.
    $project_link['#attributes'] = array('class' => array('button',

    'button--primary', 'button--small'));




    $contenido['linea2']=array (
      '#markup' =>'<i>Para crear nuevos registros, haga clie en el siguiente botón'. render($project_link) .'</i></br></br>',
    );
    $rows=array();
    $rows=listar();
    ksm($rows);
    // Build a render array which will be themed as a table with a pager.
    $contenido['table'] = [
      '#type' => 'table',
      '#header' => [$this->t('id'),$this->t('Nombre'), $this->t('Apellido'), $this->t('Email'), $this->t('telefono'), $this->t('Fecha'), $this->t('Ver'), $this->t('Editar'), $this->t('Eliminar'),],
      '#rows' => $rows,
      '#empty' => $this->t('There are no nodes to display. Please <a href=":url">create a node</a>.', [':url' => Url::fromRoute('node.add', ['node_type' => 'page'])->toString(),
         ]),
       ];
       // Add our pager element so the user can choose which pagination to see.
       // This will add a '?page=1' fragment to the links to subsequent pages.
    $contenido['pager'] = [
     '#type' => 'pager',
     '#weight' => 10,
     ];


    $contenido['linea3']=array (
      '#markup' =>'<i>Linea 3 - '.$pruebas.'</i></br></br>',
    );

    return $contenido;

  }


}

function listar(){
  $database=\Drupal::database();

  $query = $database->select('datospersonales', 'dp')
    ->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(5);
  $query->fields('dp');

  // Don't forget to tell the query object how to find the header information.
  $result = $query
    //->orderByHeader($header)
    ->execute();

  $rows = [];
  // Internal path (defined by a route in Drupal 8).
  /* use Drupal\Core\Url;
    $url = Url::fromRoute('book.admin');
    $internal_link = \Drupal::l(t('Book admin'), $url);
    */

  global $base_url;
  foreach ($result as $row) {
    // Normally we would add some nice formatting to our rows
    // but for our purpose we are simply going to add our row
    // to the array.
    $row=(array) $row;

    //-------------VER--------------
    $url = Url::fromUri($base_url.'/form_example/'.$row['id']);
    $ver_link = \Drupal::l(t('Ver'), $url);
    $row['ver']=$ver_link;
    //-------------EDITAR--------------
    $url = Url::fromUri($base_url.'/form_example/'.$row['id'].'/editar');
    $editar_link = \Drupal::l(t('Editar'), $url);
    $row['editar']=$editar_link;
    //-------------ELIMINAR--------------
    $url = Url::fromUri($base_url.'/form_example/'.$row['id'].'/delete');
    $eliminar_link = \Drupal::l(t('Eliminar'), $url);
    $row['eliminar']=$eliminar_link;

    $rows[]= $row;

  }

  return $rows;

}
