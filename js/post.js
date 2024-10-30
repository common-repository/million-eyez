(function () {

    var contentElement = document.getElementById('#content');

    var EditorProxy = {
        'tinymce': {
            getContent: function getContent() {
                return tinymce.activeEditor.getContent();
            },
            setContent: function setContent(content) {
                tinymce.activeEditor.setContent(content);
            },
            getSelection: function getSelection() {
                return tinymce.activeEditor.selection.getContent();
            },
            setSelection: function setSelection(content) {
                tinymce.activeEditor.selection.setContent(content);
            }
        },
        'html': {
            getContent: function getContent() {
                return contentElement.value;
            },
            setContent: function setContent(content) {
                contentElement.value = content;
            },
            getSelection: function getSelection() {
                return contentElement.value.substring(contentElement.selectionStart, contentElement.selectionEnd);
            },
            setSelection: function setSelection(content) {
                contentElement.value =
                    contentElement.substring(0, contentElement.selectionStart) +
                    content +
                    contentElement.substring(contentElement.selectionEnd);
            }
        }
    };

    function getEditor() {
        return EditorProxy[getUserSetting('editor')];
    }
})();
