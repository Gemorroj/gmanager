"use strict";

var Gmanager = {
    _editId: null,
    _getKey: function (e) {
        if (e.which == null && (e.charCode != null || e.keyCode != null)) {
            e.which = e.charCode != null ? e.charCode : e.keyCode;
        }
        return e.which;
    },
    _insAtCaret: function (o, s) {
        var r;
        o.focus();

        if ("selection" in document) {
            r = document.selection.createRange();
            if (r.parentElement() !== o) {
                return;
            }
            r.text = s;
            r.select();
        } else if ("selectionStart" in o) {
            r = o.selectionStart;
            o.value = o.value.substr(0, r) + s + o.value.substr(o.selectionEnd, o.value.length);
            r += s.length;
            o.setSelectionRange(r, r);
        } else {
            o.value += s;
        }
        o.focus();
    },
    _getCaretPosition: function (t) {
        var sel, clone, el;

        if ("selectionStart" in t) {
            return t.selectionStart;
        } else {
            sel = document.selection.createRange();
            el = t.createTextRange();
            clone = el.duplicate();
            el.moveToBookmark(sel.getBookmark());
            clone.setEndPoint("EndToStart", el);
            return clone.text.length;
        }
    },
    _setCaretPosition: function (t, n) {
        var r;

        if ("setSelectionRange" in t) {
            t.setSelectionRange(n, n);
        } else {
            r = t.createTextRange();
            r.collapse(true);
            r.moveStart("character", t.value.substring(0, n).replace(/\n/g, "").length + 1);
            r.moveEnd("character", 0);
            r.select();
        }
    },
    number: function (e) {
        var key = this._getKey(e);

        if (e.ctrlKey || e.altKey || key < 32) {
            return true;
        }
        return (/[\d]/.test(String.fromCharCode(key)));
    },
    check: function (f, n, c) {
        if (typeof f[n] === "undefined") {
            return false;
        } else if (f[n] instanceof HTMLInputElement) {
            f[n].checked = c;
        } else {
            for (var i = 0, l = f[n].length; i < l; i++) {
                f[n][i].checked = c;
            }
        }
        return false;
    },
    checkForm: function (f, n) {
        if (typeof f[n] === "undefined") {
            return false;
        } else if (f[n] instanceof HTMLInputElement) {
            if (f[n].checked === false) {
                window.alert(document.getElementById("chF").innerHTML);
            }
            return f[n].checked;
        }

        for (var i = 0, l = f[n].length; i < l; i++) {
            if (f[n][i].checked === true) {
                return true;
            }
        }

        window.alert(document.getElementById("chF").innerHTML);
        return false;
    },
    delNotify: function () {
        return window.confirm(document.getElementById("delN").innerHTML);
    },
    paste: function (p) {
        var o = document.getElementById("sql");

        if (p !== "" && o) {
            this._insAtCaret(o, decodeURIComponent(p));
        }
    },
    filesDel: function () {
        var fl = document.getElementById("fl");
        var flb = fl.lastChild;
        var fli = flb.previousSibling;

        if (fli.previousSibling !== null) {
            flb.parentNode.removeChild(flb);
            fli.parentNode.removeChild(fli);
        }

    },
    filesAdd: function () {
        var fl = document.getElementById("fl"),
            f = document.createElement("input");

        f.setAttribute("name", "f[]");
        f.setAttribute("type", "file");

        fl.insertBefore(f, null);
        fl.appendChild(document.createElement("br"));
    },
    _setEditId: function () {
        if (this._editId === null) {
            this._editId = document.getElementById("pedit").lastChild.getAttribute("id").substring(1);
        }
        this._editId++;
    },
    editAdd: function (n) {
        this._setEditId();

        var tr = n.parentNode.parentNode;
        var tb = tr.parentNode,
            f = tr.cloneNode(true);

        f.setAttribute("id", "i" + this._editId);
        f.cells[0].innerHTML = "+";
        f.cells[1].firstChild.setAttribute("value", "");
        tb.insertBefore(f, tr.nextSibling);
    },
    editDel: function (n) {
        this._setEditId();
        n.parentNode.parentNode.parentNode.deleteRow(n.parentNode.parentNode.rowIndex);
    },
    formatCode: function (e, t) {
        var pos = this._getCaretPosition(t),
            key = this._getKey(e),
            arr, str,
            comp = "";

        if ((key === 13 || key === 10) && !(/Opera Mini|Opera Mobi|Opera Tablet/.test(window.navigator.userAgent))) {
            arr = t.value.substring(0, pos).split("\n");
            str = arr[arr.length - 1].split("");

            for (var i = 0, l = str.length; i < l; i++) {
                if (str[i] === " ") {
                    comp += " ";
                } else {
                    break;
                }
            }

            if (t.value.slice(pos - 1, pos) === "{") {
                comp += "    ";
            }

            t.value = t.value.substring(0, pos) + "\n" + comp + t.value.substring(pos, t.value.length);
            this._setCaretPosition(t, pos + comp.length + 1);
            return false;
        }
        return true;
    },
    textareaLines: function (ta) {
        var move = false;
        var el = document.createElement("textarea");

        el.onscroll = function () {
            Gmanager.textareaLinesResize(el, ta);
        };
        el.style.height = (ta.offsetHeight - 4) + "px";
        el.style.width = "32px";
        el.style.position = "absolute";
        el.style.overflow = "hidden";
        el.style.textAlign = "right";
        el.style.resize = "none";
        el.style.left = "0px";
        el.style.paddingRight = "0.2em";
        el.style.zIndex = 0;
        el.readOnly = "readonly";

        ta.style.marginLeft = "32px";
        ta.style.zIndex = 1;
        ta.style.position = "relative";
        ta.parentNode.insertBefore(el, ta.nextSibling);
        Gmanager.textareaLinesRender(el, ta, false);
        //ta.focus();

        ta.onkeydown = function (e) {
            var key = Gmanager._getKey(e);
            if (key === 13 || key === 10) {
                Gmanager.textareaLinesRender(el, ta, true);
            } else {
                Gmanager.textareaLinesResize(el, ta);
            }
        };
        ta.onmousedown = function () {
            Gmanager.textareaLinesResize(el, ta);
            move = true;
        };
        ta.onmouseup = function () {
            Gmanager.textareaLinesResize(el, ta);
            move = false;
        };
        ta.onmousemove = function () {
            if (move === true) {
                Gmanager.textareaLinesResize(el, ta);
            }
        };
        ta.onscroll = function () {
            Gmanager.textareaLinesResize(el, ta);
        };

        return el;
    },
    textareaLinesResize: function (el, ta) {
        el.scrollTop = ta.scrollTop;
        el.style.top = (ta.offsetTop - 3) + "px";
        el.style.height = (ta.offsetHeight - 4) + "px";
    },
    textareaLinesRender: function (el, ta, newLine) {
        var str = "";
        var l = ta.value.split("\n").length + (newLine === true ? 1 : 0);
        if (l < 2) {
            str += "1\n";
        } else {
            for (var i = 1; i <= l; i++) {
                str += (i + "\n");
            }
        }

        el.value = str;

        Gmanager.textareaLinesResize(el, ta);
    }
};

window.onload = function () {
    var t = document.getElementsByTagName("textarea");
    var resize = {"el": [], "ta": []};

    for (var i = 0, l = t.length; i < l; i++) {
        var item = t[i];
        var attr = item.getAttribute('class');
        if (attr !== null && attr.indexOf('lines') !== -1) {
            resize.ta.push(item);
            resize.el.push(Gmanager.textareaLines(item));
        }
    }

    window.onresize = function () {
        for (var i = 0, l = resize.el.length; i < l; i++) {
            Gmanager.textareaLinesResize(resize.el[i], resize.ta[i]);
        }
    };
};