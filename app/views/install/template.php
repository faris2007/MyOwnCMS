<!DOCTYPE html>
<html>
<head>
    <title><?=$HEAD['TITLE']?></title>
    <script type="text/javascript">
        var Token = '<?=$this->core->token(TRUE)?>';
        var base_url = '<?=base_url()?>';
        var style_dir = base_url + 'style/default';
        var js_files = ["jquery","jquery.dataTables","functions","jquery.popupWindow"];
        for (js_x in js_files){document.write('<script type="text/javascript" src="' + style_dir + '/js/' + js_files[js_x] + '.js"></' + 'script>');}
	document.write('<link type="text/css" rel="stylesheet" href="' + style_dir + '/style.css">');
    </script>
    
    <!--[if IE 6]>
    <style>
        body {behavior: url("csshover3.htc");}
        #menu li .drop {background:url("img/drop.gif") no-repeat right 8px; 
    </style>
    <![endif]-->
    <meta charset="utf-8" />
	<?=meta($HEAD['META']['META'])?>
    <?=$HEAD['OTHER']?>
</head>

<body>
    <div id="top_bar">
    	<a href="#" id="login_link">Login</a>
        <span style="float:right;"><?=date("F j, Y, g:i a")?></span>
    </div>
	<div id="container">
    	<div id="header"></div>
        <div id="main_menu">
            
        </div>
        <?php if (@$NAV): ?>
        <div id="nav">
            <ul>
                <li>&rsaquo;</li>
                <li><a href="<?=base_url()?>">Home</a></li>
                <?php foreach($NAV as $key => $value): ?>
                <li>&rsaquo;</li>
                <li><a href="<?=$key?>"><?=$value?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <div id="main_content">
			<?=$CONTENT?>
            <br />
            <br />
        </div>
        <div id="footer">
            <span>Copyright &copy; 2013 Saudi Technical Design.</span>
            <span><?=@$DEVELOPMENT?></span>
        </div>
    </div>
</body>

</html>
