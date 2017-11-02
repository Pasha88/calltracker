<?php
	
class Customer {
	
	public $customerId;
	public $email;
	public $pwdHash;
	public $description;

    public $restore_uid;
    public $restore_valid_till;
    public $reset_pwd_uid;
    public $reset_pwd_valid_till;

    public $gaId;
    public $defaultNumber;
    public $defaultDomain;

    public $scriptToken;
    public $customerUid;

    public $timeZone;

    public $yaId;
    public $yaIdAuth;
    public $yaRefresh;
    public $yaExpires;

    public $role;
    public $upTimeFrom;
    public $upTimeTo;
    public $upTimeSchedule;

    public $tariffId;
    public $tariffName;
    public $balance;


    function __construct()
	{

	}

}
