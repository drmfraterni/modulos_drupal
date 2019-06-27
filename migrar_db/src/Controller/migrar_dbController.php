<?php

namespace Drupal\migrar_db\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity;
//use \Drupal\node\Entity\Node::loadMultiple;


/**
 * Returns responses for Node routes.
 */
class migrar_dbController extends ControllerBase {


   public function listarNodos($dato){
     // id del $usuarios
     //$id = cambiarUsuario($dato);
     $id = cambiarProducto($dato);
     var_dump($id);
     $node = Node::load($id);

     /*$registro['Nombre'] = $node->get('title')->value;
     $registro['Apellido1'] = $node->get('field_apellido1')->value;
     $registro['Apellido2'] = $node->get('field_apellido2')->value;
     $registro['email'] = $node->get('field_email')->value;
     $registro['tarjeta'] = $node->get('field_tarjeta')->value;
     $registro['id'] = $node->get('field_id')->value;
     $registro['telefono'] = $node->get('field_field_fcalta')->value;
     $registro['falta'] = $node->get('field_telefono')->value;
     */

      return [
          '#theme' => 'migrardb',
          '#titulo' => $this->t('Ver todos los registros'),
          '#descripcion' => 'Vemos todos los registros de una tabla',
          '#registros' => $registro,
      ];

  }

   public function listarCompras (){

    $registros = array ();
   	$registros = bdCompras();
    $cantidadReg = count($registros['idCompra']);

    for ($i=0; $i < $cantidadReg; $i++){
      // ARREGLOS DE CAMPOS DIVERSOS DE LA TABLA COMPRAS

      $fechaInicio = $registros['dtFechaInicio'][$i];
      $fechaFin = $registros['dtFechaFin'][$i];


      $id_usuario = cambiarUsuario($registros['idUsuario'][$i]);
      $id_producto = cambiarProducto($registros['idProducto'][$i]);
      //$dateInicio = date("d/m/Y H:i:s", strtotime($fechaInicio));
      //$datefin = date("d/m/Y H:i:s", strtotime($fechaFin));
      $dateInicio = date("Y-m-d\TH:i:s", strtotime($fechaInicio));
      $datefin = date("Y-m-d\TH:i:s", strtotime($fechaFin));



      if ($datefin == "1970-01-01T01:00:00"){
        $datefin = NULL;
      }


      $precio = $registros['nmPrecio'][$i];
      //$autor = \Drupal::currentUser()->id();

      // METEMOS LOS DATOS EN EL REGISTRO DE BZ_COMPRAS

      $node = Node::create([
        'type' => 'bz_compras',
  		  'langcode' => 'es',
  		  'uid' => 0,
  		  'moderation_state' => 'published',
  		  'title' =>"COMPRA - ". $id_usuario." - ".$id_producto,
        'field_idcompra' => $registros['idCompra'][$i],
        'field_producto' => $id_producto,
        'field_idusuario' => $id_usuario,
        'field_fecha_fin'=> [ $datefin ],
        'field_fecha'=> [ $dateInicio ],
        'field_precio_compra'=>$precio,


        ]);


        /*var_dump($dateInicio);
        var_dump($datefin);*/
        //var_dump($precio);
        //die();

        $node->save();

      }
      drupal_set_message(t('Terminado'), 'status', FALSE);

      return [
          '#theme' => 'migrardb',
          '#titulo' => $this->t('Ver todos los registros'),
          '#descripcion' => 'Vemos todos los registros de una tabla',
          //'#registros' => $registros,
      ];



  }
   public function listarUsuarios (){

   	$registros = array ();
   	$registros = bdUsuarios();
    $cantidadReg = count($registros['idUsuario']);

    var_dump($cantidadReg);
    die();

    for ($i=0; $i < $cantidadReg; $i++){

      $fecha = $registros['fcAlta'][$i];
      $fAlta = date("Y-m-d\TH:i:s", strtotime($fecha));

      $node = Node::create([
        'type' => 'bz_usuarios',
  		  'langcode' => 'es',
  		  // The user ID.
  		  'uid' => 0,
  		  'moderation_state' => 'published',
  		  'title' =>$registros['dsApellido1'][$i]." ". $registros['dsApellido2'][$i].", ".$registros['dsNombre'][$i],
        'field_id' => $registros['idUsuario'][$i],
        'field_apellido1' => $registros['dsApellido1'][$i],
        'field_apellido2' => $registros['dsApellido2'][$i],
        'field_email'=> $registros['dsEmail'][$i],
        'field_fcalta'=>[$fAlta],
        'field_telefono'=> $registros['dsTelefono'][$i],
        'field_tarjeta'=> $registros['cdTarjeta'][$i],

      ]);

      //$node->save();
    }
      drupal_set_message(t('Terminado'), 'status', FALSE);
    //var_dump($usuarios);

    return [
        '#theme' => 'migrardb',
        '#titulo' => $this->t('Ver todos los registros'),
        '#descripcion' => 'Vemos todos los registros de una tabla',
        '#registros' => $registros,
    ];




   }

