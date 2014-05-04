<html><head><title>Study Title</title></head><body>
Put instructions for your study here ....

<?php
//characters to choose from; could also be A, B, C or something else
//for an unequal distribution of elements put elements into set multiple times
$set = "12"; 
$element = substr($set,(rand()%(strlen($set))),1);

//another technique: draw number from an interval
//$element = rand(1,2);  //set interval

// assignment to conditions
if ($element == 1) {$link = 'page1a.htm';}
//if there are three conditions undo the commenting out (i.e. //) of the next line
//elseif ($element == 2) {$link = 'page1c.htm';}
else {$link = 'page1b.htm';}
?>

<form action="generic.php" method="post">
<input type="hidden" name="GHFPvar_next_page" value="<?php echo $link ?>">
<input value="Start the Study" type="submit">
</form></body></html>
