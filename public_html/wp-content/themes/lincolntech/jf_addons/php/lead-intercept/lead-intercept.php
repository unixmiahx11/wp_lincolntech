<?php
namespace Jellyfish;
/**
  * Intercepts the information from the "Contact Form 7" plugin and inserts into a separate database
  *
  * @package Jellyfish
  * @author  Michael Brose <michael.brose@jellyfish.net>
 */

  class LeadIntercept
  {
    /**
     * @var int $test_mode Test mode
     *                     0: DB insert and email sent
     *                     1: DB insert and email sent to test_email
     *                     2: DB insert and email not sent
     *                     3: No DB insert and email not sent
     * @var string $environment The INI file
     * @var array  $organic_sources Organic sources with matching queries
     */
    protected $test_mode = 0;
    protected $environment = 'wp_lincoln_tech';
    protected $organic_sources = array(
        'www.google' => array('q='),
        'daum.net/' => array('q='),
        'eniro.se/' => array('search_word=', 'hitta:'),
        'naver.com/' => array('query='),
        'yahoo.com/' => array('p='),
        'msn.com/' => array('q='),
        'bing.com/' => array('q='),
        'aol.com/' => array('query=', 'encquery='),
        'lycos.com/' => array('query='),
        'ask.com/' => array('q='),
        'altavista.com/' => array('q='),
        'search.netscape.com/' => array('query='),
        'cnn.com/SEARCH/' => array('query='),
        'about.com/' => array('terms='),
        'mamma.com/' => array('query='),
        'alltheweb.com/' => array('q='),
        'voila.fr/' => array('rdata='),
        'search.virgilio.it/' => array('qs='),
        'baidu.com/' => array('wd='),
        'alice.com/' => array('qs='),
        'yandex.com/' => array('text='),
        'najdi.org.mk/' => array('q='),
        'aol.com/' => array('q='),
        'mamma.com/' => array('query='),
        'seznam.cz/' => array('q='),
        'search.com/' => array('q='),
        'wp.pl/' => array('szukai='),
        'online.onetcenter.org/' => array('qt='),
        'szukacz.pl/' => array('q='),
        'yam.com/' => array('k='),
        'pchome.com/' => array('q='),
        'kvasir.no/' => array('q='),
        'sesam.no/' => array('q='),
        'ozu.es/' => array('q='),
        'terra.com/' => array('query='),
        'mynet.com/' => array('q='),
        'ekolay.net/' => array('q='),
        'rambler.ru/' => array('words='),
    );

    /**
      * Assigns the filter to process the form's posted data
      *
      * @access public
     */
    public function __construct()
    {
        try {
            $this->test_mode = \Ini::get($this->environment.'.leaddb.test_mode');
        } catch (\Exception $e) {
            // No INI setting, disable email/DB insert
            $this->test_mode = 3;
        }
        if ($this->test_mode > 0) {
            add_action('wpcf7_before_send_mail', array($this, 'alterWPCF7Properties'));
        }
        if ($this->test_mode < 3) {
            add_filter('wpcf7_posted_data', array($this, 'processData'));
        }
        add_filter('wpcf7_form_tag', array($this, 'prefill_values'));

        $this->getUtmDataToSession();
        //run utm code to set get to session
    }

    public function array_search_partial($list, $hostString) {
        foreach($list as $index => $string) {
            if (strpos(strtolower($hostString), strtolower($string)) !== FALSE)
                return $index;
        }
    }

    public function getUtmDataToSession()
    {
        /* Device Type Detection */
        $detect = new Mobile_Detect;
        // Find c,m, or t (Computer, Mobile, or Tablet)
        if ($detect->isMobile() && !$detect->isTablet()) {
            $device = 'm';
        } elseif ($detect->isMobile() && $detect->isTablet()) {
            $device = 't'; 
        } else {
            $device = 'c';
        }
        $_SESSION['device_type'] = $device;

        /* Referral session variable from $_SERVER - converting character coding to UTF-8 to avoid any session spoofing  */
        $previousUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (!isset($_SESSION['org_referer'])) {
            $previousUrlUTF8 = mb_convert_encoding($previousUrl, 'UTF-8', 'UTF-8');
            $_SESSION['org_referer'] = htmlentities($previousUrlUTF8, ENT_QUOTES, 'UTF-8');
        }

        // Set all UTM terms value from URL
        $this->setSessionValueFromGet('utm_term');
        $this->setSessionValueFromGet('utm_campaign');
        $this->setSessionValueFromGet('utm_audience', '', 'Prospecting');
        $this->setSessionValueFromGet('typ', 'traffic_type');
        $this->setSessionValueFromGet('utm_source');
        $this->setSessionValueFromGet('utm_medium');        
        $this->setSessionValueFromGet('leadsource');
        $this->setSessionValueFromGet('gclid');
        $this->setSessionValueFromGet('utm_adgroup');


        // Array container for grouping website domains.
        $socialWebsiteList  = array ("00","facebook.com", "twitter.com", "instagram.com", "Snapchat", "linkedIn.com", "plus.Google.com");
        $organicWebsiteList = array ("00","google.com", "yahoo.com", "bing.com", "aol.com");
        $paidWebsiteList    = array ("00","google.com", "yahoo.com", "bing.com");
    

    
        //register referrer url, originating url and capture url to session

        if (!isset($_SESSION['ReferrerURL'])) {
            $_SESSION['ReferrerURL'] = $_SERVER['HTTP_REFERER'];
        } else {
            $_SESSION['ReferrerURL'] = $_SESSION['ReferrerURL'];
        }
        
        if (!isset($_SESSION['OriginatingURL'])) {
            $_SESSION['OriginatingURL'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        } else {
            $_SESSION['OriginatingURL'] = $_SESSION['OriginatingURL'];
        }
        
        if ($_POST){
            $_SESSION['CaptureURL']      = $_POST['captureURL'];
            $_SESSION['UniversalLeadiD'] = $_POST['text-145'];
            $_SESSION['fname']           = $_POST['fname'];
            $_SESSION['lname']           = $_POST['lname'];
            $_SESSION['email']           = $_POST['email'];
            $_SESSION['user-phone']      = $_POST['phone'];
            
        }
        
        // Set UTM medium value from the URL, if available.
        if ($_SESSION['utm_medium'] != 'Not_Defined' || $_SESSION['utm_source'] != 'Not_Defined') {
            $_SESSION['utm_medium'] = $_SESSION['utm_medium'];
            $_SESSION['utm_source'] = $_SESSION['utm_source'];
            
        } else {
            if (empty($_SESSION['ReferrerURL'])) {
                // If no referral information present then it is a Direct request.
                $_SESSION['utm_medium']  = "Direct";
                $_SESSION['utm_source']  = "Direct";
                // If direct traffic referrerurl should be 'Not_Defined'
                $_SESSION['ReferrerURL'] = "Not_Defined";
            } else {

                // Check if Social and Organic referral string matches in defined website list. 
                $socialFlagIndex  = $this->array_search_partial($socialWebsiteList, $_SESSION['ReferrerURL']);
                $organicFlagIndex = $this->array_search_partial($organicWebsiteList, $_SESSION['ReferrerURL']);                      
                        
                if($organicFlagIndex){
                    $_SESSION['utm_medium']  = "Organic Search";
                    $_SESSION['utm_source']  = str_replace(".com","",$organicWebsiteList[$organicFlagIndex]); // remove .com and get only name of the domain.
                }elseif ($socialFlagIndex){
                    $_SESSION['utm_medium']  = "Social";
                    $_SESSION['utm_source']  = str_replace(".com","",$socialWebsiteList[$socialFlagIndex]); // remove .com and get only name of the domain.
                }else{
                    // if not Organic or Social but have a Referral URL then visit coming from a unknown website surce.
                    $_SESSION['utm_medium']  = "Referral";
                    $_SESSION['utm_source']  = "Not_Defined";
                }

            }
        }

        // UTM ad group. If utm ad group isn't present in the url send empty value to sparkroom.
        if ($_SESSION['utm_adgroup'] == "Not_Defined"){$_SESSION['utm_adgroup'] = "";}
    }

    /**
     * Set a $_GET value into the $_SESSION for later use
     * 
     * @param string $getParameter
     * @param string $sessionKey
     * @param string $defaultValue
     * @return mixed
     */
    protected function setSessionValueFromGet($getParameter, $sessionKey = '', $defaultValue = 'Not_Defined')
    {
        if (empty($getParameter)) {
            return false;
        }

        // Default $session value to $getParameter
        if (empty($sessionKey)) {
            $sessionKey = $getParameter;
        }

        if (isset($_GET[$getParameter])) {
            $_SESSION[$sessionKey] = $_GET[$getParameter];
        } elseif (! isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = $defaultValue;
        }
    }

    /**
     * Check if the referer is organic and get its name
     * 
     * @param  string $referer
     * @return object
     */
    protected function getOrganicReferrer($referer = '')
    {
        // No referer then not organic
        if (empty($referer)) {
            return (object) [
                'isOrganic' => false,
                'searchEngine' => '',
            ];
        }

        // Find the matching $referer in $organic_sources
        foreach ($this->organic_sources as $search_engine => $queries) {
            if (strpos($referer, $search_engine) !== false) {
                return (object) [
                    'isOrganic' => true,
                    'searchEngine' => $search_engine,
                ];
            }
        }

        // No $organic_sources matching the $referer
        return (object) [
            'isOrganic' => false,
            'searchEngine' => $referer,
        ];
    }

    /**
      * Determines the form type, encrypts the form data, and inserts a record into the leads database
      *
      * @access public
      * @param array $postedData The array created by WPCF7 which must be returned
      * @return array The untouched $postedData array
     */
    public function processData($originalData)
    {
        /* attribution fields */
        $post_data["CaptureURL"]     = isset($_SESSION['CaptureURL']) ? $_SESSION['CaptureURL'] : '';                                                  // for page url inform from the lead form
        $post_data["ReferrerURL"]    = isset($_SESSION['ReferrerURL']) ? $_SESSION['ReferrerURL'] : '';     // referral info session
        $post_data["OriginatingURL"] = isset($_SESSION['OriginatingURL']) ? $_SESSION['OriginatingURL'] : '';     // originating url        
        $post_data["UTM_MEDIUM"]     = isset($_SESSION['utm_medium']) ? $_SESSION['utm_medium'] : '';       // medium from session
        $post_data["UTM_SOURCE"]     = isset($_SESSION['utm_source']) ? $_SESSION['utm_source'] : '';       // Source from session
        $post_data["CAMPAIGN_ID"]    = isset($_SESSION['utm_campaign']) ? $_SESSION['utm_campaign'] : '';   // Campaign from session
        $post_data["Audience"]       = isset($_SESSION['utm_audience']) ? $_SESSION['utm_audience'] : '';   // Audience from session
        $post_data["UTM_TERM"]       = isset($_SESSION['utm_term']) ? $_SESSION['utm_term'] : '';
        $post_data["SubmissionType"] = 'Request More Info';                                                 // This is a hard-coded information as we have one handler for each type of lead form.
                                                                                                            // in your case it can be a dynamic field to find lead form name/type
        $post_data["TypeOfTraffic"]  = isset($_SESSION['traffic_type']) ? $_SESSION['traffic_type'] : '';
        $post_data["Device"]         = isset($_SESSION['device_type']) ? $_SESSION['device_type'] : '';

        if ($_SESSION['leadsource'] == 'Not_Defined' || $_SESSION['leadsource'] == ''){
            $post_data["CID"]        = "JellyfishMicro";
        } else {
            $post_data["CID"]        = $_SESSION['leadsource'];
        }

        $post_data["gclid"]          = isset($_SESSION['gclid']) ? $_SESSION['gclid'] : '';
        $post_data["utm_adgroup"]    = isset($_SESSION['utm_adgroup']) ? $_SESSION['utm_adgroup'] : '';
        $post_data["UniversalLeadiD"]= isset($_SESSION['UniversalLeadiD']) ? $_SESSION['UniversalLeadiD'] : '';


        $form = wpcf7_get_current_contact_form();
        $type = null;
        $remove = ['_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag', '_wpcf7_container_post'];
        $postedData = $originalData;
        $postedData = array_merge($postedData, $post_data);
        foreach ($remove as $item) {
            unset($postedData[$item]);
        }
        switch ($form->name()) {
            case 'main-form':
                $type = 0;
                $iv = openssl_random_pseudo_bytes(16);
                if (($data = $this->getZipData($postedData['zip'])) !== false) {
                    $_SESSION['zip'] = $data['zip5'];
                    $_SESSION['city'] = $data['city'];
                    $_SESSION['state'] = $data['state'];
                    unset($data['zip5']);
                    $postedData = $postedData + $data;
                }
                // Start: Normalize for LeadIQ
                // Graduation year and education status
                $postedData['education_status'] = '';
                $postedData['year_hsged'] = '';
                if ($postedData['graduation_year'] == '0') {
                    $postedData['education_status'] = 'HS diploma - No';
                } else {
                    $current = date('Y');
                    $text = false;
                    if (preg_match('/\D+/', $postedData['graduation_year']) === 1) {
                        $year = intval(preg_replace('/\D+/', '', $postedData['graduation_year']));
                        $text = true;
                    } else {
                        $year = intval($postedData['graduation_year']);
                    }
                    if ($year >= ($current - 40) && $year <= ($current + 4)) {
                        // Valid year 
                        $postedData['education_status'] = ($text === true ? $postedData['graduation_year'] : 'HS diploma - Yes');
                        #$postedData['year_hsged'] = ($text === false ? $year : '');
                        $postedData['year_hsged'] = $year;
                    }
                }
                // End
                $encrypted_data = openssl_encrypt(
                    json_encode($postedData),
                    \Ini::get($this->environment.'.encryption.method'),
                    \Ini::get($this->environment.'.encryption.password'),
                    false,
                    $iv
                );
                // Create new WPDB instance
                $db = new \wpdb(
                    \Ini::get($this->environment.'.leaddb.username'),
                    \Ini::get($this->environment.'.leaddb.password'),
                    \Ini::get($this->environment.'.leaddb.name'),
                    \Ini::get($this->environment.'.leaddb.host')
                );
                $result = $db->query(
                    $db->prepare(
                        'INSERT INTO `leads` (`data`, `type`, `created_at`, `updated_at`) VALUES (%s, %d, now(), now())',
                        bin2hex($iv).$encrypted_data,
                        $type
                    )
                );
                if ($result === false) {
                    error_log('[LeadsManagement] Error when inserting record');
                } elseif ($result === 0) {
                    error_log('[LeadsManagement] Unable to insert record');
                } else {
                    $id = $db->insert_id;
                    if (!empty($id)) {
                        $_SESSION['lead_id'] = $id;
                    }
                }
                break;
            case 'thank-you-form':
                // Create new WPDB instance
                $db = new \wpdb(
                    \Ini::get($this->environment.'.leaddb.username'),
                    \Ini::get($this->environment.'.leaddb.password'),
                    \Ini::get($this->environment.'.leaddb.name'),
                    \Ini::get($this->environment.'.leaddb.host')
                );
                // If SESSION exists and record found, add to data
                $id = null;
                if (!empty($_SESSION['lead_id'])) {
                    $row = $db->get_row(
                        $db->prepare(
                            'SELECT * FROM `leads` WHERE id = %d',
                            $_SESSION['lead_id']
                        ),
                        ARRAY_A
                    );
                    // Verify a record actually exists
                    if ($row !== null) {
                        $id = $row['id'];
                        $json = openssl_decrypt(
                            substr($row['data'], 32),
                            \Ini::get($this->environment.'.encryption.method'),
                            \Ini::get($this->environment.'.encryption.password'),
                            null,
                            hex2bin(substr($row['data'], 0, 32))
                        );
                        $data = json_decode($json,true);
                        $postedData = $postedData + $data;
                    }
                    // @todo Remove session data
                }
                // Fill in gaps if possible
                if (!empty($postedData['zip']) && (empty($postedData['city']) || empty($postedData['state']))) {
                    if (($data = $this->getZipData($postedData['zip'])) !== false) {
                        if (empty($postedData['city'])) {
                            $postedData['city'] = $data['city'];
                        }
                        if (empty($postedData['state'])) {
                            $postedData['state'] = $data['state'];
                        }
                    }
                }
                // New IV
                $iv = openssl_random_pseudo_bytes(16);
                // Encrypt the data
                $encrypted_data = openssl_encrypt(
                    json_encode($postedData),
                    \Ini::get($this->environment.'.encryption.method'),
                    \Ini::get($this->environment.'.encryption.password'),
                    false,
                    $iv
                );
                // Update record
                if ($id !== null) {
                    $result = $db->query(
                        $db->prepare(
                            'UPDATE `leads` SET `data` = %s, `sent` = 0, `updated_at` = now() WHERE id = %d',
                            bin2hex($iv).$encrypted_data,
                            $id
                        )
                    );
                    if ($result === false) {
                        error_log('[LeadsManagement] Error when updating record');
                    } elseif ($result === 0) {
                        error_log('[LeadsManagement] Unable to update record');
                    }
                } else {
                    // New record
                    $result = $db->query(
                        $db->prepare(
                            'INSERT INTO `leads` (`data`, `type`, `created_at`, `updated_at`) VALUES (%s, 0, now(), now())',
                            bin2hex($iv).$encrypted_data
                        )
                    );
                    if ($result === false) {
                        error_log('[LeadsManagement] Error when inserting secondary record');
                    } elseif ($result === 0) {
                        error_log('[LeadsManagement] Unable to insert secondary record');
                    }
                }

                // Don't reset the session right away, 
                // but only if the form gets validated
                add_filter('wpcf7_validate', function ($validation) use ($db, $id) {
                    return $this->resetUserSession($validation, $db, $id);
                });
                break;
            default:
                return $originalData; // Unknown form, no need to process
        }
        return $originalData;
    }

    /**
     * Reset the user PHP session values if the validation passed
     * 
     * @param  WPCF7_Validation $validation
     * @return WPCF7_Validation the same object passed
     */
    public function resetUserSession($validation, $db, $id)
    {
        if (empty($validation->get_invalid_fields())) {
            // Form has been validated
            // Update the lead as completed both forms
            $queryResult = $db->query(
                $db->prepare(
                    'UPDATE `leads` SET `type` = 1, `sent` = 0, `updated_at` = now() WHERE id = %d',
                    $id
                )
            );
            if ($queryResult === false) {
                error_log('[LeadsManagement] Error when updating record');
            } elseif ($queryResult === 0) {
                error_log('[LeadsManagement] Unable to update record');
            }

            // Reset session variables to avoid the
            // current lead to be overwritten
            session_destroy();
            session_start();
        }

        return $validation;
    }

    /**
      * Alters the WPCF7_ContactForm object prior to sending an email based on the value of 'test_mode'
      *
      * @access public
      * @param array $form The array created by WPCF7 which must be returned
      * @return array The untouched $postedData array
     */
    public function alterWPCF7Properties($form)
    {
        $properties = $form->get_properties();
        unset($properties['form']);
        unset($properties['messages']);
        if ($this->test_mode == 1) {
            // Alter email recipient
            unset($properties['additional_settings']);   
            $new_email = \Ini::get($this->environment.'.leaddb.test_email');
            $properties['mail']['recipient']   = $new_email;
            $properties['mail_2']['recipient'] = $new_email;
            $form->set_properties($properties);
        }
        elseif ($this->test_mode > 1 && !$form->in_demo_mode()) {
            unset($properties['mail']);
            unset($properties['mail_2']);
            // Attempt to replace 'demo_mode' regardless if it exists or not and if it was on/off
            $setting = str_replace('demo_mode','demo_dummy_variable',$properties['additional_settings']);
            // Append to the variable
            $setting .= "\ndemo_mode: on";
            $properties['additional_settings'] = $setting;
            $form->set_properties($properties);
            $form->skip_mail = true;
        }
        return $form;
    }

    /**
     * Prefills specific select elements with session values
     *
     * @access public
     * @param array $tag The CF7 element array
     * @param array The modified CF7 element array with prefilled values
     */
    public function prefill_values($tag)
    {
        // City
        // State
        // ZIP
        // Process only for /thank-you page
        $page = get_page_by_path('thank-you');
        
        if (!empty($page) && $page->ID == get_the_ID() && !empty($_SESSION['zip'])) {
            switch ($tag['name']) {
                case 'city':
                    $tag['values'] = [$_SESSION['city']];
                    $tag['raw_values'] = [$_SESSION['city']];
                    break;
                case 'zip':
                    $tag['values'] = [$_SESSION['zip']];
                    $tag['raw_values'] = [$_SESSION['zip']];
                    break;
                case 'state':
                    foreach ($tag['raw_values'] as $idx => $value) {
                        if (substr($value,-2) == $_SESSION['state']) {
                            $tag['options'][] = 'default:'.($idx+1);
                            break;
                        }
                    }
                    break;
            }
        } elseif ($tag['name'] == 'graduation_year') {
            // US academic year is different than calendar year, HS students graduate between May and June
            // Below we check if the current month is less or equal to June and set the year
            $current_month = date('m');
            $current = date('Y');
            $current = $current_month <= 6 ? $current-1 : $current;                        
            $text_values = $tag['raw_values'];
            for ($i = 2; $i <= 5; $i++) {
                $text_values[$i] = ($current + 6 - $i).' '.$text_values[$i];
            }
            $years = range($current, $current-40);
            $values = array_merge($text_values, $years);
            $tag['raw_values'] = $values;
            $tag['labels'] = $values;
            $values[1] = 0;
            $tag['values'] = $values;
        }
        return $tag;
    }

    /**
     * Retrieves the City/State for a ZIP code from USPS
     *
     * @access protected
     * @param int $zip The inputted ZIP code
     * @return array|boolean Return the array with data from USPS or false if it failed
     */
    protected function getZipData($zip = null)
    {
        if (!empty($zip)) {
            $userid = \Ini::get($this->environment.'.uspsapi.userid');
            $ch = curl_init('http://production.shippingapis.com/ShippingAPITest.dll?API=CityStateLookup&XML='.urlencode('<CityStateLookupRequest USERID="'.$userid.'"><ZipCode ID="0"><Zip5>'.$zip.'</Zip5></ZipCode></CityStateLookupRequest>'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/xml'));
            $xml = curl_exec($ch);
            curl_close($ch);
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $xpath = new \DOMXpath($dom);
            $items = $xpath->query('/CityStateLookupResponse/ZipCode[@ID=\'0\']/child::node()');
            $data = [];
            if (!empty($items)) {
                foreach ($items as $item) {
                    $data[strtolower($item->nodeName)] = $item->nodeValue;
                }
                // No error
                if (!array_key_exists('error', $data) && !empty($data)) {
                    return $data;
                }
            }
        }
        return false;
    }
}
// Make sure the session exists
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}