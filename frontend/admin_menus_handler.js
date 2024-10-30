(function () {
    var millioneyez_plugin_version = '3.4.16';
    if (millioneyez.token) {
        jQuery.ajax({
            url: 'https://api.millioneyez.com/v1.0/curator/getPendingFeedCount?authorization=Bearer+' + millioneyez.token + '&version=' + millioneyez_plugin_version,
            type: 'GET',
            success: function (data) {
                if (data.pendingFeedCount > 0) {
                    updateData(data.pendingFeedCount);
                }
            }
        });

        var socket = io('https://wapi.millioneyez.com');
        var lastUpdate;
        socket.on('currentTime', function (currentTimeFromServer) {
            if (!lastUpdate) {
                lastUpdate = currentTimeFromServer;
            }
        });
        socket.on('update', function() {
            jQuery.ajax({
                url: 'https://api.millioneyez.com/v1.0/curator/getPendingFeedCount?authorization=Bearer+' + millioneyez.token + "&lastUpdate=" + lastUpdate + "&version=" + millioneyez_plugin_version,
                type: 'GET',
                success: function (data) {
                    if (data) {
                        lastUpdate = data.lastUpdatedTime || lastUpdate;
                        if (data.pendingFeedCount != undefined) {
                            updateData(data.pendingFeedCount);
                        }
                        if (data.photoboxes) {
                            updateEditor(data.photoboxes);
                        }
                    }
                }
            });
        });
    }

    function updateData(data) {
        if (jQuery(".me-counter-badge").length > 0) {
            changeElements();
        } else {
            jQuery().ready(changeElements);
        }

        function changeElements() {
            jQuery(".me-counter-badge").removeClass("count-0").addClass("count-"+data);
            jQuery(".me-counter-badge").find("span.update-count").text(data);
        }
    }

    function updateEditor(data) {
        var iframes = jQuery('iframe').toArray();
        iframes.forEach(function(iframe) {
            var documentContent;
            try {
                documentContent = jQuery(iframe.contentDocument);
            } catch (e) {
            }
            if (documentContent) {
                for (var i = 0; i < data.length; i++) {
                    var photobox = data[i];
                    var editor = jQuery(documentContent.find('img[data-millioneyez-shortcode="' + photobox.id+'"]'));
                    if (editor) {
                        editor.parent().find('.mePendingBadge').text(photobox.pending);
                    }
                }
            }
        });
    }
})();
