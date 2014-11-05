<?php
	
if ( !class_exists( 'Whois' ) ) {
		
	class Whois {

	    var $m_status = 0;
	    var $m_domain = '';
	    var $m_servers = array();
	    var $m_data = array();
	    var $m_connectiontimeout = 5;
	    var $m_sockettimeout = 30;
	    var $m_redirectauth = true;
	    var $m_usetlds = array();
	    var $m_supportedtlds = array();
	    var $m_serversettings = array();
	
		    function Whois(){
		    $this->readconfig();
	    }
	
	    function readconfig(){
	
		    $this->m_serversettings = array();
		    $this->m_tlds = array();
		    $this->m_usetlds = array();
	
	        $servers = array("whois.crsnic.net#domain |No match for |Whois Server:|>NOTICE: The expiration date |Registrar:#Status:#Expiration Date:", "whois.afilias.net|NOT FOUND||<you agree to abide by this policy.|Expiration Date:#Status:#Registrant Email:#Admin Name:#Billing Name:#Billing Email#Tech Name:#Tech Email:#Registrant Name:#Admin Email:#Name Server:", "whois.nic.us|Not found:||>NeuStar, Inc., the Registry Administrator|Domain Expiration Date:#Domain Status:#Sponsoring Registrar:#Registrant Name:#Registrant Email:#Administrative Contact Name:#Administrative Contact Email:#Billing Contact Name:#Billing Contact Email:#Technical Contact Name:#Technical Contact Email:#Name Server:", "whois.internic.net|No match for |Whois Server:", "whois.publicinterestregistry.net|NOT FOUND||<you agree to abide by this policy.|Expiration Date:#Status:#Name Server:#Registrant Name:#Registrant Email:#Admin Name:#Admin Email:#Tech Name:#Tech Email:#Billing Name:#Billing Email:", "whois.neulevel.biz|Not found:||>NeuLevel, Inc., the Registry|Domain Expiration Date:#Domain Status:#Sponsoring Registrar:#Registrant Name:#Registrant Email:#Administrative Contact Name:#Administrative Contact Email:#Billing Contact Name:#Billing Contact Email:#Technical Contact Name:#Technical Contact Email:#Name Server:", "whois.nic.uk|No match for|||Registration Status:#Registrant:#Registrant's Address:#Renewal Date:#Name servers", "rs.domainbank.net|||<of the foregoing policies.|Administrative Contact:#Record expires on #Technical Contact:#Registrant:#Zone Contact:#Domain servers in ", "whois.moniker.com|||<you agree to abide by this policy.|Administrative Contact:#Registrant:#Domain Servers#Billing Contact:#Technical Contact:#Domain Expires on", "whois.networksolutions.com|||<right to modify these terms at any time.|Registrant:#Administrative Contact:#Record expires on #Domain servers in listed order:", "whois.enom.com|||>The data in this whois database |Registrant Contact:#Technical Contact:#Billing Contact:#Administrative Contact:#Status:#Name Servers:#Expiration date:", "whois.opensrs.net|||>The Data in the Tucows Registrar|Registrant:#Administrative Contact:#Technical Contact:#Record expires on#Domain servers in listed order:", "whois.godaddy.com|||<domain names listed in this database.|Registrant:#Expires On:#Administrative Contact:#Technical Contact:#Domain servers in listed order:", "whois.aunic.net|No Data Found|||Status:#Registrant Contact Name:#Registrant Email:#Name Server:#Tech Name:#Tech Email:", "whois.denic.de|free", "whois.worldsite.ws|No match for|||Registrant:#Name Servers:", "whois.nic.tv|", "whois.nic.tm|No match for", "whois.cira.ca|AVAIL", "whois.nic.cc|No match|Whois Server:|>The Data in eNIC Corporation|Whois Server:#Updated:", "whois.domainzoo.com|||<you agree to abide by these terms.", "whois.domaindiscover.com|||<you agree to abide by this policy.", "whois.markmonitor.com|||<you agree to abide by this policy.", "whois2.afilias-grs.net|NOT FOUND||<abide by this policy.");
	        $tlds = array("com=whois.crsnic.net", "net=whois.crsnic.net", "org=whois.publicinterestregistry.net", "info=whois.afilias.net", "biz=whois.neulevel.biz", "us=whois.nic.us", "co.uk=whois.nic.uk", "org.uk=whois.nic.uk", "ltd.uk=whois.nic.uk", "ca=whois.cira.ca", "cc=whois.nic.cc", "edu=whois.crsnic.net", "com.au=whois.aunic.net", "net.au=whois.aunic.net", "de=whois.denic.de", "ws=whois.worldsite.ws", "sc=whois2.afilias-grs.net");
	
	        $cnt = count($servers);
	
		    foreach( $servers as $server){
			    $server = trim($server);
			    $bits = explode('|', $server);
			    if( count($bits) > 1 ){
				    for( $i = count($bits); $i < 5; $i++){
					    if( !isset($bits[$i]) ) $bits[$i] = '';
				    }
				    $server = explode("#", $bits[0]);
				    if( !isset($server[1]) ) $server[1] = '';
	
				    $this->m_serversettings[$server[0]] = array('server'=>$server[0], 'available'=>$bits[1], 'auth'=>$bits[2], 'clean'=>$bits[3], 'hilite'=>$bits[4], 'extra'=>$server[1]);
			    }
		    }
		    foreach( $tlds as $tld ){
			    $tld = trim($tld);
			    $bits = explode('=', $tld);
			    if( count($bits) == 2 && $bits[0] != '' && isset($this->m_serversettings[$bits[1]])){
				    $this->m_usetlds[$bits[0]] = true;
				    $this->m_tlds[$bits[0]] = $bits[1];
			    }
		    }
	
	    }
	
	    function SetTlds($tlds = 'com,net,org,info,biz,us,co.uk,org.uk'){
		    $tlds = strtolower($tlds);
		    $tlds = explode(',',$tlds);
		    $this->m_usetlds = array();
		    foreach( $tlds as $t ){
			    $t = trim($t);
			    if( isset($this->m_tlds[$t]) ) $this->m_usetlds[$t] = true;
		    }
		    return count($this->m_usetlds);
	    }
	
	    function Lookup($domain){
		    $domain = strtolower($domain);
		    $this->m_servers = array();
		    $this->m_data = array();
		    $this->m_tld = $this->m_sld = '';
		    $this->m_domain = $domain;
		    if( $this->splitdomain($this->m_domain, $this->m_sld, $this->m_tld) ){
			    $this->m_servers[0] = $this->m_tlds[$this->m_tld];
			    $this->m_data[0] = $this->dolookup($this->m_serversettings[$this->m_servers[0]]['extra'].$domain, $this->m_servers[0]);
			    if( $this->m_data[0] != '' ){
				    if( $this->m_serversettings[$this->m_servers[0]]['auth'] != '' && $this->m_redirectauth && $this->m_status == STATUS_UNAVAILABLE){
					    if( preg_match('/'.$this->m_serversettings[$this->m_servers[0]]['auth'].'(.*)/i', $this->m_data[0], $match) ){
						    $server = trim($match[1]);
						    if( $server != '' ){
							    $this->m_servers[1] = $server;
							    $command = isset($this->m_serversettings[$this->m_servers[1]]['extra']) ? $this->m_serversettings[$this->m_servers[1]]['extra'] : '';
							    $dt = $this->dolookup($command.$this->m_domain, $this->m_servers[1]);
							    $this->m_data[1] = $dt;
						    }
					    }
				    }
				    return true;
			    }else{
				    return false;
			    }
		    }
		    return false;
	    }
	
	
	    function ValidDomain($domain){
		    $domain = strtolower($domain);
		    return $this->splitdomain($domain, $sld, $tld);
	    }
	
	    function GetDomain(){
		    return $this->m_domain;
	    }
	
	    function GetServer($i = 0){
		    return isset($this->m_servers[$i]) ? $this->m_servers[$i] : '';
	    }
	
	    function GetData($i = -1){
		    if( $i != -1 && isset($this->m_data[$i])){
			    $dt = htmlspecialchars(trim($this->m_data[$i]));
			    $this->cleandata($this->m_servers[$i], $dt);
			    return $dt;
		    }else{
			    return trim(join("\n", $this->m_data));
		    }
		    return '';
	    }
	
	
	    function splitdomain($domain, &$sld, &$tld){
		    $domain = strtolower($domain);
		    $sld = $tld = '';
		    $domain = trim($domain);
		    $pos = strpos($domain, '.');
		    if( $pos != -1){
			    $sld = substr($domain, 0, $pos);
			    $tld = substr($domain, $pos+1);
			    if( isset($this->m_usetlds[$tld]) && $sld != '' ) return true;
		    }else{
			    $tld = $domain;
		    }
		    return false;
	    }
	
	    function whatserver($domain){
		    $sld = $tld = '';
		    $this->splitdomain($domain, $sld, $tld);
		    $server = isset($this->m_usetlds[$tld]) ? $this->m_tlds[$tld] : '';
		    return $server;
	    }
	
	    function dolookup($domain, $server){
		    $domain = strtolower($domain);
		    $server = strtolower($server);
		    if( $domain == '' || $server == '' ) return false;
	
		    $data = "";
		    $fp = @fsockopen($server, 43,$errno, $errstr, $this->m_connectiontimeout);
		    if( $fp ){
			    @fputs($fp, $domain."\r\n");
			    @socket_set_timeout($fp, $this->m_sockettimeout);
			    while( !@feof($fp) ){
				    $data .= @fread($fp, 4096);
			    }
			    @fclose($fp);
	
			    return $data;
		    }else{
			    return "\nError - could not open a connection to $server\n\n";
		    }
	    }
	
	    function cleandata($server, &$data){
		    if( isset($this->m_serversettings[$server]) ){
			    $clean = $this->m_serversettings[$server]['clean'];
			    if( $clean != '' ){
				    $from = $clean[0];
				    if( $from == '>' || $from == '<' ){
					    $clean = substr($clean,1);
					    $pos = strpos(strtolower($data), strtolower($clean));
					    if( $pos !== false ){
						    if( $from == '>' ){
							    $data = trim(substr($data, 0, $pos));
						    }else{
							    $data = trim(substr($data, $pos+strlen($clean)));
						    }
					    }
				    }
			    }
		    }
	    }
	
	
	}
}

?>