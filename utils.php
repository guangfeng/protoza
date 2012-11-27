<?php
require_once('shared.php');

set_time_limit(0);
function chunk_size($handle,$max_chunk)
{
  $nl = 0;
  while(!feof($handle)) {
    if(fgets($handle) !== false)
      ++$nl;
  }
  return ceil($nl / $max_chunk);
}


function fsplit($target, $childs, $dir, $prefix = null)
{
  $fh = @fopen($target,'r');
  if(!$fh) return false;

  $size = chunk_size($fh,$childs);
  fclose($fh);
  $cmd = "split -l $size $target $dir/$prefix";
  return shell_exec($cmd);
}


function fconqur($dir,$prefix = null)
{
  $dh = opendir($dir); 
  $files = array(); 
  while (($file = readdir($dh)) !== false) { 
    $flag = false;
    if($file !== '.' && $file !== '..' && strpos($file,$prefix) !== false) { 
      $files[] = $dir.$file; 
    } 
  } 
  return $files; 
}

function run(array $jobs,$snapshot = null, $map_callback = null, $reduce_callback = null)
{
  $childs = 0;

  foreach ($jobs as $job) {
    $childs++;
    $shm_id = $snapshot.strval($childs - 1);

    //$shms[$$childs - 1] = new SimpleSHM($shm_id);
    
    $childStart = pcntl_fork();
    
    if ($childStart == 0) { 
      $child_shm = new SimpleSHM($shm_id);
      $child_shm->write(map_callback($jobs[$childs - 1]));
      exit (0);
    }
    
  }
  
  
  for ($k=0; $k<$childs; $k++) {
    $childStatus = pcntl_wait($status);
    echo "Status of $childStatus children is $status.\n";
  }

  reduce_callback($childs,$snapshot);
}


