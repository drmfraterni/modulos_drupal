<?php
namespace Drupal\bz_suscripcion\Controller;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Render\Markup;

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

  public function enviocorreo($nombre, $token){


    // CARGAR EL USUARIO A TRAVÉS DEL TOKEN
    $cd_usu = self::resolverToken($token);
    var_dump($codUsu);


    //OBTENER LOS DATOS DEL USUARIO
    $node = node_load($cd_usu);
    $nombre = $node->get('field_nombre')->value;
    $apellido1 = $node->get('field_apellido1')->value;
    $apellido2 = $node->get('field_apellido2')->value;

    $datos = array();
    $datos['nombre'] = $nombre." ".$apellido1." ".$apellido2;
    var_dump($datos['nombre']);
    //die();
    // OBTENER EL TEXTO PARA ENVIAR
    $textHtml = $config = \Drupal::config('bz_suscripcion.settings');
    $textoConsent = $textHtml->get('mensaje_contestacion.value');

    $correo = $node->get('field_email_con')->value;
    $nombreCompleto = $this->ficha['nombreCompleto'];
      //var_dump($correo);
    $search = [
      '[%nombreCompleto%]',
      '[%email%]',
    ];
    $replace = [
      $nombreCompleto,
      $correo,
    ];
      $textoConsent = str_replace($search, $replace, $textoConsent);
      $datos['texto'] = $textoConsent;
      $datos['nombre'] = $nombre;


    // MANDAR LOS DATOS A LA PLANTILLA DE CORREO
    return [
              '#theme' => 'control',
              '#titulo' => $this->t('CORREO PARA FIRMAR'),
              '#descripcion' => 'Se le ha enviado un correo para Firmar',
              '#datos' => $datos
          ];


  }

  public function firmado($token){

    // Una vez que tenemos firmado el consentimiento
    // Control del usuario a través del token
    $codUsu = self::resolverToken($token);

    // Una vez sabemos el código del usuario recuperamos los datos
    $node = node_load($codUsu);
    $nombre = $node->get('field_nombre')->value;
    $apellido1 = $node->get('field_apellido1')->value;
    $apellido2 = $node->get('field_apellido2')->value;

    $datos = array();
    $datos['nombre'] = $nombre." ".$apellido1." ".$apellido2;


    // MODIFICAR EL NODO PARA PONERLO OK - FIRMADO
    $node->set("field_firmado", 1);
    $node->save();

    // SACAMOS POR PANTALLA LO QUE TENEMOS QUE MOSTRAR.
    return [
              '#theme' => 'firmado',
              '#titulo' => $this->t('Consentimiento Informado Firmado'),
              '#descripcion' => 'Formulario para rellenar de forma automática el consentimiento autorizado',
              '#token' => $cd_usu,
              '#datos' => $datos
          ];

  }

  // FUNCIÓN PARA RECUPERAR EL USUARIO A TRAVÉS DEL TOKEN
  public function resolverToken ($token){

  $clavetoken = \Drupal::config('bz_suscripcion.settings')->get('codigo_seguridad');
  $validar = false;

  $nids = \Drupal::entityQuery('node')
  ->condition('type', 'bz_consentimiento')
  ->execute();
    
  foreach ($nids as $nid) {
    $cd_nids = hash("sha256",$nid.$clavetoken);
      if ($token == $cd_nids){
        $cd_usu = $nid;
      }
    }

  return $cd_usu;


  }

}



?>
