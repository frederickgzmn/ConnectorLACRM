<?php

/**
 * @package ConnectorLACRM
 * @version 1.0
 */
/*
Plugin Name: ConnectorLACRM
Plugin URI: https://www.infostreamusa.com
Description: Connector to LessAnnoyingCRM and Gravity Forms
Author: Frederick Guzman(InfoStreamUSA Dev)
Version: 1.0
Author URI: https://www.infostreamusa.com
*/

add_action('admin_init','detection_acf');
function detection_acf(){
    /* gravityforms detection */
    if ( !is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
        _e('<div class="error notice"> <p><strong>ConnectorLACRM doesnt work correctly if you dont have <a href="https://www.gravityforms.com/"> Gravity form</a> activated!</strong></p></div>');
    }
}

/*
 * Method to set page the plugin will use on wordpress, it require ACF pro
 * @params: none
 * @return: none
 */
function clacrm_setting_page(){
    add_submenu_page(
        'options-general.php', // top level menu page
        'CLACRM Settings Page', // title of the settings page
        'CLACRM Settings', // title of the submenu
        'manage_options', // capability of the user to see this page
        'clacrm-settings-page', // slug of the settings page
        'clacrm_page_html' // callback function when rendering the page
    );

    add_action('admin_init', 'my_settings_init');
}

add_action('admin_menu','clacrm_setting_page');

function my_settings_init() {
    add_settings_section(
        'my-settings-section', // id of the section
        'LessAnnoyingCRM Settings', // title to be displayed
        '', // callback function to be called when opening section
        'clacrm-settings-page' // page on which to display the section, this should be the same as the slug used in add_submenu_page()
    );

    // register the setting
    register_setting(
        'clacrm-settings-page', // option group
        'user_code'
    );

    // register the setting
    register_setting(
        'clacrm-settings-page', // option group
        'api_token'
    );

    add_settings_field(
        'user_code', // id of the settings field
        'User Code', // title
        'clacrm_settings_usercode', // callback function
        'clacrm-settings-page', // page on which settings display
        'my-settings-section' // section on which to show settings
    );

    add_settings_field(
        'api_token', // id of the settings field
        'Api Token', // title
        'clacrm_settings_token', // callback function
        'clacrm-settings-page', // page on which settings display
        'my-settings-section' // section on which to show settings
    );


}

function clacrm_settings_usercode() {
    $user_code = esc_attr(get_option('user_code', ''));
    ?>
    <div id="user_codediv">
        <input id="user_code" type="text" name="user_code" value="<?php echo $user_code; ?>">
    </div>

    <?php
}

function clacrm_settings_token() {
    $api_token = esc_attr(get_option('api_token', ''));
    ?>
    <div id="user_codediv">
        <input id="api_token" type="text" name="api_token" value="<?php echo $api_token; ?>">
    </div>

    <?php
}

/* Page Setting */
function clacrm_page_html() {
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <form method="POST" action="options.php">
            <?php settings_fields('clacrm-settings-page');?>
            <?php do_settings_sections('clacrm-settings-page');

                submit_button();
            ?>
        </form>
    </div>
<?php
    $forms = GFAPI::get_forms();


    if (!empty($forms)){

        $viewSelect = '<h2>Setting up Form: </h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Form ID</th>
                    <th>Name</th>       
                    <th>Action</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Form ID</th>
                    <th>Name</th>       
                    <th>Action</th>
                </tr>
            </tfoot>
            <tbody>
        <tbody>';
        foreach ($forms as $form){
            $viewSelect .= "<tr><td>".$form["id"]."</td><td>".$form["title"]."</td><td><a href='".get_admin_url()."/options-general.php?page=clacrm-settings-page&formid=".$form["id"]."'>Select Form</a> </td></tr>";
        }
        $viewSelect .= '
            </tbody>
        </table>';

        echo $viewSelect;

        if (isset($_GET["formid"])){
            $form = GFAPI::get_form( $_GET["formid"] );

            $display_form = 'none';
            if (empty($_POST)){
                $display_form = 'done';
            }

            $CSForm = clacrm_get_values($_GET['formid']);

            $viewFields = '<div style="display: '.$display_form.';"><h2>Config fields To form: '.$form["title"].'</h2>';
            $viewFields .= '
            <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Fill up with</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Field</th>
                            <th>Fill up with</th>
                        </tr>
                    </tfoot>
                    <tbody>
                <tbody>';

            $dataFieldGF = "<option value='0'> ---- </option>";

            foreach ($form['fields'] as $formS){
                $dataFieldGF .= "<option value='".$formS->id."'>".$formS->label."</option>";
            }

            $array_fields = array("Phone", "Email", "CompanyName", "FullName", "Address", "Title");
            //Continue here

            $fieldsView = "";
            $n = 0;

            foreach($array_fields as $dField){

                //get the field
                $field = GFFormsModel::get_field( $form, $CSForm[$n][1]);
                $addFieldGF = "<option value='".$CSForm[$n][1]."'>".ucfirst($field->label)."</option>";
                $fieldsView .= "<tr>
                    <td>".$dField."</td>
                    <td><select name='".$dField."'>".$addFieldGF.$dataFieldGF."</select></td>
                </tr>";
                $n++;
            }

            $viewFields .= $fieldsView;
            $viewFields .= '
                </tbody>
            </table>
            <input class="button button-primary" type="submit" value="Save Form Config"/>
            
            </form></div>';

            if (!empty($_POST) and isset($_POST)){
                formBuilderConfig($_POST, $form["id"]);
            }

            echo $viewFields;
        }
    }

}

function clacrm_get_values($form_id){
    if ($form_id) {
        $data = get_post_meta($form_id,"clacrm_form_id");
        $data_ex = explode("|",$data[0]);

        $field_full = array();
        foreach ($data_ex as $data_fields){
            $field_full[] = explode("*",$data_fields);
        }
        return $field_full;
    }
}

function formBuilderConfig($config, $formid){
    if (!empty($config) and isset($config)){

        $option_val = "";
        foreach ($config as $k=>$v){
            $option_val .= $k."*".$v."|";
        }

        update_post_meta( $formid, 'clacrm_form_id', $option_val );
    }

}

/* Resources */
require_once plugin_dir_path( __FILE__ )."/controllers/lacrm_connection.class.php";
$lacrm = new Lacrm_connection();