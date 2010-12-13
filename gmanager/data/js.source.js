function number(e)
{
    var key = (e.charCode === undefined ? e.keyCode : e.charCode);
    if (e.ctrlKey || e.altKey || key < 32) {
        return true;
    }
    return (/[\d]/.test(String.fromCharCode(key)));
}


function check(f, n, c)
{
    for (var i = 0; i < f[n].length; i++) {
        f[n][i].checked = c;
    }
}


function checkForm(f, n)
{
    if (f[n] === undefined) {
        return false;
    } else if (f[n] instanceof HTMLInputElement) {
        if (!f[n].checked) {
            window.alert(document.getElementById("chF").innerHTML);
        }
        return f[n].checked;
    }

    for (var i = 0; i < f[n].length; i++)
    {
        if (f[n][i].checked) {
            return true;
        }
    }

    window.alert(document.getElementById("chF").innerHTML);
    return false;
}


function delNotify()
{
    return window.confirm(document.getElementById("delN").innerHTML);
}


function insAtCaret(o, s)
{
    var r = null;
    o.focus();

    if (document.selection !== undefined) {
        r = document.selection.createRange();
        if (r.parentElement() !== o) {
            return;
        }
        r.text = s;
        r.select();
    } else if (o.selectionStart !== undefined) {
        r = o.selectionStart;
        o.value = o.value.substr(0, r) + s + o.value.substr(o.selectionEnd, o.value.length);
        r += s.length;
        o.setSelectionRange(r, r);
    } else {
        o.value += s;
    }
    o.focus();
}


function paste(p)
{
    var o = document.forms.post.sql;
    if (p !== "" && o) {
        insAtCaret(o, decodeURIComponent(p));
    }
}


function files(t)
{
    var f = document.createElement("input");
    var fl = document.getElementById("fl");
    var fli = null;
    var flb = null;
    var el1 = null;
    var el2 = null;

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
}


function edit(t, n)
{
    var tr = n.parentNode;
    var tb = tr.parentNode;
    var f = null;

    if (this.id === undefined) {
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
}


function formatCode(e, t)
{
    var pos = getCaretPosition(t);
    var arr = {};
    var str = {};
    var comp = "";
    var i = 0;

    if (((e.keyCode === undefined ? e.charCode : e.keyCode) === 13) && !(/opera mini|opera mobi/i.test(window.navigator.userAgent.toLowerCase()))) {
        arr = t.value.substring(0, pos).split("\n");
        str = arr[arr.length - 1].split("");

        for (i = 0; i < str.length; i++) {
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
        setCaretPosition(t, pos + comp.length + 1);
        return false;
    }
    return true;
}


function getCaretPosition(t)
{
    var sel = null;
    var clone = null;
    if (t.selectionStart !== undefined) {
        return t.selectionStart;
    } else if (document.selection) {
        sel = document.selection.createRange();
        clone = sel.duplicate();
        sel.collapse(true);
        clone.moveToElementText(t);
        clone.setEndPoint("EndToEnd", sel);
        return clone.text.length;
    }

    return 0;
}


function setCaretPosition(t, n)
{
    var r = null;
    if (document.all === undefined || window.opera !== undefined) {
        if (window.opera !== undefined) {
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
}