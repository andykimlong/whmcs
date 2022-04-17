<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

$siteBuilderText = "<strong>Site Builder</strong>";
$this->layout("layouts/learn", $serviceOffering);
$this->start("nav-tabs");
echo "    <li class=\"active\" role=\"presentation\">\n        <a aria-controls=\"home\" data-toggle=\"tab\" href=\"#about\" role=\"tab\">\n            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.tab.about");
echo "        </a>\n    </li>\n    <li role=\"presentation\">\n        <a aria-controls=\"features\" data-toggle=\"tab\" href=\"#features\" role=\"tab\">\n            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.tab.features");
echo "        </a>\n    </li>\n    <li role=\"presentation\">\n        <a aria-controls=\"highlights\" data-toggle=\"tab\" href=\"#highlights\" role=\"tab\">\n            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.tab.highlights");
echo "        </a>\n    </li>\n    <li role=\"presentation\">\n        <a aria-controls=\"freePlan\" data-toggle=\"tab\" href=\"#freePlan\" role=\"tab\">\n            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.tab.freePlan");
echo "        </a>\n    </li>\n    <li role=\"presentation\">\n        <a aria-controls=\"pricing\" data-toggle=\"tab\" href=\"#pricing\" role=\"tab\">\n            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.tab.pricing");
echo "        </a>\n    </li>\n    <li role=\"presentation\">\n        <a aria-controls=\"faq\" data-toggle=\"tab\" href=\"#faq\" role=\"tab\">\n            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.tab.faq");
echo "        </a>\n    </li>\n";
$this->end();
$this->start("content-tabs");
echo "    <div class=\"tab-pane active\" id=\"about\" role=\"tabpanel\">\n        <div class=\"content-padded\">\n            <h3>";
echo $siteBuilderText;
echo "</h3>\n            <h4>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.tagline", [":builder" => $siteBuilderText]);
echo "</h4>\n\n            <br>\n\n            <div style=\"font-size:1em;font-weight:300;\">\n                <p>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.description", [":builder" => $siteBuilderText]);
echo "</p>\n                <h4>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.solvedProblemsQ");
echo "</h4>\n                <p>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.solvedProblemsA1Title");
echo "</strong>\n                     - ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.solvedProblemsA1Response", [":builder" => $siteBuilderText]);
echo "                </p>\n                <p>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.solvedProblemsA2Title");
echo "</strong>\n                    - ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.solvedProblemsA2Response", [":builder" => $siteBuilderText]);
echo "                </p>\n                <p>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.solvedProblemsA3Title");
echo "</strong>\n                    - ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.solvedProblemsA3Response", [":builder" => $siteBuilderText]);
echo "                </p>\n            </div>\n\n            <p>\n                <br>\n                <small>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.about", [":builder" => $siteBuilderText]);
echo "                </small>\n            </p>\n        </div>\n    </div>\n    <div class=\"tab-pane\" id=\"features\" role=\"tabpanel\">\n        <div class=\"content-padded site-builder site-builder-features\">\n\n            <div class=\"row\">\n                ";
foreach ($serviceOffering["features"] as $index => $feature) {
    echo "                    <div class=\"col-lg-4 col-sm-6\">\n                        <div class=\"feature\">\n                            <div class=\"icon\">\n                                <img src=\"../assets/img/marketconnect/sitebuilder/icons/essential/icon-0";
    echo $index + 1;
    echo ".png\">\n                            </div>\n                            <h4>\n                                ";
    echo AdminLang::trans("marketConnect.siteBuilder.learn.features." . $feature);
    echo "                            </h4>\n                            <p>\n                                ";
    echo AdminLang::trans("marketConnect.siteBuilder.learn.features." . $feature . "Description", [":builder" => $siteBuilderText]);
    echo "                            </p>\n                        </div>\n                    </div>\n                ";
}
echo "            </div>\n\n        </div>\n    </div>\n    <div class=\"tab-pane\" id=\"highlights\" role=\"tabpanel\">\n        <div class=\"content-padded site-builder highlights\">\n            <h3>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.headline");
echo "</h3>\n            <br>\n            <div class=\"row site-builder-highlights\">\n                <div class=\"col-sm-4\">\n                    <i class=\"fab fa-whmcs\"></i>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.blogSettings");
echo "</strong><br>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.blogSettingsInfo");
echo "                </div>\n                <div class=\"col-sm-4\">\n                    <i class=\"fas fa-file-edit\"></i>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.postEditor");
echo "</strong><br>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.postEditorInfo");
echo "                </div>\n                <div class=\"col-sm-4\">\n                    <i class=\"fas fa-share\"></i>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.socialSharing");
echo "</strong><br>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.socialSharingInfo");
echo "                </div>\n            </div>\n            <div class=\"row site-builder-highlights\">\n                <div class=\"col-sm-4\">\n                    <i class=\"fas fa-file-alt\"></i>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.isolatedPosting");
echo "</strong><br>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.isolatedPostingInfo");
echo "                </div>\n                <div class=\"col-sm-4\">\n                    <i class=\"fas fa-blog\"></i>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.blogPage");
echo "</strong><br>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.blogPageInfo");
echo "                </div>\n                <div class=\"col-sm-4\">\n                    <i class=\"fas fa-save\"></i>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.draftPost");
echo "</strong><br>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.draftPostInfo");
echo "                </div>\n            </div>\n            <br>\n            <h3>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.title");
echo "</h3>\n            <div class=\"row top-margin-10\">\n                <div class=\"col-sm-6\">\n                    <ul>\n                        <li>\n                            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.products");
echo "                        </li>\n                        <li>\n                            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.securePayments");
echo "                        </li>\n                        <li>\n                            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.quickCheckout");
echo "                        </li>\n                        <li>\n                            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.marketplaceIntegration");
echo "                        </li>\n                        <li>\n                            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.productListing");
echo "                        </li>\n                    </ul>\n                </div>\n                <div class=\"col-sm-6\">\n                    <ul>\n                        <li>\n                            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.promotions");
echo "                        </li>\n                        <li>\n                            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.stockManagement");
echo "                        </li>\n                        <li>\n                            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.worldwideShipping");
echo "                        </li>\n                        <li>\n                            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.taxes");
echo "                        </li>\n                        <li>\n                            ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.highlights.storeOptions.orderManagement");
echo "                        </li>\n                    </ul>\n                </div>\n            </div>\n        </div>\n    </div>\n    <div class=\"tab-pane\" id=\"freePlan\" role=\"tabpanel\">\n        <div class=\"content-padded site-builder\">\n            <h3>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.freeTrial");
echo "</h3>\n            <p class=\"top-margin-10\">\n                ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.freeTrialInfo", [":builder" => $siteBuilderText]);
echo "            </p>\n            <br>\n            <div class=\"row site-builder-free\">\n                <div class=\"col-sm-3\">\n                    <i class=\"fal fa-images\"></i>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.stockPhotos");
echo "</strong><br>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.stockPhotosInfo");
echo "                </div>\n                <div class=\"col-sm-3\">\n                    <i class=\"fal fa-paint-brush\"></i>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.templates");
echo "</strong><br>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.templatesInfo");
echo "                </div>\n                <div class=\"col-sm-3\">\n                    <i class=\"fal fa-cubes\"></i>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.blocks");
echo "</strong><br>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.blocksInfo");
echo "                </div>\n                <div class=\"col-sm-3\">\n                    <i class=\"fal fa-phone-laptop\"></i>\n                    <strong>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.mobileFriendly");
echo "</strong><br>\n                    ";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.mobileFriendlyInfo");
echo "                </div>\n            </div>\n            <br>\n            <p>";
echo AdminLang::trans("marketConnect.siteBuilder.learn.free.outro", [":builder" => $siteBuilderText]);
echo "</p>\n        </div>\n    </div>\n    <div class=\"tab-pane\" id=\"pricing\" role=\"tabpanel\">\n        <div class=\"content-padded site-builder\">\n            ";
if ($feed->isNotAvailable()) {
    echo "                <div class=\"pricing-login-overlay\">\n                    <p>";
    echo AdminLang::trans("marketConnect.loginForPricing");
    echo "</p>\n                    <button type=\"button\" class=\"btn btn-default btn-sm btn-login\">\n                        ";
    echo AdminLang::trans("marketConnect.login");
    echo "                    </button>\n                    <button type=\"button\" class=\"btn btn-default btn-sm btn-register\">\n                        ";
    echo AdminLang::trans("marketConnect.createAccount");
    echo "                    </button>\n                </div>\n            ";
}
echo "            <small>\n                ";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.trialInfo");
echo "            </small>\n            <table class=\"table table-pricing\">\n                <tr>\n                    <th>\n                        ";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.feature");
echo "                    </th>\n                    <th>\n                        ";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.onePage");
echo "<br />\n                        <span>\n                            ";
echo AdminLang::trans("marketConnect.perMo", [":num" => $feed->getCostPrice(WHMCS\MarketConnect\Promotion\Service\SiteBuilder::SITEBUILDER_ONE_PAGE)]);
echo "                        </span>\n                    </th>\n                    <th>\n                        ";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.unlimited");
echo "<br>\n                        <span>\n                            ";
echo AdminLang::trans("marketConnect.perMo", [":num" => $feed->getCostPrice(WHMCS\MarketConnect\Promotion\Service\SiteBuilder::SITEBUILDER_UNLIMITED)]);
echo "                        </span>\n                    </th>\n                    <th>\n                        ";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.store");
echo "<br>\n                        <span>\n                            ";
echo AdminLang::trans("marketConnect.perMo", [":num" => $feed->getCostPrice(WHMCS\MarketConnect\Promotion\Service\SiteBuilder::SITEBUILDER_STORE)]);
echo "                        </span>\n                    </th>\n                    <th>\n                        ";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.storePlus");
echo "<br>\n                        <span>\n                            ";
echo AdminLang::trans("marketConnect.perMo", [":num" => $feed->getCostPrice(WHMCS\MarketConnect\Promotion\Service\SiteBuilder::SITEBUILDER_STORE_PLUS)]);
echo "                        </span>\n                    </th>\n                    <th>\n                        ";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.storePremium");
echo "<br>\n                        <span>\n                            ";
echo AdminLang::trans("marketConnect.perMo", [":num" => $feed->getCostPrice(WHMCS\MarketConnect\Promotion\Service\SiteBuilder::SITEBUILDER_STORE_PREMIUM)]);
echo "                        </span>\n                    </th>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.templates");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.userFirst");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.ddEditing");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.responsive");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.gallery");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.blocks");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.blog");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.layouts");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.contactForm");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.restore");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.inheritance");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.socials");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.seo");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.analytics");
echo "</td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                    <td><i class=\"icon-yes fas fa-check\"></i></td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.pages");
echo "</td>\n                    <td>1</td>\n                    <td>";
echo AdminLang::trans("global.unlimited");
echo "</td>\n                    <td>";
echo AdminLang::trans("global.unlimited");
echo "</td>\n                    <td>";
echo AdminLang::trans("global.unlimited");
echo "</td>\n                    <td>";
echo AdminLang::trans("global.unlimited");
echo "</td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.products");
echo "</td>\n                    <td><i class=\"icon-no fas fa-times\"></i></td>\n                    <td><i class=\"icon-no fas fa-times\"></i></td>\n                    <td>10</td>\n                    <td>50</td>\n                    <td>500</td>\n                </tr>\n                <tr>\n                    <td>";
echo AdminLang::trans("marketConnect.siteBuilder.pricing.rrp");
echo "</td>\n                    <td>\n                        ";
echo AdminLang::trans("marketConnect.perMo", [":num" => $feed->getRecommendedRetailPrice(WHMCS\MarketConnect\Promotion\Service\SiteBuilder::SITEBUILDER_ONE_PAGE)]);
echo "                    </td>\n                    <td>\n                        ";
echo AdminLang::trans("marketConnect.perMo", [":num" => $feed->getRecommendedRetailPrice(WHMCS\MarketConnect\Promotion\Service\SiteBuilder::SITEBUILDER_UNLIMITED)]);
echo "                    </td>\n                    <td>\n                        ";
echo AdminLang::trans("marketConnect.perMo", [":num" => $feed->getRecommendedRetailPrice(WHMCS\MarketConnect\Promotion\Service\SiteBuilder::SITEBUILDER_STORE)]);
echo "                    </td>\n                    <td>\n                        ";
echo AdminLang::trans("marketConnect.perMo", [":num" => $feed->getRecommendedRetailPrice(WHMCS\MarketConnect\Promotion\Service\SiteBuilder::SITEBUILDER_STORE_PLUS)]);
echo "                    </td>\n                    <td>\n                        ";
echo AdminLang::trans("marketConnect.perMo", [":num" => $feed->getRecommendedRetailPrice(WHMCS\MarketConnect\Promotion\Service\SiteBuilder::SITEBUILDER_STORE_PREMIUM)]);
echo "                    </td>\n                </tr>\n            </table>\n        </div>\n    </div>\n    <div class=\"tab-pane\" id=\"faq\" role=\"tabpanel\">\n        <div class=\"content-padded site-builder faq\">\n            ";
for ($i = 1; $i <= 4; $i++) {
    echo "                <h4>";
    echo AdminLang::trans("marketConnect.siteBuilder.faq.q" . $i);
    echo "</h4>\n                <p>\n                    ";
    echo AdminLang::trans("marketConnect.siteBuilder.faq.a" . $i, [":builder" => $siteBuilderText]);
    echo "                </p>\n            ";
}
echo "        </div>\n    </div>\n    <div class=\"tab-pane\" id=\"activate\" role=\"tabpanel\">\n        ";
$this->insert("shared/configuration-activate", ["currency" => $currency, "service" => $service, "firstBulletPoint" => "Offer all 6 " . $siteBuilderText . " Plans", "landingPageRoutePath" => routePath("store-product-group", $feed->getGroupSlug(WHMCS\MarketConnect\MarketConnect::SERVICE_SITEBUILDER)), "serviceOffering" => $serviceOffering, "billingCycles" => $billingCycles, "products" => $products, "serviceTerms" => $serviceTerms]);
echo "    </div>\n";
$this->end();

?>