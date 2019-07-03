<?php
namespace Drupal\migrar_db;

use Drupal\node\Entity\Node;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDatetime;
use Drupal\rdls_mailing\RdlsMailManager;

/**
 * Class RdlsMailManager.
 *
 * @package Drupal\rdls_mailing
 * @ingroup rdls_api
 */
class MigrardbService {
  use StringTranslationTrait;

  /**
   * Plan all mails for the active subscribers.
   *
   * @return string $message
   *   Message containing result & statistics.
   *
   */

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


}
