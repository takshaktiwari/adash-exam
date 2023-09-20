<nav class="navbar navbar-expand-sm navbar-dark bg-dark py-1 border-bottom">
    <div class="container-fluid">
        <div class="d-flex">
            <a class="navbar-brand" href="javascript:void(0)">Exam</a>
            <button class="btn btn-sm d-md-none d-block" data-bs-toggle="collapse" data-bs-target="#sidebar">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <ul class="navbar-nav ms-auto flex-fill d-flex me-3">
            <li class="nav-item ms-auto">
                <a class="nav-link" href="javascript:void(0)">
                    Time: <b id="time_left_timer"></b>
                </a>
            </li>
        </ul>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mynavbar">

            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0)" data-bs-toggle="modal"
                        data-bs-target="#instructions_modal">
                        <i class="fa-solid fa-info-circle"></i> Instructions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0)" id="fullscreen_btn">
                        <i class="fa-solid fa-expand"></i> Fullscreen
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
