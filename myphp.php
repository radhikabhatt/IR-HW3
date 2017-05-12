<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');


$max = 10;
$temp = 'solr';
$output = false;
$check = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;

if ($check)
{
 // The Apache Solr Client library should be on the include path
 // which is usually most easily accomplished by placing in the
 // same directory as this script ( . or current directory is a default
 // php include path entry in the php.ini)
 require_once('Apache/Solr/Service.php');

 // create a new solr service instance - host, port, and corename
 // path (all defaults in this example)
 
 $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');
 // if magic quotes is enabled then stripslashes will be needed
 
if (get_magic_quotes_gpc() == 1)
 {
 $check = stripslashes($check);
 }

 // in production code you'll always want to use a try /catch for any
 // possible exceptions emitted by searching (i.e. connection
 // problems or a query parsing error)
 
try
 {
	if(isset($_GET['methods'])&&($_GET['methods']=="solr"))
	{
		
	$output = $solr->search($check, 0, $max);
	$temp = 'solr';
}
	else{
	$set =array('sort'=>"pageRankFile desc");
	$output=$solr->search($check,0,10,$set);
	$temp = 'pr';
}
}
 catch (Exception $e)
 {
 // in production you'd probably log or email this error to an admin
 // and then show a special message to the user but for this example
 // we're going to show the full exception
 die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
 }
}
?>

<html>
 <head>
<style>
.center {
    margin: auto;
    width: 30%;
    padding: 10px;
    border: 1px solid black;
}
.button5 {
    border-radius: 3px;
    font-size: 20px;
    background-color: #e7e7e7;
    color: black;
}
.results {
    padding-left: 3px;
}
</style>
 <title>PHP Solr Client Example</title>
 </head>
 <body>

 <form accept-charset="utf-8" method="get">
 <label for="q">Solar Search:</label>
 <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($check, ENT_QUOTES, 'utf-8'); ?>"/>

<br />
<br />
<br />

<div class = "center">
<br>


<input type="radio" name="methods" value="solr" <?php if ($temp=='solr') echo ' checked="checked"';?>/>Results from Solr
<br />
<input type="radio" name="methods" value="pr"<?php if ($temp=='pr') echo ' checked="checked"';?>/>Results from PageRank 
<br /><br />
<input type="submit" class = "button5"/>
</div>
</form>


<?php
if ($output)
{
 $total = (int) $output->response->numFound;
 $max_output = min($max, $total);
 $min_output = min(1, $total);
?>
<div class='results'>Results <?php echo $min_output; ?> - <?php echo $max_output;?> of <?php echo $total; ?>:</div>


<ol><?php
$collection=array();
try
{
$csv=fopen("mergedcsv.csv","r");

while(!feof($csv)){
$readcsv=fgetcsv($csv,1024);
$collection[$readcsv[0]]=$readcsv[1];
}

fclose($csv);

}catch (Exception $err) 
{
 echo 'Error: ',  $err->getMessage(), "\n";
}

foreach ($output->response->docs as $page){
if(isset($page->dc_title)){
$title=$page->dc_title;
}
else{
$title="NA";
}

if(isset($page->description)){
$detail=$page->description;
}
else{
$detail="NA";
}

$id=$page->id;
$link = str_replace("/home/kgodse/Downloads/solr-6.2.1/crawl_data/","",$id);

?>



<div>

<li>
<p><a href="<?php echo $collection[$link];?>"><?php echo $collection[$link];?></a></p>
<p>ID:<?php echo " ".$id; ?></p>
<p>Description:<?php echo " ".$detail; ?></p>
<p>Title:<a href="<?php echo " ".$collection[$link] ?>"><?php echo $title ?></p>
</li>
</div>

<?php } ?>
</ol>
<?php } ?>
 </body>
</html>
