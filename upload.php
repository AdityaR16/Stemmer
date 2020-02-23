<!DOCTYPE html>
<html>
 <head>
   <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
  <style>
   h1 {
  color: blue;
  text-align: center;
}
  .footer {
   position: fixed;
   left: 0;
   bottom: 0;
   width: 100%;
   background-color: #c21b28 ;
   color: white;
   text-align: center;
}
  </style>

 </head>
 <body>
 
  <nav class="navbar navbar-expand-sm bg-danger navbar-dark">
  <ul class="navbar-nav">
    <li class="nav-item active">
      <a class="nav-link" >STEMMER</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="index.html">Home</a>
    </li>
   
  </ul>
</nav>
<?php

function getUserIpAddr() // Getting the User's IP
{
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$ip = getenv('HTTP_CLIENT_IP')?: getenv('HTTP_X_FORWARDED_FOR')?: getenv('HTTP_X_FORWARDED')?: getenv('HTTP_FORWARDED_FOR')?: getenv('HTTP_FORWARDED')?: getenv('REMOTE_ADDR');
$cTime = date("Y-m-d-H-i-s"); // current time
$uploaddir = 'inputs/';
$uploadfile = $uploaddir ."(". $cTime.")[".$ip."]".basename($_FILES['fileToUpload']['name']);
echo '<pre>';
if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $uploadfile)) {
    echo "File is valid, and was successfully uploaded.\n";
} else {
    echo "Possible file upload attack!\n";
}
print "</pre>";
$filename=$uploadfile;
$uploadedfile=fopen($filename,"r") or die("Unable to open Input file!");
$dir='outputs/';
$nameo=$dir."(". $cTime.")[".$ip."]". basename($_FILES['fileToUpload']['name']);
$out_file=fopen($nameo,"w") or die("Unable to open Output file!");
$dict=file('resources/dict.txt',FILE_IGNORE_NEW_LINES); //Getting each word of Dictionary in an array

//Inserting the dictionary words into an hash as key-value pairs for fast look-up
for($i=0;$i<sizeof($dict);$i++)
{
  $hash[strtolower($dict[$i])]=1;
}

function endsWith($word,$suffix) // Checking if a word ends with a particular suffix
{
  $word = trim($word);
  $suffix = trim($suffix);
  $len=strlen($suffix);
  if($len==0)
  {
    return true;
  }
  trim($word);
  trim($suffix);
  return (substr($word,-$len)===$suffix);
}

function find_in_suffix($word,$suffixList,&$pos) // Traversing through the suffix list and checking the word against each suffix
{ 
  for($i=0;$i<sizeof($suffixList);$i++)
  {
     if(endsWith($word,$suffixList[$i]))
     {
       $pos=$i;
       return true;
     }
  }
  return false;
}

function isNull($text) // Checking if a word is Null
{
    return ctype_space($text) || $text === "" || $text === null;
}

// Getting The suffix list and sorting it in decreasing order of length
function cmp($a, $b)
{
    return strlen($b)-strlen($a);
}
$suffixList=file("resources/suffix.txt",FILE_IGNORE_NEW_LINES);
usort($suffixList, "cmp");
//Sorting Section ends

while(!feof($uploadedfile))
{
  $line=fgets($uploadedfile);
  $word_arr=explode(" ",str_replace(array(" ",",","\n","\t")," ",$line));
  foreach($word_arr as $word)
  {
    $found=0;
    $outStr="";
    $pos=0;
    if(!isNull($word))
    {
      if(isset($hash[strtolower($word)]))
      {
        $outStr=$word." ---Dictionary Word ";
      }
      else if(find_in_suffix($word,$suffixList,$pos))
      {
        $outStr=$word.":  ***Stem***  : " . substr($word,0,strripos($word,$suffixList[$pos]) )." + " . $suffixList[$pos];
      }
      else
      {
        $outStr=$word ." ---OOV";
      }
      fwrite($out_file,$outStr."\n\n");
    }//if ends here
  }//foreach ends here
}//while ends here
fclose($uploadedfile);
fclose($out_file);
$out_file=fopen($nameo,"r") or die("Unable to open Output file!");
while(!feof($out_file))
{
  echo " ".fgets($out_file) . "<br>";
}
fclose($out_file);
?>
 <div class="footer page-header">
            <hr>
            <p class="mute">&copy; 2019 Stammer</p>
        </div>
</body>
</html>
