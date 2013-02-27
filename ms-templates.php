<?php
/*
    Plugin Name: Easy page templates info
    Plugin URI: http://viewsource.rs
    Description: This plugin will extend default WordPress page listing. You'll see a new column with temlate file for each page, and nice filter option for display all pages with particular template assigned.
    Version: 1.0
    Author: Milan Stojanov
    Author URI: http://www.milanstojanov.iz.rs
*/

class MS_Template {

    function __construct() {
        $this->setHooks();
    }

    function setHooks() {
        // Hooks for displaying Template column in page listing        
        add_filter('manage_pages_columns', array($this, 'addColumn'));  
        add_action('manage_pages_custom_column', array($this,'displayTemplateColumn'), 10, 2);

        // Hooks for filtering pages by available templates
        add_action('restrict_manage_posts', array($this, 'displayFilterOptions'));
        add_filter('parse_query', array($this, 'getPagesByTemplate'));
    }

    // returns page template by its id
    function getPageTemplate($post_ID) {     
        return get_post_meta( $post_ID, '_wp_page_template', true );      
    }
    
    // register a new column grid in the system
    function addColumn($defaults) {  
        $defaults['template'] = 'Template';  
        return $defaults;  
    }  
    
    // displaying columns with page template information 
    function displayTemplateColumn($column_name, $post_ID) {  
        if ($column_name == 'template') {  
            $pageTemplate = $this->getPageTemplate($post_ID);  
            if ($pageTemplate) {  
                echo $pageTemplate;  
            }  
        }  
    }

    // render available templates as filter options
    function displayFilterOptions() {

        if ($this->isPageType()) {            

            $templates = $this->getAllPageTemplates();            

            echo '<select name="template">';
            echo '<option value="">Show all</option>';
            
            $currTemplate = isset($_GET['template'])? $_GET['template']:'';

            foreach ($templates as $template) {
                echo "<option value={$template} ";
                if($template == $currTemplate) { echo 'selected="selected"'; }
                echo ">" . $template ."</option>";                        
            }
            
            echo '</select>';            
        }
    }

    // return all pages for particular template
    function getPagesByTemplate( $query ){
        global $pagenow;        
        if ($this->isPageType() && is_admin() && $pagenow=='edit.php' && $this->isTeplateSet()) {
            $query->query_vars['meta_key'] = '_wp_page_template';
            $query->query_vars['meta_value'] = $_GET['template'];
        }
    } 

    // returns all registred page templates
    function getAllPageTemplates() {
        global $wpdb;
        return $wpdb->get_col("SELECT DISTINCT meta_value FROM wp_postmeta WHERE meta_key = '_wp_page_template'");      
    }

    /* Helper functions */

    function isPageType() {
        return isset($_GET['post_type']) && 'page' == $_GET['post_type'];
    }

    function isTeplateSet() {
        return isset($_GET['template']) && $_GET['template'] != '';
    }    

    /* End helper functions */
}

new MS_Template;
