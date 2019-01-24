<?php

namespace Drupal\bz_mensajes\Controller;

use Drupal\examples\Utility\DescriptionTemplateTrait;

/**
 * Simple page controller for drupal.
 */
class Page {

  use DescriptionTemplateTrait;

  /**
   * {@inheritdoc}
   */
  public function getModuleName() {
    return 'form_api_example';
  }

}
