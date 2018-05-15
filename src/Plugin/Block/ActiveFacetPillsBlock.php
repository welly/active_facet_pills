<?php

namespace Drupal\active_facet_pills\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Block to display active facet pills
 *
 * @Block(
 *   id = "active_facet_pills",
 *   admin_label = @Translation("Active Facet Pills block"),
 *   category = @Translation("Active Facet Pills"),
 * )
 */
class ActiveFacetPillsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#theme' => 'active_facet_pills_block',
      '#facet_links' => $this->getActiveFacetLinks(),
      '#cache' => array('max-age' => 0)
    );
  }

  /**
   * Fetch the active facets and return an array of dictionaries,
   * each defining 'name' (the display name of the facet) and
   * 'url' (the url of the current page without the given facet
   * enabled)
   */
  protected function getActiveFacetLinks() {
    $facetManager = \Drupal::service('facets.manager');
    $enabled_facets = $facetManager->getEnabledFacets();
    $facetLinks = array();
    foreach ($enabled_facets as $facet) {
      $processed = $facetManager->returnProcessedFacet($facet);
      if ($processed) {
        $facetManager->build($processed);
        $results = $processed->getResults();
        $active = $processed->getActiveItems();
        foreach($results as $r) {
          if (in_array($r->getRawValue(), $active)) {
            $facetLinks[] = array(
              'name' => $r->getDisplayValue(),
              'url' => $r->getUrl()->toString()
            );
          }
        }
      }
    }
    // Let modules and themes alter the links
    \Drupal::moduleHandler()->alter('active_facet_pills_links', $facetLinks);
    $theme_manager = \Drupal::service('theme.manager');
    if ($theme_manager->hasActiveTheme()) {
        $theme_manager->alterForTheme(
            $theme_manager->getActiveTheme(), 'active_facet_pills_links', $facetLinks
        );
    }
    return $facetLinks;
  }
}
