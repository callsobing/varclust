<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lumino - Tables</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/datepicker3.css" rel="stylesheet">
    <link href="css/bootstrap-table.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">

    <!--Icons-->
    <script src="js/lumino.glyphs.js"></script>

    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

</head>

<body>
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#sidebar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#"><span>C4LAB</span>VARCLUST</a>
        </div>

    </div><!-- /.container-fluid -->
</nav>

<div id="sidebar-collapse" class="col-sm-3 col-lg-2 sidebar">
    <form role="search">
        <div class="form-group">
            <input type="text" class="form-control" placeholder="Search">
        </div>
    </form>
    <ul class="nav menu">
        <li><a href="index.html">
                <svg class="glyph stroked home">
                    <use xlink:href="#stroked-home"/>
                </svg>Introduction</a></li>
        <li><a href="submit.html">
                <svg class="glyph stroked dashboard dial">
                    <use xlink:href="#stroked-dashboard-dial"/>
                </svg>Submit Job </a></li>
        <li class="active"><a href="job_status.php">
                <svg class="glyph stroked clipboard with paper">
                    <use xlink:href="#stroked-clipboard-with-paper"/>
                </svg>Job Status</a></li>
        <li><a href="result.php"><svg class="glyph stroked line-graph"><use xlink:href="#stroked-line-graph"></use></svg>Results</a></li>
    </ul>
</div><!--/.sidebar-->

<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
    <?php
        $connection = mysqli_connect("localhost", "callsobing", "wannatobetop", "varclust") or
        die("
            <div class=\"alert bg-danger\" role=\"alert\"><svg class=\"glyph stroked cancel\">
            <use xlink:href=\"#stroked-cancel\"></use></svg>Oooops, Something went wrong. Seems like we have problem connecting to our database..</div><img src=\"img/sorry.jpg\">
            <meta http-equiv=\"refresh\" content=\"5;url=submit.html\">
        ");

        $user_token = $_GET["token"];
        $sql = "SELECT * FROM `jobs` WHERE `user_token`='$user_token'";
        $result = mysqli_query($connection, $sql) or
        die ("
            <div class=\"alert bg-danger\" role=\"alert\"><svg class=\"glyph stroked cancel\">
            <use xlink:href=\"#stroked-cancel\"></use></svg>Oooops, Something went wrong. Seems like we are facing some technical issues during creating new records into database....</div><img src=\"img/sorry.jpg\">
            <meta http-equiv=\"refresh\" content=\"5;url=submit.html\">
        ");

        $sqlarray = array();
        $path_prefix = "/var/www/html/varclust/record/";
        $genotype_success = "/GENOTYPE_SUCCESS";
        $genotype_fail = "/GENOTYPE_FAIL";
        $clustering_success = "/CLUSTERING_SUCCESS";
        $clustering_fail = "/CLUSTERING_FAIL";

        while($row = mysqli_fetch_assoc($result))
        {
            $raw_jobid = $row['job_id'];
            if(file_exists($path_prefix.$raw_jobid.$clustering_success)){
                $row['status'] = "Finished";
                $job_id = "<a href=\"result.php?job_id=".$raw_jobid."\">".$raw_jobid."</a>";
                $row['job_id'] = $job_id;
            } elseif (file_exists($path_prefix.$raw_jobid.$clustering_fail) && file_exists($path_prefix.$raw_jobid.$genotype_success)){
                $row['status'] = "Clustering failed";
            } elseif (file_exists($path_prefix.$raw_jobid.$genotype_success)){
                $row['status'] = "Process clustering";
            } elseif (file_exists($path_prefix.$raw_jobid.$genotype_fail)){
                $row['status'] = "Genotyping failed";
            } else{
                $row['status'] = "In queue";
            }
            $sqlarray[] = $row;
        }

        $fp = fopen('/var/www/html/varclust/user_status/'.$user_token, 'w');
        fwrite($fp, json_encode($sqlarray));
        fclose($fp);
    ?>
    <div class="row">
        <div class="col-lg-12"><font color="#f5f5f5">.</font></div>
    </div><!--/.row-->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">Get your Job status</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <form role="form" method="get" action="job_status.php" >
                            <div class="form-group">
                                <label>Your E-mail address:</label>
                                <input class="form-control" name="token" placeholder="type-in your email address here...">
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-default">Reset</button>
                        </form>
                    </div>
                </div>
            </div><!-- /.col-->
        </div><!-- /.row -->
    </div><!--/.row-->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">Job Status</div>
                <div class="panel-body">
                    <table data-toggle="table" data-url="<?php echo "./user_status/".$user_token ?>"  data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-pagination="true" data-sort-name="name" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="job_id" data-sortable="true">Job ID</th>
                            <th data-field="chromosome" data-sortable="true">Chromosome</th>
                            <th data-field="start"  data-sortable="true">Start</th>
                            <th data-field="end" data-sortable="true">End</th>
                            <th data-field="user_token" data-sortable="true" >E-mail</th>
                            <th data-field="status" data-sortable="true" >Status</th>
                            <th data-field="clustering_m" data-sortable="true" >Clustering Method</th>
                            <th data-field="submit_date" data-sortable="true">Submit Date</th>
                            <th data-field="update_date"  data-sortable="true">Update Date</th>
                            <th data-field="note" data-sortable="no">note</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div><!--/.row-->
</div><!--/.main-->

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/chart.min.js"></script>
<script src="js/chart-data.js"></script>
<script src="js/easypiechart.js"></script>
<script src="js/easypiechart-data.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/bootstrap-table.js"></script>
<script>
    !function ($) {
        $(document).on("click","ul.nav li.parent > a > span.icon", function(){
            $(this).find('em:first').toggleClass("glyphicon-minus");
        });
        $(".sidebar span.icon").find('em:first').addClass("glyphicon-plus");
    }(window.jQuery);

    $(window).on('resize', function () {
        if ($(window).width() > 768) $('#sidebar-collapse').collapse('show')
    })
    $(window).on('resize', function () {
        if ($(window).width() <= 767) $('#sidebar-collapse').collapse('hide')
    })
</script>
</body>

</html>
