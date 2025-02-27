<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS - Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="login.css" rel="stylesheet">
    <style>
    .gmail-btn-container, .facebook-btn-container {
        margin-top: 15px;
        text-align: center;
    }
    
    .btn-gmail, .btn-facebook {
        width: 100%;
        padding: 10px;
        background-color: #ffffff;
        border: 1px solid #dadce0;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .btn-gmail {
        color: #3c4043;
    }

    .btn-facebook {
        color: #1877f2;
    }
    
    .btn-gmail:hover, .btn-facebook:hover {
        background-color: #f8f9fa;
    }
    
    .google-icon, .facebook-icon {
        width: 18px;
        height: 18px;
        margin-right: 8px;
    }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once '../../includes/config.php';
    ?>

    <div class="bg-image"></div>
    
    <div class="login-container">
        <div class="logo">
            <img src="../../asset/img/logo.jpg" alt="SMS Logo">
        </div>
        
        <form id="loginForm" action="process_login.php" method="POST">
            <div class="form-group">
                <input type="text" class="form-control" id="username" name="username" placeholder="User Name" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <select class="language-select">
                <option value="en">English</option>
                <option value="kh">ខ្មែរ</option>
            </select>
            <button type="submit" class="btn-signin">Sign In</button>
            
            <!-- Gmail Sign In Button -->
            <div class="gmail-btn-container">
                <div id="g_id_onload"
                     data-client_id="411254282610-6l7r6fbj8ahu7ocij4e3v71ba7jg5j7g.apps.googleusercontent.com"
                     data-callback="handleGoogleResponse"
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-size="large"
                     data-theme="outline"
                     data-text="sign_in_with"
                     data-shape="rectangular"
                     data-logo_alignment="left">
                </div>
            </div>

            <!-- Facebook Sign In Button -->
            <div class="facebook-btn-container">
                <button type="button" class="btn-facebook" onclick="loginWithFacebook()">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Facebook_Logo_%282019%29.png/1024px-Facebook_Logo_%282019%29.png" alt="Facebook Icon" class="facebook-icon">
                    Continue with Facebook
                </button>
            </div>
        </form>
        
        <div class="footer">
            <p>Copyright 2024 Club Code. All Rights Reserved.<br>
            Version 2024.4.000</p>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show loading state
        Swal.fire({
            title: 'Logging in...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('process_login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Keep the loading animation and redirect after a brief delay
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 800);
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: result.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred. Please try again.'
            });
        });
    });
    </script>
    
    <script src="https://accounts.google.com/gsi/client" async></script>
    <script>
    function handleGoogleResponse(response) {
        console.log('Received Google response');
        
        // Show loading state
        Swal.fire({
            title: 'Logging in...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('process_google_login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                credential: response.credential
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Server response:', data);
            
            if (data.success) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 800);
            } else {
                console.error('Login failed:', data);
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: data.message || 'Gmail login failed. Please try again.',
                    footer: data.debug ? `Debug info: ${JSON.stringify(data.debug)}` : ''
                });
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during Gmail login. Please try again.',
                footer: error.toString()
            });
        });
    }
    </script>

    <!-- Facebook SDK -->
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
    <script>
    // Initialize Facebook SDK
    window.fbAsyncInit = function() {
        FB.init({
            // Your Facebook App ID from developers.facebook.com
            appId      : '1847627409384256', // Replace this with your actual App ID
            cookie     : true,                    // Enable cookies for server-side access
            xfbml      : true,                    // Parse social plugins on this page
            version    : 'v18.0'                  // Use Graph API version 18.0
        });
    };

    function loginWithFacebook() {
        FB.login(function(response) {
            if (response.authResponse) {
                // Show loading state
                Swal.fire({
                    title: 'Logging in...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send access token to your server
                fetch('process_facebook_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        accessToken: response.authResponse.accessToken,
                        userID: response.authResponse.userID
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 800);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: data.message || 'Facebook login failed. Please try again.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred during Facebook login. Please try again.'
                    });
                });
            } else {
                console.log('User cancelled login or did not fully authorize.');
            }
        }, {scope: 'email,public_profile'});
    }
    </script>
</body>
</html>