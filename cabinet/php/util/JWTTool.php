<?php

require_once dirname(__DIR__).'/../../../vendor/autoload.php';
require_once 'Util.php';

use \Firebase\JWT\JWT;

class JWTTool {

    static $expireTokenPeriodSec10Sec = 10;
	static $expireTokenPeriodSec10Min = 3600;
	static $expireTokenPeriodSecMonth = 2678400;
    static $expireTokenPeriodSecYear = 31556926;
    static $expireTokenPeriodSec10Year = 315569260;
	
	static $sequence = "hU309#$34424kjgsdnncl5rhsl50lspqksldfF5><";
	
	public static function createToken($login, $domain, $intervalSec) {
		$curDateMillis = Util::getCurrentDate()->getTimeStamp();

        $domain = Util::extractDomain($domain);

		$token = array(
			"iss" => $domain,
			"aud" => $login,
			"iat" => $curDateMillis, //created			
			"nbf" => $curDateMillis, //can use at
			"exp" => $curDateMillis + $intervalSec, //expire
		);

		$jwt = JWT::encode($token, self::$sequence);
		return $jwt;
	}
	
	public static function checkToken($jwt, $domain) {
        $domain = Util::extractDomain($domain);

		$jwtDecoded = JWT::decode($jwt, self::$sequence, array('HS256'));
		
		if($jwtDecoded->iss!= $domain) {
			throw new Exception('Ошибка токена. Неверный домен.');
		}
		
		if($jwtDecoded != null) {
			return true;
		}
		throw new Exception("Ошибка токена");
	}

    public static function checkCustomerToken($jwt, $domain, $login) {
        $domain = Util::extractDomain($domain);

        $jwtDecoded = JWT::decode($jwt, self::$sequence, array('HS256'));

        if($jwtDecoded->iss!= $domain) {
            throw new Exception('Ошибка токена. Неверный домен.');
        }

        if($login != $jwtDecoded->aud) {
            throw new Exception('Ошибка токена. Неверный пользователь.');
        }

        if($jwtDecoded != null) {
            return true;
        }
        throw new Exception("Ошибка токена");
    }
}

//JWTTool::createToken("cat_dz@mail.ru", "allostat.ru", JWTTool::$expireTokenPeriodSecYear);

//JWTTool::checkCustomerToken("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodW50dC5ydSIsImF1ZCI6ImRlbmlzb3Jsb3ZAbWFpbC5ydSIsImlhdCI6MTQ5ODY1MzU5MCwibmJmIjoxNDk4NjUzNTkwLCJleHAiOjE1MzAyMTA1MTZ9.nI6RAOpOd_nr_kaOoBMQZHKZbS9RUvzqpiE2zsOa4i0",
//"http://mock.huntt.ru", 'denisorlov@mail.ru');

