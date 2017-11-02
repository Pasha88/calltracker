<?php

require_once("SimpleRest.php");
require_once(dirname(__DIR__)."/commands/SaveGaIdCommand.php");
require_once(dirname(__DIR__)."/commands/SaveDefaultNumberCommand.php");
require_once(dirname(__DIR__)."/commands/CheckInstallCommand.php");
require_once(dirname(__DIR__)."/commands/customer/FindCustomerCommandByUid.php");
require_once(dirname(__DIR__)."/commands/LoadUserSettingsCommand.php");
require_once(dirname(__DIR__)."/commands/SaveUserSettingsCommand.php");
require_once(dirname(__DIR__) . "/repo/AppPropertyRepo.php");

class CustomerHandler extends SimpleRest {

    public function saveGaId($customerUid, $gaId) {
        $params = array('customerUid' => $customerUid, 'gaId' => $gaId);
        $command = new SaveGaIdCommand($params);
        $this->handle($command);
    }

    public function saveDefaultNumber($customerUid, $number, $domain)    {
        $params = array('customerUid' => $customerUid, 'number' => $number, 'domain' => $domain);
        $command = new SaveDefaultNumberCommand($params);
        $this->handle($command);
    }

    public function checkInstall($customerUid) {
        $params = array('customerUid' => $customerUid);
        $command = new CheckInstallCommand($params);
        $this->handle($command);
    }

    public function loadUserSettings($customerUid) {
        $params = array('customerUid' => $customerUid);
        $command = new LoadUserSettingsCommand($params);
        $this->handle($command);
    }

    public function saveUserSettings($customerUid, $customerTimeZone, $upTimeFrom, $upTimeTo, $upTimeSchedule){
        $params = array('customerUid' => $customerUid, 'customerTimeZone' => $customerTimeZone, 'upTimeFrom' => $upTimeFrom, 'upTimeTo' => $upTimeTo, 'upTimeSchedule' => $upTimeSchedule);
        $command = new SaveUserSettingsCommand($params);
        $this->handle($command);
    }

    public function confirmYaToken($code) {
        $result = $this->postKeys("https://oauth.yandex.ru/token",
            array(
                'grant_type'=> 'authorization_code', // тип авторизации
                'code'=> $code, // наш полученный код
                'client_id'=> AppConfig::YA_APPLICATION_ID,
                'client_secret' => AppConfig::YA_APPLICATION_KEY
            ),
            array('Content-type: application/x-www-form-urlencoded')
        );

        if ($result["code"]==200) {
            $tokenData = json_decode($result["response"],true);
            return $tokenData;
        }
        else{
            $errData = json_decode($result["response"],true);
            throw new Exception($errData->error_description);
        }
    }

