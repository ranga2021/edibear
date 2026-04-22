<?php

class HEADER {
    private $activePage;
    private $adminNavTabArr = array(
        "dashboard"=>array(
            "name"=>"Dashboard",
            "redirect"=>"./dashboard",
            "icon"=>"fas fa-chart-line text-primary"
        ),
        "home-page"=>array(
            "name"=>"Home Page",
            "redirect"=>"./home-page",
            "icon"=>"fas fa-home text-secondary"
        ),
        
        
        "add-pdf"=>array(
            "name"=>"Add Coloring Pages",
            "redirect"=>"./add-pdf",
            "icon"=>"fas fa-palette text-warning"
        ),
        "pdf"=>array(
            "name"=>"Coloring Pages",
            "redirect"=>"./pdf",
            "icon"=>"fas fa-images text-danger"
        ),
        
        "add-books"=>array(
            "name"=>"Add Books & Papers",
            "redirect"=>"./add-books",
            "icon"=>"fas fa-book-open text-warning"
        ),
        "books"=>array(
            "name"=>"Books & Papers",
            "redirect"=>"./books",
            "icon"=>"fas fa-book text-danger"
        ),

        "add-homework"=>array(
            "name"=>"Add Homeworks",
            "redirect"=>"./add-homework",
            "icon"=>"fas fa-edit text-warning"
        ),
        "homework"=>array(
            "name"=>"Homeworks",
            "redirect"=>"./homework",
            "icon"=>"fas fa-tasks text-danger"
        ),
        "add-product"=>array(
            "name"=>"Add Product",
            "redirect"=>"./add-products",
            "icon"=>"fas fa-cart-plus text-warning"
        ),
        "products"=>array(
            "name"=>"Products",
            "redirect"=>"./products",
            "icon"=>"fas fa-box-open text-danger"
        ),
        "manage-product-categories"=>array(
            "name"=>"Product categories",
            "redirect"=>"./manage-product-categories",
            "icon"=>"fas fa-tags text-info"
        ),
        "manage-product-subcategories"=>array(
            "name"=>"Product subcategories",
            "redirect"=>"./manage-product-subcategories",
            "icon"=>"fas fa-sitemap text-info"
        ),

        "add-blog"=>array(
            "name"=>"Add Blog",
            "redirect"=>"./add-blog",
            "icon"=>"fas fa-pen text-warning"
        ),
        "blogs"=>array(
            "name"=>"Blogs",
            "redirect"=>"./blogs",
            "icon"=>"fas fa-newspaper text-danger"
        ),
        "add-event"=>array(
            "name"=>"Add Brave Heart Event",
            "redirect"=>"./add-event",
            "icon"=>"fas fa-flag text-warning"
        ),
        "event"=>array(
            "name"=>"Brave Heart Events",
            "redirect"=>"./event",
            "icon"=>"fas fa-calendar-alt text-warning"
        ),
        "add-ad1"=>array(
            "name"=>"Add Home Ad 1",
            "redirect"=>"./add-ad1",
            "icon"=>"fas fa-ad text-warning"
        ),
        "ad1"=>array(
            "name"=>"Home Ad 1",
            "redirect"=>"./ad1",
            "icon"=>"fas fa-image text-danger"
        ),
        "add-ad2"=>array(
            "name"=>"Add Home Ad 2",
            "redirect"=>"./add-ad2",
            "icon"=>"fas fa-ad text-warning"
        ),
        "ad2"=>array(
            "name"=>"Home Ad 2",
            "redirect"=>"./ad2",
            "icon"=>"fas fa-image text-danger"
        ),
        
        "testimonials"=>array(
            "name"=>"Testimonials",
            "redirect"=>"./testimonials",
            "icon"=>"fas fa-comments text-primary"
        ),
        "orders"=>array(
            "name"=>"Orders",
            "redirect"=>"./order",
            "icon"=>"fas fa-shopping-cart text-primary"
        ),
        "manage-users"=>array(
            "name"=>"Manage Users",
            "redirect"=>"./manage-users",
            "icon"=>"fas fa-user text-dark"
        ),
        "manage-admins"=>array(
            "name"=>"Admins (add / edit)",
            "redirect"=>"./manage-admins",
            "icon"=>"fas fa-user text-secondary"
        ),
        "signup-admin"=>array(
            "name"=>"Sign up admin",
            "redirect"=>"./signup-admin",
            "icon"=>"fas fa-user-plus text-success"
        ),
        "log-out"=>array(
            "name"=>"Log Out",
            "redirect"=>"./logout",
            "icon"=>"fas fa-sign-out-alt text-danger"
        )
    );
    private $userNavTabArr = array(

    "learn" => array(
        "name"=>"LEARN",
        "redirect"=>"./#learn-section"
    ),

    "shop" => array(
        "name"=>"SHOP",
        "redirect"=>"./#honey-market-section"
    ),

    "play" => array(
        "name"=>"PLAY",
        "redirect"=>"./#play-section"
    ),

    "challenge" => array(
        "name"=>"CHALLENGE",
        "redirect"=>"./#challenge-section"
    ),

);

