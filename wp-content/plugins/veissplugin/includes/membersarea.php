<?php


class MembersArea{
    const MEMBERS_CATEGORY = "miembros";

    public function run(){
        add_action( 'template_redirect', [$this,'wphooks_members_logged_out_redirect'], 10 );
        add_action( 'loop_start', [$this,'members_category_only_titles'], 10);
        add_filter('get_the_archive_title', [$this,'wphooks_category_title_markup'],10,3);
        return;
    }

    public function members_category_only_titles(){
        $categories_name = $this->get_categories();
        if (in_array(self::MEMBERS_CATEGORY,$categories_name) && !is_single()){
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
        if( is_category( self::MEMBERS_CATEGORY)){
            $title = single_cat_title( '', false );
            $title = "Para ".$title;
        }
        return $title;
    }
    
    public function wphooks_members_logged_out_redirect(){
        
        if( is_single() && in_array( self::MEMBERS_CATEGORY,$this->get_categories() ) &&  !is_user_logged_in() && !in_array($this->get_user_role(),$allowedRoles)){
            wp_redirect( home_url() );
            die;
        }
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