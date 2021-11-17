<?php


class MembersArea{
    const MEMBERS_CATEGORY_DEFAULT = "miembros";

    public $members_category;

    public function run(){
        add_action('init',[$this,'veissplugin_init']);
        add_action("admin_init",[$this,"membersarea_settings"]);
        // Obtiene la categoría guardada
        $this->members_category = ((!get_option("private-category")) ? self::MEMBERS_CATEGORY_DEFAULT:get_option("private-category"));
        // Redirige a los usuarios no autenticados al home
        add_action( 'template_redirect', [$this,'wphooks_members_logged_out_redirect'], 10 );
        // Pone solo los títulos de los post "privados"
        add_action( 'loop_start', [$this,'members_category_only_titles'], 10);
        // Limpia Category: del título de la categoría que hayamos seleccionada como privada
        add_filter('get_the_archive_title', [$this,'wphooks_category_title_markup'],10,3);
        // Define el menú backend
        add_action('admin_menu',[$this,'membersarea_settings_page']);
        return;
    }

    public function veissplugin_init(){
        load_plugin_textdomain( 'veissplugin', false, 'veissplugin/languages' );
    }

    public function membersarea_settings_page(){
        add_menu_page(
            __( 'Members area Plugin', 'veissplugin' ),
            __( 'Category Select', 'veissplugin' ),
            'manage_options',
            'membersarea',
            [$this,'membersarea_settings_page_markup'],
            'dashicons-admin-plugins'
        );
    }

    public function membersarea_settings_page_markup(){
        // Double check user capabilities
        if ( !current_user_can('manage_options') ) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>'. esc_html_e( get_admin_page_title()). '</h1>';
        echo '<form method="post" action="options.php">';
    
        settings_fields( 'membersarea_settings' );
        do_settings_sections( 'membersarea' );
        submit_button(__("Save"));
        echo "</form></div>";
        
    }

    public function membersarea_settings(){
        if( false == get_option( 'private-category' ) ) {
            add_option( 'private-category' );
        }    
        add_settings_section(
            // Unique identifier for the section
            'membersarea_settings_section',
            // Section Title
            __( 'Members Area', 'veissplugin' ),
            // Callback for an optional description
            [$this,'membersarea_settings_section_callback'],
            // Admin page to add section to
            'membersarea'
          );
          add_settings_field(
            // Unique identifier for field
            'membersarea_settings_custom_text',
            // Field Title
            __( 'Select Private Category', 'veissplugin'),
            // Callback for field markup
            [$this,'membersarea_settings_custom_select_callback'],
            // Page to go on
            'membersarea',
            // Section to go in
            'membersarea_settings_section'
          );
          $this->register_my_settings();
    }

    public function membersarea_settings_section_callback(){
        esc_html_e( 'Here you can select the private category', 'veissplugin' );
    }

    public function membersarea_settings_custom_select_callback(){
        $options = get_option( 'private-category' );
        $categories = $this->get_wp_categories_sorted();
        $select_list = "<select multiple name='private-category' id='private-category'>";
        foreach($categories as $key=>$value){
            $selected = (($this->members_category === $value['name']) ? "selected":"");
            $select_list .= "<option value='". $value['name'] . "' ".$selected." >" . $value['name'] . "</option>"; 
        }
        $select_list .= "</select>";
        echo $select_list;
    }

    public function members_category_only_titles(){
        $categories_name = $this->get_categories();
        if (in_array($this->members_category,$categories_name) && !is_single()){
            add_filter('the_content', '__return_false');
            add_filter('the_time', '__return_false');
            add_filter('get_the_time', '__return_false');
            add_filter('the_modified_time', '__return_false');
            add_filter('get_the_modified_time', '__return_false');
            add_filter('the_date', '__return_false');
            add_filter('get_the_date', '__return_false');
            add_filter('the_modified_date', '__return_false');
            add_filter('get_the_modified_date', '__return_false');
            add_filter('get_comment_date', '__return_false');
            add_filter('get_comment_time', '__return_false');
        }
    }

    public function wphooks_category_title_markup($title,$original_title,$prefix){
        if( is_category( $this->members_category)){
            $title = single_cat_title( '', false );
            $title = "Para ".$title;
        }
        return $title;
    }
    
    public function wphooks_members_logged_out_redirect(){
        $allowedRoles = ["administrator","subscriber"];
        if( is_single() && 
            in_array( $this->members_category,$this->get_categories() ) &&  
            !is_user_logged_in() && 
            !in_array($this->get_user_role(),$allowedRoles)){
            
                wp_redirect( home_url() );
            die;
        }
    }

    public function register_my_settings(){
        register_setting( 'membersarea_settings', 'private-category' );
    }


    private function get_wp_categories_sorted():array{
        $categories_obj_list = get_categories();
        $categories_list = [];
        foreach($categories_obj_list as $categories){
            if ($categories->term_id>1){
                $categories_list[$categories->term_id]["name"] = $categories->name;
            }
        }
        ksort($categories_list);
        return $categories_list;
    }

    private function get_categories():array{
        $categories = get_the_category();
        $categories_name = [];
        foreach($categories as $category){
            $categories_name[] = $category->name;
        }
        return $categories_name;
    }

    private function get_user_role():string {
        $user = wp_get_current_user();
        if(!$user){
            return "";
        }
        $userId = $user->ID;
        $userMeta = get_userdata($userId);
        return (!empty($userMeta) ? $userMeta->roles[0]:"");
    }
}