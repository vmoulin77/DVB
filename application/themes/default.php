<!DOCTYPE html>
<html> 
    <head>
        <title><?php echo $title; ?></title>
        <meta charset="<?php echo $charset; ?>">
<?php foreach($css as $url): ?>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $url; ?>" />
<?php endforeach; ?>

    </head>

    <body>
        <div class="container">
            <header class="row">
                <div class="col-lg-12">
                    <h1>Doctor Vocab Backoffice</h1>
                </div>
            </header>
            <div class="row">
                <nav class="col-lg-2">
                    <?php echo $menu; ?>
                </nav>
                <section class="col-lg-10">
                    <?php echo $output; ?>
                </section>
            </div>
            <div class="spacer-2em"></div>
            <footer class="row">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            Copyright &copy; 2015 Vincent MOULIN
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <div id="generic_dialog_content"></div>
        <div id="loader"></div>
        
<?php foreach($js as $url): ?>
        <script type="text/javascript" src="<?php echo $url; ?>"></script> 
<?php endforeach; ?>

    </body>

</html>