<?php

require_once(dirname(__DIR__) . '/Command.php');

class SaveCustomerByUidCommand extends Command{

    private $updateCustomerSQL =
        'update customer set
            email			 		= ?,
            hkey			 		= ?,
            description			 	= ?,
            restore_uid			 	= ?,
            restore_valid_till		= ?,
            reset_pwd_uid			= ?,
            reset_pwd_valid_till	= ?,
            ga_id			 		= ?,
            default_phone_number    = ?,
            default_domain			= ?,
            script_token			= ?,
            time_zone			 	= ?,
            ya_id                   = ?,
            ya_id_auth              = ?,
            ya_refresh              = ?,
            ya_expires              = ?
        where customer_uid = ?';

    private $args;

    function __construct($customer) {
        $this->args['email']                    =   $customer->email;
        $this->args['pwdHash']                  =   $customer->pwdHash;
        $this->args['description']              =   $customer->description;
        $this->args['restore_uid']              =   $customer->restore_uid;
        $this->args['restore_valid_till']       =   $customer->restore_valid_till;
        $this->args['reset_pwd_uid']            =   $customer->reset_pwd_uid;
        $this->args['reset_pwd_valid_till']     =   $customer->reset_pwd_valid_till;
        $this->args['gaId']                     =   $customer->gaId;
        $this->args['defaultNumber']            =   $customer->defaultNumber;
        $this->args['defaultDomain']            =   $customer->defaultDomain;
        $this->args['scriptToken']              =   $customer->scriptToken;
        $this->args['timeZone']                 =   $customer->timeZone;
        $this->args['yaId']                     =   $customer->yaId;
        $this->args['yaIdAuth']                 =   $customer->yaIdAuth;
        $this->args['yaRefresh']                =   $customer->yaRefresh;
        $this->args['yaExpires']                =   $customer->yaExpires;
        $this->args['customerUid']              =   $customer->customerUid;
        parent::__construct();
    }

    public function execute($conn)
    {
        if ($stmt = $conn->prepare($this->updateCustomerSQL)) {
            $stmt->bind_param("sssssssssssisssis",
                $this->args['email'],
                $this->args['pwdHash'],
                $this->args['description'],
                $this->args['restore_uid'],
                $this->args['restore_valid_till'],
                $this->args['reset_pwd_uid'],
                $this->args['reset_pwd_valid_till'],
                $this->args['gaId'],
                $this->args['defaultNumber'],
                $this->args['defaultDomain'],
                $this->args['scriptToken'],
                $this->args['timeZone'],
                $this->args['yaId'],
                $this->args['yaIdAuth'],
                $this->args['yaRefresh'],
                $this->args['yaExpires'],
                $this->args['customerUid']
            );
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_SAVE_CUSTOMER->message);
        }

        return true;
    }

}