    public function __construct($activePage='') {
        $this->activePage = $activePage;
    }

    public function getActivePage() {
        return $this->activePage;
    }

    public function getActivePageName() {
        return $this->adminNavTabArr[$this->activePage]['name'];
    }

    public function printUserHeader($pageName="", $ogDesc="“edibear” is a website that provides a variety of kids' coloring pages, activity books, relevant model papers, school related study materials and fun activities for developing the abilities of kids. ", $ogImg="img/Web pic/Cover.jpg") {
        // Check if the key exists before trying to access it
    if ($pageName == "" && isset($this->userNavTabArr[$this->activePage])) {
        $pageName = $this->userNavTabArr[$this->activePage]['name'];
    } elseif ($pageName == "") {
        $pageName = "Edibear"; // Default fallback name
    }
        $mainCSS = "css/style.css";
        $mainCSS = $mainCSS . "?" . filemtime("$mainCSS");
        $ediThemeCss = "css/edibear-theme.css?" . @filemtime(__DIR__ . "/../css/edibear-theme.css");
        $html = "
            <meta charset='utf-8'>
            <title>Kids’ Coloring Pages, Activity Books & Study Packs</title>
            <meta content='width=device-width, initial-scale=1.0' name='viewport'>
            <meta name='Title' content='Kids Coloring Pages, Activity Books & Study Packs ' />
            <meta name='description' content='“edibear” is a website that provides a variety of kids coloring pages, activity books, relevant model papers, school related study materials and fun activities for developing the abilities of kids. '>
            <meta name='keywords' content='printable coloring pages for kids, free coloring pages, kids activities, Relevant past papers, model Papers, school related study materials, Fun activities for kids, Developing kids’ abilities, Educational resources for kids, Downloadable kids’ materials, Creative learning for kids, Sinhala Coloring Pages, Tamil Coloring Pages' />
            <meta property='og:title' content='Kids Coloring Pages, Activity Books & Study Packs'/>
            <meta property='og:site_name' content='edibear'/>
            <meta property='og:image' content='https://edibear.com/$ogImg' />
            <meta property='og:url' content='"."https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"."' />
            <meta property='og:description' content='$ogDesc'>
            <link href='./img/Favicon.png' rel='icon'>
            <link rel='preconnect' href='https://fonts.gstatic.com'>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap' rel='stylesheet'> 
            <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css' rel='stylesheet'>
            <link href='lib/owlcarousel/assets/owl.carousel.min.css' rel='stylesheet'>
            <link href='lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css' rel='stylesheet' />
            <link href='$mainCSS' rel='stylesheet'>
            <link rel='stylesheet' href='css/custom.css'>
            <link rel='stylesheet' href='$ediThemeCss'>
        ";
        return $html;
    }

