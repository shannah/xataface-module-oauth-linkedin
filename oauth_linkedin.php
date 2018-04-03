<?php
class modules_oauth_linkedin {
    public function __construct() {
        $app = Dataface_Application::getInstance();
        $app->registerEventListener('oauth_fetch_user_data', array($this, 'oauth_fetch_user_data'), false);
        $app->registerEventListener('oauth_extract_user_properties_from_user_data', array($this, 'oauth_extract_user_properties_from_user_data'), false);
    }
    
    function get($url, $json=true) {
        $oauth = Dataface_ModuleTool::getInstance()->loadModule('modules_oauth');
        return df_http_get($url, 'Host: api.linkedin.com'."\r\nAuthorization: Bearer ".$oauth->getOauthToken('linkedin')."\r\n", $json);
    }
    
    
    function post($url, $json=true) {
        $oauth = Dataface_ModuleTool::getInstance()->loadModule('modules_oauth');
        return df_http_post($url, array('HTTP_HEADERS' => 'Host: api.linkedin.com'."\r\nAuthorization: Bearer ".$oauth->getOauthToken('linkedin')."\r\n"), $json);
    }
    
        
    public function oauth_fetch_user_data($evt) {
        if ($evt->service !== 'linkedin') {
            return;
        }
        $app = Dataface_Application::getInstance();
        $oauth = Dataface_ModuleTool::getInstance()->loadModule('modules_oauth');
        $serviceConfig = $oauth->getServiceConfig('linkedin');
        $url = $serviceConfig['url'];
        //echo $url.'/me';exit;
        //session_write_close();
        $res = $this->get('https://api.linkedin.com/v1/people/~?format=json');
        /*  { "firstName": "Steve", 
         *  "headline": "Software Engineer at Codename One", 
         *  "id": "S3f9WHeSDA",
         *  "lastName": "Hannah", 
         *  "siteStandardProfileRequest": {"url": "https://www.linkedin.com/profile/view?id=AAoAABDTSscBtVC8scpM0LawB1okIH3nMRCcpbg&authType=name&authToken=zjX6&trk=api*a5532896*s5734166*"} }*
         * 
         */
        
        $evt->out = $res;
        return;
        
    }
    
    public function oauth_extract_user_properties_from_user_data($evt) {
        if ($evt->service !== 'linkedin') {
            return;
        }
        $evt->out = array(
            'id' => $evt->userData['id'],
            'name' => $evt->userData['firstName'].' '.$evt->userData['lastName'],
            'username' => $evt->userData['firstName'].'_'.$evt->userData['lastName']
        );
    }
    
    
    public function getConnections() {
        $res = $this->get("https://api.linkedin.com/v2/connections?q=viewer&projection=(elements*(to~(id,localizedFirstName,localizedLastName,headline,profilePhoto)))");
        print_r($res);
        return $res;
    }
            
}

