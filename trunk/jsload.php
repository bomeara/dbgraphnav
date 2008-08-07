<html>
<head>
<script type="text/javascript">
function ajax(){var xmlHttp;try{xmlHttp=new XMLHttpRequest()}catch(e){try{xmlHttp=new ActiveXObject("Msxml2.XMLHTTP")}catch(e){try{xmlHttp=new ActiveXObject("Microsoft.XMLHTTP")}catch(e){alert("Your browser does not support AJAX!");return false}}}xmlHttp.onreadystatechange=function(){if(xmlHttp.readyState==4){document.getElementById("graph").innerHTML=xmlHttp.responseText}}
xmlHttp.open("GET","main.php?<?php echo $_SERVER['QUERY_STRING'];?>",true);
xmlHttp.send(null)}
</script>
</head>
<body onload="ajax()">
Graph:
<div id="graph">
<img src="ajax-loader.gif">
</div>
</body>
</html>
