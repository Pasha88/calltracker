<?php

require_once dirname(__DIR__).'/../../../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

require_once dirname(__DIR__). '/AppConfig.php';
require_once(dirname(__DIR__) . '/util/Util.php');
require_once(dirname(__DIR__) . '/repo/AppPropertyRepo.php');

class Util {
	
	const COMMON_DATE_TIME_FORMAT = "Y-m-d H:i:s";
	const COMMON_DATE_TIME_W_MILLIS_FORMAT = "Y-m-d H:i:s.v";   
	const SAFE_DATE_TIME_FORMAT = "Y-m-d_His";

	const THE_FIRST_DATE_STRING = "1970-01-01 00:00:01";	
	
	public static function getCurrentDate(){
		return new DateTime("now", self::getMoscowTz());			
	}

    public static function getCurrentServerDate(){
        return new DateTime("now", self::getServerTz());
    }

    public static function getCurrentServerDateFormatted()    {
        $dateNow = new DateTime("now", self::getServerTz());
        return $dateNow->format(self::COMMON_DATE_TIME_FORMAT);
    }

	public static function getCurrentDateFormatted(){
		$dateNow = new DateTime("now", self::getMoscowTz());
		return $dateNow->format(self::COMMON_DATE_TIME_FORMAT);
	}

    public static function getCurrentDateSafeFormatted(){
        $dateNow = new DateTime("now", self::getMoscowTz());
        return $dateNow->format(self::SAFE_DATE_TIME_FORMAT);
    }
	
	public static function getMoscowTz(){
		return new DateTimeZone("Europe/Moscow");
	}

    public static function getServerTz(){
	    $a = AppPropertyRepo::getInstance();
        return new DateTimeZone($a->read('SERVER_TIMEZONE'));
    }

	public static function getTzOffsetFromUTC_Sec($tz, $dateTimeVar) {
		return $tz->getOffset($dateTimeVar); 
	}

	public static function convertToCustomerTz($inputDate, $offsetHour) {
        $dt = $inputDate;
        if(gettype($inputDate) == "string") {
            $dt = new DateTime($inputDate, self::getServerTz());
        }
        $serverOffset = ($dt->getOffset())/3600;
        $delta = $offsetHour - $serverOffset;
        $dt = $dt->add(new DateInterval('PT' . $delta*3600 . 'S'));
        return $dt;
    }

    public static function formatDate($dt) {
        return $dt->format(self::COMMON_DATE_TIME_FORMAT);
    }

    public static function createCommonDate($dtString) {
        return DateTime::createFromFormat(self::COMMON_DATE_TIME_FORMAT, $dtString, self::getServerTz());
    }

    public static function createCommonWMillisDate($dtString) {
        return DateTime::createFromFormat(self::COMMON_DATE_TIME_W_MILLIS_FORMAT, $dtString, self::getServerTz());
    }

    public static function create01011970Date() {
        return DateTime::createFromFormat(self::COMMON_DATE_TIME_FORMAT, "1970-01-01 00:00:01", self::getServerTz());
    }

    public static function setZeroTime($dt) {
        $dt->setTime(0,0);
        return $dt;
    }	public static function uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

	public static function normErrMsg($str) {
        return mb_convert_encoding($str, 'UTF-8', 'UTF-16BE');
    }
	
	public static function createUserError($msgString, $paramArray) {
		$obj = new stdClass();
		$obj->msg = $msgString;
		
		for($i=0; $i<count($paramArray); $i++) {
			$obj->params[$paramArray[$i][0]] = $paramArray[$i][1];
		}
		
		return json_encode($obj);
	}

	public static function __autoload($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		// $fileName .= $className . '.php'; //sometimes you need a custom structure
		//require_once "library/class.php"; //or include a class manually
		require $fileName;
	}
	
