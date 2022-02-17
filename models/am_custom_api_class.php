<?php

/**
 * Class AM_Custom_api_Class.
 * Class built to handle the custom APIs added to Wordpress
 * @param: none
 */
class AM_Custom_Api_Class {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'tag_api_endpoint'  ));
        add_action( 'rest_api_init', array( $this, 'marker_endpoint'  ) );
        add_action( 'rest_api_init', array( $this, 'marker_list_endpoint' ) );
        add_action( 'rest_api_init', array( $this, 'marker_find_endpoint' ) );
    }

    /*
     * URL api creation to the tags creation
     * @params: none
     * @return: none
     */
    public function tag_api_endpoint() {
        register_rest_route( 'tags/', 'create', array(
            'methods'  => 'POST',
            'callback' => array($this, 'create_tag_api'),
        ) );
    }

    /*
     * URL api creation to the markers creation
     * @params: none
     * @return: none
     */
    public function marker_endpoint() {
        register_rest_route( 'marker/', 'create', array(
            'methods'  => 'POST',
            'callback' => array($this, 'create_marker_api'),
        ) );
    }
    /*
     * URL api creation to the list of markers
     * @params: none
     * @return: none
     */
    public function marker_list_endpoint() {
        register_rest_route( 'marker/', 'list', array(
            'methods'  => 'get',
            'callback' => array($this, 'marker_list_api'),
        ) );
    }
    /*
     * URL api creation to searching of markers
     * @params: none
     * @return: none
     */
    public function marker_find_endpoint() {
        register_rest_route( 'marker/', 'find', array(
            'methods'  => 'POST',
            'callback' => array($this, 'marker_list_api'),
        ) );
    }
    /*
     * API method to get the list of marker in the wp DB
     * @params: post or get (optional)
     * @return: array converted to json
     */
    public function marker_list_api($request_data = null){

        $argumt = array(
            'post_type'              => array( 'marker' ),
            'post_status'            => array( 'publish'),
        );

        if ($request_data->get_params()){
            $params = $request_data->get_params();

            if (isset($params['name']) and $params['name']!= null){

                $argumt["s"] = $params['name'];
            }
        }


        $posts = new WP_Query( $argumt );

        $postRT = array();

        foreach ($posts->posts as $post){
            $tags = '';
            $tagobject = get_the_tags($post->ID);
            if ($tagobject){
                foreach($tagobject as $tag){
                    $tags .= $tag->name." ";
                }
            }

            $postRT[] = array(
                "marker_title"  =>  $post->post_title,
                "long"          =>  get_field("long", $post->ID),
                "lat"           =>  get_field("lat", $post->ID),
                'CTdate'        =>  get_the_date("d/m/Y",$post->ID),
                'CTtags'        =>  $tags,
                'author'        =>  $post->post_author
            );
        }

        return $postRT;
    }

    /*
     * API method to create the tags
     * @params: post
     * @return: int
     */
    public function create_tag_api( $request_data ) {
        $parameters = $request_data->get_params();

        $response = false;
        if (isset($parameters['tagname']) and $parameters['tagname'] != null and is_string($parameters['tagname'])){
            $response = wp_insert_term(
                $parameters['tagname'],
                'post_tag',
                array(
                    'description'=> $parameters['tagname'],
                )
            );
        }

        return $response;
    }

    /*
     * API method to create the markers
     * @params: post
     * @return: int
     */
    public function create_marker_api( $request_data ) {
        $parameters = $request_data->get_params();

        $response = false;
        if (isset($parameters['markername']) and $parameters['markername'] != null and is_string($parameters['markername'])){
            // Gather post data.
            $data_post = array(
                'post_title'    => $parameters['markername'],
                'post_status'   => 'publish',
                'post_author'   => get_current_user_id(),
                'tags_input' => $parameters['tags'],
                'post_type' => 'marker'
            );

            if(is_user_logged_in()){
                $post_id = wp_insert_post( $data_post );

                update_field('long', $parameters['long'], $post_id);
                update_field('lat', $parameters['lat'], $post_id);
                $response = $post_id;
            }
        }

        return $response;
    }
}