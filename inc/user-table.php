<?php


class UserTable
{

    public function __construct()
    {
    }

    public function getUserEmail($user_email)
    {
        return get_user_by('email', $user_email);
    }

    public function setUserPassword($user_email, $new_password)
    {
        $user = get_user_by('email', $user_email);
        if(!empty($user)) {
            wp_set_password($new_password, $user->ID);
            return true;
        }
        return false;
    }

}
