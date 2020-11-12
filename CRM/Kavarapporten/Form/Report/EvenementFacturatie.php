<?php
use CRM_Kavarapporten_ExtensionUtil as E;

class CRM_Kavarapporten_Form_Report_EvenementFacturatie extends CRM_Report_Form {

function __construct() {
    $this->_columns = [
      'civicrm_contact' => [
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => [
          'sort_name' => [
            'title' => E::ts('Contact Name'),
            'required' => FALSE,
            'default' => TRUE,
          ],
          'jaar' => [
            'title' => 'jaar',
            'required' => TRUE,
            'dbAlias' => "year(start_date)",
          ],
          'maand' => [
            'title' => 'maand',
            'required' => TRUE,
            'dbAlias' => 'month(start_date)',
          ],
          'klantnummer' => [
            'title' => 'klantnummer',
            'required' => TRUE,
            'dbAlias' => "'WELK VELD DEELNEMER?'",
          ],
          'zevende_cijfer' => [
            'title' => '7de cijfer',
            'required' => TRUE,
            'dbAlias' => "'???'",
          ],
        ],
      ],
      'civicrm_value_event_registration' => [
        'fields' => [
          'artikelcode_46' => [
            'title' => 'artikel',
            'required' => TRUE,
          ],
          'aantal' => [
            'title' => 'aantal',
            'required' => TRUE,
            'dbAlias' => "1",
          ],
        ],
      ],
      'civicrm_participant' => [
        'dao' => 'CRM_Event_DAO_Participant',
        'fields' => [
          'fee_amount' => [
            'title' => 'bedrag',
            'required' => TRUE,
          ],
        ],
        'filters' => [
          'event_id' => [
            'name' => 'event_id',
            'title' => ts('Event'),
            'operatorType' => CRM_Report_Form::OP_ENTITYREF,
            'type' => CRM_Utils_Type::T_INT,
            'attributes' => array(
              'entity' => 'Event',
              'select' => array('minimumInputLength' => 0),
            ),
          ],
        ],
      ],
      'civicrm_event' => [
        'dao' => 'CRM_Event_DAO_Event',
        'fields' => [
          'title' => [
            'title' => 'tekstlijn',
            'required' => TRUE,
          ],
        ],
      ],
    ];

    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', 'Facturatie evenement');
    parent::preProcess();
  }

  function from() {
    $this->_from = "
      FROM
        civicrm_contact {$this->_aliases['civicrm_contact']}
      INNER JOIN
        civicrm_participant {$this->_aliases['civicrm_participant']}
      ON
        {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_participant']}.contact_id
      INNER JOIN
        civicrm_event {$this->_aliases['civicrm_event']}
      ON
        {$this->_aliases['civicrm_event']}.id = {$this->_aliases['civicrm_participant']}.event_id
      LEFT OUTER JOIN
        civicrm_value_event_registration {$this->_aliases['civicrm_value_event_registration']}
      ON
        {$this->_aliases['civicrm_event']}.id = {$this->_aliases['civicrm_value_event_registration']}.entity_id
    ";
  }
}