    public function printUserTopBar($logout=false) {
        $logo = "./img/Logo.png";
        $logo = $logo . "?" . filemtime("$logo");
        $html = "
            <div class='container-fluid pt-5' style='position:relative; z-index:2;'>
                <div class='container logocontainer' style='height: ; postion: relative;'>
                    <div class='row logorow'>
                        <div class='col-sm-6 navbarcolumns text-center text-sm-left mb-2 mb-lg-0'>
                            <img class='headerLogo cursor-pointer image-responsive' src='$logo' alt='logo' onclick=location.href='./'>
                        </div>

                        <div class='col-sm-4 navbarcolumns pt-sm-3 text-center mb-2 mb-lg-0' style='display: flex;justify-content: space-evenly;'>
<!---------
                        <span class='nav-col-tab cursor-pointer' onclick=location.href='./index'>Home</span>
                        <span class='nav-col-tab cursor-pointer' onclick=location.href='./blogs'>Blog</span>
                        <span class='nav-col-tab cursor-pointer' onclick=location.href='./shop'>Shop</span>
                        <span class='nav-col-tab cursor-pointer' onclick=location.href='./about'>About</span>
                        --------->
                        


                        </div>

                        <div class='col-sm-2 navbarcolumns navbarsignintext pt-sm-3 text-center text-md-right'>";
                            if (isset($_SESSION['session_tourism_user'])) {
                                if ( $logout ) {
                                    $html .= "<span class='signInText cursor-pointer' onclick=\"localStorage.removeItem('admin_session'); location.href='./logout';\"><i class='pr-1 fas fa-sign-out-alt'></i>Log out</span>";
                                } else {
                                    $html .= "<span class='signInText cursor-pointer' onclick=location.href='./account'><i class='pr-1 fa fa-user'></i>My Account</span>";
                                }
                            } else {
                                $html .= "<span class='signInText cursor-pointer' onclick=location.href='./login'><i class='pr-1 fa fa-user'></i> Sign in</span>";
                            }
        $html .="
                        </div>
                    </div>
                </div>
            </div>
        ";
        return $html;
    }

