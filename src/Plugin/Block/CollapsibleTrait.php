<?php

namespace Drupal\localgov_publications\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

trait CollapsibleTrait {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['collapsible'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Collapsible'),
      '#description' => $this->t('<insert description>'),
      '#default_value' => $this->configuration['collapsible'] ?? 0,

    ];

    $form['collapse_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Auto-collapse window width (px)'),
      '#description' => $this->t('<insert description>'),
      '#states' => [
        'visible' => [
          ':input[name="settings[collapsible]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $this->configuration['collapse_width'] ?? 768,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['collapsible'] = $form_state->getValue('collapsible');
    $this->configuration['collapse_width'] = $form_state->getValue('collapse_width');
  }

  protected function addCollabsibleData(array &$output) {
    $output['#attached']['drupalSettings']['localgov_publications'][$this->pluginId] = [
      'collapsible' => (bool) $this->configuration['collapsible'],
      'collapse_width' => (int) $this->configuration['collapse_width'],
    ];

    $output['#attached']['library'][] = 'localgov_publications/localgov-publications-blocks';
  }

}
