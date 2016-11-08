<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lumino - Panels</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/datepicker3.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">

    <!--Icons-->
    <script src="js/lumino.glyphs.js"></script>

    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

</head>

<style>
table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 100%;
}

td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
}

tr:nth-child(even) {
    background-color: #dddddd;
}
</style>

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
                </svg>
                Introduction</a></li>
        <li><a href="submit.html">
                <svg class="glyph stroked dashboard dial">
                    <use xlink:href="#stroked-dashboard-dial"/>
                </svg>
                Submit Job </a></li>
        <li><a href="job_status.php">
                <svg class="glyph stroked clipboard with paper">
                    <use xlink:href="#stroked-clipboard-with-paper"/>
                </svg>
                Job Status</a></li>
        <li><a href="result.php">
                <svg class="glyph stroked line-graph">
                    <use xlink:href="#stroked-line-graph"></use>
                </svg>
                Results</a></li>
    </ul>
</div><!--/.sidebar-->

<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Job Submission Status</h1>
        </div>
    </div><!--/.row-->


    <div class="row">
        <div class="col-lg-12">
            <?php
            // Connect to server and select database.
            $link = mysqli_connect("localhost", "callsobing", "wannatobetop", "varclust") or
            die("
                        <div class=\"alert bg-danger\" role=\"alert\"><svg class=\"glyph stroked cancel\">
                        <use xlink:href=\"#stroked-cancel\"></use></svg>Oooops, Something went wrong. Seems like we have problem connecting to our database..</div><img src=\"img/sorry.jpg\">
                        <meta http-equiv=\"refresh\" content=\"5;url=submit.html\">
                    ");

            // Check if user already has more than 2 jobs in queue.
            $token = $_POST["email"];
            $sql = "SELECT * FROM `jobs` WHERE `user_token`='$token'";
            $result = mysqli_query($link, $sql) or
            die ("
                <div class=\"alert bg-danger\" role=\"alert\"><svg class=\"glyph stroked cancel\">
                <use xlink:href=\"#stroked-cancel\"></use></svg>Oooops, Something went wrong. Seems like we are facing some technical issues during selecting new records in the database....</div><img src=\"img/sorry.jpg\">
                <meta http-equiv=\"refresh\" content=\"5;url=submit.html\">
            ");

            $nums = 0;
            $path_prefix = "/var/www/html/varclust/record/";
            $genotype_success = "/GENOTYPE_SUCCESS";
            $genotype_fail = "/GENOTYPE_FAIL";
            $clustering_success = "/CLUSTERING_SUCCESS";
            $clustering_fail = "/CLUSTERING_FAIL";
            while($row = mysqli_fetch_assoc($result))
            {
                $raw_jobid = $row['job_id'];
                if(file_exists($path_prefix.$raw_jobid.$clustering_success)){
                } elseif (file_exists($path_prefix.$raw_jobid.$clustering_fail)){
                } elseif (file_exists($path_prefix.$raw_jobid.$genotype_success)){
                } elseif (file_exists($path_prefix.$raw_jobid.$genotype_fail)){
                } else{
                    $nums += 1;
                }
            }

            if($nums > 1){
                die("
                <div class=\"alert bg-danger\" role=\"alert\"><svg class=\"glyph stroked cancel\">
                <use xlink:href=\"#stroked-cancel\"></use></svg>You already have two jobs running/in queue, please submit later!!</div><img src=\"img/serverLoad.jpg\">
                <meta http-equiv=\"refresh\" content=\"8;url=submit.html\">
            ");
            }


            $chromosome = $_POST["chromosome"];
            $start = $_POST["start"];
            $end = $_POST["end"];
            $email = $_POST["email"];
            $notes = $_POST["notes"];
            $clustering_m = $_POST["clustering_method"];
            $job_id = "varclust_" . md5(uniqid(rand()));

            $sql = "INSERT INTO jobs (job_id, status, chromosome, start, end, user_token, submit_date, update_date, note, clustering_m) VALUES ( '$job_id' , 'submitted', '$chromosome', '$start', '$end', '$email', now(), now(), '$notes', '$clustering_m')";
            mysqli_query($link, $sql) or die ("
                        <div class=\"alert bg-danger\" role=\"alert\"><svg class=\"glyph stroked cancel\">
                        <use xlink:href=\"#stroked-cancel\"></use></svg>Oooops, Something went wrong. Seems like we are facing some technical issues during creating new records into database....</div><img src=\"img/sorry.jpg\">
                        <meta http-equiv=\"refresh\" content=\"5;url=submit.html\">
					");

            exec("mkdir -m 777 /var/www/html/varclust/record/$job_id");
            exec("python3 testSpark.py $chromosome $start $end $job_id $clustering_m > /dev/null 2>&1 &", $output, $status);
            ?>
            <div class="alert bg-success" role="alert">
                <svg class="glyph stroked checkmark">
                    <use xlink:href="#stroked-checkmark"></use>
                </svg>
                Your job has been submitted! <a href="#" class="pull-right"><span
                        class="glyphicon glyphicon-remove"></span></a>
            </div>
            <meta http-equiv="refresh" content="5;url=job_status.php">
            <div class="col-lg-12">
                <label>Job Submission Summary</label>
                <table>
                  <tr>
                    <td>E-mail</td>
                    <td><?php echo $email ?></td>
                  </tr>
                  <tr>
                    <td>Job ID</td>
                    <td><?php echo $job_id ?></td>
                  </tr>
                  <tr>
                    <td>Chromosome</td>
                    <td>chr<?php echo $chromosome ?></td>
                  </tr>
                  <tr>
                    <td>Start</td>
                    <td><?php echo $start ?></td>
                  </tr>
                  <tr>
                    <td>End</td>
                    <td><?php echo $end ?></td>
                  </tr>
                  <tr>
                    <td>Clustering Method</td>
                    <td><?php echo $clustering_m ?></td>
                  </tr>
                  <tr>
                    <td>Notes</td>
                    <td><?php echo $notes ?></td>
                  </tr>
                </table>
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
<script>
    !function ($) {
        $(document).on("click", "ul.nav li.parent > a > span.icon", function () {
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