    public function printUserNav() {
    $logo = "./img/Logo.png";
    $logo = $logo . "?" . filemtime("$logo");

    $html = "
    <style>
        /* Ensure the header wrapper is ALWAYS on top of the carousel */
.edibear-header-wrapper {
    position: relative; 
    z-index: 1000; /* Higher than the carousel */
    background: #fff;
    width: 100%;
}

/* Ensure the green line is visible and not collapsed */
.edibear-topline {
    height: 10px !important;
    background-color: #33a675 !important;
    display: block !important;
    width: 100%;
    position: relative;
    z-index: 1001;
}

/* Adjust the carousel if it overlaps too much */
.HomeCarousel {
    /* Keep absolute value in sync with body.index --edi-home-carousel-lift in css/edibear-theme.css */
    margin-top: -85px;
    z-index: 1;
}

        .edibear-navbar {
            padding: 25px 0px 15px 0px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .edibear-nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 40px;
            gap: 12px;
        }

        .edibear-nav-main {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex: 1;
            min-width: 0;
            gap: 20px;
        }

        .edibear-nav-tools {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .edibear-link {
            text-decoration: none !important;
            color: #606062 !important;
            font-size: 14px;
            font-weight: 400; /* Regular weight, not bold */
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Poppins', sans-serif;
        }

        .edibear-signin {
            text-decoration: none !important;
            color: #33a675 !important;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 2px;
            margin-left: 6px;
        }
    </style>

   <div class='edibear-header-wrapper'>
        <div class='edibear-topline'></div>
        <div class='edibear-navbar'>
            <div class='edibear-nav-container'>
                <div class='edibear-logo'>
                    <a href='./'>
                        <img src='$logo' alt='Edibear' style='height: 55px;'>
                    </a>
                </div>
                <div class='edibear-nav-main'>
                <nav class='edibear-nav-links' id='edibear-nav-drawer' aria-label='Main navigation'>";

    foreach ($this->userNavTabArr as $key=>$subArr) {
        $html .= "<a href='".$subArr['redirect']."' class='edibear-link'>
                    ".$subArr['name']."
                  </a>";
    }

    $html .= "
                </nav>
                <button type='button' class='edibear-nav-toggle' id='edibear-nav-toggle' aria-controls='edibear-nav-drawer' aria-expanded='false' aria-label='Open menu'>
                    <span class='edibear-nav-toggle-bar' aria-hidden='true'></span>
                    <span class='edibear-nav-toggle-bar' aria-hidden='true'></span>
                    <span class='edibear-nav-toggle-bar' aria-hidden='true'></span>
                </button>
                <div class='edibear-nav-tools'>
               <a href='#' onclick='checkCartAccess()' id='cart-icon' class='edibear-link edibear-nav-tool edibear-nav-cart' aria-label='Cart'>
                    <img src='./img/honey_cart_icon.png' class='edibear-nav-cart-img' alt=''>
                    <span id='cart-badge' class='edibear-cart-badge' aria-hidden='true'></span>
               </a>
               <a href='#' id='accountIcon' class='edibear-signin edibear-nav-tool edibear-nav-account' style='display:none;' aria-label='My account'>
                    <i class='fa fa-user' aria-hidden='true'></i>
               </a>
               <a href='./login' id='userAuthBtn' class='edibear-signin edibear-nav-tool edibear-nav-auth'>
                    <i class='fa fa-user' aria-hidden='true'></i><span class='edibear-nav-auth-label'> Sign In</span>
               </a>
                </div>
                </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

    function edibearSyncCartBadge() {
        var el = document.getElementById('cart-badge');
        var link = document.getElementById('cart-icon');
        if (!el) return;
        var raw = localStorage.getItem('cart_count');
        var n = raw ? parseInt(raw, 10) : 0;
        if (isNaN(n) || n < 0) n = 0;
        if (n > 0) {
            el.textContent = n > 99 ? '99+' : String(n);
            el.style.display = 'flex';
            if (link) link.setAttribute('aria-label', 'Cart, ' + n + (n === 1 ? ' item' : ' items'));
        } else {
            el.textContent = '';
            el.style.display = 'none';
            if (link) link.setAttribute('aria-label', 'Cart');
        }
    }
    window.edibearSyncCartBadge = edibearSyncCartBadge;
    edibearSyncCartBadge();
    window.addEventListener('storage', function (e) {
        if (e.key === 'cart_count') edibearSyncCartBadge();
    });

    (function () {
        var wrap = document.querySelector('.edibear-header-wrapper');
        var btn = document.getElementById('edibear-nav-toggle');
        var drawer = document.getElementById('edibear-nav-drawer');
        if (!wrap || !btn || !drawer) {
            return;
        }
        function setOpen(open) {
            wrap.classList.toggle('edibear-nav-menu-open', !!open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        }
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            setOpen(!wrap.classList.contains('edibear-nav-menu-open'));
        });
        document.addEventListener('click', function () {
            setOpen(false);
        });
        drawer.addEventListener('click', function (e) {
            e.stopPropagation();
        });
        drawer.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                setOpen(false);
            });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                setOpen(false);
            }
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                setOpen(false);
            }
        });
    })();

});
        function checkCartAccess() {
           var isLoggedIn = !!localStorage.getItem('user_session');
            if (!isLoggedIn) {
                showLoginPopup();
            } else {
                window.location.href = './cart.php';
            }
        }
        
        function showLoginPopup() {
            // Create popup HTML
            const popup = document.createElement('div');
            popup.id = 'login-popup';
            popup.innerHTML = `
                <div style=\"
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 10000;
                \">
                    <div style=\"
                        background: white;
                        padding: 30px;
                        border-radius: 10px;
                        text-align: center;
                        max-width: 400px;
                        width: 90%;
                        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                    \">
                        <h3 style=\"margin-bottom: 20px; color: #333;\">Please login to continue</h3>
                        <p style=\"margin-bottom: 25px; color: #666;\">You need to be logged in to access your cart.</p>
                        <a href=\"./login\" style=\"
                            display: inline-block;
                            background: #007bff;
                            color: white;
                            padding: 12px 24px;
                            text-decoration: none;
                            border-radius: 5px;
                            font-weight: bold;
                            transition: background 0.3s;
                        \" onmouseover=\"this.style.background='#0056b3'\" onmouseout=\"this.style.background='#007bff'\">Go to Login</a>
                        <br><br>
                        <button onclick=\"closeLoginPopup()\" style=\"
                            background: none;
                            border: none;
                            color: #666;
                            cursor: pointer;
                            text-decoration: underline;
                        \">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);
        }

        function closeLoginPopup() {
            const popup = document.getElementById('login-popup');
            if (popup) {
                popup.remove();
            }
        }
    </script>";
    
    $html .= "
<script>
document.addEventListener('DOMContentLoaded', function() {

    const userSession = localStorage.getItem('user_session');
    const authBtn = document.getElementById('userAuthBtn');
    const accountIcon = document.getElementById('accountIcon');

    if (!authBtn) return; // safety

    if (userSession) {
    
     // ✅ SHOW GREEN USER ICON
    if (accountIcon) {
        accountIcon.style.display = 'inline-block';
        accountIcon.onclick = function() {
            window.location.href = './account?uid=' + userSession;
        };
    }
        // ✅ Logged in → Logout
        authBtn.innerHTML = \"<i class='fa fa-sign-out' aria-hidden='true'></i><span class='edibear-nav-auth-label'>Logout</span>\";
        authBtn.href = '#';

        authBtn.onclick = function(e) {
            e.preventDefault();
            localStorage.removeItem('user_session');
            localStorage.removeItem('session_time');
            window.location.href = './login';
        };

    } else {
      if (accountIcon) accountIcon.style.display = 'none';
        // ❌ Not logged in → Sign In
        authBtn.innerHTML = \"<i class='fa fa-user' aria-hidden='true'></i><span class='edibear-nav-auth-label'>Sign In</span>\";
        authBtn.href = './login';
    }

});
</script>
";

    return $html;
}

    public function printHomeCarousel($carouselDataArr) {
        $active = "active";
        $html = "
            <div class='container-fluid p-0 HomeCarousel'>
                <div id='header-carousel' class='carousel slide' data-ride='carousel'>
                    <div class='carousel-inner'>";
                foreach ( $carouselDataArr as $carouselData ) {
                    $carouselText1 = $carouselData['text1'];
                    $carouselText2 = $carouselData['text2'];
                    $carouselSrc = "./img/carousel/".$carouselData['src'];
                    $carouselSrc = $carouselSrc . "?" . filemtime("$carouselSrc");
                    $html .= "
                        <div class='carousel-item $active'>
                            <img class='w-100' src='$carouselSrc' alt='Image'>
                            <div class='carousel-caption carouselboxeka d-flex flex-column justify-content-end pb-3' style='height: 100%;'>
                                <div class='p-2 p-md-3 d-flex align-items-center flex-column align-items-center' style='max-width: 900px; margin: 0 auto;'>
                                    <span class='text-white mt-2 mt-md-3 mb-1 mb-md-2 carouselText1'>$carouselText1</span>
                                    <h1 class='display-3 text-white mb-2 mb-md-3 carouselText2'>$carouselText2</h1>
                                    <a onclick='goToAyubowan()' class='btn btn-primary rounded homeLetsGoBtn py-md-2 px-md-5 mt-1 mb-1'><b>FIND Edi</b></a>
                                </div>
                            </div>
                        </div>
                    ";
                    $active = "";
                }
        $html .= "
                    </div>";
        // if ( count($carouselDataArr) > 1 ) {
        //     $html .= "
        //         <a class='carousel-control-prev' href='#header-carousel' data-slide='prev'>
        //             <div class='btn btn-dark' style='width: 45px; height: 45px;'>
        //                 <span class='carousel-control-prev-icon mb-n2'></span>
        //             </div>
        //         </a>
        //         <a class='carousel-control-next' href='#header-carousel' data-slide='next'>
        //             <div class='btn btn-dark' style='width: 45px; height: 45px;'>
        //                 <span class='carousel-control-next-icon mb-n2'></span>
        //             </div>
        //         </a>
        //     ";
        // }
        $html .= "      
                </div>
            </div>";
        return $html;
    }

    public function printUserFooter() {
        $html = "
            <div class='container-fluid footer pl-3 pr-3 d-flex flex-column align-items-center'>
                <div class='row mb-4 py-5 justify-content-center'>
                    <div class='text-center footerEmailCustom pl-2 pr-2 pl-md-2 pr-md-2 pl-sm-3 pr-sm-3'>
                        <h1 class='text-danger'>JOIN MY FAMILY</h1>
                        <p style='
    text-align:left;
    color:#666;
    line-height:1.7;
'>
    Don’t miss a single adventure! Sign up for Edi’s newsletter to get new worksheets,
    ‘Brave Heart’ challenge <br>alerts, and special surprises delivered straight to your inbox.
    Come on in — there’s always a seat for you at <br>our table!
</p>
                        <div class='input-group mt-4 px-2 px-sm-5'>
                            <input type='email' class='form-control newsl-border' id='newsletterEmail' style='padding: 25px;' placeholder='Your Email Here'>
                            <div class='input-group-append'>
                                <button class='btn newsl-subscribe-btn px-4' onclick='subscribeNewsletter()' style='background-color:#33a675;'>JOIN NOW</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='row footercontentwidth footer-overlap-row justify-content-center pt-5'>
                    <div class='col-lg-4 col-md-6 mb-5'>
                        <a href='./' class='navbar-brand'>
                            <img class='headerLogo cursor-pointer image-responsive pb-3' src='./img/Logo.png' alt='logo'>
                        </a>
                        <p class='text-justify' style='color: #666; line-height: 1.6; hyphens: auto; -webkit-hyphens: auto; -ms-hyphens: auto;'>
                       Edibear is a world where education meets adventure! From magical resources in our explorer training camp to the 'Brave Heart' challenge,we provide everything your little buddies need to grow. Explore The Honey Market,where we gathered the 
                           world's top educational and entertainment products under one roof.   
                        </p>
                    </div>
                    <div class='col-lg-4 col-sm-6 mb-5' style='margin-top:20px !important'>
                        <div class='d-flex flex-column justify-content-start pl-xl-5 pl-lg-5 pl-md-5 pl-sm-0'>
                        <!---------
                            <a class='mb-2' href='./'><i class='fa fa-angle-right mr-2'></i>Home</a>
                            <a class='mb-2' href='./blogs'><i class='fa fa-angle-right mr-2'></i>Blogs</a>
                        ------->
                        <a class='mb-2' href='./about' style='color:#33a675'><i class='fa fa-angle-right mr-2'></i>Edi’s Story</a>
                        <a class='mb-2' href='./jungle-rules' style='color:#33a675'><i class='fa fa-angle-right mr-2'></i>Jungle Rules</a>
                            
                             <a class='mb-2' href='./privacy-policy' style='color:#33a675'><i class='fa fa-angle-right mr-2'></i>Privacy Policy</a>
                             <a class='mb-2' href='./faq' style='color:#33a675'><i class='fa fa-angle-right mr-2'></i>FAQ</a>
                        </div>
                        <div class=' pl-xl-5 pl-lg-5 pl-md-5 pl-sm-0'>
                            
                            <span>Colombo Road, Gampaha,<br>Sri Lanka.</span><br>
                            <span>Email: info.edibear@gmail.com</span>
                        </div>
                    </div>
                    <div class='col-lg-4 col-sm-6 mb-5 footer-follow-col'>
    <h6 class='text-uppercase mt-4 mb-3 text-center' style='letter-spacing: 2px; color: #333;'>FOLLOW ME</h6>
    <div class='d-flex justify-content-center align-items-center footer-social-icons flex-wrap'>
        <a class='social-icon-box' target='_blank' href='https://www.facebook.com/edibearsworld'>
            <i class='fab fa-facebook-f'></i>
        </a>
        <a class='social-icon-box' target='_blank' href='https://instagram.com/edibearsworld'>
            <i class='fab fa-instagram'></i>
        </a>
        <a class='social-icon-box' target='_blank' href='https://www.youtube.com/channel/UCEMob_TpTUErMEKeK9jiz_w'>
            <i class='fab fa-youtube'></i>
        </a>
        <a class='social-icon-box' target='_blank' href='https://www.pinterest.com/edibearsworld/'>
            <i class='fab fa-pinterest'></i>
        </a>
        <a class='social-icon-box' target='_blank' href='https://www.tiktok.com/@edibearsworld'>
            <img src='./img/tiktok.png' alt='Tiktok'>
        </a>
    </div>
</div>
                </div>
            </div>



            <div class='container-fluid pl-0 pr-0' style='background-color:#004924;'>
                
                    <div class='col-lg-12 text-center '>
                        <p class='copyrighttext text-white mb-0'>Copyright &copy; <a href='./' class='text-white'>edibear</a>. All Rights Reserved.</a>
                        </p>
                        <p class='copyright-small'>All rights of the four pictures of edibear (Edi's) which have used in this edibear.com website is reserved to the <a href='./' class='text-ash'>edibear.com</a></p>
                    </div>
                   
                
            </div>


<!------ same part as above but duplicating for future needs ----------->
            <!----
            <div class='container-fluid pb-4 px-sm-3 px-md-5' style='background-color:#95C523;'>
                <div class='row'>
                    <div class='col-lg-12 text-center text-md-left mb-3 mb-md-0 d-flex'>
                        <p class='copyrighttext text-white'>Copyright &copy; <a href='./' class='text-white'>edibear</a>. All Rights Reserved.</a>
                        </p>
                    </div>
                  
                    <div class='col-lg-6 text-center text-md-right'>
                        <p class='m-0 text-white'>Designed by <a href='' class='text-black'></a>
                        </p>
                    </div>
                  
                </div>
            </div>
            ------>
            

            <a href='#' class='btn btn-lg btn-primary btn-lg-square back-to-top'><i class='fa fa-angle-double-up'></i></a>
            <script src='lib/jquery-3.4.1.min.js'></script>
            <script src='lib/bootstrap.bundle.min.js'></script>
            <script src='lib/easing/easing.min.js'></script>
            <script src='lib/owlcarousel/owl.carousel.min.js'></script>
            <script src='lib/tempusdominus/js/moment.min.js'></script>
            <script src='lib/tempusdominus/js/moment-timezone.min.js'></script>
            <script src='lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js'></script>
            <script src='mail/jqBootstrapValidation.min.js'></script>
            <script src='mail/contact.js'></script>
            <script src='js/main.js'></script>
            <script>
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
                function subscribeNewsletter() {
                    var email = $('#newsletterEmail').val();
                    if ( email != '' ) {
                        $.ajax({
                            type: 'POST',
                            url: 'ajax.php',
                            data: {
                                subscribeNewsletter: email
                            },
                            success: function(html) {
                                $('#subscribeNewsletter').html(html).show();
                            }
                        }); 
                    }
                }
            </script>
            <div id='subscribeNewsletter'></div>
        ";
        return $html;
    }

    public function printAdminHeader($pageName="") {
        $pageName = ($pageName!="") ? $pageName : $this->adminNavTabArr[$this->activePage]['name'];
        $html = "
            <meta charset='utf-8'/>
            <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
            <link rel='apple-touch-icon' sizes='76x76' href='./assets/img/apple-icon.png'>
            <link rel='icon' type='image/png' href='../img/Favicon.png'>
            <title>KIDS’ COLORING PAGES, ACTIVITY BOOKS & STUDY PACKS</title>
            <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet'/>
            <link href='./assets/css/nucleo-icons.css' rel='stylesheet' />
            <link href='./assets/css/nucleo-svg.css' rel='stylesheet' />
            <script src='https://kit.fontawesome.com/42d5adcbca.js' crossorigin='anonymous'></script>
            <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' rel='stylesheet' crossorigin='anonymous' />
            <link id='pagestyle' href='./assets/css/argon-dashboard.css?v=2.0.4' rel='stylesheet' />
            <link href='./assets/css/edibear-admin-theme.css?" . @filemtime(__DIR__ . "/../admin-area/assets/css/edibear-admin-theme.css") . "' rel='stylesheet' />
            <style>
                /* Remove default green header strip on admin pages */
                .min-height-300.bg-primary.position-absolute.w-100 {
                    background: transparent !important;
                }
            </style>
            <script src='./assets/js/plugins/jquery.min.js'></script>
        ";
        return $html;
    }

    public function printAdminNav() {
        $html = "
            <aside class='sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4' id='sidenav-main'>
                <div class='sidenav-header'>
                <i class='fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none' aria-hidden='true' id='iconSidenav'></i>
                <center>
                    <a class='navbar-brand m-0' href='./dashboard'>
                    <img src='../img/Logo.png' style='max-width: 100px;' class='navbar-brand-img h-100' alt='main_logo'>
                    </a>
                </center>  
                </div>
                <hr class='horizontal dark mt-0'>
                <div class='collapse navbar-collapse w-auto' id='sidenav-collapse-main' style='height:100%;'>
                    <ul class='navbar-nav'>";
                    foreach( $this->adminNavTabArr as $key=>$subArr ) {
                        $active = ($this->activePage==$key) ? "active" : "";
                        $html .= "
                            <li class='nav-item'>
                                <a class='nav-link $active' href='".$subArr['redirect']."'>
                                    <div class='icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center'>
                                    <i class='".$subArr['icon']." text-sm'></i>
                                    </div>
                                    <span class='nav-link-text ms-1'>".$subArr['name']."</span>
                                </a>
                            </li>
                        ";
                    }
        $html .="
                    </ul>
                </div>
            </aside>
        ";
        return $html;
    }

    public function printAdminNav2($pageName) {
        $html = "
            <nav class='navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl' id='navbarBlur' data-scroll='false'>
                <div class='container-fluid py-1 px-3'>
                    <nav aria-label='breadcrumb'>
                    <ol class='breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5'>
                        <li class='breadcrumb-item text-sm'><a class='opacity-5 text-white' href='javascript:;'>Pages</a></li>
                    </ol>
                    <h6 class='font-weight-bolder mb-0' style='color: black !important;'>$pageName</h6>
                    </nav>
                    <div class='collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4' id='navbar'>
                    <div class='ms-md-auto pe-md-3 d-flex align-items-center'></div>
                    <ul class='navbar-nav  justify-content-end'>
                        <li class='nav-item d-flex align-items-center'>
                        <a href='./manage-admins' class='nav-link text-white font-weight-bold px-0'>
                            <i class='fa fa-user me-sm-1'></i>
                        </a>
                        </li>
                        <li class='nav-item d-xl-none ps-3 d-flex align-items-center'>
                        <a href='javascript:;' class='nav-link text-white p-0' id='iconNavbarSidenav'>
                            <div class='sidenav-toggler-inner'>
                            <i class='sidenav-toggler-line bg-white'></i>
                            <i class='sidenav-toggler-line bg-white'></i>
                            <i class='sidenav-toggler-line bg-white'></i>
                            </div>
                        </a>
                        </li>
                        <li class='nav-item dropdown px-3 pe-2 d-flex align-items-center'>
                        <a href='javascript:;' class='nav-link text-white p-0' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                            <i class='fa fa-bell cursor-pointer'></i>
                        </a>
                        <ul class='dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4' aria-labelledby='dropdownMenuButton'>
                            <li class='mb-2'>
                            <a class='dropdown-item border-radius-md' href='javascript:;''>
                                <div class='d-flex py-1'>
                                <div class='my-auto'>
                                    <img src='./assets/img/small-logos/logo-spotify.svg' class='avatar avatar-sm bg-gradient-dark  me-3'>
                                </div>
                                <div class='d-flex flex-column justify-content-center'>
                                    <h6 class='text-sm font-weight-normal mb-1'>
                                    <span class='font-weight-bold'>New album</span> by Travis Scott
                                    </h6>
                                    <p class='text-xs text-secondary mb-0'>
                                    <i class='fa fa-clock me-1'></i>
                                    1 day
                                    </p>
                                </div>
                                </div>
                            </a>
                            </li>
                            <li>
                            <a class='dropdown-item border-radius-md' href='javascript:;''>
                                <div class='d-flex py-1'>
                                <div class='avatar avatar-sm bg-gradient-secondary  me-3  my-auto'>
                                    <svg width='12px' height='12px' viewBox='0 0 43 36' version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'>
                                    <title>credit-card</title>
                                    <g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'>
                                        <g transform='translate(-2169.000000, -745.000000)' fill='#FFFFFF' fill-rule='nonzero'>
                                        <g transform='translate(1716.000000, 291.000000)'>
                                            <g transform='translate(453.000000, 454.000000)'>
                                            <path class='color-background' d='M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z' opacity='0.593633743'></path>
                                            <path class='color-background' d='M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z'></path>
                                            </g>
                                        </g>
                                        </g>
                                    </g>
                                    </svg>
                                </div>
                                <div class='d-flex flex-column justify-content-center'>
                                    <h6 class='text-sm font-weight-normal mb-1'>
                                    Payment successfully completed
                                    </h6>
                                    <p class='text-xs text-secondary mb-0'>
                                    <i class='fa fa-clock me-1'></i>
                                    2 days
                                    </p>
                                </div>
                                </div>
                            </a>
                            </li>
                        </ul>
                        </li>
                    </ul>
                    </div>
                </div>
            </nav>
        ";
        return $html;
    }

    public function printAdminFooter() {
        $year = (int) date('Y');
        $html = "
            <footer class='footer pt-3 edibear-admin-footer'>
                <div class='container-fluid'>
                <div class='row align-items-center justify-content-lg-between'>
                    <div class='col-lg-6 mb-lg-0 mb-4'>
                    <div class='copyright text-center text-sm text-lg-start edibear-admin-footer-copy'>
                        © $year, Designed & Developed by
                        <a href='https://groovymark.com' class='font-weight-bold edibear-admin-footer-link' target='_blank' rel='noopener noreferrer'>Groovymark Pvt Ltd</a>.
                    </div>
                    </div>
                    <div class='col-lg-6'>
                    </div>
                </div>
                </div>
            </footer>
        ";
        return $html;
    }

    public function printAdminFooterJS() {
        $html = "
        <!--   Core JS Files   -->
        <script src='./assets/js/core/popper.min.js'></script>
        <script src='./assets/js/core/bootstrap.min.js'></script>
        <script src='./assets/js/plugins/perfect-scrollbar.min.js'></script>
        <script src='./assets/js/plugins/smooth-scrollbar.min.js'></script>
        <script>
            var win = navigator.platform.indexOf('Win') > -1;
            if (win && document.querySelector('#sidenav-scrollbar')) {
                var options = {
                damping: '0.5'
                }
                Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
            }
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        </script>
        <!-- Github buttons -->
        <script async defer src='./assets/js/plugins/buttons.js'></script>
        <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
        <script src='./assets/js/argon-dashboard.min.js?v=2.0.4'></script>
        ";
        return $html;
    }
}