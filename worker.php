<?php
require_once('shared.php');

function browser_parse($user_agent){
	if( !$user_agent || $user_agent=='-' ) return false;
        $user_agent_lower = strtolower( $user_agent );
        $preg_agent_arr = array(
		 'safari' => '/webkit/',
                 'opera' => '/opera/',
                 'max' => '/maxthon/',
                 'se360' => '/360se/',
                 'tw' => '/theworld/',
                 'tt' => '/tencenttraveler/',
                 'ttqq' => '/QQBrowser/',
                 'tt5' => '/qqbrowser/',
                 'sg' => '/;?se.+?MetaSr/i',
                 'ie' => '/msie/',
                 'ff' => '/mozilla/',
                 
                 'ucweb' => '/ucweb/',
                 'sam' => '/samsung/',
                 'series' => '/series60/',
                 'nokia' => '/nokia/',
                 'maui' => '/maui/',
                 'utrust' => '/trusted/',
                 'htc' => '/htc/',
                 'zte' => '/zte/',
                 'sonyer' => '/sonyericsson/',
                 'android' => '/android/',
		 'iPad' => '/iPad/',
                 'huaw' => '/huawei/',
                 '3gpp' => '/3gpp\-gba/',
                 'bb' => '/blackberry/',
                 'oppo' => '/oppo/',
                 'ios' => '/ios/',
                 'lg' => '/^lg/',
                 'j2me' => '/j2me/',
                 'openwa' => '/openwave/',
                 'ua_mob' => '/ua\/mobile/',
                 'chun' => '/chinaunicom/',
                 'w186' => '/^w186/',
                 'dopod' => '/^dopod/',
        );
        $preg_ver = '/(?:rv|it|ra|ie)[\/: ]([\d.]+)/';
        foreach( $preg_agent_arr as $k => $v ){
        	if( preg_match($v, $user_agent_lower) ){
                	$agent = $k;
                	break;
            	}
        }
        if( !$agent ){ $agent = 'no'; }
        preg_match_all( $preg_ver, $user_agent_lower, $ver_arr );
        $ver = $ver_arr[1][0];
        if( !$ver ) $ver = 'no';
        /*
        if( $agent=='ie' && $ver=='6.0' ){
            echo $user_agent,"\n";
        }
         */
        return array( $agent, $ver );
}


function process_line($line)
{
  $line_column_arr = explode( '  ', $line);//两个空格
  $aid = null;
  preg_match("/aid=(\d+)/",$line,$aid);

  if($aid[1])
    $aid = $aid[1];
  else
    return false;

  $hour = substr( $line_column_arr[1], 13, 2 );
  $minute = substr( $line_column_arr[1], 16, 2 );
  
  $agent_ver = browser_parse($line_column_arr[4]);
  if( !$agent_ver ) return false;

  //return array('aid' => $aid, 'browser' => $agent_ver[0], 'version'=> $agent_ver[1],'hour'=>$hour);
  return array($aid.'_'.$agent_ver[0].'_'.$agent_ver[1],$hour);
}

function map_callback($obj) 
{
  $chunk_result = array();

  $fh = @fopen($obj,'r');
  if(!$fh) {
    $t = time();
    echo "ERROR $t $obj open failed\n";
    exit;
  }
  
  while(!feof($fh)) {
    if(($line = fgets($fh)) !== false) {
   
      if(($pline = process_line($line)) !== false )
	if(array_key_exists($pline[0],$chunk_result))
	  $chunk_result[$pline[0]]++;
	else
	  $chunk_result[$pline[0]] = 1;
    }
  }
  
  return json_encode($chunk_result);
}

function reduce_callback($childs,$snapshot)
{
  $res = array();
  
  for($i = 0; $i < $childs ; ++$i) {

        $s = new SimpleSHM(intval($snapshot.strval($i)));
        $r = json_decode($s->read());

        foreach($r as $k=>$v) {
	  if(array_key_exists($k ,$res))
	    $res[$k] += $v;
	  else
	    $res[$k] = $v;
	}       
	$s->delete();
  }
}