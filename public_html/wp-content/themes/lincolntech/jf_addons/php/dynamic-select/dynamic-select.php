<?php
namespace Jellyfish;
/**
  * Dynamically populates the program and campus selects
  *
  * @package Jellyfish
  * @author  Michael Brose <michael.brose@jellyfish.net>
 */
class DynamicSelect
{
    private $cat = [
        'campus' => 5,
        'program' => 6,
    ];


    private $values = ['campus' => [], 'program' => []];
    private $labels = ['campus' => [], 'program' => []];
    /**
      * Adds filters, actions, and includes a class
      *
      * @access public
     */
    public function __construct() {
        include('Response.php');
        add_action('wp_enqueue_scripts', array($this, 'add_scripts'), 11);
        add_action('wp_ajax_populate_selects', array($this, 'ajax'));
        add_action('wp_ajax_nopriv_populate_selects', array($this, 'ajax'));
        add_filter("wpcf7_form_tag", array($this, "populate_selects"));
    }


    /**
     * Adds two variables to the Javascript variable "ajax_object"
     *
     * @access public
     */
    public function add_scripts() {
        wp_localize_script(
            'lincolntech-app-scripts',
            'ajax_object',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('rti'),
            ]
        );
    }

    /**
     * Checks for the appropriate select tag from CF7 and populates the options
     *
     * @access public
     * @param array $tag The CF7 array
     * @return array The formated CF7 tag array
     */
    public function populate_selects($tag) {        
        //echo "\n\n populate_select:: \n";
        //print_r($tag);
        
        $options = (array)$tag['options'];        
        $select = false;
        $term = false;

        foreach ($options as $option) {
            if ($option == 'id:program') {
                $select = 'program';
                break;
            } elseif  ($option == 'id:campus') {
                $select = 'campus';
                break;
            }
        }
        if (false !== $select) {
            $categories = get_the_category();

            if (!empty($categories)) {
                if ($this->cat['campus'] == $categories[0]->term_id) {
                    $term = 'campus';
                } elseif ($this->cat['program'] == $categories[0]->term_id) {
                    $term = 'program';
                }
            }

        
            // page id
            $id = get_the_ID();

            $data = $this->populate_the_select($select, $term, $id);
            // We have a full data array of both select fields

            if (!empty($data[$select]['values'])) {
                foreach ($data[$select]['values'] as $key => $value) {
                    if ('primary' === $key) {
                        // Single value, so remove the "first_as_label" option
                        if (($k = array_search('first_as_label', $tag['options'])) !== false) {
                            unset($tag['options'][$k]);
                        }
                        $tag['raw_values'] = [$value];
                        $tag['values'] = [$value];
                        $tag['labels'] = [$data[$select]['labels'][$key]];
                        break;
                    } else {
                        $tag['raw_values'][] = $value;
                        $tag['values'][] = $value;
                        $tag['labels'][] = $data[$select]['labels'][$key];
                    }
                }
            }
            $this->remove_first_label_when_only_one_option($tag);
        } // if
        return $tag;
    } // populate_selects


    /**
     * Ajax function used in the main RFI form to populate the campus and program
     *
     * @access public
     * @uses \Jellyfish\Response
     */
    public function ajax() {
        // POST campus ID, return programs
        // POST program ID, return campuses
        //echo "\n\n :: AJAX() :: \n";
        check_ajax_referer('rti', 'security');
        $select = '';
        if ($_POST['select'] == 'campus') {
            $metavalue = 'campus_bid';
            $select = 'program';
        } elseif ($_POST['select'] == 'program') {
            $metavalue = 'program_id';
            $select = 'campus';
        }
        if (empty($select)) {
            response()->json(['status' => false, 'reason' => 1]);
        }
        if (!empty($_POST['value'])) {
            $qry = new \WP_Query([
                'post_type' => 'page',
                'meta_key' => $metavalue,
                'meta_value' => $_POST['value'],
                'post_status' => ['publish', 'draft'],
                'cat' => $this->cat[$_POST['select']],
            ]);
            if ($qry->have_posts()) {

            //    echo "\n ajax:: select:: post->value:: qry->post \n"; 
            //    echo "select:: ".$select;

                $row = reset($qry->posts);

            //    echo "\n ajax:: select:: post->value:: qry->post:: row->ID \n"; 
            //    echo $row->ID;

                $result = $this->populate_the_select($select, $_POST['select'], $row->ID);

                if ($result === false) {
                    response()->json(['status' => false, 'reason' => 2]);
                }

            //    echo "if result:";
            //    print_r($result);

                response()->json($result + ['status' => true]);
            }
        } 
        else {
        //    echo "\n:: ajax:: select:: empty Value ::\n"; 
        //    echo $select;

        //    echo "else result:";
        //    print_r($result);
                
            $result = $this->populate_the_select($select, $_POST['select'], null);
            if ($result === false) {
                response()->json(['status' => false, 'reason' => 3]);
            }
            response()->json($result + ['status' => true]);
        }
        response()->json(['status' => false, 'reason' => 10]);
    } // ajax




    /** ***************************************************************************
     * Filters the data (helper functions)
     *
     * @access private
     * @param array $data The current set of items
     * @return array The campus/program values and labels
     */
    private function filter_data_by_status($data) {
        // echo "\n  filter status :: \n";

        foreach ($data as $key => $item) {
            $testKey = (array_key_exists('program',$item))?'program':'campus';
            //echo "\n ::: Key ::: ".$testKey."\n";
            if ( get_post_status($item[$testKey]) == 'publish' ) {
                //echo "\n item :: ".$item[$testKey]." is published  \n";
                $out[] = $item;
            } // if
            else {
                //echo "\n item :: ".$item[$testKey]." is NOT published \n";
            }
        } // foreach

        // echo "\n  filter Out :: \n";
        // print_r($out);

        $data = $out;

        return $data;
    } // filter_data_by_status


    private function filter_programs_by_state($data) {
        if( 
            (stripos($_SERVER['REQUEST_URI'], '-campuses') !== false ) ||
            (
                (stripos($_SERVER['REQUEST_URI'], 'admin-ajax') !== false ) &&
                (stripos($_SERVER['HTTP_REFERER'], '-campuses') !== false ) 
            )
        ) {
            $search = $this->get_location_fron_url();
            $out = [];
            // for each element in the array,

            //echo "\n  filter URL :: ".$search."\n";
            //print_r($data);
            foreach ($data as $item) {
                // for each campus,
                foreach ($item['campuses'] as $c => $campus) {
                    // if 'campus_title_override' contains search,
                    if (stripos($campus['campus_title_override'], $search) === false) {
        //                echo "\n  filter stripos campus:: " .$campus['campus_title_override']."\n";
                        unset($item['campuses'][$c]);
                    } // if
                } // foreach
                if (!empty($item['campuses'])) {
                    $out[] = $item;
                } // if
            } // foreach

        //    echo "\n  filter Out :: \n";
        //    print_r($out);

            $data = $out;
        } // if '-campuses'

        return $data;
    } // filter_programs_by_state
    
 
    private function filter_campus_by_state($data) {
        if( 
            (stripos($_SERVER['REQUEST_URI'], '-campuses') !== false ) ||
            (
                (stripos($_SERVER['REQUEST_URI'], 'admin-ajax') !== false ) &&
                (stripos($_SERVER['HTTP_REFERER'], '-campuses') !== false ) 
            )
        ) {
            $search = $this->get_location_fron_url();
            $out = [];
            // for each element in the array,

            //echo "\n  filter URL :: ".$search."\n";
            //print_r($data);
            foreach ($data as $item) {

                if (strpos($item['campus_title_override'], $search) !== false) {
                    $out[]= $item ;
                } // if

            } // foreach

            $data = $out;
        } // if '-campuses'
        return $data;
    } // filter_campus_by_state


    /**********  Format List Output ************/
    private function build_output($values, $labels) {
        $output = [
            'campus' => [
                'values' => $values['campus'],
                'labels' => $labels['campus']
            ],
            'program' => [
                'values' => $values['program'],
                'labels' => $labels['program']
            ],
        ];

        return $output;
    } //build_output
    
    
    /**
     * Filters the data based off of the url location variable
     *
     * @return string The search string (state)
     */
    private function get_location_fron_url() {
        if(stripos($_SERVER['REQUEST_URI'], '-campuses') !== false ) {
            $testURL = $_SERVER['REQUEST_URI'];
        }
        
        if(     (stripos($_SERVER['REQUEST_URI'], 'admin-ajax') !== false ) &&
                (stripos($_SERVER['HTTP_REFERER'], '-campuses') !== false ) 
        ) {
            $testURL = $_SERVER['HTTP_REFERER'];
            $testURL = parse_url($testURL, PHP_URL_PATH);
        }
        //echo "\n  Test URL :: ".$testURL;
        $trimURL = str_replace('/','', str_replace('-campuses','', $testURL));
        $splitSearch = implode(' ', explode('-',$trimURL));
        $search = ucwords($splitSearch);
        //echo "\n  search URL :: ".$search."\n";
        return $search;
    } // get_location_fron_url


    /**
     * Area of Study additions
     *
     */
    private function filter_by_area_of_study($data, $search) {
        $out = [];
        // for each element in the array,
        foreach ($data as $item) {
            // echo "\n :: apply_area_study ::Item :: ".$search." \n";
            // print_r($item);
                if (strpos($item['area_of_study'], $search) !== false) {
                    $out[] = $item ;
                    //break; // no need to continue, we got the item.
                }
            //print_r($item['campus_title_override']);

        } // foreach   
        $data = $out;
        return $data;
    } // area_of_study


    private function apply_area_study($data, $id) {
        global $values, $labels;
        $tempData = array();
        // needs to be refactored to pull area of study options in dynamially and build the correct data areas with headers inserted.
        //echo "\n :: apply_area_study :: ".$id. "\n";
        //print_r($data);
        
        // easiest way was to track down the field in the database (ACF builting functions are hoopty (technical term))
        $daPost = get_posts( array(
            'post_type'  => 'acf-field',
            'post_excerpt' => 'area_of_study',
            'posts_per_page' => 1
            )
        );        
        //print_r( $daPost );
        
        // get the list of options from the post_content field
        if ( $daPost ) {
            foreach ( $daPost as $daPostItem ) {
                //echo "\n post_content:: ". $daPostItem->post_content ."\n";
                $AOSItem = unserialize( $daPostItem->post_content );
            }
            $AOSOptions = $AOSItem['choices'];
            //print_r($AOSOptions);
        }

        // get the progrmas by choice, in order and add them to new array 
        foreach ( $AOSOptions as $item ) {
            //echo "\n :: AOSOptions as item :: ".$item."\n";
            $tempData = array_merge($tempData, $this->filter_by_area_of_study($data, $item));
            // echo "\n :: tempData :: ". $item . " :: tempdata length :: ". count($tempData) ."\n";
            // print_r($tempData);
        }
        
        //echo "\n :: tempData :: " ."\n";
        //print_r($tempData);

        //build the output options with the otpgroup headers to be returned
        $currGrp = '';
        foreach ($tempData as $item) {
            // echo "\n :: Item :: " ."\n";
            // print_r($item);
            // echo "\n".get_the_title($item['program'])."\n ";

            if(isset($item['area_of_study']) && isset($item['program'])) {
                $itemAOS = $item['area_of_study'];
                //echo "\n :: ItemAOS:: ".$itemAOS ."\n";
        
                // if new optgrp, insert the optgrp element
                if(!($currGrp == $itemAOS)) {
                    // echo "\n :: ".$currGrp ." < currGrup ---- ItemAOS > ". $itemAOS."\n";
                    //echo "\n :: New OptGrouping :: \n" . $itemAOS . "\n";
                    $currGrp = $itemAOS;

                    $values['program'][] = strtoupper(str_replace(' ','',$itemAOS));
                    $labels['program'][] = "-- ".$itemAOS." --";
                }

                // continue to add the current item to the output arrays
                //echo "\n :: New option :: \n" . $item['program'] . "\n";
                $values['program'][]= get_field('program_id', $item['program']);
                $labels['program'][]= (!empty($item['program_title_override']) ? $item['program_title_override'] : get_the_title($item['program']));
            }
        } // foreach

        //echo "\n :: Item :: values['program'] :: " ."\n";
        //print_r($values['program']);

        $output = $this->build_output($values, $labels);

        //echo "\n :: Item :: output :: " ."\n";
        //print_r($output);
        return $output;

    } // apply_area_study





    /** ***************************************************************************
     * Builds out the data array (helper functions)
     *
     * @access private
     * @param array $data The current set of items
     * @param array $id The current selected item
     * @param array $term The type of item
     * @return array The campus/program values and labels
     */
    
    /**********   DEFAULT ACTIONS ************/
    private function build_default_campus_list($data, $id) {
        global $values, $labels;
        //echo "\n build_default_campus_list id::".$id;
        //print_r($data);

        foreach ($data as $item) {
            $values['campus'][] = get_field('campus_bid', $item['campus']);
            $labels['campus'][] = (!empty($item['campus_title_override']) ? $item['campus_title_override'] : get_the_title($item['campus']));
        } // foreach
        
        $output = $this->build_output($values, $labels);
        return $output;
    } // build_default_campus_list


    private function build_default_program_list($data, $id) {
        global $values, $labels;
        //echo "\n build_default_program_list id:: ".$id." \n";
        //print_r($data);

        $output = $this->apply_area_study($data, $id); 
        return $output;
    } // build_default_program_list


    private function build_default_default_list($data, $id) {
        //placeholder
        return;
    } // build_default_default_list
    /**********   // DEFAULTS ************/



    /**********   CAMPUS ************/
    private function  build_campus_program_list($data, $term, $id) { 
        global $values, $labels;
        //echo "\n  build_campus_program_list:: ID:: ".$id."\n";
        //print_r($data);

        //echo "get all the default programs";
        $prgData = get_field('global_programs', GLOBAL_ID);   
       
        if(!isset($id) ) { 
            //echo "get the default programs";
            //$data = get_field('global_programs', GLOBAL_ID);     
            $prgData = $this->filter_data_by_status($prgData);                   
            // filter campus if we're on a campuses page
            $prgData = $this->filter_programs_by_state($prgData);
            $output = $this->build_default_program_list($prgData, $id);
        } // !id
        else {
            foreach ($data as $item) {
                if ($item['campus'] == $id) {
                    foreach ($item['programs'] as $program) {
                        // get the program details
                        $tempProgram['program'] = $program['program'];
                        $tempProgram['value']  = get_field('program_id', $program['program']);
                        $tempProgram['title']  = (!empty($program['program_title_override']) ? $program['program_title_override'] : get_the_title($program['program']));
                        
                        // need this bit to get the area-of-study from the full list for the matching program
                        foreach ($prgData as $fullProgListItem) { // full list item

                            // if curr list item the current program ge tthe area-of-study
                            if($fullProgListItem['program'] == $program['program']) {
                                $tempProgram['area_of_study'] = $fullProgListItem['area_of_study'];
                            } //if
                        } // foreach

                        $tempData[] = $tempProgram;
                    } // foreach
                } // if 
            } // foreach

            $output = $this->apply_area_study($tempData, $id); 
        }    
        return $output;
    } // build_campus_program_list


    private function build_campus_campus_list($data, $term, $id) { 
        global $values, $labels;
        //echo "build_campus_campus_list:: ID:: " .$id ."\n";
        //print_r($data);

        foreach ($data as $item) {
            // Page is a CAMPUS, only get this one
            if ('campus' == $term && null !== $id) {
                if ($item['campus'] == $id) {
                    $values['campus']['primary'] = get_field('campus_bid', $item['campus']);
                    $labels['campus']['primary'] = (!empty($item['campus_title_override']) ? $item['campus_title_override'] : get_the_title($item['campus']));
                    break;
                } // if
            } // if 
            else {
                $this->build_default_campus_list($data, $id);
            }
        } // foreach
        
        $output = $this->build_output($values, $labels);
        return $output;
    } // build_campus_campus_list    
    /**********  //  CAMPUS ************/



    /**********   Programs ************/
    private function build_program_campus_list($data, $term, $id) { 
        global $values, $labels;
        //echo "build_program_campus_list:: ID:: " .$id ."\n";
        //print_r($data);

        if(!isset($id) ) {
            //echo "get the campuses";
            $data = get_field('global_campuses', GLOBAL_ID);     
            $data = $this->filter_data_by_status($data);                   
            // filter campus if we're on a campuses page
            $data = $this->filter_campus_by_state($data);
            $output = $this->build_default_campus_list($data, $id);
        } // !id
        else {
            foreach ($data as $item) {
                if ($item['program'] == $id) {
                    foreach ($item['campuses'] as $campus) {
                        $values['campus'][] = get_field('campus_bid', $campus['campus']);
                        $labels['campus'][] = (!empty($campus['campus_title_override']) ? $campus['campus_title_override'] : get_the_title($campus['campus']));
                    }
                    break;
                } // if 
            } // foreach

            $output = $this->build_output($values, $labels);
        }
        return $output;
    } // build_program_campus_list


    private function  build_program_program_list($data, $term, $id) { 
        global $values, $labels;

        foreach ($data as $item) {
            // Skip programs not belonging to the current parent program
            // when this page is an area-of-study program
            if (isset($child_programs_id) && !in_array($item['program'], $child_programs_id)) {
                continue;
            }

            // Page is a CAMPUS, only get this one
            if ('program' == $term && null !== $id) {
                if ($item['program'] == $id) {
                    $values['program']['primary'] = get_field('program_id', $item['program']);
                    $labels['program']['primary'] = (!empty($item['program_title_override']) ? $item['program_title_override'] : get_the_title($item['program']));
                    break;
                }
            } 
            else {
                $this->build_default_campus_list($data, $id);
            }
        } // foreach
        
        $output = $this->build_output($values, $labels);
        return $output;
    } // build_program_program_list
    /**********   // Programs ************/



    /*************** MCP *****************/
    /**
     * Populates the selects based off of the global list
     *
     * @access private
     * @param string $select The current <select> element
     * @param string $term The term for the current page (program/campus)
     * @param int|null $id The selected ID. If null, reset both select elements
     * @return array The campus/program values and labels
     */
    private function populate_the_select($select, $term = false, $id = null) {
        //echo "\n\n populate_the_select:: \n";
        //echo ":: select | term | id ::\n";
        //echo ":: ".$select .' | '. $term .' | '. $id ." ::\n";

        global $values, $labels;

        $values = ['campus' => [], 'program' => []];
        $labels = ['campus' => [], 'program' => []];

        $allowed_select = ['program', 'campus'];
        if (!in_array($select, $allowed_select)) {
            return false;
        }
        if (!in_array($term, $allowed_select) && $term !== false) {
            return false;
        }
        # If page is Campus
        # - Campus select only has selected campus
        # - Program select has all programs under campus
        #
        # If page is Program
        # - Campus select has all campuses that program belongs to
        # - Program select only has selected program
        #
        # If page is FALSE
        # - Campus select has all campuses
        # - Program select has all programs
        # - 20180510: Adding ability to paredown initial lists by state
        # * JS/AJAX dynamically changes campus/program selects
        # replaced all that janky if/else/foreach with a nested switch and some proper functions


        switch ($term) {
            case "program":
                //echo "get the programs";
                $data = get_field('global_programs', GLOBAL_ID);
                $data = $this->filter_data_by_status($data);
                //filter campus if we're on a campuses page
                $data = $this->filter_programs_by_state($data);
                switch ($select) {
                    case "campus":
                        // echo "\n program - campus \n";
                        $output = $this->build_program_campus_list($data, $term, $id);
                        break;
                    case "program":
                        // echo "\n program - program \n";
                        //Build a list of child programs id for
                        //area-of-study program pages
                        $post_id = get_the_ID() ?: url_to_postid(
                            isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null
                        );
                        $post_categories_slug = array_map(function ($cat) {
                            return $cat->slug;
                        }, get_the_category($post_id));
                        if ($post_categories_slug && in_array('area-of-study', $post_categories_slug)) {
                            $child_programs_id = array_map(function($page) {
                                return $page->ID;
                            }, get_pages(['child_of' => $post_id]));
                        }
                        $output = $this->build_program_program_list($data, $term, $id);
                        break;
                    default:
                        // echo "\n program - default \n";
                        $output = $this->build_default_program_list($data, $id);
                        break;
                }
                break;
            
            case "campus":    
                //echo "get the campuses";
                $data = get_field('global_campuses', GLOBAL_ID); 
                $data = $this->filter_data_by_status($data);                   
                // filter campus if we're on a campuses page
                $data = $this->filter_campus_by_state($data);
                switch ($select) {
                    case "campus":
                        // echo "\n campus - campus \n";
                        $output = $this->build_campus_campus_list($data, $term, $id);
                        break;
                    case "program":
                        // echo "\n campus - program \n";
                        $output = $this->build_campus_program_list($data, $term, $id);
                        //print_r($data);
                        break;
                    default:
                        // echo "\n campus - default \n";
                        $output = $this->build_default_campus_list($data, $id);
                        break;
                }
                break;
        
                //$output = $this->apply_area_study($data, $term, $id); 
            default:
                switch ($select) {
                    case "campus":
                        // echo "\n default - campus \n";
                        // get campuses
                        $data = get_field('global_campuses', GLOBAL_ID);
                        $data = $this->filter_data_by_status($data);
                        // filter campus if we're on a campuses page
                        $data = $this->filter_campus_by_state($data);

                        //echo "\n:: data ::\n";
                        //print_r($data);
                        $output = $this->build_default_campus_list($data, $id);
                        break;
                    case "program":
                        // echo "\n default - program \n";
                        // get programs
                        $data = get_field('global_programs', GLOBAL_ID);  
                        // $data = $this->filter_data_by_status($data);     
                        // filter campus if we're on a campuses page
                        $data = $this->filter_programs_by_state($data);

                        //echo "\n:: data ::\n";
                        //print_r($data);
                        $output = $this->build_default_program_list($data, $id);
                        break;
                    default:
                        // echo "\n default - default \n";
                        $data = ['not sure what goes here yet'];
                        $output = $this->build_default_default_list($data, $id);
                        break;
                }
                break;
        } // switch $term
        //print_r($output);
        return $output;
    } // populate_the_select


    /**
     * Remove the first item of a select when there's only one real option
     *
     * @param  array  &$select_configuration Select options to amend
     * @return void
     */
    protected function remove_first_label_when_only_one_option(array &$select_configuration = []) {
        if (count($select_configuration['raw_values']) === 2
            && false !== ($label_key = array_search('first_as_label', $select_configuration['options']))
        ) {
            $select_configuration['options'][$label_key] = 'required';
            // Remove the label added as the first option
            // on values and labels
            unset($select_configuration['raw_values'][0]);
            unset($select_configuration['values'][0]);
            unset($select_configuration['labels'][0]);
        }
    }
} // Class DynamicSelect
