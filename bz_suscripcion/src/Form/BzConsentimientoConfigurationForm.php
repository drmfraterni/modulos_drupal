<?php

/**
 * @file
 * Contains \Drupal\rdls_subscriptions\Form\MuprespaNodeUnistepConfigurationForm.
 */
namespace Drupal\bz_suscripcion\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RequestContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Defines a form that configures form module settings.
 */
class BzConsentimientoConfigurationForm extends ConfigFormBase {

 /**
  * The path alias manager.
  *
  * @var \Drupal\Core\Path\AliasManagerInterface
  */
  protected $aliasManager;

 /**
  * The path validator.
  *
  * @var \Drupal\Core\Path\PathValidatorInterface
  */
  protected $pathValidator;

 /**
  * The request context.
  *
  * @var \Drupal\Core\Routing\RequestContext
  */
 protected $requestContext;

 /**
  * Constructs a MuprespaDuesConfigurationForm object.
  *
  * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
  *         The factory for configuration objects.
  * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
  *         The path alias manager.
  * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
  *         The path validator.
  * @param \Drupal\Core\Routing\RequestContext $request_context
  *         The request context.
  */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AliasManagerInterface $alias_manager,
    PathValidatorInterface $path_validator,
    RequestContext $request_context) {
    parent::__construct ( $config_factory );

    $this->aliasManager = $alias_manager;
    $this->pathValidator = $path_validator;
    $this->requestContext = $request_context;
 }

 /**
  *
  * {@inheritdoc}
  *
  */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('config.factory'),
      $container->get('path.alias_manager'),
      $container->get('path.validator'),
      $container->get('router.request_context')
    );
  }

  /**
   *
   * {@inheritdoc}
   *
   */
   public function getFormId() {
    return 'bz_suscripcion_admin_settings';
  }

  /**
   *
   * {@inheritdoc}
   *
   */
  protected function getEditableConfigNames() {
    return [
      'bz_suscripcion.settings',
    ];
  }

  /**
   *
   * {@inheritdoc}
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    $config = $this->config('bz_suscripcion.settings');

    $form['personalizacion'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Personalizacion del consentimiento'),
      '#description' => $this->t('Introduce las variables para la personalización del Consentimiento'),
      '#default_value' => $config->get('personalizacion.value'),
      '#format' => $config->get('personalizacion.format'),

    ];
		
	$form['consentimiento'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Consentimiento Informado'),
      '#description' => $this->t('Introduce el texto del CONSENTIMIENTO INFORMADO'),
      '#default_value' => $config->get('consentimiento.value'),
      '#format' => $config->get('consentimiento.format'),

    ];

    $form['mensaje_contestacion'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Mensaje de contestación'),
      '#description' => $this->t('Una vez enviado relleno el formulario se envía este mensaje de contestación'),
      '#default_value' => $config->get('mensaje_contestacion.value'),
      '#format' => $config->get('mensaje_contestacion.format'),

    ];
	
	$codseg = $config->get('codigo_seguridad');
	
	//var_dump($codseg);
	
	isset($codseg) ? '_boulderz' : $config->get('codigo_seguridad');

    // En el caso que hubiera un código de seguridad este seria el por defecto
    //$codseg = isset($pruebas) ? $config->get('codigo_seguridad') : '_boulderz';
    //$codseg = isset( $config->get('codigo_seguridad')) ? $config->get('codigo_seguridad') : '_boulderz';
    $form['codigo_seguridad'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Código de Seguridad'),
      '#description' => $this->t('Introduce el texto para el código de seguridad del Token'),
      '#default_value' => $codseg,

    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    var_dump($values['codigo_seguridad']);

    $this->config('bz_suscripcion.settings')->set('personalizacion.value', $values['personalizacion']['value'])->save();
    $this->config('bz_suscripcion.settings')->set('personalizacion.format', $values['personalizacion']['format'])->save();
	$this->config('bz_suscripcion.settings')->set('consentimiento.value', $values['consentimiento']['value'])->save();
    $this->config('bz_suscripcion.settings')->set('consentimiento.format', $values['consentimiento']['format'])->save();
    $this->config('bz_suscripcion.settings')->set('mensaje_contestacion.value', $values['mensaje_contestacion']['value'])->save();
    $this->config('bz_suscripcion.settings')->set('mensaje_contestacion.format', $values['mensaje_contestacion']['format'])->save();
    $this->config('bz_suscripcion.settings')->set('codigo_seguridad', $values['codigo_seguridad'])->save();
    //$this->config('bz_suscripcion.settings')->save();

    parent::submitForm($form, $form_state);

    }


  }
