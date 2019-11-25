<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('solr-php-client-master/Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/hw4/');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
      $radioVal = $_GET["rdb"];
      if($radioVal == "PageRank"){
          $additionalParameters=array(
          //'fl'=>'og_url',
         //'fl'=>'true',
          'fl'=>array('title','og_url','id','description'),
          'sort'=>'pageRankFile desc');
      }
      else{
          $additionalParameters=array(
           'fl'=>array('title','og_url','id','description'));
       
}

   $results=$solr->search($query, 0, $limit, $additionalParameters);

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
    <title>PHP Solr Client</title>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input type="submit"/>
       <div align="left">
        <input type="radio" name="rdb" value="Default Ranking" checked> Default Ranking<br>
        <input type="radio" name="rdb" value="PageRank"> PageRank<br>
      </div>
     </form>
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <table style="border: 1px solid black; text-align: left">
<?php
    // iterate document fields / values
    foreach($doc as $field => $value){
	 if($field == 'og_url'){
                $url = $value;
        }
   }

    foreach ($doc as $field => $value)
    {
?>
          <tr>
            <th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?></th>
             <td>
		<?php if ($field == 'title' or $field == 'og_url'): ?>
		<a href=
			<?php 
			   echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8');
			?>><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');?> 
	        </a>
		<?php  else: ?><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');?>
		<?php  endif; ?>
             </td>
          </tr>
<?php
    }
?>
        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>