   public function listarProductos (){

     $registros = array ();
     $registros = bdProductos();
     $cantidadReg = count($registros['dsProducto']);

     for ($i=0; $i < $cantidadReg; $i++){

        $node = Node::create([
          'type' => 'bza_productos',
     		  'langcode' => 'es',
     		  // The user ID.
     		  'uid' => 0,
     		  'moderation_state' => 'published',
     		  'title' => $registros['dsProducto'][$i],
          'field_prod_precio' => $registros['nmPrecio'][$i],
          'field_periodicidad' => $registros['itPeriodicidad'][$i],
          'field_idproducto' => $registros['idProducto'][$i],

       ]);

       $node->save();
     }
       drupal_set_message(t('Terminado'), 'status', FALSE);
     //var_dump($usuarios);

     return [
         '#theme' => 'migrardb',
         '#titulo' => $this->t('Ver todos los registros'),
         '#descripcion' => 'Vemos todos los registros de una tabla',
         '#registros' => $registros,
     ];

   }


}

// FUNCIÓN QUE BUSCA EN LA TABLA USUARIOS DE LA BBDD
function bdUsuarios(){
    // función para conectar con una base de datos externa al drupal 8
    \Drupal\Core\Database\Database::setActiveConnection('external');

    $con = \Drupal\Core\Database\Database::getConnection();

    $result = $con->query("SELECT idUsuario, dsNombre, dsApellido1, dsApellido2,
    dsEmail, fcAlta, dsTelefono1, cdTarjeta FROM bza_usuarios");
    $registros = array();
    if ($result){
      while ($row = $result->fetchAssoc()){
        $registros['idUsuario'][] = $row['idUsuario'];
        $registros['dsNombre'][] = $row['dsNombre'];
        $registros['dsApellido1'][] = $row['dsApellido1'];
        $registros['dsApellido2'][] = $row['dsApellido2'];
        $registros['dsEmail'][] = $row['dsEmail'];
        $registros['fcAlta'][] = $row['fcAlta'];
        $registros['dsTelefono'][] = $row['dsTelefono1'];
        $registros['cdTarjeta'][] = $row['cdTarjeta'];

      }
    }

    \Drupal\Core\Database\Database::setActiveConnection();
    return $registros;

}
// FUNCIÓN QUE BUSCA EN LA TABLA PRODUCTOS DE LA BBDD
function bdProductos(){
    // función para conectar con una base de datos externa al drupal 8
    \Drupal\Core\Database\Database::setActiveConnection('external');

    $con = \Drupal\Core\Database\Database::getConnection();

    $result = $con->query("SELECT idProducto, dsProducto, nmPrecio, itPeriodicidad FROM bza_productos");
    $registros = array();
    if ($result){
      while ($row = $result->fetchAssoc()){
        $registros['idProducto'][] = $row['idProducto'];
        $registros['dsProducto'][] = $row['dsProducto'];
        $registros['nmPrecio'][] = $row['nmPrecio'];
        $registros['itPeriodicidad'][] = $row['itPeriodicidad'];
      }
    }else{
      drupal_set_message(t('Error en la consulta de Productos'), 'status', FALSE);
    }

    \Drupal\Core\Database\Database::setActiveConnection();
    return $registros;

}
// FUNCIÓN QUE BUSCA EN LA TABLA COMPRAS DE LA BBDD
function bdCompras(){
    // función para conectar con una base de datos externa al drupal 8
    \Drupal\Core\Database\Database::setActiveConnection('external');

    $con = \Drupal\Core\Database\Database::getConnection();

    $result = $con->query("SELECT idCompra, idUsuario, idProducto, dtFechaInicio, dtFechaFin, nmPrecio FROM bza_compras");
    $registros = array();
    if ($result){
      while ($row = $result->fetchAssoc()){
        $registros['idCompra'][] = $row['idCompra'];
        $registros['idUsuario'][] = $row['idUsuario'];
        $registros['idProducto'][] = $row['idProducto'];
        $registros['dtFechaInicio'][] = $row['dtFechaInicio'];
        $registros['dtFechaFin'][] = $row['dtFechaFin'];
        $registros['nmPrecio'][] = $row['nmPrecio'];
      }
    }else{
      drupal_set_message(t('Error en la consulta de Productos'), 'status', FALSE);
    }

    \Drupal\Core\Database\Database::setActiveConnection();

    return $registros;

}
// FUNCIÓN CAMBIA EL ID DEL USUARIO POR ID_NODO DEL NODO TIPO USUARIO
function cambiarUsuario($dato){

    $query = \Drupal::entityQuery('node')
          ->condition('type', 'bz_usuarios')
          ->condition('field_id', $dato, '=')
          ->execute();

      if (!empty($query)) {
        foreach ($query as $cod) {
            //$codigo = intval($cod);
            $codigo = $cod;
        }
    }

    return $codigo;

  }
  // FUNCIÓN CAMBIA EL ID DEL USUARIO POR ID_NODO DEL NODO TIPO PRODUCTO
function cambiarProducto($dato){

    $query = \Drupal::entityQuery('node')
          ->condition('type', 'bza_productos')
          ->condition('field_idproducto', $dato, '=')
          ->execute();

      if (!empty($query)) {
        foreach ($query as $cod) {
            //$codigo = intval($cod);
            $codigo = $cod;
        }
    }

    return $codigo;

  }


?>
