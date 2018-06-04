<?php
namespace Jellyfish;
/**
  * Processes the leads stored in the leads DB
  *
  * @package Jellyfish
  * @author  Michael Brose
 */
class LeadProcessing {
    /**
     * @var string $log_file The location and name of the log file
     * @var MySQLI|null  $db_resource The database resource (connection)
     * @var float $benchmark_start The starting time of the script
     */
    protected $log_file        = '/var/log/lincolntech-lead-processing.log';
    
    protected $db_resource     = null;
    protected $benchmark_start = null;
    protected $environment     = 'wp_lincoln_tech';
    protected $cli_options     = [
        'long'  => ['send-leads::', 'include-sent::', 'mark-not-sent::', 'start::', 'end::', 'help::', 'test::'],
        'short' => 'h::q::t::',
    ];

    private $start             = null;
    private $startNotCompleted = null;
    private $end               = null;
    private $endNotCompleted   = null;
    private $include_sent      = false;
    private $mark_not_sent     = false;
    private $send_leads        = false;
    private $output            = true;
    private $test_mode         = 'N';

    private $processed_ids     = [];
    
    /**
      * Sets the start time, runs the methods (DB connection and lead processing), and outputs time taken
      *
      * @access public
     */
    public function __construct() {
        $this->benchmark_start = microtime(true);
        require '../public_html/jf-ini.php';
        #$this->log_file = \Ini::get($this->environment.'.mailchimp.log_file');
        $this->prepare_options();
        $this->out('Starting Process');
        $this->connect_to_database();
        $this->mark_not_sent();
        $this->process_leads();
        $this->mark_sent();
        $time = microtime(true);
        $difference = $time - $this->benchmark_start;
        $this->out('Finished process in '.$difference.' seconds');
    }
    private function prepare_options() {
        $options = getopt($this->cli_options['short'], $this->cli_options['long']);
        if (isset($options['help']) || isset($options['h'])) {
            print cl('brown').'Usage:'.cl()."\n";
            print "  php lead_processing.php [options]\n\n";
            print cl('brown').'Options:'.cl()."\n";
            print cl('green').'      --mark-not-sent'.cl()."       Mark all matching leads as unsent\n";
            print cl('green').'      --include-sent'.cl()."        Include leads marked as sent in the export\n";
            print cl('green').'      --send-leads'.cl()."          Export leads to API\n";
            print cl('green').'      --start[=START-DATE]'.cl()."  The start date of the lead range in include\n";
            print cl('green').'      --end[=END-DATE]'.cl()."      The end date of the lead range to include\n";
            print cl('green').'  -t, --test'.cl()."                Enables TEST mode for the export\n";
            print cl('green').'  -h, --help'.cl()."                Display this help message\n";
            print cl('green').'  -q, --quiet'.cl()."               Do not output any message\n";
            print "\n";
            print cl('brown').'Help:'.cl()."\n";
            print "  Export the leads table\n";
            exit;              
        }
        if (empty($options['start'])) {
            $date = new \DateTime();
            $date->modify('-3 days');
            $this->start = $date->format('Y-m-d H:i:s');
            $date->modify('-5 minutes');
            $this->startNotCompleted = $date->format('Y-m-d H:i:s');
        } else {
            $this->start = date('Y-m-d H:i:s', strtotime($options['start']));
            $this->startNotCompleted = $this->start;
        }

        if (empty($options['end'])) {
            $this->end = date('Y-m-d H:i:s');
            $this->endNotCompleted = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        } else {
            $this->end = date('Y-m-d H:i:s', strtotime($options['end']));
            $this->endNotCompleted = $this->end;
        }
        $this->include_sent = (isset($options['include-sent']) ? true : false);
        $this->mark_not_sent = (isset($options['mark-not-sent']) ? true : false);
        $this->send_leads = (isset($options['send-leads']) ? true : false);
        $this->output = (isset($options['quiet']) || isset($options['q']) ? false : true);
        $this->test_mode = (isset($options['test']) || isset($options['t']) ? 'Y' : 'N');

        $this->out('------------');
        $this->out('Date Start: '.$this->start);
        $this->out('Date End: '.$this->end);
        $this->out('Include Sent: '.(int)$this->include_sent);
        $this->out('Mark Not Sent: '.(int)$this->mark_not_sent);
        $this->out('Send Leads: '.(int)$this->send_leads);
        $this->out('------------');
    }

