<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<title>Monitor</title>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<meta name="author" content="Erwin Aligam - styleshout.com" />
<meta name="description" content="Site Description Here" />
<meta name="keywords" content="keywords, here" />
<meta name="robots" content="index, follow, noarchive" />
<meta name="googlebot" content="noarchive" />

<link rel="stylesheet" type="text/css" media="screen" href="view/css/screen.css" />

</head>
<body>

    <!-- header starts-->
    <div id="header-wrap"><div id="header" class="container_16">

        <h1 id="logo-text"><a href="index.html" title="">AdShow</a></h1>
        <p id="intro">Мониторинг устройств</p>

        <!-- navigation -->
        <div  id="nav">
            <ul>
                <li id="current"><a href="monitor.php">Monitor</a></li>
            </ul>
        </div>

        <div id="header-image"></div>

        <form id="quick-search" action="index.html" method="get" >
            <p>
            <label for="qsearch">Поиск:</label>
            <input class="tbox" id="qsearch" type="text" name="qsearch" value="Поиск..." title="" />
            <input class="btn" alt="Search" type="image" name="searchsubmit" title="Search" src="view/images/search.gif" />
            </p>
        </form>

    <!-- header ends here -->
    </div></div>

    <!-- content starts -->
    <div id="content-outer"><div id="content-wrapper" class="container_16">

        <!-- main -->
        <div id="main" class="grid_12">
            <?= $template ?>

            <div class="clear">&nbsp;</div>
        <!-- main ends -->
        </div>

        <!-- left-columns starts -->
        <div id="left-columns" class="grid_4">

            <div class="grid_4 alpha">

                <?php if (Auth::isLogin()):?>
                <div class="sidemenu">
                    <h3>Меню</h3>
                    <ul>
                        <li><a href="monitor.php">Магазины</a></li>
                        <li><a href="monitor.php?action=devices">Устройства</a></li>
                        <li><a href="monitor.php?action=logout">Выход</a></li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>


        <!-- end left-columns -->
        </div>

    <!-- contents end here -->
    </div></div>

    <!-- footer starts here -->
    <div id="footer-wrapper" class="container_16">

        <div id="footer-bottom">

            <p class="bottom-left">
            &nbsp; &copy;2011 All your copyright info here &nbsp; &nbsp;
            Design by <a href="http://www.styleshout.com/">styleshout</a>
            </p>

            <p class="bottom-right" >
                <a href="index.html">Home</a> |
                <a href="index.html">Sitemap</a> |
                <a href="index.html">RSS Feed</a> |
                <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a> |
                   <a href="http://validator.w3.org/check/referer">XHTML</a>
            </p>

        </div>

    </div>
    <!-- footer ends here -->

</body>
</html>
