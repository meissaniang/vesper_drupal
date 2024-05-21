<?php

namespace Drupal\views_serialization_pager\Plugin\views\style;

use Drupal\rest\Plugin\views\style\Serializer;

/**
 * The style plugin for serialized output formats.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_serialization_pager",
 *   title = @Translation("Serialization with Pager"),
 *   help = @Translation("Serializes views row data and pager using the Serializer component."),
 *   display_types = {"data"}
 * )
 */
class ViewsSerializationPager extends Serializer {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = [];
    // If the Data Entity row plugin is used, this will be an array of entities
    // which will pass through Serializer to one of the registered Normalizers,
    // which will transform it to arrays/scalars. If the Data field row plugin
    // is used, $rows will not contain objects and will pass directly to the
    // Encoder.
    foreach ($this->view->result as $row_index => $row) {
      $this->view->row_index = $row_index;
      $rows[] = $this->view->rowPlugin->render($row);
    }
    unset($this->view->row_index);

    // Get the content type configured in the display or fallback to the
    // default.
    if ((empty($this->view->live_preview))) {
      $content_type = $this->displayHandler->getContentType();
    }
    else {
      $content_type = !empty($this->options['formats']) ? reset($this->options['formats']) : 'json';
    }

    $pager = $this->view->pager;
    $class = get_class($pager);
    $current_page = $pager->getCurrentPage();
    $items_per_page = $pager->getItemsPerPage();
    $total_items = $pager->getTotalItems();
    $total_pages = 0;
    if (!in_array($class, [
      'Drupal\views\Plugin\views\pager\None',
      'Drupal\views\Plugin\views\pager\Some',
    ])) {
      $total_pages = $pager->getPagerTotal();
    }

    $result = [
      'rows' => $rows,
      'pager' => [
        'current_page' => $current_page,
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'items_per_page' => $items_per_page,
      ],
    ];
    return $this->serializer->serialize($result, $content_type, ['views_style_plugin' => $this]);
  }

}