    private function mark_not_sent() {
        if ($this->mark_not_sent) {
            $sql = sprintf('UPDATE leads SET sent = 0 WHERE (updated_at BETWEEN \'%s\' AND \'%s\')', $this->start, $this->end);
            if (!$query_result = $this->db_resource->query($sql)) {
                // Log error message
               $this->out('Query failed: '.$this->db_resource->error);
               exit;
            }
        }
    }

    private function mark_sent() {
        if (!empty($this->processed_ids)) {
            $ids = implode(',',$this->processed_ids);
            $sql = sprintf('UPDATE leads SET sent = 1 WHERE id in (%s)', $ids);
            if (!$query_result = $this->db_resource->query($sql)) {
                // Log error message
                $this->out('Query failed: '.$this->db_resource->error);
                exit;
            }
        }
    }
    
    /**
      * Connects to the MySQL database defined in /etc/jellyfish/wp_casepoint.ini
      *
      * @access private
     */
    private function connect_to_database() {
        $this->db_resource = new \mysqli(
            \Ini::get($this->environment.'.leaddb.host'),
            \Ini::get($this->environment.'.leaddb.username'),
            \Ini::get($this->environment.'.leaddb.password'),
            \Ini::get($this->environment.'.leaddb.name')
        );
        if ($this->db_resource->connect_errno) {
            // Log error message
            $this->out('Connection failed: '.$this->db_resource->connect_error);
            exit;
        }
    }
    
