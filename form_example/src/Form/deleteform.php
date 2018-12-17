<?php

namespace Drupal\form_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements an example form.
 */
class deleteform extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_example_deleteform';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg=null) {

    $form['elemento_imagen'] = array(

      '#markup' =>'El resgistro a eliminar es '. $arg.'<br/><br/><i>Esta acción no se podrá deshacer</i>',
    );


    $form['#attached']['library'][] = 'form_example/form_example_libraries';

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#attributes'=>array(
        'class'=>array('mibotonprincipal')
      ),
    ];
    $form['actions']['cancelar'] =array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('form_example_cancelar'),
      '#limit_validation_errors' => array(),
      '#attributes'=>array(
        'class'=>array('mibotonprincipal')
      ),

    );

    $form['idregistro'] = array(
      '#type' => 'hidden',
      '#value' => $arg,
    );


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */



  public function submitForm(array &$form, FormStateInterface $form_state) {

    $id=$form_state->getValue('idregistro');
    // MODIFICAMOS LOS DATOS EN LA BBDD.
    // https://www.drupal.org/docs/8/api/database-api/update-queries

    $connection = \Drupal::database();

    $num_deleted = $connection->delete('datospersonales')
      ->condition('id',$id)
      ->execute();


    drupal_set_message("Datos eliminados correctamente. Se ha eliminado el registro ".$id);

    // la dirección lo toma del routing de form_example.

    $form_state->setRedirect('form_example.mostrartodo');

    /*
    drupal_set_message($this->t('El número de teléfono es  @number', ['@number' => $form_state->getValue('telefono')]));

    global $base_url;

    $response = new RedirectResponse($base_url);
    $response->send();
    */
  }

}









 ?>
