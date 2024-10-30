(function () {
    var millioneyez_plugin_version = '3.4.16';

    tinymce.PluginManager.add('millioneyez', function (editor, url) {

        var media = wp.media, shortcode_string = 'millioneyez', options = {}, toolbar, photoToolbar, toolbarWithoutEdit;
        wp.mce = wp.mce || {};

        initEditor();

        jQuery(window).on('message', function (e) {
            if (e && e.originalEvent && e.originalEvent.origin.substr(-'//curator.millioneyez.com'.length) == '//curator.millioneyez.com') {
                var message = JSON.parse(e.originalEvent.data);

                if (message.type == 'photobox' && message.missionId) {
                    var args = {
                        tag: shortcode_string,
                        type: 'closed',
                        attrs: {
                            missionid: message.missionId
                        }
                    };

                    if (message.isUpdate) {
                        if (isWpVersionOlderThan('4.6')) {
                            var elementObj = getElementContent();
                            editor.setContent(elementObj.before + wp.shortcode.string(args) + elementObj.after);
                        } else {
                            editor.selection.setContent(wp.shortcode.string(args));
                        }
                    } else {
                        editor.insertContent(wp.shortcode.string(args));
                    }
                }

                if (message.type == 'photo' && message.missionId && message.photoId) {
                    var args = {
                        tag: shortcode_string,
                        type: 'closed',
                        attrs: {
                            missionid: message.missionId,
                            photoid: message.photoId
                        }
                    };

                    if (message.isUpdate) {
                        if (isWpVersionOlderThan('4.6')) {
                            var elementObj = getElementContent();
                            editor.setContent(elementObj.before + wp.shortcode.string(args) + elementObj.after);
                        } else {
                            editor.selection.setContent(wp.shortcode.string(args));
                        }
                    } else {
                        editor.insertContent(wp.shortcode.string(args));
                    }
                }
            }
        });

        function getElementContent() {
            var selected = jQuery(editor.selection.getNode()).parent().parent();
            var shortcode = decodeURIComponent(selected.attr('data-wpview-text'));
            var meHolders = jQuery(content_ifr.contentDocument.querySelectorAll('.wpview-wrap[data-wpview-text="'+selected.attr('data-wpview-text')+'"] .meHolder')).parent().parent().parent();
            var index = meHolders.toArray().indexOf(selected[0]);
            var before;
            var after;

            var pos = 0;
            var content = editor.getContent();
            for (var i = 0; i <= index && pos != -1; i++) {
                pos = content.indexOf(shortcode, pos);
            }

            if (pos == -1) {
                before = "";
                after = content;
            } else {
                before = content.substr(0, pos);
                after = content.substr(pos + shortcode.length);
            }

            return {
                before: before,
                after: after
            }
        }

        function isWpVersionOlderThan(versionToCompare) {
            var localVersionArray = millioneyez.wpVersion.split('.');
            var localVersion = parseFloat(localVersionArray[0] + '.' + localVersionArray[1]);
            var compareVersion = parseFloat(versionToCompare);
            return localVersion < compareVersion;
        }

        function isMillioneyezPlaceholder(elm) {
            if (elm && elm.nodeName == 'DIV') {
                var wpViewType = editor.dom.getAttrib(elm, 'data-wpview-type');
                return wpViewType == 'millioneyez';
            }
            return false;
        }

        function getPhotoboxId(node) {
            var result;
            try {
                result = node.innerText.match(/missionid="(.*?)"/)[1];
            } catch (e) {
                result = decodeURIComponent(jQuery(node.children[0]).attr("data-wpview-text") || jQuery(node).attr("data-wpview-text"));
                result = result.match(/missionid="(.*?)"/)[1];
            }
            return result;
        }

        function getPhotoId(node) {
            var result;
            try {
                result = node.innerText.match(/photoid="(.*?)"/)[1];
            } catch (e) {
                result = decodeURIComponent(jQuery(node.children[0]).attr("data-wpview-text") || jQuery(node).attr("data-wpview-text"));
                result = result.match(/photoid="(.*?)"/)[1];
            }
            return result;
        }

        function initEditor() {
            editor.addButton('millioneyez_pending', {
                tooltip: 'Review photos',
                icon: 'dashicon dashicons-camera',
                onclick: function (e) {
                    if (millioneyez.browserUnsupported) {
                        millioneyez.onShowUnsupportedDialog();
                    } else {
                        var node = editor.selection.getNode();
                        var photoboxId = getPhotoboxId(node);
                        tb_show("Photobox", "//curator.millioneyez.com/editMission.html#/tab/" + photoboxId + "/?token=" + millioneyez.token + "&TB_iframe=true&modal=true&version=" + millioneyez_plugin_version);
                        jQuery('.mce-toolbar-grp.mce-inline-toolbar-grp').css('z-index','1');
                    }
                }
            });
            editor.addButton('millioneyez_edit', {
                tooltip: 'Edit',
                icon: 'dashicon dashicons-edit',
                onclick: function (e) {
                    if (millioneyez.browserUnsupported) {
                        millioneyez.onShowUnsupportedDialog();
                    } else {
                        var node = editor.selection.getNode();
                        var photoboxId = getPhotoboxId(node);
                        tb_show("Edit", "//curator.millioneyez.com/create.html#/?articleUrl=" + encodeURIComponent(millioneyez.shortlink) + "&isUpdate=" + true + "&token=" + millioneyez.token + "&TB_iframe=true&modal=true&version=" + millioneyez_plugin_version);
                        jQuery('.mce-toolbar-grp.mce-inline-toolbar-grp').css('z-index','1');
                    }
                }
            });

            editor.addButton('millioneyez_photo_edit', {
                tooltip: 'Edit',
                icon: 'dashicon dashicons-edit',
                onclick: function (e) {
                    if (millioneyez.browserUnsupported) {
                        millioneyez.onShowUnsupportedDialog();
                    } else {
                        var node = editor.selection.getNode();
                        var photoboxId = getPhotoboxId(node);
                        var photoId = getPhotoId(node);
                        tb_show("Edit", "//curator.millioneyez.com/create.html#/?articleUrl=" + encodeURIComponent(millioneyez.shortlink) + "&isUpdate=" + true + "&photoId=" + photoId + "&token=" + millioneyez.token + "&TB_iframe=true&modal=true&version=" + millioneyez_plugin_version);
                        jQuery('.mce-toolbar-grp.mce-inline-toolbar-grp').css('z-index','1');
                    }
                }
            });

            editor.addButton('millioneyez_remove', {
                tooltip: 'Delete',
                icon: 'dashicon dashicons-no',
                onclick: function (e) {
                    if (millioneyez.browserUnsupported) {
                        millioneyez.onShowUnsupportedDialog();
                    } else {
                        var node = editor.selection.getNode();
                        if (isWpVersionOlderThan('4.6')) {
                            var view = editor.wp.getView(node);
                            editor.undoManager.transact( function() {
                                editor.wp.setViewCursor(null, view);
                                wp.mce.views.remove(editor, view);
                            });
                        } else {
                            editor.dom.remove(node);
                            editor.nodeChanged();
                            editor.undoManager.add();
                        }
                    }
                }
            });

            editor.addButton('millioneyez_upload', {
                tooltip: 'Upload your own photos',
                icon: 'dashicon dashicons-upload',
                onclick: function (e) {
                    var node = editor.selection.getNode();
                    var photoboxId = getPhotoboxId(node);
                    tb_show("Upload", "//curator.millioneyez.com/editMission.html#/tab/" + photoboxId + "/upload?token=" + millioneyez.token + "&TB_iframe=true&modal=true");
                    jQuery('.mce-toolbar-grp.mce-inline-toolbar-grp').css('z-index','1');
                }
            });


            editor.once('preinit', function () {
                var removeHandler = editor && editor.wp && editor.wp.setViewCursor ? 'millioneyez_remove' : 'wp_view_remove';
                toolbar = editor.wp._createToolbar([
                    'millioneyez_pending',
                    'millioneyez_edit',
                    'millioneyez_upload',
                    removeHandler
                ], true);
                toolbarWithoutEdit = editor.wp._createToolbar([
                    'millioneyez_pending',
                    removeHandler,
                ], true);
                photoToolbar = editor.wp._createToolbar([
                    'millioneyez_photo_edit',
                    'wp_view_remove'
                ], true);

                editor.on('wptoolbar', function (event) {
                    if (isMillioneyezPlaceholder(event.element)) {
                        if (this.dom.getAttrib(event.element,'data-wpview-text').indexOf('photoid') > -1) {
                            event.toolbar = photoToolbar;
                        } else {
                            var elm = jQuery(event.element);
                            if (elm.find('div[data-status="preinit"]').length == 1) { // editor element has not been initialized yet and as such should have no toolbar
                                event.toolbar = null;
                            } else {
                                if (elm.find('.mePhotoboxNotApprove:not(".hidden")').length > 0 || elm.find('.meNoLivePhotos:not(".hidden")').length > 0) {
                                    event.toolbar = toolbarWithoutEdit;
                                } else {
                                    event.toolbar = toolbar;
                                }
                            }
                        }
                    }
                });
            });

        }

        wp.mce.millioneyez_placeholder = {
            template: media.template('millioneyez-placeholder'),
            photoTemplate: media.template('millioneyez-photo-placeholder'),
            getContent: function () {
                options.missionId = this.shortcode.attrs.named.missionid;
                options.url = url.substr(0, url.lastIndexOf('/'));
                options.pending_number = options.pending_number || 0;
                getData(this.shortcode.attrs.named.missionid);
                if (this.shortcode.attrs.named.photoid) {
                    options.photoId = this.shortcode.attrs.named.photoid;
                    return this.photoTemplate(options);
                } else {
                    return this.template(options);
                }
            },
            View: { // before WP 4.2:
                initialize: function (options) {
                    this.shortcode = options.shortcode;
                    wp.mce.millioneyez_placeholder.shortcode_data = this.shortcode;
                },
                getHtml: function () {
                    options.missionId = this.shortcode.attrs.named.missionid;
                    options.url = url.substr(0, url.lastIndexOf('/'));
                    options.pending_number = options.pending_number || 0;
                    getData(this.shortcode.attrs.named.missionid);
                    if (this.shortcode.attrs.named.photoid) {
                        options.photoId = this.shortcode.attrs.named.photoid;
                        return this.photoTemplate(options);
                    } else {
                        return this.template(options);
                    }
                }
            }
        };
        wp.mce.views.register(shortcode_string, wp.mce.millioneyez_placeholder);

        function getData(missionid) {
            if (!missionid) {
                return false;
            }
            jQuery.ajax({
                url: 'https://api.millioneyez.com/v1.0/publisher/getPhotoboxInfo/' + missionid + '?authorization=Bearer+' + millioneyez.token + '&version=' + millioneyez_plugin_version,
                type: 'GET',
                data: {},
                success: function (data) {
                    var iframes = jQuery('iframe').toArray();
                    iframes.forEach(function(iframe) {
                        var documentContent;
                        try {
                            documentContent = iframe.contentDocument;
                        } catch (e) {
                        }
                        if (documentContent) {
                            var topBarContent = jQuery(documentContent.body.getElementsByClassName('mePendingPhotobox'));
                            var holderContent = jQuery(documentContent.body.getElementsByClassName('meHolderShowDefault'));
                            var badges = documentContent.body.getElementsByClassName('mePendingBadge');

                            if (holderContent && holderContent.attr('data-status') == 'preinit') {
                                holderContent.removeAttr('data-status');
                            }

                            options.pending_number = data.pendingPhotos;
                            if (badges.length > 0) {
                                for (var i = 0; i < badges.length; i++) {
                                    badges[i].innerText = data.pendingPhotos;
                                }
                            }
                            if (data.photoboxLive == null) {
                                topBarContent.find('.mePhotoboxNotApprove').removeClass('hidden');
                            } else {
                                var pendingSpanCount;

                                if (data.photoboxLiveNumber == 0) {
                                    topBarContent.find('.mePhotoboxNotApprove').addClass('hidden');
                                    topBarContent.find('.meNoLivePhotos').removeClass('hidden');
                                } else {
                                    topBarContent.find('.mePhotoboxLive').removeClass('hidden');
                                    topBarContent.find('.meNoLivePhotos').addClass('hidden');
                                    var featuredPhotos = jQuery(documentContent.body.getElementsByClassName('meHolder')).find('.meFeaturedPhoto[data-src]');
                                    for(var i = 0; i< featuredPhotos.length; i++) {
                                        var imgElm = jQuery(featuredPhotos[i]);
                                        var newSrc = imgElm.attr('data-src')+"&refresh=" + Date.now();
                                        imgElm.attr('src',newSrc);
                                    }
                                    jQuery(documentContent.body.getElementsByClassName('meHolder')).removeClass('meHolderShowDefault').addClass('meHolderShowFeaturedPhoto');
                                }
                            }
                        }
                    })
                }
            });
        }
    });
})();
