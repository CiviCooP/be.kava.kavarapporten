<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return array (
  0 => 
  array (
    'name' => 'CRM_Kavarapporten_Form_Report_DeelnemerControle',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => 'DeelnemerControle',
      'description' => 'DeelnemerControle (be.kava.kavarapporten)',
      'class_name' => 'CRM_Kavarapporten_Form_Report_DeelnemerControle',
      'report_url' => 'be.kava.kavarapporten/deelnemercontrole',
      'component' => 'CiviEvent',
    ),
  ),
);
