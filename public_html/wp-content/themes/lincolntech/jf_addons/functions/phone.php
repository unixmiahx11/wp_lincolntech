<?php
/**
  * Allows the usage of a dynamic phone number from multiple sources
  *
  * @author  Michael Brose <michael.brose@jellyfish.net>
 */
class Phone
{
    public static $data = false;

    /**
     * Initialize function that adds an action to find the phone source
     *
     * @access public
     */
    public static function init()
    {
        add_action('init', array( get_called_class(), 'phoneSource' ));
    }

    /**
     * Finds the paid search and organic search phone numbers and sends them to the validate function
     *
     * @access public
     * @param string $phone (Optional) The phone number to validate
     */
    public static function phoneSource($phoneDefault = null)
    {
        // Handle $_REQUEST['comm'] (Paid Search)
        $phone = empty($_REQUEST['comm']) ? false : $_REQUEST['comm'];
        if ($phone) {
            return self::validateNumber($phone);
        }

        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
        if ($referrer) {
            // Handle organic search results
            $url = parse_url($referrer);
            $referrers = get_field('organic_referrers', GLOBAL_ID);
            foreach ($referrers as $item) {
                if (strpos($url['host'], $item['referrer']) !== false) {
                    $phone = $item[$phoneFields[self::isMobile() ? 'mobile_phone_number' : 'phone_number']];
                    break;
                }
            }
        }

        // Fallback to the $phoneDefault value
        // when no organic search matches
        if (!$phone && !empty($phoneDefault)) {
            return self::validateNumber($phoneDefault);
        }

        return self::validateNumber($phone);
    }
    /**
     * Validates an inputted phone number either passed to the function through
     * the first parameter or through the $_REQUEST['comm']
     *
     * @access public
     * @param string|null $phone The inputted phone number
     * @return array|boolean On success, the phone number. Otherwise false
     */
    public static function validateNumber($phone = null)
    {
        if (!empty($phone)) {
            // Replace any non-digit character
            $phone = preg_replace('/\D+/', '', $phone);
            if (10 == strlen($phone)) {
                try {
                    // Format the number to 000-000-0000
                    $phone = preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $phone);
                    $domain = '';
                    if (!empty($_SERVER['HTTP_HOST'])) {
                        $domain = $_SERVER['HTTP_HOST'];
                    } else {
                        $url = site_url();
                        $parse = parse_url($url);
                        $domain = $parse['host'];
                    }
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    // Set the phone (ph) number (nb) cookie for a year
                    @setcookie('phnb', $phone, time() + (86400 * 365), '/', $domain);
                    $_SESSION['phone'] = $phone;
                    self::$data = $phone;
                    return $phone;
                } catch (\Exception $e) {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Retrieves the phone number from a hierarchy of sources
     *
     * @access public
     * @return string|boolean The phone number or false
     */
    public static function get()
    {
        // Internal class variable
        if (self::$data !== false) {
			return self::$data;
		}
        // PHP Session
        if (!empty($_SESSION['phone'])) {
            return $_SESSION['phone'];
        }
        // Cookie
        if (isset($_COOKIE['phnb'])) {
            return self::validateNumber($_COOKIE['phnb']);
        }
        // No stored number
        return false;
    }

    /**
     * Determines if the User Agent is a mobile device or not
     *
     * @see http://detectmobilebrowsers.com/download/php
     * @return boolean True if mobile, false if not
     */
    public static function isMobile() {
        $useragent=$_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
            return true;
        }
        return false;
    }

}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
Phone::init();