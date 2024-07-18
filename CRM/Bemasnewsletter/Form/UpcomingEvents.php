<?php

use CRM_Bemasnewsletter_ExtensionUtil as E;

class CRM_Bemasnewsletter_Form_UpcomingEvents extends CRM_Core_Form {
  public function buildQuickForm(): void {
    $this->setTitle('Evenementen voor nieuwsbrief');

    $this->addFormElements();
    $this->addFormButtons();

    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess(): void {
    $values = $this->exportValues();
    $eventList = CRM_Bemasnewsletter_BAO_UpcomingEvents::getFormattedList($values['event_date_from'], $values['event_date_to'], $values['newsletter_lang'], array_keys($values['events_lang']));
    $this->assign('event_list', $eventList);

    parent::postProcess();
  }

  private function addFormElements() {
    $this->add('datepicker', 'event_date_from', 'Van', [], TRUE, ['time' => FALSE]);
    $this->add('datepicker', 'event_date_to', 'Tot', [], TRUE, ['time' => FALSE]);
    $this->addRadio('newsletter_lang', 'Taal nieuwsbrief', ['nl_NL' => 'Nederlands', 'fr_FR' => 'Frans'], [], '<br>', TRUE);
    $this->addCheckBox('events_lang', 'Taal evenementen', ['Nederlands' => 'V', 'Frans' => 'W', 'Engels' => 'N'], [], [], TRUE);
  }

  private function addFormButtons() {
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ],
    ]);
  }

  public function getRenderableElementNames(): array {
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
