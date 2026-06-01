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
                <li class="breadcrumb-item active" aria-current="page">FAQ</li>
            </ol>
        </nav>

        <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>FAQ</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

        <div class="row mt-4">
                <div class="col-12 text-left" style="text-align: justify;">
                <h1 class="text-primary mb-3">FREQUENTLY ASKED QUESTIONS</h1>
                <div class="px-lg-5 mx-lg-5">
          
<b>What is Edibear?</b><br>
Edibear is an educational and entertainment website that provides everything your children need to learn, play, and grow.
<br><br>

<b>What age groups are Edibear products designed for?</b><br>
Our physical educational materials are specially curated for children from Preschool up to Grade 5.
<br><br>

<b>How can we access the educational materials?</b><br>
They are completely free! Simply select your Language, Grade, and Category, then click the search button. If you are looking for
something more specific, you can also filter by sub-category.
<br><br>

<b>What is The Honey Market?</b><br>
As parents, we know you don't have much time to search everywhere for your children's needs. The Honey Market is a one-stop shop
where you can find the best products all in one place. Just head over to the sign-up area, create your account, and start collecting
your treasures!
<br><br>

<b>Can my child place an order directly?</b><br>
No. The Honey Market and account creation are strictly intended for adults (parents, guardians, or teachers).
<br><br>

<b>What are the payment methods?</b><br>
At this moment, we accept Cash on Delivery (COD) and Direct Bank Transfers only.
<ul>
<li>If you choose COD: Full payment must be made in cash to the courier agent at the time of delivery.</li>
<li>If you choose Bank Transfer: Please deposit the amount into our account and send a photo of your receipt via WhatsApp to 075 5002004.
Please clearly mention your Order ID in the payment reference section, or send the Order ID along with your receipt to our
WhatsApp number. Your goods will be dispatched via courier once the payment is credited to our account.</li>
</ul>
<br><br>

<b>How long will it take for my order to arrive?</b><br>
Delivery is typically completed within 3 to 5 working days from your order date. Please note that slight delays may occasionally occur
due to special circumstances. Please note: We are currently accepting orders and delivering within Sri Lanka only.
<br><br>

<b>How is the shipping rate calculated?</b><br>
The shipping rate is calculated based on the total weight of your order and may vary accordingly.
<br><br>

<b>Do you support instore pickups?</b><br>
We do not support instore pickups at this time. All orders from The Honey Market are delivered directly to your doorstep via courier.
<br><br>

<b>Can I exchange a product?</b><br>
Yes, some products can be exchanged! You must inform us within one day (24 hours) of receiving the product and let us know the
reason for the exchange. After notifying us, you have 3 days from the delivery date to complete the exchange. To be eligible, the item
must be completely unused and in its original condition. Please note that you will need to cover the return courier shipping fee. Some
products may not be eligible for exchange; we always mention this clearly in the product's description area. 
<br><br>

<b>What is your cancellation and refund policy?</b><br>
We do not accept order cancellations once an order has been placed. However, we will fully refund your payment for any orders that
we are unable to fulfill.
<br><br>

<b>What is The Hidden Den?</b><br>
Kids love to play! In The Hidden Den, they can find lots of fun ways to make their playtime more exciting and meaningful. Edi guides
them step-by-step through every fun project.
<br><br>

<b>What is the 'Brave Heart' Challenge?</b><br>
Every kid has a special talent, and it’s time to show it off! Edi posts exciting new challenges in this area for children to participate in and
win special prizes. Because every challenge is unique, the specific rules, guidelines, winner selection, and prizes will be published
alongside each new challenge.
<br><br>

<b>What is My Family?</b><br>
This is Edi's official community area. It’s a warm space where you can discuss everything related to your kids' education and
entertainment, share your knowledge, swap creative activity ideas, and get genuine support from other family members.
<br><br>

(Please read our Ground Rules area for more details)
<br><br>

                    <img class="img-fluid" src="./img/Web pic/aboutpage.jpg" alt="About">
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