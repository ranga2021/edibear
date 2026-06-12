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
                <li class="breadcrumb-item active" aria-current="page">Ground Rules</li>
            </ol>
        </nav>

        <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>Ground Rules</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

        <!-- Content -->
        <div class="row mt-4">
            <div class="col-12 text-justify">
                <p><strong>Effective Date:</strong> May 24, 2026</p>
                <p>Welcome to the jungle, Explorer! Edibear's World (accessible via edibear.com) is an adventurous, educational ecosystem designed to help little buddies learn, play, and grow.</p>
                <p>These Jungle Rules govern your use of our website, the free learning resource search engine, the community areas, and your purchases from The Honey Market. By browsing our paths, downloading our resources, or securing gear from our shop, you (the "Explorer" or "Parent") agree to follow these rules. If you do not agree to the Jungle Rules, please do not step onto the trail!</p>

                <b>1. The Trail Guide (Eligibility & Adult Oversight)</b>
                <ul>
                    <li><strong>For the Little Buddies:</strong> Our free downloadable study packs, worksheets, and step-by-step playtime guides are meant for kids from Preschool up to Grade 5 to enjoy under supervision.</li>
                    <li><strong>For the Big Explorers:</strong> The Honey Market, account registration, community group participation ("My Family"), and checkout systems are strictly restricted to adults (parents, legal guardians, or teachers) who are at least 18 years old. Minors are not permitted to create accounts or make purchases independently. By placing an order, you confirm that you are an adult completing the purchase on behalf of a child.</li>
                </ul>

                <b>2. Guarding the Treasure (Intellectual Property Rights)</b>
                <ul>
                    <li><strong>Edi's Property:</strong> Everything you see in this jungle—including the character design of Edi and his friends, custom-designed worksheets in the training camp, the "Brave Heart" imagery, text, and logos—is owned exclusively by edibear.com and protected by copyright laws.</li>
                    <li><strong>Personal Use Only:</strong> You are given a personal, non-transferable license to download and print our free educational resources for your own children or classroom students.</li>
                    <li><strong>Strict Prohibitions:</strong> You are not allowed to copy, alter, redistribute, re-upload to other sites, or resell any Edibear materials for commercial gain. Keep the treasure safe!</li>
                </ul>

                <b>3. Trading at The Honey Market (Product Details & Pricing)</b>
                <ul>
                    <li><strong>Currency & Location:</strong> All prices displayed across The Honey Market are listed in Sri Lankan Rupees (LKR). We currently accept orders and deliver within the borders of Sri Lanka only.</li>
                    <li><strong>Price Updates:</strong> We reserve the right to change prices for physical materials and gear without prior notice.</li>
                    <li><strong>Visual Accuracy:</strong> We do our absolute best to showcase colors, dimensions, and features of our physical products accurately. However, because screen displays vary, we cannot guarantee that the physical item will perfectly match what you see on your device.</li>
                </ul>

                <b>4. Supply Drop Logistics (Payment, Shipping & Order Rules)</b>
                <ul>
                    <li><strong>Payment Paths:</strong> We accept Cash on Delivery (COD) and Direct Bank Transfers only.
                        <ul>
                            <li><strong>COD:</strong> Full cash payment must be handed to the courier agent upon arrival at your doorstep.</li>
                            <li><strong>Bank Transfer:</strong> If you prefer to pay via Bank Transfer, please deposit the total amount into our specified bank account. To verify your payment and avoid any delivery delays, you must send a photo of your deposit receipt along with your Order ID via WhatsApp to 075 5002004. When making the deposit, please clearly mention your Order ID in the payment reference section; if you forget to do so, simply send the Order ID together with your receipt image to our WhatsApp number. Please note that your goods will be handed over to the courier and dispatched only after the full payment has cleared and credited to our account.</li>
                        </ul>
                    </li>
                    <li><strong>Special Vouchers:</strong> From time to time, Edibear may issue special voucher codes for discounts or promotions. These vouchers must be applied at checkout before completing your order, cannot be exchanged for cash, and are subject to specific expiry dates or usage terms published alongside the code.</li>
                    <li><strong>Order Adjustments:</strong> We reserve the right to refuse or cancel any order due to unexpected stock shortages, delivery area limitations, or pricing errors. If an order is canceled after a Bank Transfer is completed, a full refund will be sent back to your bank account.</li>
                    <li><strong>Shipping & Weights:</strong> Delivery fees are dynamically calculated based on the weight of the total order. Packages are shipped via local courier and typically arrive within 3 to 5 working days.</li>
                </ul>

                <b>5. Trail Adjustments (Cancellations & Exchanges)</b>
                <ul>
                    <li><strong>No Cancellations:</strong> Once an order is locked in and placed through our system, we do not accept cancellations.</li>
                    <li><strong>Strict Exchange Timeline:</strong> If you order the wrong item by mistake, you must report the issue and the reason for exchange within one day (24 hours) of receiving the package. From there, you have 3 days to ensure the completely unused, pristine item is sent back to us.</li>
                    <li><strong>Courier Fees:</strong> The Explorer is entirely responsible for covering the return courier shipping fees. Please note that certain promotional items or specific goods are exempt from exchanges; these are always noted clearly in their shop descriptions.</li>
                </ul>

                <b>6. Code of the Jungle (Community Guidelines for "My Family")</b>
                <p>Our "My Family" community space is a sanctuary for parents and educators to support one another. While running wild in the community, you must obey these basic tribal rules:</p>
                <ul>
                    <li><strong>Be Kind:</strong> No bullying, harassment, or harsh language.</li>
                    <li><strong>Stay Focused:</strong> Keep posts relevant to children's education, growth, and creative entertainment.</li>
                    <li><strong>Protect Privacy:</strong> Do not post personal identification details (like home addresses or phone numbers) in public discussion spaces.</li>
                    <li><strong>No Advertising:</strong> Do not spam the community with outside links, unauthorized promotions, or secondary marketplaces.</li>
                    <li><em>Edibear reserves the right to remove any explorer from the campfire who violates these community expectations.</em></li>
                </ul>

                <b>7. Disclaimer of Adventure (Limitation of Liability)</b>
                <p>While Edi guides your little buddies step-by-step through creative play, crafts, and activities, parents are always responsible for physical safety. Edibear is not responsible for any mishaps, scissor slips, or minor accidents that happen during hands-on home activities. We provide our platform and digital search resources on an "as-is" basis without warranties of any kind.</p>

                <b>8. Governing Law</b>
                <p>These Jungle Rules are built under and governed by the commercial laws of the Democratic Socialist Republic of Sri Lanka. Any disputes regarding our services will be handled within the jurisdiction of the local courts.</p>

                <b>9. Need to Reach the Base Camp?</b>
                <p>If you have any questions about these Jungle Rules, need help tracking an order, or want clarification on an exchange, don't hesitate to reach out to Edi's support crew:</p>
                <ul>
                    <li>Email: <a href="mailto:info.edibear@gmail.com">info.edibear@gmail.com</a></li>
                    <li>WhatsApp Support: 075 5002004</li>
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- FOOTER -->
<?php echo $userHeader->printUserFooter(); ?>

</body>
</html>