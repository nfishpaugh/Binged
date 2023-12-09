<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="navbar-brand-wrapper d-flex justify-content-center">
        <div class="navbar-brand-inner-wrapper d-flex justify-content-between align-items-center w-100">
            <a class="navbar-brand brand-logo" href="index.php"><img id="mainLogo" src="images/logocustom.jpg"
                                                                     alt="logo"/></a>
            <a class="navbar-brand brand-logo-mini" href="index.php"><img src="images/logo-mini.svg" alt="logo"/></a>
            <div>
                <span id="webTitle">Telephile</span>
            </div>

        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <ul class="navbar-nav mr-lg-4 w-100">
            <li class="nav-item nav-search d-none d-lg-block w-100">
                <div class="input-group" id="search">
                    <div class="input-group-append">
                <span class="input-group-text">
                  <i class="mdi mdi-magnify"></i>
                </span>
                    </div>
                    <form action="show_search.php" method="post" id="form" name="form">
                        <input type="text" class="form-control" name="searchbar" id="searchbar"
                               placeholder="Search now">
                        <input type="submit" name="sub" id="sub" hidden>
                    </form>
                    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
                    <script>
                        $(function ())
                        {
                            $("form").submit(function (e) {
                                e.preventDefault();
                                var form = document.getElementById('form');
                                var formData = new FormData(form);
                                $.ajax({
                                    url: 'show_search.php',
                                    method: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    success: function (response) {
                                        alert('Form has been submitted');
                                    },
                                    error: function (xhr, status, error) {
                                        alert('Form was not sent');
                                        console.error(error);
                                    }
                                });
                            });
                        }
                    </script>
                </div>
            </li>
        </ul>
        <ul class="navbar-nav navbar-nav-right">


            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
                    <img src="images/faces/face1.jpg" alt="profile"/>
                    <span class="nav-profile-name"><?php echo $_SESSION[PREFIX . '_username']; ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">

                    <a class="dropdown-item" href="logout.php">
                        <i class="mdi mdi-logout text-primary"></i>
                        Logout
                    </a>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>