<?php
session_start();
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/class.widgets.php");

$userHeader = new HEADER("");
$user = new USER();
$widgets = new WIDGETS();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader(); ?>
</head>

<body>

<?php echo $userHeader->printUserNav(); ?>
<div class="page-header-bg"></div>

<!-- PAGE START -->
<div class="container-fluid page-header-content">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="edi-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-0">
                <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
            </ol>
        </nav>

        <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>Privacy Policy</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

        <!-- PRIVACY POLICY CONTENT -->
        <div style="min-height:400px;">

            <div class="row mt-4">
                <div class="col-12">
                <div class="edi-policy-content">

                    <p class="edi-policy-date">Effective Date: May 24, 2026</p>

                    <h5 class="edi-policy-heading">1. Introduction & Data Controller</h5>
                    <p>Welcome to Edibear (accessible via edibear.com). We create, curate, and retail physical educational and entertainment products tailored for children from Preschool to Grade 5, while providing free digital learning resources.</p>
                    <p>Your privacy is incredibly important to us. This Privacy Policy explains how we collect, use, protect, and manage your personal information when you navigate our paths, download free worksheets, join our community, or buy treasures from The Honey Market. Under the Sri Lankan Personal Data Protection Act, No. 9 of 2022 (PDPA), Edibear acts as the official Data Controller.</p>

                    <h5 class="edi-policy-heading">2. Special Protection for Children's Privacy</h5>
                    <p>While our digital worksheets and physical materials are designed to delight and educate children, this website is fully built for adult oversight. The Honey Market storefront, account registration, community group ("My Family") participation, and newsletter sign-ups are strictly intended for adults (parents, teachers, or legal guardians).</p>
                    <p>We do not knowingly collect or solicit personal data directly from children under the age of 16. All transaction details, delivery profiles, and community forum accounts must belong to an adult.</p>

                    <h5 class="edi-policy-heading">3. Information We Collect</h5>
                    <p>We only gather the essential details needed to give you and your little buddies an awesome experience. This falls into three categories:</p>
                    <ol>
                        <li>Information You Actively Provide:
                            <ul>
                                <li>Account & Newsletter Profiles: Your name and email address when you create an account or subscribe to Edi's updates and worksheets.</li>
                                <li>Community Data: Any text, activity ideas, or parental stories you intentionally post inside the "My Family" or "Trail of Tales" community areas.</li>
                            </ul>
                        </li>
                        <li>Transaction & Delivery Details (The Honey Market):
                            <ul>
                                <li>Your full name, contact phone number, billing address, and physical shipping address required to deliver your ordered items across Sri Lanka.</li>
                                <li>Payment Details: We support Cash on Delivery (COD) and Direct Bank Transfer. For Bank Transfers, we collect bank reference details or payment receipts sent via WhatsApp (075 5002004) solely to verify payments. We do not collect, process, or store credit card numbers on our website.</li>
                            </ul>
                        </li>
                        <li>Automated Information (Cookies & Analytics):
                            <ul>
                                <li>We use Google Analytics to track anonymized user behavior (such as pages visited, time spent on the site, and traffic sources) to improve our website design.</li>
                                <li>We use temporary cookies to keep our website functioning smoothly—such as remembering the items you placed inside your shopping cart while you continue browsing.</li>
                            </ul>
                        </li>
                    </ol>

                    <h5 class="edi-policy-heading">4. Lawful Basis and Purpose of Processing</h5>
                    <p>In strict compliance with the Sri Lankan PDPA, your data is processed only under clear, lawful justifications:</p>
                    <ul>
                        <li>Performance of a Contract: To pack, process, track, and deliver physical products from The Honey Market straight to your doorstep.</li>
                        <li>Explicit Consent: To send you your free printable learning packs, newsletter updates, and 'Brave Heart' challenge milestones when you choose to join our mailing list.</li>
                        <li>Legitimate Interests: To run, optimize, protect, and improve our website search engines, and to maintain a safe, spam-free community chat.</li>
                        <li>Legal Compliance: To preserve sales records and transaction receipts for tax and business compliance under Sri Lankan commercial laws.</li>
                    </ul>

                    <h5 class="edi-policy-heading">5. Third-Party Sharing & Data Transfers</h5>
                    <p>We love our family, and we never sell, rent, or trade your personal information to outside marketers. We share your information only with trusted partners required to run Edibear's World:</p>
                    <ul>
                        <li>Delivery & Courier Partners: Local Sri Lankan courier networks so they can find your home and deliver your physical goods.</li>
                        <li>E-Commerce Infrastructure: Our secure website hosting platform (Mochahost.com) that keeps our data safe.</li>
                        <li>Analytics Providers: Google LLC (via Google Analytics) to help us see which worksheets and categories are the most popular.</li>
                        <li>Communication Platforms: Secure tools used to deliver your transactional emails, WhatsApp order updates, and newsletters.</li>
                    </ul>

                    <h5 class="edi-policy-heading">6. Data Security & Retention Timeline</h5>
                    <p>We use standard encryption protocols (HTTPS/SSL) to secure your online checkout journey and keep your account details locked safe from bad actors.</p>
                    <p>We keep your shipping and transaction records only as long as required to complete product delivery, handle potential product exchanges (within our 3-day window), and meet legal tax obligations in Sri Lanka. Newsletter and community account profiles are held securely until you choose to delete your account or opt out.</p>

                    <h5 class="edi-policy-heading">7. Your Legal Rights Under the PDPA</h5>
                    <p>As a resident of Sri Lanka, the PDPA grants you explicit control over your personal records. You can exercise these rights at any time by emailing us:</p>
                    <ul>
                        <li>Right of Access: Request a copy of the personal files and information we store about you.</li>
                        <li>Right to Rectification: Instantly update or fix inaccurate shipping addresses, spelling mistakes, or contact numbers.</li>
                        <li>Right to Erasure: Request the complete deletion of your account history, community posts, and contact data from our active databases.</li>
                        <li>Right to Object/Withdraw Consent: Instantly opt out of receiving marketing emails or worksheets by clicking the "Unsubscribe" link at the bottom of any newsletter.</li>
                    </ul>

                    <h5 class="edi-policy-heading">8. Changes to this Privacy Policy</h5>
                    <p>As Edibear's World grows, we might occasionally add new features or update our paths. Any updates to this policy will be posted right here on this page with an updated "Effective Date". We encourage parents to review this page from time to time to stay fully informed.</p>
                    <p><strong>Need to Reach the Data Controller?</strong></p>
                    <p>If you want to update your information, delete your community profile, or have any questions regarding your data privacy, get in touch with Edi's support crew:</p>
                    <ul>
                        <li>Email: info.edibear@gmail.com</li>
                        <li>WhatsApp Support: 075 5002004</li>
                    </ul>

                </div>
                <div class="edi-policy-content mt-4">
                    <img class="img-fluid" src="./img/Web pic/aboutpage.jpg" alt="About">
                </div>
                </div>
            </div>
        </div>
    </div>
   


















        </div>

  
</div>

<!-- FOOTER -->
<?php echo $userHeader->printUserFooter(); ?>

</body>
</html>