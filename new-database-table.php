<?php

/*
  Plugin Name: Pet Adoption (New DB Table)
  Version: 1.0
  Author: Brad
  Author URI: https://www.udemy.com/user/bradschiff/
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once plugin_dir_path(__FILE__) . 'inc/generatePet.php';


class PetAdoptionTablePlugin
{
  function __construct()
  {
    global $wpdb;
    $this->charset = $wpdb->get_charset_collate();
    $this->tablename = $wpdb->prefix . 'pets';

    // Set up actions for creating and deleting pets.
    add_action('admin_post_createpet', array($this, 'createPet'));
    add_action('admin_post_nopriv_createpet', array($this, 'createPet'));
    add_action('admin_post_deletepet', array($this, 'deletePet'));
    add_action('admin_post_nopriv_deletepet', array($this, 'deletePet'));

    // Set up actions for loading assets and templates.
    add_action('wp_enqueue_scripts', array($this, 'loadAssets'));
    add_filter('template_include', array($this, 'loadTemplate'), 99);

    // Set up action for plugin activation.
    add_action('activate_new-database-table/new-database-table.php', array($this, 'onActivate'));
  }

  /**
   * Function for creating a new pet.
   * 
   * Inserts a new pet into the WordPress database if the current user is an administrator.
   */
  function createPet()
  {
    if (current_user_can('administrator')) {
      $pet = generatePet();
      $pet['petname'] = sanitize_text_field($_POST['incomingpetname']);
      global $wpdb;
      $wpdb->insert(
        $this->tablename,
        $pet
      );
      wp_safe_redirect(site_url('/pet-adoption'));
    } else {
      wp_safe_redirect(site_url());
    }
    exit;
  }

  /**
   * Function for deleting a pet.
   * 
   * Deletes a pet from the WordPress database if the current user is an administrator.
   */
  function deletePet()
  {
    if (current_user_can('administrator')) {
      $id = sanitize_text_field($_POST['idtodelete']);
      global $wpdb;
      $wpdb->delete(
        $this->tablename,
        array('id' => $id)
      );
      wp_safe_redirect(site_url('/pet-adoption'));
    } else {
      wp_safe_redirect(site_url());
    }
    exit;
  }

  /**
   * Function for plugin activation.
   * 
   * Creates the necessary table in the WordPress database when the plugin is activated.
   */
  function onActivate()
  {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta("CREATE TABLE $this->tablename (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      birthyear smallint(5) NOT NULL DEFAULT 0,
      petweight smallint(5) NOT NULL DEFAULT 0,
      favfood varchar(60) NOT NULL DEFAULT '',
      favhobby varchar(60) NOT NULL DEFAULT '',
      favcolor varchar(60) NOT NULL DEFAULT '',
      petname varchar(60) NOT NULL DEFAULT '',
      species varchar(60) NOT NULL DEFAULT '',
      PRIMARY KEY  (id)
    ) $this->charset;");
  }

  /**
   * Function for loading assets.
   * 
   * Loads the necessary CSS file for the pet adoption page.
   */
  function loadAssets()
  {
    if (is_page('pet-adoption')) {
      wp_enqueue_style('petadoptioncss', plugin_dir_url(__FILE__) . 'pet-adoption.css');
    }
  }

  /**
   * Function for loading templates.
   * 
   * Loads the necessary template file for the pet adoption page.
   */
  function loadTemplate($template)
  {
    if (is_page('pet-adoption')) {
      return plugin_dir_path(__FILE__) . 'inc/template-pets.php';
    }
    return $template;
  }

  /**
   * Function for populating the database with pets.
   * 
   * Populates the WordPress database with a large number of pets for testing purposes.
   */
  function populateFast()
  {
    $query = "INSERT INTO $this->tablename (`species`, `birthyear`, `petweight`, `favfood`, `favhobby`, `favcolor`, `petname`) VALUES ";
    $numberofpets = 10_000;
    for ($i = 0; $i < $numberofpets; $i++) {
      $pet = generatePet();
      $query .= "('{$pet['species']}', {$pet['birthyear']}, {$pet['petweight']}, '{$pet['favfood']}', '{$pet['favhobby']}', '{$pet['favcolor']}', '{$pet['petname']}')";
      if ($i != $numberofpets - 1) {
        $query .= ", ";
      }
    }

    global $wpdb;
    $wpdb->query($query);
  }
}

// Create a new instance of the PetAdoptionTablePlugin class.
$petAdoptionTablePlugin = new PetAdoptionTablePlugin();