	public static function convertUnicodeCodepoint($string) {
		return html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $string), ENT_NOQUOTES, 'UTF-8');
	}

    public static function sendEmail($from, $to, $subject, $body, $attachments, $replyTo, $replyToString)    {
        $propRepo = AppPropertyRepo::getInstance();

        $mail=new PHPMailer();
        $mail->SMTPAutoTLS = false;

        $mail->CharSet = 'UTF-8';
        $mail->Encoding    = '8bit';
        $mail->IsSMTP();
        $mail->IsHTML(true);
        $mail->Host       = $propRepo->read('MAIL_SMTP_HOST');
        $mail->Port       = $propRepo->read('MAIL_SMTP_PORT');
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = false;
        $mail->Username   = $propRepo->read('MAIL_SMTP_HOST');
        $mail->Password   = $propRepo->read('MAIL_SMTP_PWD');
        $mail->SMTPDebug  = 0;
        $mail->SetFrom($from);
        $mail->AddAddress($to);
        $mail->Subject    = $subject;
        $mail->Body = $body;

        $ind_f = 0;
        $tmp_files = array();
        if($attachments != null) {
            foreach($attachments as $attachment) {
                $tmp_files[$ind_f] = tmpfile();;
                $metaDatas = stream_get_meta_data($tmp_files[$ind_f]);
                $tmpFilename = $metaDatas['uri'];
                fwrite($tmp_files[$ind_f], $attachment['content']);
                $mail->AddAttachment($tmpFilename, $attachment['name']);
                $ind_f++;
            }
        }

        if($replyTo != null) {
            $mail->AddReplyTo($replyTo, $replyToString);
            $mail->FromName = $replyToString;
        }
        else {
            $mail->AddReplyTo($propRepo->read('MAIL_SMTP_USER'));
        }

        if(!$mail->send()) {
            foreach($tmp_files as $tmp_f) {
                fclose($tmp_f);
            }
            error_log(Util::normErrMsg($mail->ErrorInfo));
            throw new Exception('Ошибка отправки почтового сообщения: ' . $mail->ErrorInfo);
        }

        foreach($tmp_files as $tmp_f) {
            fclose($tmp_f);
        }

        return true;
    }

    public static function extractDomain($domain) {
        $i = strrpos($domain, ".", -1);
        if($i===false) {
            $j = strrpos($domain, "/", -1);
            if($j===false) {
                return $domain;
            }
            else {
                return substr($domain, $j+1);
            }
        }
        $lastDotOffset = $i-strlen($domain)-1;
        $j = strrpos($domain, ".", $lastDotOffset);
        if($j === false) {
            $j = strrpos($domain, "/", $lastDotOffset);
        }
        $i = $j === false ?  0 : $j + 1;
        return substr($domain, $i);
    }

    public static function get_client_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = gethostbyname(AppConfig::APP_HOST);
        return $ipaddress;
    }

    public static function get_client_ip_address() {
        // check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && Util::validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        // check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // check if multiple ips exist in var
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($iplist as $ip) {
                    if (Util::validate_ip($ip))
                        return $ip;
                }
            } else {
                if (Util::validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED']) && Util::validate_ip($_SERVER['HTTP_X_FORWARDED']))
            return $_SERVER['HTTP_X_FORWARDED'];
        if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && Util::validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && Util::validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
            return $_SERVER['HTTP_FORWARDED_FOR'];
        if (!empty($_SERVER['HTTP_FORWARDED']) && Util::validate_ip($_SERVER['HTTP_FORWARDED']))
            return $_SERVER['HTTP_FORWARDED'];
        if (!empty($_SERVER['REMOTE_ADDR']) && Util::validate_ip($_SERVER['REMOTE_ADDR']))
            return $_SERVER['REMOTE_ADDR'];
        // return unreliable ip since all else failed

        if (!empty($_SERVER['SERVER_NAME']) && gethostbyname($_SERVER['SERVER_NAME'])!= false && Util::validate_ip( gethostbyname($_SERVER['SERVER_NAME']) ))
            return gethostbyname($_SERVER['SERVER_NAME']);

        return gethostbyname(AppConfig::APP_HOST);
    }

    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     */
    public static function validate_ip($ip) {
        if (strtolower($ip) === 'unknown')
            return false;

        // generate ipv4 network address
        $ip = ip2long($ip);

        // if the ip is set and not equivalent to 255.255.255.255
        if ($ip !== false && $ip !== -1) {
            // make sure to get unsigned long representation of ip
            // due to discrepancies between 32 and 64 bit OSes and
            // signed numbers (ints default to signed in PHP)
            $ip = sprintf('%u', $ip);
            // do private network range checking
            if ($ip >= 0 && $ip <= 50331647) return false;
            if ($ip >= 167772160 && $ip <= 184549375) return false;
            if ($ip >= 2130706432 && $ip <= 2147483647) return false;
            if ($ip >= 2851995648 && $ip <= 2852061183) return false;
            if ($ip >= 2886729728 && $ip <= 2887778303) return false;
            if ($ip >= 3221225984 && $ip <= 3221226239) return false;
            if ($ip >= 3232235520 && $ip <= 3232301055) return false;
            if ($ip >= 4294967040) return false;
            return true;
        }
        return false;
    }
    static function bin2uuidString($str) {
        $str = bin2hex($str);
        return strtoupper(
    	substr($str,0,8).'-'.
            substr($str,8,4).'-'.
            substr($str,12,4).'-'.
            substr($str,16,4).'-'.
            substr($str,20,12)); //Stays, no swap
    }
}
