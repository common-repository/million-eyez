(function() {
    var millioneyez_plugin_version = '3.4.16';
    var supportedBrowsers = JSON.parse('{"DESKTOP":{"Chrome":{"MIN":"33","MAX":""},"Firefox":{"MIN":"33","MAX":""},"Microsoft Edge":{"MIN":"12","MAX":""},"Internet Explorer":{"MIN":"10","MAX":""},"Safari":{"MIN":"8","MAX":""}},"MOBILE":{"Chrome":{"MIN":"38","MAX":""},"Safari":{"MIN":"8","MAX":""}}}');
    if (!isBrowserSupported()) {
        millioneyez.browserUnsupported = true;
        jQuery().ready(function() {
            jQuery('.me-unsupported-browser').removeClass('hidden');
        });
    }

    function isBrowserSupported() {
        var deviceType = (bowser.mobile || bowser.tablet) ? "MOBILE" : "DESKTOP";
        var allowedBrowsers = supportedBrowsers[deviceType];
        var foundBrowser = allowedBrowsers[bowser.name];
        if (!foundBrowser) {
            return false;
        }
        /**
         * force only if we can't extract version
         * For ios and specifically for fb browser in ios we're checking os version instead of browser version
         * because browser version sometimes is not defined.
         */

        if (!bowser.version && bowser.ios === true && parseInt(bowser.osversion.split('.')[0]) >= foundBrowser.MIN) {
            return true
        }
        return versioncompare(bowser.version, foundBrowser.MIN) >= 0 && versioncompare(bowser.version, foundBrowser.MAX) <= 0;
    }

    function versioncompare(version1, version2) {
        if (version1 == version2 || !version2) {
            return 0;
        }

        var v1 = normalize(version1);
        var v2 = normalize(version2);
        var len = Math.max(v1.length, v2.length);

        for (var i = 0; i < len; i++) {
            v1[i] = v1[i] || 0;
            v2[i] = v2[i] || 0;
            if (v1[i] == v2[i]) {
                continue;
            }
            return v1[i] > v2[i] ? 1 : -1;
        }

        return 0;
    }

    function normalize(version) {
        return version.split('.').map(function (value) {
            return parseInt(value, 10);
        });
    }
})();