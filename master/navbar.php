<!--**********************************
            Nav header end
        ***********************************-->

<!--**********************************
            Header start
        ***********************************-->
<div class="header">
    <div class="header-content clearfix">

       <div class="nav-control">
            <div id="hamburger">
                <span class="toggle-icon"><i class="icon-menu"></i></span>
            </div>
        </div>
        <!-- <div class="header-left">
            <div class="input-group icons">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0 pr-2 pr-sm-3" id="basic-addon1"><i
                            class="mdi mdi-magnify"></i></span>
                </div>
                <input type="search" class="form-control" placeholder="Search Dashboard" aria-label="Search Dashboard">
                <div class="drop-down animated flipInX d-md-none">
                    <form action="#">
                        <input type="text" class="form-control" placeholder="Search">
                    </form>
                </div>
            </div>
        </div>  -->
        <div class="header-right">
            <ul class="clearfix">
                <li class="icons dropdown">
                    <div class="user-icon-wrapper c-pointer position-relative" data-toggle="dropdown">
                        <span class="activity active"></span>
                        <div class="user-icon-circle">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    </div>
                    <div class="drop-down dropdown-profile animated fadeIn dropdown-menu">
                        <div class="dropdown-content-body">
                            <ul>
                                <li>
                                    <a href="profile.php"><i class="icon-user"></i> <span>Profile</span></a>
                                </li>
                                <li>
                                    <a href="#" onclick="confirmLogout(event)"><i class="icon-key"></i> <span>Logout</span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
<!--**********************************
            Header end ti-comment-alt
        ***********************************-->

<style>
/* Hamburger Menu Position */
.nav-control {
    margin-left: 10px;
}

#hamburger {
    cursor: pointer;
    padding: 10px;
    transition: all 0.3s ease;
}

#hamburger:hover {
    transform: scale(1.1);
}

.toggle-icon i {
    font-size: 24px;
    color: #333;
    transition: color 0.3s ease;
}

#hamburger:hover .toggle-icon i {
    color: #ffd700;
}

/* User Icon Styling */
.user-icon-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 30px;
}

.user-icon-circle {
    width: 40px;
    height: 40px;
    background-color: #f0f0f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: 2px solid #e0e0e0;
}

.user-icon-circle:hover {
    background-color: #ffd700;
    border-color: #ffd700;
    transform: scale(1.05);
}

.user-icon-circle i {
    font-size: 18px;
    color: #333;
    transition: color 0.3s ease;
}

.user-icon-circle:hover i {
    color: #000;
}

/* Activity indicator (dot hijau) */
.user-icon-wrapper .activity {
    position: absolute;
    top: 0;
    right: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    z-index: 1;
}

.user-icon-wrapper .activity.active {
    background-color: #28a745;
}

/* Responsive */
@media (max-width: 768px) {
    .nav-control {
        margin-left: 15px;
    }
    
    .user-icon-wrapper {
        margin-right: 15px;
    }
}
</style>

<script>
function confirmLogout(event) {
    event.preventDefault(); // Mencegah link default
    if (confirm("Apakah Anda yakin ingin logout?")) {
        window.location.href = "../logout.php";
    }
}
</script>