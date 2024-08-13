<?php

namespace Drupal\localgov_publications\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Transliteration\PhpTransliteration;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Apply identifiers (anchors) to headings in content.
 *
 * This filter is copied from the auto_heading_ids module.
 *
 * @Filter(
 *   id = "localgov_publications_heading_ids",
 *   title = @Translation("Automatically apply identifiers (anchors) to headings in content"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   weight = 10
 * )
 */
class HeadingIdFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * Word separator.
   */
  const SEPARATOR = '-';

  /**
   * Transliteration service.
   *
   * @var \Drupal\Core\Transliteration\PhpTransliteration
   */
  protected $transliteration;

  /**
   * Constructs a \Drupal\editor\Plugin\Filter\EditorFileReference object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Transliteration\PhpTransliteration $transliteration
   *   The transliteration service instance.
   *
   * @internal param \Drupal\Core\Entity\EntityManagerInterface $entity_manager An entity manager object.*   An entity manager object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PhpTransliteration $transliteration) {
    $this->transliteration = $transliteration;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('transliteration')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return new FilterProcessResult($this->filterAttributes($text));
  }

  /**
   * Applies ids to headings.
   *
   * @param string $text
   *   The HTML text string to be filtered.
   *
   * @return string
   *   Filtered HTML.
   */
  public function filterAttributes($text) {
    $output = $text;
    $html_dom = Html::load($text);
    $xpath = new \DOMXPath($html_dom);
    $heading_tags = '//h2|//h3|//h4|//h5|//h6';
    $keep_existing_ids = $this->settings['keep_existing_ids'] ?? FALSE;

    // Apply attribute restrictions to headings.
    $headings_found = FALSE;
    foreach ($xpath->query($heading_tags) as $heading_tag) {
      if ($heading_tag instanceof \DOMElement) {
        if ($keep_existing_ids && $heading_tag->hasAttribute('id')) {
          continue;
        }

        $id = $this->transformHeadingToId($heading_tag->nodeValue);
        $heading_tag->setAttribute('id', Html::getUniqueId($id));
        $headings_found = TRUE;
      }
    }

    if ($headings_found) {
      // Only bother serializing if something changed.
      $output = Html::serialize($html_dom);
      $output = trim($output);
    }

    return $output;
  }

  /**
   * Creates a machine name based on the heading.
   *
   * Inspired by Drupal\migrate\Plugin\migrate\process\MachineName::transform.
   * Improved by Drupal\pathauto\AliasCleaner.
   *
   * @param string $heading
   *   String to convert to id.
   *
   * @return mixed
   *   A dash separated id.
   */
  public function transformHeadingToId($heading) {
    // Reduce to ascii.
    $new_value = $this->transliteration->transliterate($heading);
    // Reduce to letters and numbers.
    $new_value = preg_replace('/[^a-zA-Z0-9\/]+/', static::SEPARATOR, $new_value);
    // Remove consecutive separators.
    $new_value = preg_replace('/' . static::SEPARATOR . '+/', static::SEPARATOR, $new_value);
    // Remove leading and trailing separators.
    $new_value = trim($new_value, static::SEPARATOR);
    // Convert to lowercase.
    $new_value = mb_strtolower($new_value);
    // Truncate to 128 chars.
    return Unicode::truncate($new_value, 128, TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $form['keep_existing_ids'] = [
      '#title' => 'Keep existing IDs',
      '#type' => 'checkbox',
      '#default_value' => $this->settings['keep_existing_ids'] ?? FALSE,
      '#description' => $this->t('When this is selected, existing IDs in content will not be changed.'),
    ];

    return $form;
  }

}
