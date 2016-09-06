<?php

class ModulesFieldController extends Kirby\Panel\Controllers\Field {
  protected $defaults = array();
  protected $options = array();
  protected $origin = null;

  public function __construct($model, $field) {
    parent::__construct($model, $field);
    $this->options = $this->field()->options();
  }

  public function add() {
    $origin = $this->origin();
    $page = $this->model();

    $form = $this->form('add', array($origin, $page));

    $templates = array();

    foreach($origin->blueprint()->pages()->template() as $template) {
      $templates[] = array(
        'options' => $this->options($template->name()),
        'title' => $template->title(),
        'name' => $template->name(),
      );
    }

    $options = array(
      'templates' => $templates,
      'redirect' => $page->uri('edit'),
    );

    return $this->modal('add', compact('form', 'options'));
  }

  public function delete() {
    $page = $this->origin()->find(get('uid'));

    $form = $this->form('delete', array($page, $this->model()));
    $form->style('delete');

    return $this->modal('delete', compact('form'));
  }

  public function duplicate() {
    $page = $this->origin()->find(get('uid'));
    $root = kirby()->roots()->content();
    dump(dir::copy($root . DS . $page->diruri(), $root . DS . 'modules-test/modules/test'));
  }

  public function field() {
    $fields = $this->model()->blueprint()->fields(null);
    return $fields->get($this->fieldname());
  }

  public function origin() {
    // Return from cache if possible
    if($this->origin) return $this->origin;

    // Determine where the modules live
    if(!$origin = $this->model()->find(Kirby\Modules\Modules::parentUid())) $origin = $this->model();

    return $this->origin = $origin;
  }

  public function defaults() {
    // Return from cache if possible
    if($this->defaults) return $this->defaults;

    // Filter options for default values
    $defaults = array_filter($this->options, function($value) {
      return !is_array($value);
    });

    return $this->defaults = $defaults;
  }

  public function options($template) {
    // Get module specific options
    $options = a::get($this->options, $template, array());

    return a::update($this->defaults(), $options);
  }
}
