<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

echo "<div class=\"container\">\n    <div class=\"apps-and-integrations\">\n\n        <h1>\n            <a href=\"https://marketplace.whmcs.com/?utm_source=inproduct&utm_medium=poweredby\" target=\"_blank\" class=\"hidden-xs hidden-sm\">\n                <img src=\"";
echo $assetHelper->getImgPath();
echo "/powered-by-marketplace.png\" class=\"powered-by\" alt=\"Powered by WHMCS Marketplace\">\n            </a>\n            ";
echo AdminLang::trans("apps.title");
echo "        </h1>\n\n        ";
if ($connectionError) {
    echo "            <div class=\"app-wrapper error\">\n                <h3>Unable to connect to Apps and Integrations</h3>\n                <p>Your WHMCS installation is unable to connect to the Apps and Integrations data feed at this time.</p>\n                <p>Please check and ensure your server is able to communicate with <em>https://appsfeed.whmcs.com/</em> and then try again.</p>\n            </div>\n        ";
} else {
    if ($renderError) {
        echo "            <div class=\"app-wrapper error\">\n                <h3>Oops! There's a problem.</h3>\n                <p>Apps and Integrations failed to initialise. The following error occurred:</p>\n                <div class=\"alert alert-danger\" style=\"margin:20px;\">\n                    ";
        echo $renderError;
        echo "                </div>\n            </div>\n        ";
    } else {
        echo "            <div class=\"input-group search\">\n                <span class=\"input-group-btn\">\n                    <button class=\"btn btn-default\" type=\"button\"><i class=\"far fa-search\"></i></button>\n                </span>\n                <input type=\"text\" id=\"inputAppSearch\" class=\"form-control\" placeholder=\"";
        echo AdminLang::trans("apps.searchPlaceholder");
        echo "\">\n            </div>\n\n            <ul class=\"nav nav-pills aai-primary-nav\" role=\"tablist\">\n                <li role=\"presentation\" class=\"active\"><a href=\"#featured\" aria-controls=\"featured\" role=\"tab\" data-toggle=\"tab\">";
        echo AdminLang::trans("apps.nav.featured");
        echo "</a></li>\n                <li role=\"presentation\"><a href=\"#browse\" aria-controls=\"browse\" role=\"tab\" data-toggle=\"tab\" id=\"tabBrowse\">";
        echo AdminLang::trans("apps.nav.browse");
        echo "</a></li>\n                <li role=\"presentation\"><a href=\"#active\" aria-controls=\"active\" role=\"tab\" data-toggle=\"tab\" id=\"tabActive\">";
        echo AdminLang::trans("apps.nav.active");
        echo "</a></li>\n                <li role=\"presentation\"><a href=\"#search\" aria-controls=\"search\" role=\"tab\" data-toggle=\"tab\" id=\"tabSearch\">";
        echo AdminLang::trans("apps.nav.search");
        echo "</a></li>\n            </ul>\n\n            <div class=\"tab-content\">\n                <div role=\"tabpanel\" class=\"tab-pane fade in active\" id=\"featured\">\n\n                    <div class=\"app-wrapper\">\n                        <div class=\"owl-carousel owl-theme apps-hero-banners\">\n                            ";
        foreach ($heros as $hero) {
            echo "                                ";
            if ($hero->hasRemoteUrl()) {
                echo "                                    <a href=\"";
                echo urlencode($hero->getRemoteUrl());
                echo "\" target=\"_blank\" class=\"app-external-url\">\n                                ";
            } else {
                if ($hero->hasTargetAppKey()) {
                    echo "                                    <a href=\"";
                    echo routePath("admin-apps-info", $hero->getTargetAppKey());
                    echo "\" class=\"app-inner open-modal\" data-modal-class=\"app-info-modal\" data-modal-size=\"modal-lg\">\n                                ";
                }
            }
            echo "                                    <img class=\"owl-lazy\" data-src=\"";
            echo escape($hero->getImageUrl());
            echo "\">\n                                </a>\n                            ";
        }
        echo "                        </div>\n                    </div>\n\n                    <div id=\"featuredContentPane\">\n\n                        <div class=\"app-wrapper clearfix\">\n                            <div class=\"loader\">\n                                ";
        echo AdminLang::trans("global.loading");
        echo "                            </div>\n                        </div>\n\n                    </div>\n\n                </div>\n                <div role=\"tabpanel\" class=\"tab-pane fade\" id=\"browse\">\n\n                    <div id=\"browseContentPane\">\n                        <div class=\"app-wrapper\">\n                            <div class=\"loader\">\n                                ";
        echo AdminLang::trans("global.loading");
        echo "                            </div>\n                        </div>\n                    </div>\n\n                </div>\n                <div role=\"tabpanel\" class=\"tab-pane fade\" id=\"active\">\n\n                    <div class=\"app-category-title\">\n                        <h2>";
        echo AdminLang::trans("apps.nav.active");
        echo " <span>";
        echo AdminLang::trans("apps.apps");
        echo "</span></h2>\n                        <p class=\"lead\">";
        echo AdminLang::trans("apps.activeDescription");
        echo "</p>\n                    </div>\n\n                    <div class=\"app-wrapper clearfix\">\n                        <div id=\"activeContentPane\">\n                            <div class=\"loader\">\n                                ";
        echo AdminLang::trans("global.loading");
        echo "                            </div>\n                        </div>\n                    </div>\n\n                </div>\n                <div role=\"tabpanel\" class=\"tab-pane fade\" id=\"search\">\n\n                    <div id=\"searchContentPane\">\n                        ";
        $this->insert("apps/search");
        echo "                    </div>\n\n                </div>\n            </div>\n\n            <a href=\"https://marketplace.whmcs.com/?utm_source=inproduct&utm_medium=poweredby\" target=\"_blank\" class=\"visible-xs visible-sm\">\n                <img src=\"";
        echo $assetHelper->getImgPath();
        echo "/powered-by-marketplace.png\" class=\"powered-by\" alt=\"Powered by WHMCS Marketplace\">\n            </a>\n        ";
    }
}
echo "\n    </div>\n</div>\n\n<style>\n\n.contentarea.grey-bg {\n    background-color: #e2e7e9;\n}\n\n.apps-and-integrations h1 {\n    margin: 25px 0 30px 0;\n    color: #353535;\n    font-size: 2.8em;\n    font-weight: 300;\n}\n.apps-and-integrations .app-wrapper {\n    margin: 20px 0;\n    padding: 30px 30px 10px 30px;\n    background-color: #fff;\n}\n.apps-and-integrations .app-wrapper.slim {\n    margin: 0 0 20px 0;\n}\n.apps-and-integrations h2 {\n    margin: 0 0 7px 0;\n    font-size: 1.8em;\n    color: #000;\n    white-space: nowrap;\n    overflow: hidden;\n    text-overflow: ellipsis;\n}\n.apps-and-integrations h2 span {\n    font-weight: 300;\n}\n.apps-and-integrations h3 {\n    font-size: 1.5em;\n    color: #444;\n}\n.apps-and-integrations .app-category-title {\n    margin: 30px 0;\n}\n.apps-and-integrations .category-choose {\n    float: right;\n}\n.apps-and-integrations .lead {\n    font-size: 1.3em;\n    white-space: nowrap;\n    overflow: hidden;\n    text-overflow: ellipsis;\n}\n.apps-and-integrations .app-wrapper.error {\n    margin: 0 0 20px 0;\n    padding: 70px;\n    text-align: center;\n    font-size: 1.1em;\n    font-weight: 300;\n}\n.apps-and-integrations .app-wrapper .loader {\n    margin: 0 0 20px 0;\n    padding: 40px;\n    text-align: center;\n    opacity: 0.5;\n    font-size: 1.4em;\n    font-weight: 300;\n}\n.apps-and-integrations .app-wrapper .apps {\n    display: block;\n    margin-left: -10px;\n    margin-right: -10px;\n}\n.apps-and-integrations .app-wrapper .app {\n    float: left;\n    position: relative;\n    min-height: 1px;\n    padding-left: 10px;\n    padding-right: 10px;\n    width: 25%;\n}\n.apps-and-integrations .col-md-9 .app-wrapper .app {\n    width: 33.33%;\n}\n.apps-and-integrations .app-wrapper.slim .app {\n    float: left;\n    position: relative;\n    min-height: 1px;\n    padding-left: 10px;\n    padding-right: 10px;\n    width: 50%;\n}\n.apps-and-integrations .app-wrapper .app .app-inner {\n    display: block;\n    margin: 0 0 20px 0;\n    padding: 0;\n    border: 1px solid #ddd;\n    background-color: #fff;\n    border-radius: 3px;\n    text-align: left;\n    text-decoration: none;\n    color: #444;\n}\n.apps-and-integrations .app-wrapper .app.featured .app-inner {\n    background-color: #fff;\n}\n.apps-and-integrations .app .logo-container {\n    display: none;\n    padding: 10px 10px 0 10px;\n    height: 100px;\n    line-height: 95px;\n    text-align: center;\n}\n.apps-and-integrations .app.featured .logo-container,\n.apps-and-integrations .apps.active .app .logo-container {\n    display: block;\n}\n.apps-and-integrations .app .logo-container img {\n    max-width: 85%;\n    max-height: 80%;\n    vertical-align: middle;\n}\n.apps-and-integrations .app .logo-container .no-image-available {\n    display: block;\n    font-size: 11px;\n    font-style: italic;\n    color: #ccc;\n}\n.apps-and-integrations .app .content-container {\n    padding: 15px;\n    height: 120px;\n}\n.apps-and-integrations .app .content-container .title {\n    font-size: 1.35em;\n    white-space: nowrap;\n    overflow: hidden;\n    text-overflow: ellipsis;\n}\n.apps-and-integrations .app .content-container .description {\n    height: 47px;\n    font-size: 0.9em;\n}\n.apps-and-integrations .app .content-container .category {\n    display: none;\n    text-transform: uppercase;\n    font-size: 0.8em;\n    color: #ccc;\n}\n.apps-and-integrations .app.search .content-container .category {\n    display: inline-block;\n}\n.apps-and-integrations .app .content-container .popular {\n    text-transform: uppercase;\n    font-size: 0.8em;\n    color: #ccc;\n}\n.apps-and-integrations .app .content-container .popular-star {\n    float: right;\n    color: #336699;\n}\n.apps-and-integrations .app .content-container .description.none {\n    color: #888;\n    font-style: italic;\n}\n\n.apps-and-integrations .nav-pills a {\n    margin-right: 10px;\n}\n\n.apps-and-integrations .app .badge {\n    background-color: #ccc;\n    text-transform: capitalize;\n    padding: 5px 10px;\n    border-radius: 3px;\n}\n.apps-and-integrations .app .badge.popular {\n    background-color: #0ab738;\n}\n.apps-and-integrations .app .badge.new {\n    background-color: #336699;\n}\n.apps-and-integrations .wrapper .app a:hover {\n    border-color: #336699;\n}\n\n.app-info-modal .modal-header {\n    display: none;\n}\n.app-info-modal .close {\n    margin: -30px -25px 0 0;\n}\n.app-info-modal .modal-body {\n    padding: 50px;\n}\n.app-info-modal .logo {\n    max-width: 200px;\n    margin-bottom: 20px;\n}\n.app-info-modal .logo img {\n    max-width: 100%;\n    max-height: 90px;\n}\n.app-info-modal .title {\n    margin: 0 0 15px 0;\n    font-size: 1.7em;\n}\n.app-info-modal .tagline {\n    font-size: 1.3em;\n    font-weight: 300;\n}\n.app-info-modal .description {\n    margin: 20px 0;\n    font-size: 1.1em;\n    font-weight: 300;\n}\n.app-info-modal .features {\n    font-weight: 300;\n}\n.app-info-modal .app-info-sidebar {\n    margin: 0;\n    padding: 20px;\n    background-color: #f6f6f6;\n}\n.app-info-modal .app-info-sidebar ul {\n    margin: 0 0 15px 0;\n    padding: 0;\n    list-style: none;\n}\n.app-info-modal .app-info-sidebar strong {\n    display: block;\n}\n.app-info-modal .app-info-sidebar span {\n    display: block;\n    margin: 0 0 15px 0;\n}\n.app-info-modal .app-info-sidebar .price {\n    text-align: center;\n    font-size: 1.1em;\n}\n.app-info-modal .app-info-sidebar .free-trial {\n    text-align: center;\n    font-style: italic;\n}\n.app-info-modal .app-info-sidebar .price + .btn,\n.app-info-modal .app-info-sidebar .free-trial + .btn {\n    margin-top: 4px;\n}\n.app-info-modal  .management-buttons {\n    margin: 20px 0 0 0;\n}\n.app-info-modal  .management-buttons .btn {\n    margin: 5px 0 0 0;\n}\n\n.app-info-modal .error-title {\n    display: block;\n    margin: 0 0 15px 0;\n    font-size: 1.4em;\n}\n\n.apps-and-integrations .powered-by {\n    float: right;\n    padding: 12px 0;\n    max-width: 355px;\n}\n\n.apps-and-integrations .no-active-apps {\n    margin: 20px 20px 40px;\n    text-align: center;\n}\n.apps-and-integrations .no-active-apps span {\n    font-size: 1.4em;\n    font-weight: 300;\n}\n.apps-and-integrations .no-active-apps .btn {\n    margin-top: 20px;\n    font-weight: 300;\n}\n\n.apps-and-integrations .search {\n    margin: 30px 0;\n}\n@media (min-width: 750px) {\n    .apps-and-integrations .search {\n        float: right;\n        margin: 0;\n        width: 260px;\n    }\n}\n.apps-and-integrations .search input,\n.apps-and-integrations .search button {\n    border-radius: 0;\n    height: 40px;\n}\n.apps-and-integrations .search input {\n    font-size: 16px;\n    font-weight: 300;\n    border-left: 0;\n    box-shadow: none;\n}\n.apps-and-integrations .search.active input,\n.apps-and-integrations .search.active button {\n    border-color: #66afe9;\n    outline: 0;\n    box-shadow: none;\n}\n\n.apps-and-integrations .min-search-term span,\n.apps-and-integrations  .no-results-found span {\n    display: block;\n    margin: 10px 0 30px;\n    text-align: center;\n}\n\n.apps-and-integrations .aai-primary-nav a {\n    background-color: #fff;\n    border: 1px solid #ccc;\n    border-radius: 3px;\n}\n.apps-and-integrations .aai-primary-nav>li.active>a,\n.apps-and-integrations .aai-primary-nav>li.active>a:focus,\n.apps-and-integrations .aai-primary-nav>li.active>a:hover {\n    color: #fff;\n    background-color: #336699;\n    border-color: #336699;\n}\n\n.apps-and-integrations .categories-nav {\n    list-style: none;\n    margin: 30px 0;\n    padding: 0;\n    border-bottom: 1px solid #ddd;\n}\n.apps-and-integrations .categories-nav li.title {\n    margin: 0 0 10px 0;\n}\n.apps-and-integrations .categories-nav a {\n    display: block;\n    padding: 8px 10px;\n    background-color: #fff;\n    border: 1px solid #ddd;\n    border-bottom: 0;\n    text-decoration: none;\n}\n.apps-and-integrations .categories-nav a:hover {\n    background-color: #eee;\n}\n.apps-and-integrations .categories-nav a.active {\n    background-color: #336699;\n    color: #fff;\n}\n.apps-and-integrations .categories-nav a i {\n    display: inline-block;\n    width: 32px;\n    text-align: center;\n}\n.apps-and-integrations .categories-nav a i.fa-spinner {\n    float: right;\n    line-height: 20px;\n}\n\n.apps-and-integrations .category-chooser {\n    margin: 30px 0 0 0;\n}\n\n@media (max-width: 1199px) {\n    .apps-and-integrations .app-wrapper .app {\n        width: 33.33%;\n    }\n    .apps-and-integrations .col-md-9 .app-wrapper .app {\n        width: 50%;\n    }\n}\n@media (max-width: 991px) {\n    .apps-and-integrations .app-wrapper .app,\n    .apps-and-integrations .col-md-9 .app-wrapper .app {\n        width: 50%;\n    }\n    .app-info-modal .app-info-sidebar {\n        margin-top: 30px;\n    }\n}\n@media (max-width: 710px) {\n    .apps-and-integrations .app-wrapper .app,\n    .apps-and-integrations .col-md-9 .app-wrapper .app {\n        width: 100%;\n    }\n    .apps-and-integrations .app-wrapper.slim .app {\n        width: 100%;\n    }\n    .app-info-modal .modal-body {\n        padding: 30px;\n    }\n    .app-info-modal .close {\n        margin: 0;\n    }\n}\n@media (max-width: 500px) {\n    .apps-and-integrations h1 {\n        margin-top: 0;\n    }\n    .apps-and-integrations {\n        margin-left: -15px;\n        margin-right: -15px;\n    }\n    .apps-and-integrations .app-wrapper {\n        padding: 20px 20px 0 20px;\n    }\n}\n</style>\n\n<script>\nvar originalDisplayTitle = document.title;\nvar postLoadClick = false;\n\n\$(document).ready(function() {\n    \$('.contentarea').addClass('grey-bg').find('h1').first().hide();\n\n    \$(\".apps-hero-banners\").owlCarousel({\n        items: 1,\n        lazyLoad: true,\n        loop: true,\n        autoplay: true,\n        autoplayTimeout: 10000,\n        autoplayHoverPause: true\n    });\n\n    WHMCS.http.jqClient.post('";
echo routePath("admin-apps-featured");
echo "', '', function(data) {\n        \$('#featuredContentPane').html(data.content);\n    }, 'json');\n\n    \$(document).on('click', '.btn-view-all', function(e) {\n        e.preventDefault();\n        \$('#tabBrowse').data('category-slug', \$(this).data('category-slug'))\n            .data('category-display-name', \$(this).data('category-display-name'))\n            .click();\n    });\n\n    \$('.aai-primary-nav a[data-toggle=\"tab\"]').on('shown.bs.tab', function (e) {\n        var tabName = \$(e.target).attr('aria-controls');\n        if (tabName == 'featured') {\n            document.title = originalDisplayTitle;\n            window.history.pushState({\"pageTitle\": document.title}, \"\", \"";
echo routePath("admin-apps-index");
echo "\");\n        } else if (tabName == 'browse') {\n            var categorySlug = \$('#tabBrowse').data('category-slug');\n            var categoryDisplayName = \$('#tabBrowse').data('category-display-name');\n            if (!categorySlug) {\n                categorySlug = \$('.featured-cat').first().data('slug');\n            }\n            if (!\$('#browse').hasClass('loaded') || \$('#browse').data('loaded-category-slug') != categorySlug) {\n                browseCategory(categorySlug);\n            }\n            if (postLoadClick) {\n                postLoadClick = false;\n            } else {\n                if (!categoryDisplayName) {\n                    categoryDisplayName = 'Browse';\n                }\n                document.title = categoryDisplayName + ' Apps - ' + originalDisplayTitle;\n                window.history.pushState({\"pageTitle\": document.title}, \"\", \"";
echo routePath("admin-apps-browse");
echo "\");\n            }\n        } else if (tabName == 'search') {\n            \$('#inputAppSearch').focus();\n            if (!\$('#search').hasClass('loaded')) {\n                WHMCS.http.jqClient.post('";
echo routePath("admin-apps-search");
echo "', '', function(data) {\n                    \$('#searchContentPane .search-apps-load-target').html(data.content);\n                    \$('.search-apps-regular .app.featured').detach().appendTo('.search-apps-featured');\n                    \$('#inputAppSearch').keyup();\n                }, 'json');\n                \$('#search').addClass('loaded');\n            }\n            document.title = 'Search - ' + originalDisplayTitle;\n            window.history.pushState({\"pageTitle\": document.title}, \"\", \"";
echo routePath("admin-apps-search");
echo "\");\n        } else if (tabName == 'active') {\n            WHMCS.http.jqClient.post('";
echo routePath("admin-apps-active");
echo "', '', function(data) {\n                \$('#activeContentPane').html(data.content);\n            }, 'json');\n            document.title = 'Active Apps - ' + originalDisplayTitle;\n            window.history.pushState({\"pageTitle\": document.title}, \"\", \"";
echo routePath("admin-apps-active");
echo "\");\n        }\n    });\n\n    \$(document).on('click', '.categories-nav a', function(e) {\n        e.preventDefault();\n        \$('.categories-nav a').removeClass('active');\n        \$(this).addClass('active').append('<i class=\"fa fa-spinner fa-spin\"></i>');\n        document.title = \$(this).data('name') + ' Apps - ' + originalDisplayTitle;\n        window.history.pushState({\"pageTitle\": document.title}, \"\", \"";
echo routePath("admin-apps-category", "");
echo "\" + \$(this).data('slug'));\n        browseCategory(\$(this).data('slug'));\n    });\n\n    \$(document).on('change', '#inputCategoryDropdown', function(e) {\n        e.preventDefault();\n        document.title = \$(this).find(':selected').data('name') + ' Apps - ' + originalDisplayTitle;\n        window.history.pushState({\"pageTitle\": document.title}, \"\", \"";
echo routePath("admin-apps-category", "");
echo "\" + \$(this).val());\n        browseCategory(\$(this).val());\n    });\n\n    \$(document).on('submit', '.app-info-modal form', function(e) {\n        \$(this).find('button[type=\"submit\"]').prop('disabled', true).html('<i class=\"fa fa-spinner fa-spin fa-fw\"></i> ' + \$(this).find('button[type=\"submit\"]').html());\n    });\n\n    \$('#inputAppSearch').keyup(function() {\n        var searchTerm = \$(this).val().toUpperCase();\n\n        \$('#tabSearch').click();\n\n        if (searchTerm.length < 3) {\n            \$('.min-search-term').show();\n            \$('.no-results-found').hide();\n            \$('#searchResultsCount').html('0');\n            \$('.search-wrapper').hide();\n            return;\n        }\n\n        \$('.min-search-term').hide();\n        \$('.search-apps-featured').parent('.app-wrapper').show();\n        \$('.search-apps-regular').parent('.app-wrapper').show();\n\n        var searchResultCount = 0;\n        \$('.search-apps-featured .app, .search-apps-regular .app').each(function(index) {\n            if (\$(this).text().toUpperCase().indexOf(searchTerm) > -1) {\n                \$(this).show();\n                searchResultCount++;\n            } else {\n                \$(this).hide();\n            }\n        });\n\n        \$('.search-wrapper').removeClass('hidden').show();\n\n        if (\$('.search-apps-featured .app:visible').length <= 0) {\n            \$('.search-apps-featured').parent('.app-wrapper').hide();\n        }\n        if (\$('.search-apps-regular .app:visible').length <= 0) {\n            \$('.search-apps-regular').parent('.app-wrapper').hide();\n        }\n\n        \$('#searchResultsCount').html(searchResultCount);\n        if (searchResultCount == 0) {\n            \$('.no-results-found').removeClass('hidden').show();\n        } else {\n            \$('.no-results-found').hide();\n        }\n    });\n\n    \$('#inputAppSearch').focus(function() {\n        \$('.input-group.search').addClass('active');\n    });\n    \$('#inputAppSearch').blur(function() {\n        \$('.input-group.search').removeClass('active');\n    });\n\n    \$(document).on('click', '.app-external-url', function(e) {\n        WHMCS.http.jqClient.jsonPost({\n            url: 'https://api1.whmcs.com/apps/track/external',\n            data: 'url=' + encodeURIComponent(\$(this).attr('href')),\n        });\n    });\n\n    ";
if ($postLoadAction) {
    echo "        postLoadClick = true;\n        ";
    if ($postLoadAction == "browse") {
        echo "            ";
        if (isset($postLoadParams["category"]) && $postLoadParams["category"]) {
            echo "                \$('#tabBrowse').data('category-slug', '";
            echo $postLoadParams["category"];
            echo "').click();\n            ";
        } else {
            echo "                \$('#tabBrowse').click();\n            ";
        }
        echo "        ";
    } else {
        if ($postLoadAction == "active") {
            echo "            \$('#tabActive').click();\n        ";
        } else {
            if ($postLoadAction == "search") {
                echo "            \$('#tabSearch').click();\n        ";
            }
        }
    }
    echo "    ";
}
echo "});\n\nfunction browseCategory(slug) {\n    WHMCS.http.jqClient.jsonPost({\n        url: '";
echo routePath("admin-apps-browse");
echo "/' + slug,\n        data: '',\n        success: function(data) {\n            document.title = data.displayname + ' Apps - ' + originalDisplayTitle;\n            \$('#browseContentPane').html(data.content);\n            \$('.app-wrapper.category-view').each(function() {\n                if (\$(this).find('.apps').children('.app').size() == 0) {\n                    \$(this).hide();\n                }\n            });\n            \$('#browse').addClass('loaded').data('loaded-category-slug', slug);\n            \$('#tabBrowse').data('category-slug', slug)\n                .data('category-display-name', data.displayname);\n        }\n    });\n}\n</script>\n\n<div class=\"clearfix\"></div>\n";

?>