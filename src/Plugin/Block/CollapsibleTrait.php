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

  /**
   * Add collapsible data to a block build.
   */
  protected function addCollabsibleData(array &$output) {

    $collapsible = (bool) $this->configuration['collapsible'] ?? FALSE;
    $collapseWidth = (int) $this->configuration['collapse_width'] ?? 0;

    // This needs a width to work.
    if ($collapseWidth === 0) {
      $collapsible = FALSE;
    }

    $output['#attached']['drupalSettings']['localgov_publications'][$this->pluginId] = [
      'collapsible' => $collapsible,
      'collapse_width' => $collapseWidth,
    ];
    $output['#attached']['library'][] = 'localgov_publications/localgov-publications-blocks';
  }

}
