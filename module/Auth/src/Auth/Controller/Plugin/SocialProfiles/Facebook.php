<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2104 Cross Solution (http://cross-solution.de)
 * @license   AGPLv3
 */

/** Facebook.php */ 
namespace Auth\Controller\Plugin\SocialProfiles;

class Facebook extends AbstractAdapter
{
    
    
    protected function queryApi($api)
    {
        return $api->api('/me');
    }
}

