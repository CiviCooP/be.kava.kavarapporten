<?php
use CRM_Kavarapporten_ExtensionUtil as E;

class CRM_Kavarapporten_Form_Report_DeelnemerControle extends CRM_Report_Form {
  protected $_customGroupGroupBy = TRUE;

  private $debug = 0;

  function __construct() {
    $this->_columns = [
      'civicrm_contact' => [
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => [
          'id' => [
            'no_display' => TRUE,
            'required' => TRUE,
          ],
          'name' => [
            'title' => 'Naam',
            'required' => TRUE,
            'dbAlias' => 'concat(last_name, \', \', first_name)',
          ],
        ],
        'filters' => [
          'is_deleted' => [
            'no_display' => TRUE,
            'type' => CRM_Utils_Type::T_INT,
            'default_op' => 'eq',
            'default' => 0,
          ],
        ],
        'grouping' => 'Contactgegevens',
        'group_bys' => [
          'id' => ['title' => E::ts('Contact ID'), 'required' => TRUE],
          'name' => ['title' => 'Naam', 'required' => TRUE],
        ],
      ],
      'civicrm_participant' => [
        'dao' => 'CRM_Event_DAO_Participant',
        'fields' => [
          'id' => [
            'no_display' => TRUE,
            'required' => TRUE,
          ],
          'register_date' => [
            'title' => 'Ingeschreven op',
            'required' => TRUE,
            'dbAlias' => 'DATE_FORMAT(participant_civireport.register_date, \'%d %b om %H:%i\')',
          ],
        ],
        'group_bys' => [
          'id' => ['title' => E::ts('Participant ID')],
          'register_date' => ['title' => 'Ingeschreven op'],
        ],
      ],
      'civicrm_event' => [
        'dao' => 'CRM_Event_DAO_Event',
        'fields' => [
          'id' => [
            'no_display' => TRUE,
            'required' => TRUE,
          ],
          'title' => [
            'title' => 'Evenement',
            'required' => TRUE,
          ],
        ],
        'group_bys' => [
          'id' => ['title' => E::ts('Event ID')],
          'title' => ['title' => 'Evenement'],
        ],
      ],
    ];

    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', 'Deelnemers zonder klantnummer');

    $this->_defaults['group_bys']['id'] = TRUE;
    $this->_defaults['group_bys']['name'] = TRUE;
    $this->_defaults['group_bys']['title'] = TRUE;
    $this->_defaults['group_bys']['register_date'] = TRUE;

    parent::preProcess();
  }

  function postProcess() {
    if (isset($this->debug) && $this->debug) {
      $this->beginPostProcess();
      $sql = $this->buildQuery(TRUE);
      echo $sql;
      exit;
    }
    else {
      parent::postProcess();
    }
  }

  function from() {
    $this->_from = "
			FROM
				civicrm_participant {$this->_aliases['civicrm_participant']}
			INNER JOIN
				civicrm_event {$this->_aliases['civicrm_event']}
			ON
				{$this->_aliases['civicrm_participant']}.event_id = {$this->_aliases['civicrm_event']}.id
			INNER JOIN
				civicrm_contact {$this->_aliases['civicrm_contact']}
			ON
				{$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_participant']}.contact_id
			LEFT OUTER JOIN
				civicrm_value_contact_extra extra
			ON
				extra.entity_id = {$this->_aliases['civicrm_contact']}.id
			LEFT OUTER JOIN
				civicrm_relationship rel_titularis
			ON
				rel_titularis.contact_id_b = {$this->_aliases['civicrm_contact']}.id and rel_titularis.relationship_type_id = 35
			LEFT OUTER JOIN
				civicrm_relationship rel_cotitularis
			ON
				rel_cotitularis.contact_id_b = {$this->_aliases['civicrm_contact']}.id and rel_cotitularis.relationship_type_id = 41
			LEFT OUTER JOIN
				civicrm_relationship rel_adjunct
			ON
				rel_adjunct.contact_id_a = {$this->_aliases['civicrm_contact']}.id and rel_adjunct.relationship_type_id = 37
			LEFT OUTER JOIN
				civicrm_relationship rel_plaatsverv
			ON
				rel_plaatsverv.contact_id_a = {$this->_aliases['civicrm_contact']}.id and rel_plaatsverv.relationship_type_id = 38
			LEFT OUTER JOIN
				civicrm_relationship rel_fta
			ON
				rel_fta.contact_id_a = {$this->_aliases['civicrm_contact']}.id and rel_fta.relationship_type_id = 53
			LEFT OUTER JOIN
		    civicrm_value_contact_extra extra_titularis ON rel_titularis.contact_id_a = extra_titularis.entity_id
			LEFT OUTER JOIN
			  civicrm_value_contact_extra extra_cotitularis ON rel_cotitularis.contact_id_a = extra_cotitularis.entity_id
			LEFT OUTER JOIN
			  civicrm_value_contact_extra extra_adjunct ON rel_adjunct.contact_id_b = extra_adjunct.entity_id
			LEFT OUTER JOIN
			  civicrm_value_contact_extra extra_plaatsverv ON rel_plaatsverv.contact_id_b = extra_plaatsverv.entity_id
			LEFT OUTER JOIN
			  civicrm_value_contact_extra extra_fta ON rel_fta.contact_id_b = extra_fta.entity_id
		";
  }

  public function where() {
    parent::where();

    // registration status = 8 (in afwachting van goedkeuring)
    // or
    // registered via the website (to detect based on source) and not processed yet)
    $this->_where .= "
    and
      (ifnull(extra.klantnummer_kava_203, 0)
      + ifnull(extra_titularis.klantnummer_kava_203, 0)
      + ifnull(extra_cotitularis.klantnummer_kava_203, 0)
      + ifnull(extra_adjunct.klantnummer_kava_203, 0)
      + ifnull(extra_plaatsverv.klantnummer_kava_203, 0)
      + ifnull(extra_fta.klantnummer_kava_203, 0) = 0)
      and
        {$this->_aliases['civicrm_event']}.start_date > NOW() - INTERVAL 365 DAY
    ";
  }

  function alterDisplay(&$rows) {
    foreach ($rows as $rowNum => $row) {
      // convert contact name to link
      if (array_key_exists('civicrm_contact_id', $row) && array_key_exists('civicrm_contact_name', $row)) {
        $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=" . $row['civicrm_contact_id']);
        $rows[$rowNum]['civicrm_contact_name'] = '<a href="' . $url . '">' . $row['civicrm_contact_name'] . '</a>';
      }

      // convert event title to link
      if (array_key_exists('civicrm_event_id', $row) && array_key_exists('civicrm_event_title', $row)) {
        $url = CRM_Utils_System::url('civicrm/event/manage/settings', "reset=1&action=update&id=" . $row['civicrm_event_id']);
        $rows[$rowNum]['civicrm_event_title'] = '<a href="' . $url . '">' . $row['civicrm_event_title'] . '</a>';
      }

      // convert number of participants to link
      if (array_key_exists('civicrm_contact_id', $row) && array_key_exists('civicrm_participant_id', $row) && array_key_exists('civicrm_participant_register_date', $row)) {
        $url = CRM_Utils_System::url('civicrm/contact/view/participant', "reset=1&action=update&id=" . $row['civicrm_participant_id'] . '&cid=' . $row['civicrm_contact_id']);
        $rows[$rowNum]['civicrm_participant_register_date'] = '<a href="' . $url . '">' . $row['civicrm_participant_register_date'] . '</a>';
      }
    }
  }

}

