<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <button type="button" id="sidebarCollapse" class="btn">
            <i class="fas fa-bars"></i>
        </button>
        <div class="d-flex justify-content-between w-100 align-items-center ms-3">
            <input type="text" class="form-control search-input" placeholder="Search Student Name">
            <div class="d-flex align-items-center">
                <div class="notification-icon me-3">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="language-selector">
                    <i class="fas fa-globe"></i>
                    <span>English</span>
                </div>
                <div class="dropdown ms-3" style="background-color:DarkSlateGray;color:green">
                    <button class="btn btn-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user"></i>
                                <span>គណនី</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-cog"></i>
                                <span>ការកំណត់</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="/SMS/views/login/logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>ចាកចេញ</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.btn-user {
    color: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border: none;
    background: transparent;
    font-family: 'Battambang', cursive;
}

.btn-user:hover, 
.btn-user:focus {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
}

.btn-user i {
    font-size: 1.2rem;
}

.dropdown-toggle::after {
    display: none;
}

.dropdown-menu {
    margin-top: 10px;
    padding: 8px;
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    background: DarkSlateGray;
    min-width: 200px;
    font-family: 'Battambang', cursive;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 16px;
    border-radius: 4px;
    transition: all 0.2s ease;
    color: #fff;
    font-family: 'Battambang', cursive;
}

.dropdown-item i {
    width: 16px;
    text-align: center;
    color: #fff;
}

.dropdown-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.dropdown-divider {
    margin: 8px 0;
    border-color: rgba(255, 255, 255, 0.1);
}

.text-danger {
    color: #ff6b6b !important;
}

.text-danger:hover {
    background: rgba(255, 107, 107, 0.1) !important;
    color: #ff6b6b !important;
}
</style>