    public function loadNumHandler($customerUid)    {
        $propRepo = AppPropertyRepo::getInstance();
        $fileName = "numloader.js";

        $c = new FindCustomerCommandByUid( array( 'customerUid' => $customerUid ) );
        $customer = $this->handleWithResult($c);

//        $yaClientIdGetter = isset($customer->yaId) ? "yaCounter" . $customer->yaId . ".getClientID()" : "\"\"";

        $fileContent = "var AUTH_TOKEN = \"" . $customer->scriptToken . "\";
	var PHONE_COOKIE_NAME = \"phone_num_gtm\";
	var PHONE_ID_COOKIE_NAME = \"phone_num_id_gtm\";
    var CUSTOMER_UID = \"$customer->customerUid\";	
	var COOKIE_LIFETIME_SEC = " . $propRepo->read('PHONE_NUMBER_BUSY_INTERVAL_SEC') . ";
    var PHONE_NUM_ELEMENT_CLASS = 'phoneAllostat';
	var URL = \"" . AppConfig::OCCUPY_NUMBER_URL . "\";	
	var IDLE_TIMEOUT = 30; //seconds
	var _idleSecondsCounter = 0;
  	var DELTA = " . $propRepo->read('CALLS_PAGE_IDLE_TO_ALIVE_DELAY') . ";
  	var COOKIE_GET_NUMBER_CALL_FLAG = 'phone_num_flag';
 
  	function getYaCounterNum() {
        for(var i in window){
            if(new RegExp(/yaCounter/).test(i)){
                return i.substr(9);
            }
        }
  	}
	
	function startIdleCheck(toAliveCallBack)  {
		document.onclick = function() {
			if(_idleSecondsCounter > IDLE_TIMEOUT) {
				_idleSecondsCounter = 0;
				window.setTimeout(function() { toAliveCallBack(); }, DELTA*1000);
			}
			_idleSecondsCounter = 0;
		};
		document.onmousemove = function() {
			if(_idleSecondsCounter > IDLE_TIMEOUT) {
				_idleSecondsCounter = 0;
				window.setTimeout(function() { toAliveCallBack(); }, DELTA*1000);
			}
			_idleSecondsCounter = 0;
		};
		document.onkeypress = function() {
			if(_idleSecondsCounter > IDLE_TIMEOUT) {
				_idleSecondsCounter = 0;
				window.setTimeout(function() { toAliveCallBack(); }, DELTA*1000);
			}
			_idleSecondsCounter = 0;
		};
		window.setInterval(function() { _idleSecondsCounter++; }, 1000);	
	}
	
	var prolongNumberLifeTime = function() {
		if(_idleSecondsCounter > IDLE_TIMEOUT) {
			return;
		}
		setPhoneNumber(true);
	}
	
	function getCookie(cname) {
		var name = cname + \"=\";
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(';');
		for(var i = 0; i <ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
		return \"\";
	}

    function set_cookie(name, value, intervalInSeconds) {
        var cookie_string = name + \"=\" + escape(value);
        var expires = new Date();
        expires.setSeconds( expires.getSeconds() + intervalInSeconds);
        //document.getElementById('cid').textContent = expires;
        cookie_string += \"; expires=\" + expires.toGMTString();
        document.cookie = cookie_string;
    }
  
	function delete_cookie(name) {
        var cookie_string = name + \"=\";
        cookie_string += \"; expires=Thu, 01 Jan 1970 00:00:01 GMT;\";
        document.cookie = cookie_string;
    }  
    
	function tryToGetPhoneNum(xhttp, CLIENT_ID, yaClientId, CUSTOMER_UID, xId,  yaCounterNum, tryNumber, force) {
		var flag = getCookie(COOKIE_GET_NUMBER_CALL_FLAG);
		if(flag != 0 && tryNumber < 3 && force == false) {
			var nextTry = tryNumber + 1;
			window.setTimeout(function() { tryToGetPhoneNum(xhttp, CLIENT_ID, yaClientId, CUSTOMER_UID, xId,  yaCounterNum, nextTry, false); }, 1000);
		}
		else {
            var dataToSend = {
                clientId: CLIENT_ID,
                yaClientId: yaClientId,
                customerUid: CUSTOMER_UID,
                phoneNumberId: xId,
                yaId: yaCounterNum,
                url: document.URL
            };
			set_cookie(COOKIE_GET_NUMBER_CALL_FLAG, 1, 5);
			xhttp.send(JSON.stringify(dataToSend));			
		}
	}
	    
    function getYaCounterAndGaClientIdAndProcess(force, tryNumber, CUSTOMER_UID, xId, xhttp) {
        var yaClientId = typeof window['yaCounter' + getYaCounterNum()] === 'undefined' ? \"0\" : window['yaCounter' + getYaCounterNum()].getClientID();
        
        CLIENT_ID = null;
		var tmp = getCookie('_ga');
        var cookie = tmp.split(\".\");
         
        if(typeof cookie[2] != 'undefined' && typeof cookie[3] != 'undefined') {
            CLIENT_ID = cookie[2] + \".\" + cookie[3];
        }
        
        if( (yaClientId == '0' || CLIENT_ID == null) && tryNumber < 3) {
            var nextTry = tryNumber + 1;
            setTimeout(function() {
                getYaCounterAndGaClientIdAndProcess(force, nextTry, CUSTOMER_UID, xId, xhttp);
            }, 200);
            return;
        }
        
		tryToGetPhoneNum(xhttp, CLIENT_ID, yaClientId, CUSTOMER_UID, xId,  getYaCounterNum(), 0, force);
    }

	function setPhoneNumber(prolong, force) {
		var x = getCookie(PHONE_COOKIE_NAME);

		if(typeof x != 'undefined' && x != null && x.length > 0) {
			if(prolong == false) {
				var eList = document.getElementsByClassName(PHONE_NUM_ELEMENT_CLASS);
				for(var i=0; i<eList.length; i++) {
					eList[i].textContent = x;
				}				
				return;
			}
		}
		else {
			x = '';
		}
		
		var xId = getCookie(PHONE_ID_COOKIE_NAME);
		
		if(typeof xId != 'undefined' && xId == -1) {
		    return;
		}
		
		var xhttp = new XMLHttpRequest();
		xhttp.open(\"POST\", URL, true);
		xhttp.setRequestHeader('Content-Type', 'application/json; charset=utf-8');
		xhttp.setRequestHeader('X-Auth-Token', 'Bearer ' + AUTH_TOKEN);
		xhttp.setRequestHeader('Accept', 'application/json');
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4) {
				if(this.status == 200) {
					var response = JSON.parse(xhttp.responseText);
					if(response.id > 0) {
						PHONE_NUMBER = response.number;	
						PHONE_NUMBER_ID = response.id;
						
                        var eList = document.getElementsByClassName(PHONE_NUM_ELEMENT_CLASS);
                        for(var i=0; i<eList.length; i++) {
                            eList[i].textContent = PHONE_NUMBER;
                        }
                        set_cookie(PHONE_COOKIE_NAME, PHONE_NUMBER, COOKIE_LIFETIME_SEC);                  
                        set_cookie(PHONE_ID_COOKIE_NAME, PHONE_NUMBER_ID, COOKIE_LIFETIME_SEC);
					}
                    else if(response.id == -1) {
                        var eList = document.getElementsByClassName(PHONE_NUM_ELEMENT_CLASS);
                        set_cookie(PHONE_COOKIE_NAME, eList[0].textContent, COOKIE_LIFETIME_SEC + 3); // 3 - Дополнительный люфт, чтобы превысить время занятости номера                   
                        set_cookie(PHONE_ID_COOKIE_NAME, -1, COOKIE_LIFETIME_SEC + 3);
                    }
				}
              	delete_cookie(COOKIE_GET_NUMBER_CALL_FLAG);
			}
		};
      
        getYaCounterAndGaClientIdAndProcess(force, 0, CUSTOMER_UID, xId ,xhttp);
    }
	
    startIdleCheck(prolongNumberLifeTime);
	window.setInterval(function() { prolongNumberLifeTime(); }, 30000);
  	setPhoneNumber(false, true);";

        // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
        // если этого не сделать файл будет читаться в память полностью!
        if (ob_get_level()) {
            ob_end_clean();
        }
        // заставляем браузер показать окно сохранения файла
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($fileContent));
        // читаем файл и отправляем его пользователю
        print $fileContent;
    }

}