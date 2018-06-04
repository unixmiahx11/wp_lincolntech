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
    /**
      * Adds filters, actions, and includes a class
      *
      * @access public
     */
    public function __construct()
    {
        include('Response.php');
        add_action('wp_enqueue_scripts', array($this, 'add_scripts'), 11);
        add_action('wp_ajax_populate_selects', array($this, 'ajax'));
        add_action('wp_ajax_nopriv_populate_selects', array($this, 'ajax'));
        add_filter('wpcf7_form_tag', array($this, 'populate_selects'));
    }

    /**
     * Adds two variables to the Javascript variable "ajax_object"
     *
     * @access public
     */
    public function add_scripts()
    {
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

            echo "\n\n\n\n::Start::\n";
            echo "populate_selects:: cats::\n";
            print_r($categories);

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

            echo "\n\ntest-2:: \n";
            echo "populate_selects:: data::\n";
            print_r($data);

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

                echo "\najax:: select:: post->value:: qry->post \n"; 
                echo "select:: ".$select;

                $row = reset($qry->posts);

                echo "\najax:: select:: post->value:: qry->post:: row->ID \n"; 
                echo $row->ID;

                $result = $this->populate_the_select($select, $_POST['select'], $row->ID);

                if ($result === false) {
                    response()->json(['status' => false, 'reason' => 2]);
                }

                echo "if result:";
                print_r($result);

                response()->json($result + ['status' => true]);
            }
        } 
        else {
            echo "\najax:: select:: empty Value \n"; 
            echo $select;

                echo "else result:";
                print_r($result);
                
            $result = $this->populate_the_select($select, $_POST['select'], null);
            if ($result === false) {
                response()->json(['status' => false, 'reason' => 3]);
            }
            response()->json($result + ['status' => true]);
        }
        response()->json(['status' => false, 'reason' => 10]);
    } // ajax



    /**
     * Populates the selects based off of the global list
     *
     * @access private
     * @param string $select The current <select> element
     * @param string $term The term for the current page (program/campus)
     * @param int|null $id The selected ID. If null, reset both select elements
     * @return array The campus/program values and labels
     */
    private function populate_the_select($select, $term = false, $id = null)
    {
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
        $values = ['campus' => [], 'program' => []];
        $labels = ['campus' => [], 'program' => []];
        
        // Select is CAMPUS
        if ('campus' == $select || null === $id) {
            // Page is a PROGRAM
            if ('program' == $term && null !== $id) {
                $data = get_field('global_programs', GLOBAL_ID);
                
                //if ($_SERVER['REQUEST_URI'] == "/new-jersey-campuses/") {
                    $search = "New Jersey";
                    $out = [];
                    // for each element in the array,
                    foreach ($data as $index => $item) {
                        // for each campuses,
                        foreach ($item['campuses'] as $c => $campus) {
                            // if 'campus_title_override' contains search,
                            if (strpos($campus['campus_title_override'], $search) === false) {
                                unset($item['campuses'][$c]);
                            }
                        }
                        if (!empty($item['campuses'])) {
                           $out[] = $item;
                        }
                    }
                        //print_r($out);

                        $data = $out;
                //}

                echo "\npopulate_the_select:: campus:: program:: Data:: \n"; 
                print_r ($data);

                foreach ($data as $item) {
                    if ($item['program'] == $id) {
                        echo "item:program:: ";
                        echo $item['program'];
                        echo "---";
                        foreach ($item['campuses'] as $campus) {

                            $values['campus'][] = get_field('campus_bid', $campus['campus']);
                            $labels['campus'][] = (!empty($campus['campus_title_override']) ? $campus['campus_title_override'] : get_the_title($campus['campus']));
                        }
                        break;
                    } // if 
                    else {
                        echo "item:program\n";
                        echo $item['program'];
                        echo "---\n";
                       
                        $values['campus'][] = get_field('campus_bid', $item['campus']);
                        $labels['campus'][] = (!empty($item['campus_title_override']) ? $item['campus_title_override'] : get_the_title($item['campus']));
                        //print_r($labels['campus']);
                        //print_r($values['campus']);
                    }
                } // foreach
            } // if
            // Page is a CAMPUS or not PROGRAM
            else {
                $data = get_field('global_campuses', GLOBAL_ID);

                if ($_SERVER['REQUEST_URI'] == "/new-jersey-campuses/"){
                    $search = "New Jersey";
                    $out = [];
                    // for each element in the array,
                    foreach ($data as $index => $item) {
                    // for each campuses,
                        //foreach ($item['campuses'] as $campus) {
                            // if 'campus_title_override' contains search,
                            if (strpos($item['campus_title_override'], $search) !== false) {
                                $out[] = $item ;
                                //break; // no need to continue, we got the item.
                            }
                        //}
                        //print_r($item['campus_title_override']);

                    }
                        //print_r($out);

                        $data = $out;
                }
                
                echo "\npopulate_the_select:: campus:: Data:: \n"; 
                print_r ($data);

                foreach ($data as $item) {
                    // Page is a CAMPUS, only get this one
                    if ('campus' == $term && null !== $id) {
                        echo "item:campus";
                        echo $item['campus'];
                        echo "---";
                        if ($item['campus'] == $id) {
                            $values['campus']['primary'] = get_field('campus_bid', $item['campus']);
                            $labels['campus']['primary'] = (!empty($item['campus_title_override']) ? $item['campus_title_override'] : get_the_title($item['campus']));
                            break;
                        }
                    } // if 
                    else {
                        echo "item:campus";
                        echo $item['campus'];
                        echo "---";
                       
                        $values['campus'][] = get_field('campus_bid', $item['campus']);
                        $labels['campus'][] = (!empty($item['campus_title_override']) ? $item['campus_title_override'] : get_the_title($item['campus']));
                        //print_r($labels['campus']);
                        //print_r($values['campus']);
                    }
                }
            }
        } // if select == campus

        // Select is PROGRAM
        if ('program' == $select || null === $id) {
            // Page is a CAMPUS
            if ('campus' == $term && null !== $id) {
                $data = get_field('global_campuses', GLOBAL_ID);

                $search = "New Jersey";
                $out = [];
                // for each element in the array,
                foreach ($data as $index => $item) {
                // for each campuses,
                    //foreach ($item['campuses'] as $campus) {
                        // if 'campus_title_override' contains search,
                        if (strpos($item['campus_title_override'], $search) !== false) {
                            $out[] = $item ;
                            //break; // no need to continue, we got the item.
                        }
                    //}
                    //print_r($item['campus_title_override']);

                }
                //print_r($out);
                $data = $out;       
                         
                echo "\npopulate_the_select:: select program:: page campus:: Data:: \n"; 
                print_r ($data);
                

                foreach ($data as $item) {
                    if ($item['campus'] == $id) {
                        foreach ($item['programs'] as $program) {
                            $values['program'][] = get_field('program_id', $program['program']);
                            $labels['program'][] = (!empty($program['program_title_override']) ? $program['program_title_override'] : get_the_title($program['program']));
                        }
                        break;
                    } 
                }
            } // if page campus
            // Page is a PROGRAM or not CAMPUS
            else {

                $data = get_field('global_programs', GLOBAL_ID);

                if ($_SERVER['REQUEST_URI'] == "/new-jersey-campuses/"){
                    $search = "New Jersey";
                    $out = [];
                    // for each element in the array,
                    foreach ($data as $index => $item) {
                        // for each campuses,
                        foreach ($item['campuses'] as $c => $campus) {
                            // if 'campus_title_override' contains search,
                            if (strpos($campus['campus_title_override'], $search) === false) {
                                unset($item['campuses'][$c]);
                            }
                        }
                        if (!empty($item['campuses'])) {
                           $out[] = $item;
                        }
                    }
                    //print_r($out);

                    $data = $out;
                
                    echo "\npopulate_the_select:: select program:: page program:: Data:: \n"; 
                    print_r ($data);
                } // iff NJ

                // lloking good wo0T

                // Build a list of child programs id for
                // area-of-study program pages
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
                        echo "\npopulate_the_select:: select program:: page program:: Item:: \n"; 
                        print_r ($item);
                        $values['program'][] = get_field('program_id', $item['program']);
                        $labels['program'][] = (!empty($item['program_title_override']) ? $item['program_title_override'] : get_the_title($item['program']));
                    }
                } // foreach
            } // else page program
        } // select program
        
        return [
            'campus' => [
                'values' => $values['campus'],
                'labels' => $labels['campus']
            ],
            'program' => [
                'values' => $values['program'],
                'labels' => $labels['program']
            ],
        ];
    } // populate_the_select

    /**
     * Remove the first item of a select when there's only one real option
     *
     * @param  array  &$select_configuration Select options to amend
     * @return void
     */
    protected function remove_first_label_when_only_one_option(array &$select_configuration = [])
    {
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
}
