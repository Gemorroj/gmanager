var Gmanager = {
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
            if ("opera" in window) {
                t.setSelectionRange(n + 1, n + 1);
            } else {
                t.setSelectionRange(n, n);
            }
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
        var i = 0;
        for (; i < f[n].length; i++) {
            f[n][i].checked = c;
        }
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

        var i = 0;
        for (; i < f[n].length; i++) {
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
        var o = document.forms.post.sql;

        if (p !== "" && o) {
            this._insAtCaret(o, decodeURIComponent(p));
        }
    },
    files: function (t) {
        var f = document.createElement("input"),
            fl = document.getElementById("fl"),
            fli, flb, el1, el2;

        f.setAttribute("name", "f[]");
        f.setAttribute("type", "file");

        if (t === 1) {
            fl.insertBefore(f, null);
            fl.appendChild(document.createElement("br"));
        } else {
            fli = fl.getElementsByTagName("input");
            flb = fl.getElementsByTagName("br");
            if (fli.length > 0) {
                el1 = fli[fli.length - 1];
                el1.parentNode.removeChild(el1);
                el2 = flb[flb.length - 1];
                el2.parentNode.removeChild(el2);
            }
        }
    },
    edit: function (t, n) {
        var tr = n.parentNode;
        var tb = tr.parentNode,
            f;

        if (typeof this.id === "undefined") {
            this.id = tb.lastChild.getAttribute("id").substring(1);
        }
        this.id++;

        if (t === 1) {
            f = tr.cloneNode(true);
            f.setAttribute("id", "i" + this.id);
            f.getElementsByTagName("input").item(0).setAttribute("value", "");
            f.getElementsByTagName("td").item(0).innerHTML = "+";
            tb.insertBefore(f, tr.nextSibling);
        } else {
            tb.removeChild(tr);
        }
    },
    formatCode: function (e, t) {
        var pos = this._getCaretPosition(t),
            arr, str,
            comp = "",
            i = 0;

        if ((this._getKey(e) === 13) && !(/opera mini|opera mobi/.test(window.navigator.userAgent.toLowerCase()))) {
            arr = t.value.substring(0, pos).split("\n");
            str = arr[arr.length - 1].split("");

            for (; i < str.length; i++) {
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
    }
};