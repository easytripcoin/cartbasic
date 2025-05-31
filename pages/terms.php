<?php
// Ensure SITE_URL and PROJECT_ROOT_PATH are available
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__DIR__));
}

if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $subdirectory = ''; // Define or load your subdirectory if applicable
    define('SITE_URL', rtrim($protocol . $host . $subdirectory, '/'));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service | CartBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>

    <main class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="display-4 fw-bold mb-4">Terms of Service for CartBasic System</h1>
                    <p class="lead">Last updated: <?php echo date("F j, Y"); // Or a static date for your last review ?>
                    </p>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-file-text-fill text-primary me-2"></i>1. Acceptance of Terms
                            </h2>
                            <p>By accessing and using CartBasic System (the "Service"), which includes Browse,
                                registering an account, or making purchases, you accept and agree to be bound by the
                                terms and provision of this agreement. In addition, when using these particular
                                services, you shall be subject to any posted guidelines or rules applicable to such
                                services. Any participation in this Service will constitute acceptance of this
                                agreement.</p>
                            <p>If you do not agree to abide by these terms, please do not use this Service.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-person-check-fill text-primary me-2"></i>2. User Accounts
                            </h2>
                            <p>When you create an account with us, you must provide us information that is accurate,
                                complete, and current at all times. Failure to do so constitutes a breach of the Terms,
                                which may result in immediate termination of your account on our Service.</p>
                            <p>You are responsible for safeguarding the password that you use to access the Service and
                                for any activities or actions under your password. You agree not to disclose your
                                password to any third party. You must notify us immediately upon becoming aware of any
                                breach of security or unauthorized use of your account.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-cart-check-fill text-primary me-2"></i>3. Products, Services,
                                and Purchases</h2>
                            <p><strong>Product Information:</strong> We strive to ensure that all details, descriptions,
                                and prices of products appearing on the Service are accurate. However, errors may occur.
                                If we discover an error in the price or description of any goods which you have ordered,
                                we will inform you of this as soon as possible and give you the option of reconfirming
                                your order at the correct price/description or cancelling it.</p>
                            <p><strong>Availability:</strong> All orders for products are subject to availability. We
                                reserve the right to limit the quantity of products we supply; supply only part of an
                                order or to divide up orders.</p>
                            <p><strong>Order Acceptance:</strong> Your receipt of an electronic or other form of order
                                confirmation does not signify our acceptance of your order, nor does it constitute
                                confirmation of our offer to sell. We reserve the right at any time after receipt of
                                your order to accept or decline your order for any reason or for no reason at all.</p>
                            <p><strong>Payment:</strong> (Currently, CartBasic System uses a simulated payment process).
                                In a live environment, by providing payment information, you represent and warrant that
                                the information is accurate, that you are authorized to use the payment method provided,
                                and that you will notify us of changes to payment information. We reserve the right to
                                cancel any order if we are unable to verify or authorize your payment information.</p>
                            <p><strong>Pricing and Promotions:</strong> Prices for products are subject to change
                                without notice. We reserve the right at any time to modify or discontinue a Service (or
                                any part or content thereof) without notice at any time. Special offers or promotions
                                are valid only for the period stated, if any.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-slash-circle-fill text-primary me-2"></i>4. Prohibited Uses &
                                User Conduct</h2>
                            <p>You may use the Service only for lawful purposes and in accordance with these Terms. You
                                agree not to use the Service:</p>
                            <ul>
                                <li>In any way that violates any applicable national or international law or regulation.
                                </li>
                                <li>For the purpose of exploiting, harming, or attempting to exploit or harm minors.
                                </li>
                                <li>To transmit, or procure the sending of, any "junk mail", "chain letter," "spam," or
                                    any other similar solicitation.</li>
                                <li>To impersonate or attempt to impersonate CartBasic System, a CartBasic System
                                    employee, another user, or any other person or entity.</li>
                                <li>To engage in any fraudulent activity, including but not limited to making fraudulent
                                    purchases or using stolen payment information.</li>
                                <li>To interfere with or disrupt the integrity or performance of the Service or data
                                    contained therein, including product listings, pricing, or other users' accounts.
                                </li>
                                <li>To attempt to gain unauthorized access to the Service, user accounts, or computer
                                    systems or networks connected to the Service.</li>
                                <li>In any way that infringes upon the rights of others, or in any way is illegal,
                                    threatening, or harmful.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-key-fill text-primary me-2"></i>5. Intellectual Property</h2>
                            <p>The Service and its original content (excluding content provided by users, such as
                                product information if applicable in the future), features, and functionality are and
                                will remain the exclusive property of CartBasic System and its licensors. The Service is
                                protected by copyright, trademark, and other laws of both [Your Country/Jurisdiction]
                                and foreign countries. Our trademarks and trade dress may not be used in connection with
                                any product or service without the prior written consent of CartBasic System.</p>
                            <p>Product images and descriptions provided on this site are for informational and sales
                                purposes within this Service only. Unauthorized use, reproduction, or distribution of
                                these materials is prohibited.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-x-octagon-fill text-primary me-2"></i>6. Termination</h2>
                            <p>We may terminate or suspend your account and bar access to the Service immediately,
                                without prior notice or liability, under our sole discretion, for any reason whatsoever
                                and without limitation, including but not limited to a breach of the Terms.</p>
                            <p>If you wish to terminate your account, you may simply discontinue using the Service or
                                contact us to request account deletion (if this feature is offered).</p>
                            <p>All provisions of the Terms which by their nature should survive termination shall
                                survive termination, including, without limitation, ownership provisions, warranty
                                disclaimers, indemnity, and limitations of liability.</p>
                        </div>
                    </div>


                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-shield-exclamation text-primary me-2"></i>7. Disclaimer of
                                Warranties</h2>
                            <p>The Service is provided on an "AS IS" and "AS AVAILABLE" basis. The Service is provided
                                without warranties of any kind, whether express or implied, including, but not limited
                                to, implied warranties of merchantability, fitness for a particular purpose,
                                non-infringement, or course of performance. This includes information related to
                                products, such as descriptions or availability.</p>
                            <p>CartBasic System, its subsidiaries, affiliates, and its licensors do not warrant that a)
                                the Service will function uninterrupted, secure or available at any particular time or
                                location; b) any errors or defects will be corrected; c) the Service is free of viruses
                                or other harmful components; or d) the results of using the Service will meet your
                                requirements.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-gavel text-primary me-2"></i>8. Limitation of Liability</h2>
                            <p>In no event shall CartBasic System, nor its directors, employees, partners, agents,
                                suppliers, or affiliates, be liable for any indirect, incidental, special, consequential
                                or punitive damages, including without limitation, loss of profits, data, use, goodwill,
                                or other intangible losses, resulting from (i) your access to or use of or inability to
                                access or use the Service; (ii) any conduct or content of any third party on the
                                Service; (iii) any content or products obtained from the Service; and (iv) unauthorized
                                access, use or alteration of your transmissions or content, whether based on warranty,
                                contract, tort (including negligence) or any other legal theory, whether or not we have
                                been informed of the possibility of such damage, and even if a remedy set forth herein
                                is found to have failed of its essential purpose.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-arrow-repeat text-primary me-2"></i>9. Changes to Terms</h2>
                            <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any
                                time. If a revision is material we will try to provide at least 30 days' notice (or
                                other reasonable notice) prior to any new terms taking effect. What constitutes a
                                material change will be determined at our sole discretion.</p>
                            <p>By continuing to access or use our Service after those revisions become effective, you
                                agree to be bound by the revised terms. If you do not agree to the new terms, please
                                stop using the Service.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-envelope-paper-fill text-primary me-2"></i>10. Contact Us
                            </h2>
                            <p>If you have any questions about these Terms, please contact us via the <a
                                    href="<?php echo SITE_URL; ?>/contact">contact page</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>