    /**
      * Fetches the newsletter leads, sends a request to the MailChimp API, and returns the result
      *
      * @access private
     */
    private function process_leads() {
        $sql = preg_replace('/\s+/', ' ', sprintf(
            "SELECT id, data, updated_at FROM leads 
            WHERE sent in (%s) 
            AND (
                (type = 1 AND updated_at BETWEEN '%s' AND '%s')
                OR (type = 0 AND updated_at BETWEEN '%s' AND '%s')
            )",
            '0' . ($this->include_sent ? ',1' : ''),
            $this->start, $this->end,
            $this->startNotCompleted, $this->endNotCompleted
        ));
        if (!$query_result = $this->db_resource->query($sql)) {
            // Log error message
            $this->out('Query failed: '.$this->db_resource->error);
            exit;
        }

        if ($query_result->num_rows > 0) {
            $records = $query_result->num_rows;
            $processed = 0;
            $default = [
                #'CID' => 'JellyfishTEST',
                'CID' => '',
                'LeadBuyerID' => 783,
                #'VendorLeadID' => '',
                #'SubAffID' => '', // Google, Bing, Yahoo
                #'inquiryIPAddress' => '',
                #'UniverasalLeadiD' => '',
                'BID' => '',
                'programs' => '',
                'Email' => '',
                'FirstName' => '',
                #'MiddleName' => '',
                'LastName' => '',
                'Address' => '',
                #'Address2' => '',
                #'Address3' => '',
                'City' => '',
                'State' => '',
                'Country' => 'US',
                'Zip' => '',
                'DaytimePhone' => '',
                'isCellPhone' => 'No',
                #'EveningPhone' => '',
                #'WorkPhone' => '',
                'CellPhone' => '',
                'YearHSGED' => '',
                'EducationStatus' => '',
                'TCPAExpressConsent' => 'No',
                #'CSRC' => '',

                /* attribution fields */
                'CaptureURL' => '',
                'ReferrerURL' => '',
                'OriginatingURL' => '',
                'UTM_MEDIUM' => '',
                'UTM_SOURCE' => '',
                'CAMPAIGN_ID' => '',
                'Audience' => '',
                'UTM_TERM' => '',
                'SubmissionType' => '', 
                'TypeOfTraffic' => '', 
                'Device' => '',
                'gclid'  => '',
                'UniversalLeadiD' => '',
            ];
            if ($this->test_mode != 'N') {
                $default['Test'] = $this->test_mode;
            }
            
            // Process records
            $error = false;
            while ($row = $query_result->fetch_assoc()) {
                $error = false;
                $data = openssl_decrypt(
                    substr($row['data'], 32),
                    \Ini::get($this->environment.'.encryption.method'),
                    \Ini::get($this->environment.'.encryption.password'),
                    null,
                    hex2bin(substr($row['data'], 0, 32))
                );
                $data = [$row['updated_at']] + json_decode($data,true);
                // Normalize data_array
                $data_array = $default;
                $data_array['BID'] = (!empty($data['campus']) ? $data['campus'] : '');
                $data_array['programs'] = (!empty($data['program']) ? $data['program'] : '');
                $data_array['Email'] = (!empty($data['email']) ? $data['email'] : '');
                $data_array['FirstName'] = (!empty($data['fname']) ? $data['fname'] : '');
                $data_array['LastName'] = (!empty($data['lname']) ? $data['lname'] : '');
                $data_array['Address'] = (!empty($data['address']) ? $data['address'] : '');
                $data_array['City'] = (!empty($data['city']) ? $data['city'] : '');
                $data_array['State'] = (!empty($data['state']) ? $data['state'] : '');
                $data_array['Zip'] = (!empty($data['zip']) ? $data['zip'] : '');
                $phone = preg_replace('/\D+/', '', (!empty($data['phone']) ? $data['phone'] : ''));
                if (strlen($phone) == 10) {
                    // Valid 10 digits
                    $data_array['DaytimePhone'] = $phone;
                    if ($data['is_cell'][0] == 'Yes') {
                        $data_array['isCellPhone'] = 'Yes';
                        $data_array['CellPhone'] = $phone;
                    }
                }
                $data_array['YearHSGED'] = (!empty($data['year_hsged']) ? $data['year_hsged'] : '');
                $data_array['EducationStatus'] = (!empty($data['education_status']) ? $data['education_status'] : '');
                if (!empty($data['toc'][0])) {
                    $data_array['TCPAExpressConsent'] = 'Yes';
                }

                /* attribution fields */
                $data_array['CaptureURL']       = (!empty($data['captureURL']) ? $data['captureURL'] : '');
                $data_array['ReferrerURL']      = (!empty($data['ReferrerURL']) ? $data['ReferrerURL'] : '');
                $data_array['OriginatingURL']   = (!empty($data['OriginatingURL']) ? $data['OriginatingURL'] : '');
                $data_array['UTM_MEDIUM']       = (!empty($data['UTM_MEDIUM']) ? $data['UTM_MEDIUM'] : '');
                $data_array['UTM_SOURCE']       = (!empty($data['UTM_SOURCE']) ? $data['UTM_SOURCE'] : '');
                $data_array['CAMPAIGN_ID']      = (!empty($data['CAMPAIGN_ID']) ? $data['CAMPAIGN_ID'] : '');
                $data_array['Audience']         = (!empty($data['Audience']) ? $data['Audience'] : '');
                $data_array['UTM_TERM']         = (!empty($data['UTM_TERM']) ? $data['UTM_TERM'] : '');
                $data_array['SubmissionType']   = (!empty($data['SubmissionType']) ? $data['SubmissionType'] : '');
                $data_array['TypeOfTraffic']    = (!empty($data['TypeOfTraffic']) ? $data['TypeOfTraffic'] : '');
                $data_array['Device']           = (!empty($data['Device']) ? $data['Device'] : '');
                $data_array['CID']              = (!empty($data['CID']) ? $data['CID'] : '');
                $data_array['gclid']            = (!empty($data['gclid']) ? $data['gclid'] : '');
                $data_array['utm_adgroup']      = (!empty($data['utm_adgroup']) ? $data['utm_adgroup'] : '');
                $data_array['UniversalLeadiD']  = (!empty($data['UniversalLeadiD']) ? $data['UniversalLeadiD'] : '');

                if (!$this->send_leads) {
                    $this->out(json_encode($data_array));
                } else {
                    # https://leadiq.sparkroom.com/Sparkroom/submitLead/783/leadCapture?JsonPostData=Y
                    $result = $this->leadiq_curl_connect('https://leadiq.sparkroom.com/Sparkroom/postLead', $data_array);
                    // For debugging purposes
                    file_put_contents($this->log_file, $result, FILE_APPEND);
                    file_put_contents($this->log_file, $data_array, FILE_APPEND);
                    #$result = $this->leadiq_curl_connect('https://leadiq.sparkroom.com/Sparkroom/submitLead/783/leadCapture?JsonPostData=Y', $data_array);
                    /*
                    $result = '<?xml version="1.0" encoding="UTF-8"?><SPARKROOM_RESPONSE><RESULT>FAIL</RESULT><STATUS_CODE>SR-400</STATUS_CODE><MESSAGE>Lead delivery routing failure. Lead buyer or interface reference could not be resolved. Check values in post parameters. (Lead Capture Log ID: 58194892)</MESSAGE></SPARKROOM_RESPONSE>';
                    */
                    try {
                        $xml = new \SimpleXMLElement($result);
                        if ($xml->RESULT == 'FAIL') {
                            $error = true;
                            $this->out('Error processing ID '.$row['id'].'. '.$xml->STATUS_CODE.': '.$xml->MESSAGE);
                        } elseif ($xml->RESULT == 'REJECT') {
                            $this->out('Rejected ID '.$row['id'].'. '.$xml->STATUS_CODE.': '.$xml->MESSAGE);
                        } else {
                            $this->out($result);
                        }
                    } catch (\Exception $e) {
                        $error = true;
                        $this->out(var_export($result, true));
                    }
                }
                if (!$error) {
                    $this->processed_ids[] = $row['id'];
                    $processed++;
                }
            }
            $this->out('Finished processing '.$processed.'/'.$records.' records');
        } else {
            $this->out('No records to process');
        }
    }

