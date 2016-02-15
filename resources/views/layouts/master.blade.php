<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Unnati Narang's E-Learning Data Access Dashboard">
        <meta name="author" content="Unnati Narang">

        <title>@yield('title')</title>

        <link rel="stylesheet" href="css/vendor/bootstrap.css">
        <link rel="stylesheet" href="css/vendor/sb-admin.css">
        <link rel="stylesheet" href="css/vendor/plugins/morris.css">
        <link href="fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        
    </head>
    <body>
        <div id="wrapper">

            @include('partials.nav')

            <div id="page-wrapper">

                <div class="container-fluid">

                    @yield('content')

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- /#page-wrapper -->

        </div>
        <!-- /#wrapper -->



        <script src="/js/vendor/jquery.js"></script>
        <script src="/js/vendor/bootstrap.js"></script>
        <script src="js/vendor/plugins/morris/raphael.min.js"></script>
        <script src="js/vendor/plugins/morris/morris.min.js"></script>
        <script src="js/vendor/plugins/morris/morris-data.js"></script>
    </body>
</html>
