(function () {
    var millioneyez_plugin_version = '3.4.16';

    // NOTE: Any change here should also be done in mce_plugin.js
    jQuery(window).on('message', function (e) {
        if (e && e.originalEvent.origin.substr(-'//curator.millioneyez.com'.length) == '//curator.millioneyez.com') {
            var message = JSON.parse(e.originalEvent.data);
            if (message.type != 'auth') {
                tb_remove();
                jQuery('.mce-toolbar-grp.mce-inline-toolbar-grp').css('z-index','');
            }

            if (message.type == 'photobox' && message.missionId) {
                var shortcode = '[millioneyez missionid="' + message.missionId + '"][/millioneyez]';

                var contentElement = document.getElementById('content');
                contentElement.value =
                    contentElement.value.substring(0, contentElement.selectionEnd) +
                    shortcode +
                    contentElement.value.substring(contentElement.selectionEnd);
            }
            if (message.type == 'photo' && message.missionId && message.photoId) {
                var shortcode = '[millioneyez missionid="' + message.missionId + '" photoid="' + message.photoId + '"][/millioneyez]';
                var contentElement = document.getElementById('content');
                contentElement.value =
                    contentElement.value.substring(0, contentElement.selectionEnd) +
                    shortcode +
                    contentElement.value.substring(contentElement.selectionEnd);
            }

            if (message.type == 'auth' && message.token) {
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'millioneyez_set_key',
                        bloggerKey: message.token
                    },
                    success: function(data) {
                        parent.location.reload();
                    }
                });
            }
        }
    });

    millioneyez.onClickedAdd = function (element) {
        if (millioneyez.browserUnsupported) {
            showUnsupportDialog();
        } else {
            jQuery('.mce-toolbar-grp.mce-inline-toolbar-grp').css('z-index','1');
            var params = {
                articleUrl: millioneyez.shortlink,
                title: jQuery('#title').val(),
                version: millioneyez_plugin_version
            };

            var paramsParsed = '';
            for (var key in params) {
                paramsParsed += key + '=' + EncodeURIComponentWithSpecialChars(params[key]) + '&';
            }

            var oldHref = '//curator.millioneyez.com/create.html#?token=' + millioneyez.token + '&PARAMS&TB_iframe=true&modal=true';
            var updatedHref = oldHref.replace('PARAMS', paramsParsed);

            tb_show('Add million eyez', updatedHref);
        }
    };

    jQuery(document).on( 'click', '.me-no-key-notice .notice-dismiss', function() {
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'millioneyez_dismiss_no_key_notice'
            }
        })
    });
    // this function is based upon the following MDN article:
    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/encodeURIComponent
    // it makes sure that special chars such as apostrophy will get encoded
    function EncodeURIComponentWithSpecialChars(str) {
      return encodeURIComponent(str).replace(/[!'()*]/g, function(c) {
        return '%' + c.charCodeAt(0).toString(16);
      });
    }

    millioneyez.onShowNoKeyDialog = function() {
        jQuery(function($) {
            var confirm;
            if (window.millioneyez_edit_post_script) {
                confirm = jQuery('<div>To add a million eyez photobox, please get your key to connect your site from the <a href="' + millioneyez_edit_post_script.settings_page + '">settings page</a></div>');
            } else {
                confirm = jQuery('<div>To add a million eyez photobox, please get your key to connect your site.</div>');
            }

            confirm.dialog({
                'title': 'Welcome to million eyez',
                'dialogClass'   : 'me-confirm-dialog',
                'modal'         : true,
                'autoOpen'      : false,
                'closeOnEscape' : true,
                'buttons'       : {
                    "Close": function() {
                        jQuery(this).dialog('destroy');
                    }
                }
            });
            jQuery('.me-confirm-dialog').css('z-index',1000001);
            confirm.dialog('open');
        });
    };

    millioneyez.onShowUnsupportedDialog = function() {
       showUnsupportDialog();
    }

    function showUnsupportDialog() {
        var confirm = jQuery('<div>Seems this browser is not supported by million eyez. Please update to the latest version or check out the FAQ section in millioneyez.com to see a list of supported platforms.</div>');
        confirm.dialog({
            'title': 'Unsupported browser',
            'dialogClass': 'me-unsupported-dialog',
            'modal': true,
            'autoOpen': false,
            'closeOnEscape': true,
            'buttons': {
                Ok: function() {
                    jQuery(this).dialog("destroy");
                }
            }
        })
        jQuery('.me-unsupported-dialog').css('z-index',1000001);
        confirm.dialog('open');
    }
})();
