<?php

require_once 'bemasnewsletter.civix.php';

use CRM_Bemasnewsletter_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function bemasnewsletter_civicrm_config(&$config): void {
  _bemasnewsletter_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function bemasnewsletter_civicrm_install(): void {
  _bemasnewsletter_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function bemasnewsletter_civicrm_enable(): void {
  _bemasnewsletter_civix_civicrm_enable();
}
