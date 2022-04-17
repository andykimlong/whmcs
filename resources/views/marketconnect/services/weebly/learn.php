<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

$this->layout("layouts/learn", $serviceOffering);
$this->start("nav-tabs");
echo "<li class=\"active\" role=\"presentation\">\n    <a aria-controls=\"about\" data-toggle=\"tab\" href=\"#about\" role=\"tab\">About</a>\n</li>\n<li role=\"presentation\">\n    <a aria-controls=\"features\" data-toggle=\"tab\" href=\"#features\" role=\"tab\">Features</a>\n</li>\n<li role=\"presentation\">\n    <a aria-controls=\"lite\" data-toggle=\"tab\" href=\"#lite\" role=\"tab\">SiteLock Lite</a>\n</li>\n<li role=\"presentation\">\n    <a aria-controls=\"pricing\" data-toggle=\"tab\" href=\"#pricing\" role=\"tab\">Pricing</a>\n</li>\n<li role=\"presentation\">\n    <a aria-controls=\"faq\" data-toggle=\"tab\" href=\"#faq\" role=\"tab\">FAQ</a>\n</li>\n";
$this->end();
$this->start("content-tabs");
echo "<div class=\"tab-pane active\" id=\"about\" role=\"tabpanel\">\n    <div class=\"content-padded sitelock\">\n        <h3>Website security & malware protection for your customers</h3>\n        <h4>Sitelock's Daily Vulnerability Scanning identifies vulnerabilities and fixes them automatically</h4>\n        <br>\n        <img src=\"../assets/img/marketconnect/sitelock/cyberattacks.png\" height=\"45\" class=\"pull-left\" style=\"padding-right:25px;\">\n        <p>Cyber attacks are on the rise, increasing by 16% from Q2 to Q3 2017, so protecting your website from attacks is more important than ever. Offer your customers industry leading protection with SiteLock.</p>\n        <div style=\"margin:20px 0;padding:10px;background-color:#eee;font-size:0.95em;text-align:center;\">\n            In a recent survey, 70% of customers who responded said they thought website security was their web host's responsibility.\n        </div>\n\n        <div class=\"row icons text-center\">\n            <div class=\"col-sm-3\">\n                <img src=\"../assets/img/marketconnect/sitelock/calendar.png\">\n                <br>\n                The average website is attacked 59 times per day.\n            </div>\n            <div class=\"col-sm-3\">\n                <img src=\"../assets/img/marketconnect/sitelock/files.png\">\n                <br>\n                Malware infects 2,573 sites on average per week.\n            </div>\n            <div class=\"col-sm-3\">\n                <img src=\"../assets/img/marketconnect/sitelock/search.png\">\n                <br>\n                Visitor attacks account for 14.6% of malware infections.\n            </div>\n            <div class=\"col-sm-3\">\n                <img src=\"../assets/img/marketconnect/sitelock/wordpress.png\">\n                <br>\n                57% of WordPress hacks are done with the latest version.\n            </div>\n        </div>\n\n        <p><small><strong>About Sitelock</strong><br>Sitelock&trade;, the global leader in website security, is the only security solution to offer complete, cloud-based website protection. Its 360-degree monitoring finds and fixes threats, prevents future attacks, accelerates website performance and meets PCI compliance standards for businesses and websites of all sizes. Founded in 2008, SiteLock protects over 12 million websites worldwide</small></p>\n\n    </div>\n</div>\n<div class=\"tab-pane\" id=\"features\" role=\"tabpanel\">\n    <div class=\"content-padded sitelock\">\n\n        <p>A range of features designed to protect both your website and your business’ reputation:</p>\n\n        <div class=\"row\">\n            <div class=\"col-md-6\">\n                <div class=\"feature-wrapper\">\n                    <i class=\"fas fa-search fa-fw\"></i>\n                    <div class=\"content\">\n                        <h4>Malware Scan</h4>\n                        <p>Proactively monitors for and alerts you about any malware that is detected on your website.</p>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-6\">\n                <div class=\"feature-wrapper\">\n                    <i class=\"fas fa-wrench fa-fw\"></i>\n                    <div class=\"content\">\n                        <h4>Automatic malware removal</h4>\n                        <p>If a scan finds anything, SiteLock will safely remove any known malware automatically.</p>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-md-6\">\n                <div class=\"feature-wrapper\">\n                    <i class=\"fas fa-code fa-fw\"></i>\n                    <div class=\"content\">\n                        <h4>Vulnerability Scan</h4>\n                        <p>Automatically checks your applications to ensure they're up-to-date and secured against known vulnerabilities.</p>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-6\">\n                <div class=\"feature-wrapper\">\n                    <i class=\"far fa-file-code fa-fw\"></i>\n                    <div class=\"content\">\n                        <h4>OWASP Protection</h4>\n                        <p>Get protection against the top 10 web app security flaws as recognised by OWASP, the Open Web Application Security Project.</p>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-md-6\">\n                <div class=\"feature-wrapper\">\n                    <i class=\"fas fa-trophy fa-fw\"></i>\n                    <div class=\"content\">\n                        <h4>SiteLock™ Trust Seal</h4>\n                        <p>Give your visitors added confidence by showing your website is protected by SiteLock.</p>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-6\">\n                <div class=\"feature-wrapper\">\n                    <i class=\"fas fa-shield-alt fa-fw\"></i>\n                    <div class=\"content\">\n                        <h4>Firewall</h4>\n                        <p>The TrueShield™ Web Application Firewall protects your website against hackers and attacks.</p>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-md-6\">\n                <div class=\"feature-wrapper\">\n                    <i class=\"fas fa-lock fa-fw\"></i>\n                    <div class=\"content\">\n                        <h4>Protect your reputation</h4>\n                        <p>Daily scans help detect malware early before search engines have a chance to find it and blacklist your site.</p>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-6\">\n                <div class=\"feature-wrapper\">\n                    <i class=\"fas fa-star fa-fw\"></i>\n                    <div class=\"content\">\n                        <h4>Fast automated setup</h4>\n                        <p>Instant and fully automated setup gives you protection immediately without anything to install.</p>\n                    </div>\n                </div>\n            </div>\n        </div>\n\n    </div>\n</div>\n<div class=\"tab-pane\" id=\"lite\" role=\"tabpanel\">\n    <div class=\"content-padded sitelock\">\n\n        <img src=\"../assets/img/marketconnect/sitelock/free.png\" class=\"pull-right\">\n\n        <h3>Introducing the SiteLock Lite Plan</h3>\n        <h2>Available <u>exclusively</u> to WHMCS powered web hosts</h2>\n\n        <p>Offer your customers the ability to try SiteLock <strong>free of charge</strong> with the SiteLock Lite plan.</p>\n\n        <p>SiteLock Lite includes the following features:</p>\n\n        <div class=\"row lite-features\">\n            <div class=\"col-sm-6\">\n                <i class=\"fas fa-check\"></i> Daily Malware Scanning\n            </div>\n            <div class=\"col-sm-6\">\n                <i class=\"fas fa-check\"></i> Daily Blacklist Monitoring\n            </div>\n            <div class=\"col-sm-6\">\n                <i class=\"fas fa-check\"></i> SiteLock Risk Score\n            </div>\n            <div class=\"col-sm-6\">\n                <i class=\"fas fa-check\"></i> Scanning for up to 5 Pages\n            </div>\n        </div>\n\n        <div class=\"alert alert-info\">\n            SiteLock Lite can be added to any existing hosting plan and new hosting orders. You also have the option to pre-select it by default for all new shared and reseller web hosting orders.\n        </div>\n\n        <p><small>* SiteLock Lite may only be offered as a bundle offering with web hosting service plans. SiteLock Lite may not be offered as a standalone service. Access to SiteLock Lite may be revoked at any time.</small></p>\n\n    </div>\n</div>\n<div class=\"tab-pane\" id=\"pricing\" role=\"tabpanel\">\n    <div class=\"content-padded sitelock pricing\">\n\n        ";
if ($feed->isNotAvailable()) {
    echo "            <div class=\"pricing-login-overlay\">\n                <p>To view pricing, you must first register or login to your MarketConnect account.</p>\n                <button type=\"button\" class=\"btn btn-default btn-sm btn-login\">Login</button> <button type=\"button\" class=\"btn btn-default btn-sm btn-register\">Create Account</button>\n            </div>\n        ";
}
echo "\n        <table class=\"table table-pricing table-bordered table-striped\">\n            <thead>\n                <tr>\n                    <th></th>\n                    <th>FIND</th>\n                    <th>FIX</th>\n                    <th>DEFEND</th>\n                    <th>EMERGENCY</th>\n                </tr>\n            </thead>\n            <tbody>\n                <tr>\n                    <td>Reputation Management</td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Blacklist Monitoring</td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Network Scan (Port Scan)</td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Verifiable Trust Seal</td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>SiteLock Risk Assessment</td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Spam Verification</td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Business Verification</td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Platform Scan (WordPress)</td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>PAGES SCANNED</td>\n                    <td>25</td>\n                    <td>500</td>\n                    <td>500</td>\n                    <td>Unlimited</td>\n                </tr>\n                <tr>\n                    <td>Daily Malware Scan</td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>SQL Injection Scan</td>\n                    <td>One Time</td>\n                    <td>Daily</td>\n                    <td>Daily</td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Cross Site Scripting (XSS) Scan</td>\n                    <td>One Time</td>\n                    <td>Daily</td>\n                    <td>Daily</td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Website Application Scan</td>\n                    <td>One Time</td>\n                    <td>Daily</td>\n                    <td>Daily</td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Automatic Malware Removal</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>One Time</td>\n                </tr>\n                <tr>\n                    <td>Daily FTP Scanning</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>File Change Monitoring</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Web Application Firewall</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Bad Bot Blocking</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>SSL Support</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>OWASP Top 10 Threat Protection</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>SQL Injection Prevention</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Cross Site Scripting Prevention</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Light DDoS Protection</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                <tr>\n                    <td>Fine-grained security settings</td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-times\"></i></td>\n                    <td><i class=\"fas fa-check\"></i></td>\n                    <td>N/A</td>\n                </tr>\n                ";
$pricingMatrix = $feed->getPricingMatrix([WHMCS\MarketConnect\Promotion\Service\Sitelock::SITELOCK_FIND, WHMCS\MarketConnect\Promotion\Service\Sitelock::SITELOCK_FIX, WHMCS\MarketConnect\Promotion\Service\Sitelock::SITELOCK_DEFEND, WHMCS\MarketConnect\Promotion\Service\Sitelock::SITELOCK_EMERGENCY]);
echo "                ";
foreach ($pricingMatrix[WHMCS\MarketConnect\Promotion\Service\Sitelock::SITELOCK_FIND] as $term => $data) {
    echo "                    <tr>\n                        <td>";
    echo (new WHMCS\Billing\Cycles())->getNameByMonths($term);
    echo "</td>\n                        ";
    foreach ($pricingMatrix as $product) {
        echo "                            ";
        foreach ($product as $xterm => $xdata) {
            echo "                                ";
            if ($term == $xterm) {
                echo "                                    ";
                if ($xdata) {
                    echo "                                        <td>\n                                            Your Cost: \$";
                    echo $xdata["price"];
                    echo "<br>\n                                            <small><strong>RRP: \$";
                    echo $xdata["recommendedRrp"];
                    echo "</strong></small>\n                                        </td>\n                                    ";
                } else {
                    echo "                                        <td>-</td>\n                                    ";
                }
                echo "                                ";
            }
            echo "                            ";
        }
        echo "                        ";
    }
    echo "                    </tr>\n                ";
}
echo "            </tbody>\n        </table>\n\n    </div>\n</div>\n<div class=\"tab-pane\" id=\"faq\" role=\"tabpanel\">\n    <div class=\"content-padded sitelock faq\">\n\n        <div class=\"panel-group faq\" id=\"accordion\" role=\"tablist\" aria-multiselectable=\"true\">\n          <div class=\"panel panel-default\">\n            <div class=\"panel-heading\" role=\"tab\" id=\"headingOne\">\n              <h4 class=\"panel-title\">\n                <a role=\"button\" data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapseOne\" aria-expanded=\"true\" aria-controls=\"collapseOne\">\n                  What is SiteLock?\n                </a>\n              </h4>\n            </div>\n            <div id=\"collapseOne\" class=\"panel-collapse collapse in\" role=\"tabpanel\" aria-labelledby=\"headingOne\">\n              <div class=\"panel-body\">\n                SiteLock provides simple, fast and affordable website security to websites of all sizes. Founded in 2008, the company protects over 12 million websites worldwide. The SiteLock cloud-based suite of products offers automated website vulnerability detection and malware removal, DDoS protection, website acceleration, website risk assessments, and PCI compliance.\n              </div>\n            </div>\n          </div>\n          <div class=\"panel panel-default\">\n            <div class=\"panel-heading\" role=\"tab\" id=\"headingTwo\">\n              <h4 class=\"panel-title\">\n                <a class=\"collapsed\" role=\"button\" data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapseTwo\" aria-expanded=\"false\" aria-controls=\"collapseTwo\">\n                  What is the difference between the security I am providing as a host and the protection SiteLock provides?\n                </a>\n              </h4>\n            </div>\n            <div id=\"collapseTwo\" class=\"panel-collapse collapse\" role=\"tabpanel\" aria-labelledby=\"headingTwo\">\n              <div class=\"panel-body\">\n                As a hosting provider you're responsible for the security and functionality of the server hosting your customers' sites, but not their individual sites.SiteLock provides comprehensive website security for all websites within a shared or dedicated server environment.<br><br>\n                To learn more about the differences, <a href=\"https://vimeo.com/209592176\" target=\"_blank\">watch the 'SiteLock and Hosting Provider Partnership Explained' video by clicking here</a>.\n              </div>\n            </div>\n          </div>\n          <div class=\"panel panel-default\">\n            <div class=\"panel-heading\" role=\"tab\" id=\"headingThree\">\n              <h4 class=\"panel-title\">\n                <a class=\"collapsed\" role=\"button\" data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapseThree\" aria-expanded=\"false\" aria-controls=\"collapseThree\">\n                  Does SiteLock work with any hosting, server and software?\n                </a>\n              </h4>\n            </div>\n            <div id=\"collapseThree\" class=\"panel-collapse collapse\" role=\"tabpanel\" aria-labelledby=\"headingThree\">\n              <div class=\"panel-body\">\n                Yes, SiteLock is compatible with any hosting environment.\n              </div>\n            </div>\n          </div>\n          <div class=\"panel panel-default\">\n            <div class=\"panel-heading\" role=\"tab\" id=\"headingFour\">\n              <h4 class=\"panel-title\">\n                <a class=\"collapsed\" role=\"button\" data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapseFour\" aria-expanded=\"false\" aria-controls=\"collapseFour\">\n                  Will SiteLock impact hosting resources/does software need to be installed on the server?\n                </a>\n              </h4>\n            </div>\n            <div id=\"collapseFour\" class=\"panel-collapse collapse\" role=\"tabpanel\" aria-labelledby=\"headingFour\">\n              <div class=\"panel-body\">\n                No. During a website scan, SiteLock downloads the relevant files via FTP to a secure server and perform scans there. There is no impact to the website content, code, bandwidth, or server resources and no software needs to be installed on the server.\n              </div>\n            </div>\n          </div>\n          <div class=\"panel panel-default\">\n            <div class=\"panel-heading\" role=\"tab\" id=\"headingFive\">\n              <h4 class=\"panel-title\">\n                <a class=\"collapsed\" role=\"button\" data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapseFive\" aria-expanded=\"false\" aria-controls=\"collapseFive\">\n                  What is the difference between the firewall I currently provide and the firewall that SiteLock offers?\n                </a>\n              </h4>\n            </div>\n            <div id=\"collapseFive\" class=\"panel-collapse collapse\" role=\"tabpanel\" aria-labelledby=\"headingFive\">\n              <div class=\"panel-body\">\n                Servers have different layers for communication. The OSI model has 7 layers. The firewall provided by most hosting companies is going to be focused on layers 3 and 4 of the OSI model. These layers are set to always allow http requests from port 80. Port 80 is set to always allow HTTP requests from Web clients. This is what allows a site to be visible to the internet. However, malware attacks today can be sent via an HTTP request through port 80. The difference between a safe request and a malicious request is the content being sent. A host's firewall does not examine the content being sent via port 80, it is merely interested on ensuring the the request is the correct type through the right port. If it is an HTTP request, it will be allowed through port 80. A web application firewall (WAF) works at Layer 7 of the OSI model, which is the application layer. A WAF utilizes a general rule set to determine if the content being sent is safe or malicious.\n              </div>\n            </div>\n          </div>\n          <div class=\"panel panel-default\">\n            <div class=\"panel-heading\" role=\"tab\" id=\"headingSix\">\n              <h4 class=\"panel-title\">\n                <a class=\"collapsed\" role=\"button\" data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapseSix\" aria-expanded=\"false\" aria-controls=\"collapseSix\">\n                  What is SMART?\n                </a>\n              </h4>\n            </div>\n            <div id=\"collapseSix\" class=\"panel-collapse collapse\" role=\"tabpanel\" aria-labelledby=\"headingSix\">\n              <div class=\"panel-body\">\n                SMART is the Secure Malware Alert and Removal Tool (SMART). SMART  can be set to 1 of 2 settings; \"Yes, automatically remove the malware found\" or \"No, just warn me\". SMART performs an inside-out scan by connecting to the site via FTP and,making as copy of the website files to download to a SiteLock secure server. SMART is able to identify and remove coding from the files. Once the scan is complete, if malware was removed, a clean copy of the file(s) will be uploaded to the server,replacing the infected file(s). If you choose to set SMART to \"No, just warn me,\" you will only be notified of the malware found and have the ability to review the findings inside the dashboard.\n              </div>\n            </div>\n          </div>\n          <div class=\"panel panel-default\">\n            <div class=\"panel-heading\" role=\"tab\" id=\"headingSeven\">\n              <h4 class=\"panel-title\">\n                <a class=\"collapsed\" role=\"button\" data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapseSeven\" aria-expanded=\"false\" aria-controls=\"collapseSeven\">\n                  What is the sign-up process? How is SiteLock configured?\n                </a>\n              </h4>\n            </div>\n            <div id=\"collapseSeven\" class=\"panel-collapse collapse\" role=\"tabpanel\" aria-labelledby=\"headingSeven\">\n              <div class=\"panel-body\">\n                The SiteLock product will be active once the user has paid for and configured the service(s). SiteLock Scanner Lite and SiteLock Find do not require configuration. SiteLock Premium and SiteLock Defend do require configuration. There are multiple scans included with each product and many of the scans will not require a configuration, as they run via HTTPS.<br>\n        Services that need to be configured include;<br>\n        (1) SMART - Instructions can be found inside the SiteLock Dashboard.<br>\n        \"Settings\" tab -> \"Download settings\" tab. From the \"Download Settings\" screen, click on \"use the wizard\" at the top right.<br>\n        (2) Web application firewall (SiteLock Defend only). Instructions can be found inside the SiteLock Dashboard.<br>\n        From the \"Dashboard Tab\", click on the circle that says \"Trueshield Configure.\" Once you click on the circle, you will be taken to another screen that has instructions on step by step set up. If you require assistance with setting up the Web Application Firewall (WAF), please call SiteLock technical support team available 24/7/365.\n              </div>\n            </div>\n          </div>\n          <div class=\"panel panel-default\">\n            <div class=\"panel-heading\" role=\"tab\" id=\"headingEight\">\n              <h4 class=\"panel-title\">\n                <a class=\"collapsed\" role=\"button\" data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapseEight\" aria-expanded=\"false\" aria-controls=\"collapseEight\">\n                  What level of access will my clients need to configure SiteLock?\n                </a>\n              </h4>\n            </div>\n            <div id=\"collapseEight\" class=\"panel-collapse collapse\" role=\"tabpanel\" aria-labelledby=\"headingEight\">\n              <div class=\"panel-body\">\n                SiteLock Scanner Lite and SiteLock Find run via HTTPS and scan what is web visible. Your clients will not require any server access to use these products.. For services Such as Fix and Defend they will require FTP, SFTP, or FTPS access so that SMART can access and download the website flies to the SiteLock Secure Server. Additionally, in order to utilize the WAF, clients will require access to their DNS records.\n              </div>\n            </div>\n          </div>\n        </div>\n\n    </div>\n</div>\n<div class=\"tab-pane\" id=\"activate\" role=\"tabpanel\">\n    ";
$this->insert("shared/configuration-activate", ["currency" => $currency, "service" => $service, "firstBulletPoint" => "Offer all SiteLock Services", "landingPageRoutePath" => routePath("store-product-group", $feed->getGroupSlug(WHMCS\MarketConnect\MarketConnect::SERVICE_SITELOCK)), "serviceOffering" => $serviceOffering, "billingCycles" => $billingCycles, "products" => $products, "serviceTerms" => $serviceTerms]);
echo "</div>\n";
$this->end();

?>