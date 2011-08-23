<?php
	$page = $_GET["page"];
	if($page=="") $page="news";
	if(!file_exists($page.".txt"))
	{
		$page = "error";
	}
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Quantus <?php if ($_GET["page"]!=""&&$_GET["page"]!="index") echo " : ".$_GET["page"]; ?></title>
	<meta content='application/xhtml+xml; charset=UTF-8' http-equiv='Content-Type'/>
	<link rel="stylesheet" type="text/css" href="main.css" />
</head>
<body>
<div id="main">

<div id="left">
	<div id="left-top"></div>
	<div id="left-menu">
		<div id="menu">
			<div id="menu-news" class="menu-item<?php if ($page=="news") echo " selected"; ?>">
				<a href="index.php?page=news">News</a>
			</div>
			<div id="menu-info" class="menu-item<?php if ($page=="game-info") echo " selected"; ?>">
				<a href="index.php?page=game-info">Game Info</a>
			</div>
			<div id="menu-forum" class="menu-item<?php if ($page=="forum") echo " selected"; ?>">
				<a href="http://forum.quantusgame.org">Forum</a>
			</div>
			<div id="menu-about" class="menu-item<?php if ($page=="about") echo " selected"; ?>">
				<a href="index.php?page=about">About</a>
			</div>
		</div>
	</div>
	<div id="left-menu-bottom"></div>

</div>

<div id="center">
	<div id="head">
		<a href="index.php">
			<img id="header-image" src="images/logo.png"
					title="go to Quantus home page"
					alt="Quantus" height="65" width="305" />
		</a>
	</div>
	<div id="center-middle">
		<div id="center-middle-top-left"></div>
		<div id="center-middle-top-right"></div>
		<div id="center-middle-top-center"></div>
		<div id="center-middle-center-left">
		<div id="center-middle-center-right">
		<div id="center-middle-center">
			<div id="content">
				<?php
					$file = fopen($page.".txt", "r");
					echo "\r\n";
					//Output a line of the file until the end is reached
					while(!feof($file))
					{
						echo "\t\t\t\t\t\t\t".fgets($file);
					}
					echo "\r\n";
					fclose($file);
				?>
			</div>
			<div class="layout"></div>
		</div>
		</div>
		</div>
		<div id="center-middle-bottom-left"></div>
		<div id="center-middle-bottom-right"></div>
		<div id="center-middle-bottom-center"></div>
	</div>
	<div id="end">
		<div id="end-left"></div>
		<div id="end-right"></div>
	</div>
	<div id="foot">
		<p>
		<a href="http://validator.w3.org/check?uri=referer">
			<img src="http://www.w3.org/Icons/valid-xhtml11-blue"
			alt="Valid XHTML 1.1"
			height="31" width="88" /></a>
		<a href="http://jigsaw.w3.org/css-validator/validator?uri=www.quantusgame.org&amp;profile=css3">
			<img style="border:0;width:88px;height:31px"
			src="http://jigsaw.w3.org/css-validator/images/vcss-blue"
			alt="Valid CSS!" /></a>
		</p>
	</div>
</div>

</div>
</body>
</html>