    private function leadiq_curl_connect($url, $data = array()) {
        $conn = curl_init();
        /*$headers = array(
            'Content-Type: application/json',
            #'Authorization: Basic '.base64_encode( 'user:'. $this->api_key )
            'Content-Length: '.strlen($json),
        );*/
        curl_setopt($conn, CURLOPT_URL, $url );
        #curl_setopt($conn, CURLOPT_HTTPHEADER, 0);
        curl_setopt($conn, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, true); // do not echo the result, write it into variable
        curl_setopt($conn, CURLOPT_TIMEOUT, 10);
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false); // certificate verification for TLS/SSL connection
     
        #curl_setopt($conn, CURLOPT_POSTFIELDS, $json); // send data in json
        curl_setopt($conn, CURLOPT_POSTFIELDS, http_build_query($data));
     
        $return = curl_exec($conn);
        curl_close($conn);
        return $return;
    }
    
    /**
      * Outputs the $string
      *
      * @access private
      * @param string $string The string to be written to the log file with a timestamp
     */
    private function out($string) {
        $out = '['.date('Y-m-d H:i:s.u').'] '.$string."\n";
        #file_put_contents($this->log_file, $out, FILE_APPEND);
        if ($this->output === true) {
            echo $out;
        }
    }
}

$process = new \Jellyfish\LeadProcessing();

function cl($color = 'reset') {
    $c['reset'] = '0';
    $c['black'] = '0;30';
    $c['dark_gray'] = '1;30';
    $c['blue'] = '0;34';
    $c['light_blue'] = '1;34';
    $c['green'] = '0;32';
    $c['light_green'] = '1;32';
    $c['cyan'] = '0;36';
    $c['light_cyan'] = '1;36';
    $c['red'] = '0;31';
    $c['light_red'] = '1;31';
    $c['purple'] = '0;35';
    $c['light_purple'] = '1;35';
    $c['brown'] = '0;33';
    $c['yellow'] = '1;33';
    $c['light_gray'] = '0;37';
    $c['white'] = '1;37';
    return "\033[".$c[$color].'m';
} 